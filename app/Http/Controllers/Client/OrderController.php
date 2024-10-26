<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\LicensePlateRequest;
use App\Http\Requests\Client\StoreOrderRequest;
use App\Models\Bus;
use App\Models\BusProductPrice;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
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