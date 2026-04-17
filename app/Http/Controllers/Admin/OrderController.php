<?php

namespace App\Http\Controllers\Admin;

use App\Exports\OrderExport;
use App\Http\Controllers\Controller;
use App\Models\BreadRemain;
use App\Models\Bus;
use App\Models\BusProductPrice;
use App\Models\CartCount;
use App\Models\Invoice;
use App\Models\InvoiceReturn;
use App\Models\InvoiceReturnShop;
use App\Models\InvoiceShop;
use App\Models\Markdown;
use App\Models\Order;
use App\Models\OrderChangeLog;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Realization;
use App\Models\RealizationShop;
use App\Models\Remainder;
use App\Models\RemainderItem;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Exception;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class OrderController extends Controller
{
    /**
     * @param Request $request
     * @return Factory|Application|View|\Illuminate\Contracts\Foundation\Application
     */
    public function index(Request $request): Factory|Application|View|\Illuminate\Contracts\Foundation\Application
    {
        $date = $request->input('date', date('Y-m-d', strtotime('+1 day')));

        $buses = Bus::query()
            ->with([
                'orders' => function ($query) use ($date) {
                    $query->whereDate('date', $date)
                        ->with(['items']);
                }
            ])
            ->where('is_active', '=', Bus::IS_ACTIVE)
            ->orderBy('sort')
            ->get();

        $products = $this->getProducts();
        $productIds = $products->pluck('id');
        $sumMarkdowns = $this->getSumMarkdowns($date);
        $sumRealizations = $this->getSumRealizations($date);
        $sumRemainders = $this->getSumRemainders($date);
        $sumInvoices = $this->getSumInvoices($date);
        $sumInvoiceReturns = $this->getSumInvoiceReturns($date);

        $prevDate = date('Y-m-d', strtotime($date . ' -1 day'));
        $prevSumRemainders = $this->getSumRemainders($prevDate);
        $prevSumRealizations = $this->getSumRealizations($prevDate);

        $busesData = $buses->map(function ($bus) use ($products, $sumMarkdowns, $sumRealizations, $sumRemainders, $sumInvoices, $sumInvoiceReturns, $prevSumRemainders, $prevSumRealizations) {
            $orderAmounts = [];
            $orderMarked = [];
            $orderSum = 0;

            /** @var Order $order */
            foreach ($bus->orders as $order) {
                /** @var OrderItem $item */
                foreach ($order->items as $item) {
                    $orderAmounts[$item->product_id] = $item->amount;
                    $orderMarked[$item->product_id] = (bool) $item->is_marked;
                    $orderSum += ($item->amount ?? 0) * ($item->price ?? 0);
                }
            }

            $cashbox = $orderSum
                - ($sumMarkdowns[$bus->id] ?? 0)
                - ($sumRealizations[$bus->id] ?? 0)
                - ($sumInvoices[$bus->id] ?? 0)
                + ($sumInvoiceReturns[$bus->id] ?? 0)
                - ($sumRemainders[$bus->id] ?? 0)
                + ($prevSumRemainders[$bus->id] ?? 0)
                + ($prevSumRealizations[$bus->id] ?? 0);

            return [
                'id' => $bus->id,
                'license_plate' => $bus->license_plate . ' ' . $bus->serial_number,
                'products' => $products->map(function ($product) use ($orderAmounts, $orderMarked) {
                    return [
                        'product_id' => $product->id,
                        'order_amount' => $orderAmounts[$product->id] ?? '',
                        'is_marked' => $orderMarked[$product->id] ?? false,
                    ];
                }),
                'total_markdown_sum' => $sumMarkdowns[$bus->id] ?? '',
                'total_realization_sum' => $sumRealizations[$bus->id] ?? '',
                'total_invoice_sum' => $sumInvoices[$bus->id] ?? '',
                'total_invoice_return_sum' => $sumInvoiceReturns[$bus->id] ?? '',
                'total_remainder_sum' => $sumRemainders[$bus->id] ?? '',
                'order_sum' => $orderSum,
                'prev_remainder_sum' => $prevSumRemainders[$bus->id] ?? 0,
                'prev_realization_sum' => $prevSumRealizations[$bus->id] ?? 0,
                'total_cashbox' => $cashbox ?: '',
            ];
        });

        // Рассчитываем итоговые суммы order_amount по всем автобусам для каждого продукта
        $totalOrderAmounts = [];
        foreach ($busesData as $bus) {
            foreach ($bus['products'] as $productData) {
                $productId = $productData['product_id'];
                $orderAmount = $productData['order_amount'] ?: 0;
                $totalOrderAmounts[$productId] = ($totalOrderAmounts[$productId] ?? 0) + $orderAmount;
            }
        }

        // Загружаем сохраненные остатки хлеба для расчета тележек
        $savedBreadRemainsForCarts = BreadRemain::query()
            ->whereDate('date', $date)
            ->whereIn('product_id', $productIds)
            ->get()
            ->keyBy('product_id');

        // Рассчитываем количество тележек для итоговой строки
        // вычитаем остатки хлеба в штуках перед делением, чтобы избежать двойного округления
        $totalCarts = $products->map(function ($product) use ($totalOrderAmounts, $savedBreadRemainsForCarts) {
            $totalAmount = $totalOrderAmounts[$product->id] ?? 0;
            $orderMultiplier = $product->order_multiplier ?? 1;
            $piecesPerCart = $product->pieces_per_cart ?? 1;
            $multipliedAmount = $totalAmount * $orderMultiplier;

            $breadRemain = $savedBreadRemainsForCarts->get($product->id);
            $breadRemainAmount = $breadRemain ? ($breadRemain->amount ?? 0) : 0;

            $netPieces = $multipliedAmount - $breadRemainAmount;
            $calculatedCarts = $netPieces > 0 && $piecesPerCart > 0
                ? round($netPieces / $piecesPerCart, 1)
                : 0;

            return $calculatedCarts > 0 ? $calculatedCarts : '';
        });

        // Рассчитываем промежуточные значения для отображения
        $multipliedAmounts = $products->map(function ($product) use ($totalOrderAmounts) {
            $totalAmount = $totalOrderAmounts[$product->id] ?? 0;
            $orderMultiplier = $product->order_multiplier ?? 1;
            $multipliedAmount = $totalAmount * $orderMultiplier;
            return $multipliedAmount > 0 ? $multipliedAmount : '';
        });

        $piecesPerCarts = $products->map(function ($product) {
            return $product->pieces_per_cart ?? 1;
        });

        // Загружаем сохраненные значения тележек
        $savedCartCounts = CartCount::query()
            ->whereDate('date', $date)
            ->whereIn('product_id', $productIds)
            ->get()
            ->keyBy('product_id');

        $savedCarts = $products->map(function ($product) use ($savedCartCounts) {
            $cartCount = $savedCartCounts->get($product->id);
            return $cartCount ? $cartCount->carts : null;
        });

        $breadRemains = $products->map(function ($product) use ($savedBreadRemainsForCarts) {
            $breadRemain = $savedBreadRemainsForCarts->get($product->id);
            return $breadRemain ? $breadRemain->amount : null;
        });

        // Итого тележек: берём значение из базы напрямую (теперь хранится итого, а не запас)
        $totalCartsValues = $products->map(function ($product, $index) use ($savedCarts) {
            $savedCartsValue = $savedCarts->values()->get($index);
            return ($savedCartsValue !== null && $savedCartsValue !== '') ? (float)$savedCartsValue : '';
        });

        // Запас тележек = Итого тележек (из базы) - Тележек из заказов
        $reserveCarts = $products->map(function ($product, $index) use ($totalCarts, $savedCarts) {
            $savedCartsValue = $savedCarts->values()->get($index);
            if ($savedCartsValue !== null && $savedCartsValue !== '') {
                $calculatedCarts = $totalCarts->values()->get($index) ? (float)$totalCarts->values()->get($index) : 0;
                $reserve = round((float)$savedCartsValue - $calculatedCarts, 2);
                return $reserve != 0 ? $reserve : 0;
            }
            return '';
        });

        // Итого шт.: если есть итого тележек из базы → итогоТележек * штук на тележку, иначе из заказов
        $finalTotals = $products->map(function ($product, $index) use ($multipliedAmounts, $savedCarts, $piecesPerCarts, $savedBreadRemainsForCarts) {
            $savedCartsValue = $savedCarts->values()->get($index);
            $piecesPerCart = $piecesPerCarts->values()->get($index) ?? 1;

            if ($savedCartsValue !== null && $savedCartsValue !== '') {
                $totalPieces = round((float)$savedCartsValue * $piecesPerCart);
                return $totalPieces > 0 ? $totalPieces : '';
            }

            $multipliedAmount = (float)($multipliedAmounts->values()->get($index) ?? 0);
            $breadRemain = $savedBreadRemainsForCarts->get($product->id);
            $breadRemainAmount = $breadRemain ? ($breadRemain->amount ?? 0) : 0;
            $totalAmount = $multipliedAmount - $breadRemainAmount;
            return $totalAmount > 0 ? round($totalAmount) : '';
        });

        return view('admin.orders.index', compact('date', 'busesData', 'products', 'totalCarts', 'multipliedAmounts', 'piecesPerCarts', 'savedCarts', 'finalTotals', 'totalCartsValues', 'reserveCarts', 'breadRemains'));
    }

    /**
     * @param Request $request
     * @return BinaryFileResponse
     * @throws Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function exportToExcel(Request $request): BinaryFileResponse
    {
        $date = $request->input('date', date('Y-m-d', strtotime('+1 day')));
        $products = $this->getProducts();
        $sumMarkdowns = $this->getSumMarkdowns($date);
        $sumRealizations = $this->getSumRealizations($date);
        $sumRemainders = $this->getSumRemainders($date);

        $sumInvoices = $this->getSumInvoices($date);
        $sumInvoiceReturns = $this->getSumInvoiceReturns($date);

        return Excel::download(new OrderExport($date, $products, $sumMarkdowns, $sumRealizations, $sumRemainders, $sumInvoices, $sumInvoiceReturns), 'orders_' . date('d.m.Y', strtotime($date)) . '.xlsx');
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws Throwable
     */
    public function getMarkdownItems(Request $request): JsonResponse
    {
        $date = $request->input('date');
        $busId = $request->input('bus_id');

        $markdown = Markdown::query()
            ->with('items.product')
            ->whereDate('date', $date)
            ->where('bus_id', '=', $busId)
            ->first();

        return response()->json([
            'markdownDetails' => view('admin.orders.markdown_details', [
                'markdown' => $markdown
            ])->render()
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws Throwable
     */
    public function getRealizationShops(Request $request): JsonResponse
    {
        $date = $request->input('date');
        $busId = $request->input('bus_id');

        $realization = Realization::query()
            ->with('shops')
            ->whereDate('date', $date)
            ->where('bus_id', '=', $busId)
            ->first();

        return response()->json([
            'realizationDetails' => view('admin.orders.realization_details', [
                'realization' => $realization
            ])->render()
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws Throwable
     */
    public function getRemainderItems(Request $request): JsonResponse
    {
        $date = $request->input('date');
        $busId = $request->input('bus_id');

        $remainder = Remainder::query()
            ->whereHas('items', function ($query) {
                $query->whereNotNull('amount');
            })
            ->with(['items' => function ($query) {
                $query->with('product');
            }])
            ->whereDate('date', $date)
            ->where('bus_id', '=', $busId)
            ->first();

        return response()->json([
            'remainderDetails' => view('admin.orders.remainder_details', [
                'remainder' => $remainder
            ])->render()
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws Throwable
     */
    public function getInvoiceShops(Request $request): JsonResponse
    {
        $date = $request->input('date');
        $busId = $request->input('bus_id');

        $invoice = Invoice::query()
            ->with('shops')
            ->whereDate('date', $date)
            ->where('bus_id', '=', $busId)
            ->first();

        return response()->json([
            'invoiceDetails' => view('admin.orders.invoice_details', [
                'invoice' => $invoice
            ])->render()
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws Throwable
     */
    public function getInvoiceReturnShops(Request $request): JsonResponse
    {
        $date = $request->input('date');
        $busId = $request->input('bus_id');

        $invoiceReturn = InvoiceReturn::query()
            ->with('shops')
            ->whereDate('date', $date)
            ->where('bus_id', '=', $busId)
            ->first();

        return response()->json([
            'invoiceReturnDetails' => view('admin.orders.invoice_return_details', [
                'invoiceReturn' => $invoiceReturn
            ])->render()
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function toggleOrderItemMark(Request $request): JsonResponse
    {
        $data = $request->validate([
            'date' => 'required|date',
            'bus_id' => 'required|integer|exists:buses,id',
            'product_id' => 'required|integer|exists:products,id',
        ]);

        $order = Order::query()
            ->where('bus_id', $data['bus_id'])
            ->whereDate('date', $data['date'])
            ->first();

        if (! $order) {
            return response()->json(['success' => false, 'message' => 'Заказ не найден'], 404);
        }

        $orderItem = OrderItem::query()
            ->where('order_id', $order->id)
            ->where('product_id', $data['product_id'])
            ->first();

        if (! $orderItem) {
            return response()->json(['success' => false, 'message' => 'Позиция заказа не найдена'], 404);
        }

        $orderItem->is_marked = ! $orderItem->is_marked;
        $orderItem->save();

        return response()->json(['success' => true, 'is_marked' => $orderItem->is_marked]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws Throwable
     */
    public function updateOrderItemsBatch(Request $request): JsonResponse
    {
        $data = $request->validate([
            'date' => 'required|date',
            'order_items' => 'present|array',
            'order_items.*.bus_id' => 'required|integer|exists:buses,id',
            'order_items.*.product_id' => 'required|integer|exists:products,id',
            'order_items.*.amount' => 'nullable',
        ]);

        $date = $data['date'];

        DB::transaction(function () use ($data, $date) {
            foreach ($data['order_items'] as $row) {
                $amount = $this->normalizeNullableInt($row['amount']);
                $this->persistOrderAmount($date, (int) $row['bus_id'], (int) $row['product_id'], $amount);
            }
        });

        return response()->json(['success' => true]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws Throwable
     */
    public function updateBreadRemainsBatch(Request $request): JsonResponse
    {
        $data = $request->validate([
            'date' => 'required|date',
            'bread_remains' => 'present|array',
            'bread_remains.*.product_id' => 'required|integer|exists:products,id',
            'bread_remains.*.amount' => 'nullable',
        ]);

        $date = $data['date'];

        DB::transaction(function () use ($data, $date) {
            foreach ($data['bread_remains'] as $row) {
                $this->persistBreadRemainAmount($date, (int) $row['product_id'], $this->normalizeNullableInt($row['amount']));
            }
        });

        return response()->json(['success' => true]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws Throwable
     */
    public function updateCartCountsBatch(Request $request): JsonResponse
    {
        $data = $request->validate([
            'date' => 'required|date',
            'cart_counts' => 'present|array',
            'cart_counts.*.product_id' => 'required|integer|exists:products,id',
            'cart_counts.*.carts' => 'nullable',
        ]);

        $date = $data['date'];

        DB::transaction(function () use ($data, $date) {
            foreach ($data['cart_counts'] as $row) {
                $this->persistCartCountValue($date, (int) $row['product_id'], $this->normalizeNullableFloat($row['carts']));
            }
        });

        return response()->json(['success' => true]);
    }

    /**
     * @param mixed $value
     * @return int|null
     */
    private function normalizeNullableInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (! is_numeric($value)) {
            return null;
        }

        return (int) $value;
    }

    /**
     * @param mixed $value
     * @return float|null
     */
    private function normalizeNullableFloat(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (! is_numeric($value)) {
            return null;
        }

        return (float) $value;
    }

    /**
     * @param string $date
     * @param int $busId
     * @param int $productId
     * @param int|null $newAmount
     * @return void
     */
    private function persistOrderAmount(string $date, int $busId, int $productId, ?int $newAmount): void
    {
        $order = Order::query()
            ->where('bus_id', $busId)
            ->whereDate('date', $date)
            ->first();

        if (! $order) {
            $order = new Order();
            $order->bus_id = $busId;
            $order->date = $date;
            $order->save();
        }

        $busProductPrice = BusProductPrice::query()
            ->where('bus_id', $busId)
            ->where('product_id', $productId)
            ->first();

        $price = $busProductPrice ? $busProductPrice->price : 0;

        $orderItem = OrderItem::query()
            ->where('order_id', $order->id)
            ->where('product_id', $productId)
            ->first();

        $oldAmount = $orderItem ? $orderItem->amount : null;

        if (! $orderItem) {
            $orderItem = new OrderItem();
            $orderItem->order_id = $order->id;
            $orderItem->product_id = $productId;
            $orderItem->price = $price;
        }

        $orderItem->amount = $newAmount;
        $orderItem->save();

        if ($oldAmount !== $newAmount) {
            OrderChangeLog::create([
                'order_id' => $order->id,
                'product_id' => $productId,
                'bus_id' => $busId,
                'date' => $date,
                'old_amount' => $oldAmount,
                'new_amount' => $newAmount,
            ]);
        }
    }

    /**
     * @param string $date
     * @param int $productId
     * @param int|null $amount
     * @return void
     */
    private function persistBreadRemainAmount(string $date, int $productId, ?int $amount): void
    {
        $breadRemain = BreadRemain::query()
            ->whereDate('date', $date)
            ->where('product_id', $productId)
            ->first();

        if (! $breadRemain) {
            $breadRemain = new BreadRemain();
            $breadRemain->date = $date;
            $breadRemain->product_id = $productId;
        }

        $breadRemain->amount = $amount;
        $breadRemain->save();
    }

    /**
     * @param string $date
     * @param int $productId
     * @param float|null $carts
     * @return void
     */
    private function persistCartCountValue(string $date, int $productId, ?float $carts): void
    {
        $cartCount = CartCount::query()
            ->whereDate('date', $date)
            ->where('product_id', $productId)
            ->first();

        if (! $cartCount) {
            $cartCount = new CartCount();
            $cartCount->date = $date;
            $cartCount->product_id = $productId;
        }

        $cartCount->carts = $carts;
        $cartCount->save();
    }

    /**
     * @return Collection
     */
    private function getProducts(): Collection
    {
        return Product::query()
            ->where('is_active', '=', Product::IS_ACTIVE)
            ->where('is_in_report', '=', Product::IS_IN_REPORT)
            ->orderBy('sort')
            ->get();
    }

    /**
     * @param $date
     * @return Collection
     */
    private function getSumMarkdowns($date): Collection
    {
        return Markdown::query()
            ->select('markdowns.bus_id')
            ->selectRaw('SUM(markdown_items.amount) as total')
            ->leftJoin('markdown_items', 'markdown_items.markdown_id', '=', 'markdowns.id')
            ->whereDate('markdowns.date', $date)
            ->whereNotNull('markdown_items.amount')
            ->groupBy('markdowns.bus_id')
            ->pluck('total', 'bus_id');
    }

    /**
     * @param $date
     * @return Collection
     */
    private function getSumRealizations($date): Collection
    {
        return RealizationShop::query()
            ->select('realizations.bus_id')
            ->selectRaw('SUM(realization_shops.amount) as total')
            ->leftJoin('realizations', 'realization_shops.realization_id', '=', 'realizations.id')
            ->whereDate('realizations.date', $date)
            ->whereNotNull('realization_shops.amount')
            ->groupBy('realizations.bus_id')
            ->pluck('total', 'bus_id');
    }

    /**
     * @param $date
     * @return Collection
     */
    private function getSumRemainders($date): Collection
    {
        return RemainderItem::query()
            ->select('remainders.bus_id')
            ->selectRaw('SUM(remainder_items.amount * remainder_items.price) as total')
            ->leftJoin('remainders', 'remainder_items.remainder_id', '=', 'remainders.id')
            ->whereDate('remainders.date', $date)
            ->whereNotNull('remainder_items.amount')
            ->groupBy('remainders.bus_id')
            ->pluck('total', 'bus_id');
    }

    /**
     * @param $date
     * @return Collection
     */
    private function getSumInvoices($date): Collection
    {
        return InvoiceShop::query()
            ->select('invoices.bus_id')
            ->selectRaw('SUM(invoice_shops.amount) as total')
            ->leftJoin('invoices', 'invoice_shops.invoice_id', '=', 'invoices.id')
            ->whereDate('invoices.date', $date)
            ->whereNotNull('invoice_shops.amount')
            ->groupBy('invoices.bus_id')
            ->pluck('total', 'bus_id');
    }

    /**
     * @param $date
     * @return Collection
     */
    private function getSumInvoiceReturns($date): Collection
    {
        return InvoiceReturnShop::query()
            ->select('invoice_returns.bus_id')
            ->selectRaw('SUM(invoice_return_shops.amount) as total')
            ->leftJoin('invoice_returns', 'invoice_return_shops.invoice_return_id', '=', 'invoice_returns.id')
            ->whereDate('invoice_returns.date', $date)
            ->whereNotNull('invoice_return_shops.amount')
            ->groupBy('invoice_returns.bus_id')
            ->pluck('total', 'bus_id');
    }

}
