<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\LicensePlateRequest;
use App\Http\Requests\Client\StoreOrderRequest;
use App\Models\Bus;
use App\Models\BusProductPrice;
use App\Models\InvoiceReturnShop;
use App\Models\InvoiceShop;
use App\Models\Markdown;
use App\Models\MarkdownItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\RealizationShop;
use App\Models\RemainderItem;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * @return Factory|Application|View|\Illuminate\Contracts\Foundation\Application
     */
    public function showEnterLicensePlateForm(): Factory|Application|View|\Illuminate\Contracts\Foundation\Application
    {
        return view('client.orders.enter_license_plate');
    }

    /**
     * @param LicensePlateRequest $request
     * @return RedirectResponse
     */
    public function processLicensePlate(LicensePlateRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $licensePlate = $data['license_plate'];

        $request->session()->put('license_plate', $licensePlate);

        if ($data['type_operation'] == Order::TYPE_OPERATION_REALIZATION) {
            return redirect()->route('realizations.create');
        } else if ($data['type_operation'] == Order::TYPE_OPERATION_REMAINDER) {
            return redirect()->route('remainders.create');
        } else if ($data['type_operation'] == Order::TYPE_OPERATION_MARKDOWN) {
            return redirect()->route('markdowns.create');
        } else if ($data['type_operation'] == Order::TYPE_OPERATION_INVOICE) {
            return redirect()->route('invoices.create');
        } else if ($data['type_operation'] == Order::TYPE_OPERATION_INVOICE_RETURN) {
            return redirect()->route('invoice-returns.create');
        } else {
            return redirect()->route('orders.create');
        }
    }

    /**
     * @param Request $request
     * @return View|Application|Factory|RedirectResponse|\Illuminate\Contracts\Foundation\Application
     */
    public function create(Request $request): View|Application|Factory|RedirectResponse|\Illuminate\Contracts\Foundation\Application
    {
        $licensePlate = $request->session()->get('license_plate');

        if (!$licensePlate) {
            return redirect()->route('orders.enter_license_plate')->with('error', ['text' => 'Введите номер машины!']);
        }

        $busId = Bus::query()
            ->where('license_plate', '=', $licensePlate)
            ->value('id');

        $date = date('Y-m-d', strtotime('+1 day'));

        $order = Order::query()
            ->where('bus_id', '=', $busId)
            ->whereDate('date', $date)
            ->first();

        if (!$order) {
            $order = new Order();
            $order->bus_id = $busId;
            $order->date = $date;
            $order->save();
        }

        $products = BusProductPrice::query()
            ->select([
                'bus_product_prices.product_id as id',
                'products.name as name',
                'bus_product_prices.price'
            ])
            ->from('bus_product_prices')
            ->where('bus_product_prices.bus_id', '=', $busId)
            ->where('products.is_active', '=', Product::IS_ACTIVE)
            ->where('products.is_in_report', '=', Product::IS_IN_REPORT)
            ->join('products', 'bus_product_prices.product_id', '=', 'products.id')
            ->orderBy('products.sort')
            ->with('product')
            ->get();

        $itemAmounts = $order->items->keyBy('product_id');

        return view('client.orders.create', compact('licensePlate', 'order', 'products', 'itemAmounts'));
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getCashboxBreakdown(Request $request): JsonResponse
    {
        $licensePlate = (int) $request->input('license_plate');

        $bus = Bus::query()
            ->where('license_plate', '=', $licensePlate)
            ->where('is_active', '=', Bus::IS_ACTIVE)
            ->first();

        if (!$bus) {
            return response()->json(['error' => 'Автобус не найден'], 404);
        }

        $date = date('Y-m-d', strtotime('+1 day'));
        $prevDate = date('Y-m-d');

        $order = Order::query()
            ->where('bus_id', $bus->id)
            ->whereDate('date', $date)
            ->with('items')
            ->first();

        $orderSum = 0;
        if ($order) {
            foreach ($order->items as $item) {
                $orderSum += ($item->amount ?? 0) * ($item->price ?? 0);
            }
        }

        $markdownSum = (int) MarkdownItem::query()
            ->selectRaw('COALESCE(SUM(markdown_items.amount), 0) as total')
            ->join('markdowns', 'markdown_items.markdown_id', '=', 'markdowns.id')
            ->whereDate('markdowns.date', $date)
            ->where('markdowns.bus_id', $bus->id)
            ->whereNotNull('markdown_items.amount')
            ->value('total');

        $realizationSum = (int) RealizationShop::query()
            ->selectRaw('COALESCE(SUM(realization_shops.amount), 0) as total')
            ->join('realizations', 'realization_shops.realization_id', '=', 'realizations.id')
            ->whereDate('realizations.date', $date)
            ->where('realizations.bus_id', $bus->id)
            ->whereNotNull('realization_shops.amount')
            ->value('total');

        $invoiceSum = (int) InvoiceShop::query()
            ->selectRaw('COALESCE(SUM(invoice_shops.amount), 0) as total')
            ->join('invoices', 'invoice_shops.invoice_id', '=', 'invoices.id')
            ->whereDate('invoices.date', $date)
            ->where('invoices.bus_id', $bus->id)
            ->whereNotNull('invoice_shops.amount')
            ->value('total');

        $invoiceReturnSum = (int) InvoiceReturnShop::query()
            ->selectRaw('COALESCE(SUM(invoice_return_shops.amount), 0) as total')
            ->join('invoice_returns', 'invoice_return_shops.invoice_return_id', '=', 'invoice_returns.id')
            ->whereDate('invoice_returns.date', $date)
            ->where('invoice_returns.bus_id', $bus->id)
            ->whereNotNull('invoice_return_shops.amount')
            ->value('total');

        $remainderSum = (int) RemainderItem::query()
            ->selectRaw('COALESCE(SUM(remainder_items.amount * remainder_items.price), 0) as total')
            ->join('remainders', 'remainder_items.remainder_id', '=', 'remainders.id')
            ->whereDate('remainders.date', $date)
            ->where('remainders.bus_id', $bus->id)
            ->whereNotNull('remainder_items.amount')
            ->value('total');

        $prevRemainderSum = (int) RemainderItem::query()
            ->selectRaw('COALESCE(SUM(remainder_items.amount * remainder_items.price), 0) as total')
            ->join('remainders', 'remainder_items.remainder_id', '=', 'remainders.id')
            ->whereDate('remainders.date', $prevDate)
            ->where('remainders.bus_id', $bus->id)
            ->whereNotNull('remainder_items.amount')
            ->value('total');

        $prevRealizationSum = (int) RealizationShop::query()
            ->selectRaw('COALESCE(SUM(realization_shops.amount), 0) as total')
            ->join('realizations', 'realization_shops.realization_id', '=', 'realizations.id')
            ->whereDate('realizations.date', $prevDate)
            ->where('realizations.bus_id', $bus->id)
            ->whereNotNull('realization_shops.amount')
            ->value('total');

        $cashbox = $orderSum
            - $markdownSum
            - $realizationSum
            - $invoiceSum
            + $invoiceReturnSum
            - $remainderSum
            + $prevRemainderSum
            + $prevRealizationSum;

        return response()->json([
            'bus' => $bus->license_plate . ' ' . $bus->serial_number,
            'order_sum' => $orderSum,
            'markdown' => $markdownSum,
            'realization' => $realizationSum,
            'invoice' => $invoiceSum,
            'invoice_return' => $invoiceReturnSum,
            'remainder' => $remainderSum,
            'prev_remainder' => $prevRemainderSum,
            'prev_realization' => $prevRealizationSum,
            'total' => $cashbox,
        ]);
    }

    /**
     * @param StoreOrderRequest $request
     * @return RedirectResponse
     */
    public function store(StoreOrderRequest $request): RedirectResponse
    {
        $validatedData = $request->validated();

        $orderId = $validatedData['id'];

        foreach ($validatedData['item_amounts'] as $productId => $amount) {
            if ($amount !== null) {
                $price = $validatedData['item_price'][$productId] ?? 0;

                OrderItem::query()->updateOrCreate(
                    ['order_id' => $orderId, 'product_id' => $productId],
                    ['amount' => $amount, 'price' => $price]
                );
            }
        }

        $request->session()->forget('license_plate');

        return redirect()
            ->route('orders.enter_license_plate')
            ->with('success', ['text' => 'Данные успешно сохранены!']);
    }
}