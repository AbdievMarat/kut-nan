<?php

namespace App\Http\Controllers\Admin;

use App\Exports\OrderExport;
use App\Http\Controllers\Controller;
use App\Models\BreadRemain;
use App\Models\Bus;
use App\Models\BusProductPrice;
use App\Models\CartCount;
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
use Symfony\Component\HttpFoundation\BinaryFileResponse;

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
        $sumMarkdowns = $this->getSumMarkdowns($date);
        $sumRealizations = $this->getSumRealizations($date);
        $sumRemainders = $this->getSumRemainders($date);

        $busesData = $buses->map(function ($bus) use ($products, $sumMarkdowns, $sumRealizations, $sumRemainders) {
            $orderAmounts = [];

            /** @var Order $order */
            foreach ($bus->orders as $order) {
                /** @var OrderItem $item */
                foreach ($order->items as $item) {
                    $orderAmounts[$item->product_id] = $item->amount;
                }
            }

            return [
                'id' => $bus->id,
                'license_plate' => $bus->license_plate . ' ' . $bus->serial_number,
                'products' => $products->map(function ($product) use ($orderAmounts) {
                    return [
                        'product_id' => $product->id,
                        'order_amount' => $orderAmounts[$product->id] ?? '',
                    ];
                }),
                'total_markdown_sum' => $sumMarkdowns[$bus->id] ?? '',
                'total_realization_sum' => $sumRealizations[$bus->id] ?? '',
                'total_remainder_sum' => $sumRemainders[$bus->id] ?? ''
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
            ->whereIn('product_id', $products->pluck('id'))
            ->get()
            ->keyBy('product_id');

        // Рассчитываем количество тележек для итоговой строки
        // Учитываем остатки хлеба в пересчете на тележки
        $totalCarts = $products->map(function ($product) use ($totalOrderAmounts, $savedBreadRemainsForCarts) {
            $totalAmount = $totalOrderAmounts[$product->id] ?? 0;
            $orderMultiplier = $product->order_multiplier ?? 1;
            $piecesPerCart = $product->pieces_per_cart ?? 1;
            $multipliedAmount = $totalAmount * $orderMultiplier;

            // Рассчитываем тележки из заказов
            $calculatedCartsFromOrders = $multipliedAmount > 0 && $piecesPerCart > 0
                ? round($multipliedAmount / $piecesPerCart, 1)
                : 0;

            // Добавляем остатки хлеба в пересчете на тележки
            $breadRemain = $savedBreadRemainsForCarts->get($product->id);
            $breadRemainAmount = $breadRemain ? ($breadRemain->amount ?? 0) : 0;
            $breadRemainCarts = $breadRemainAmount > 0 && $piecesPerCart > 0
                ? round($breadRemainAmount / $piecesPerCart, 1)
                : 0;

            $totalCartsValue = $calculatedCartsFromOrders + $breadRemainCarts;
            return $totalCartsValue > 0 ? round($totalCartsValue, 1) : '';
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
            ->whereIn('product_id', $products->pluck('id'))
            ->get()
            ->keyBy('product_id');

        $savedCarts = $products->map(function ($product) use ($savedCartCounts) {
            $cartCount = $savedCartCounts->get($product->id);
            return $cartCount ? $cartCount->carts : null;
        });

        // Загружаем сохраненные остатки хлеба
        $savedBreadRemains = BreadRemain::query()
            ->whereDate('date', $date)
            ->whereIn('product_id', $products->pluck('id'))
            ->get()
            ->keyBy('product_id');

        $breadRemains = $products->map(function ($product) use ($savedBreadRemains) {
            $breadRemain = $savedBreadRemains->get($product->id);
            return $breadRemain ? $breadRemain->amount : null;
        });

        // Рассчитываем итоговые значения тележек (рассчитанное + введенное)
        // Точные значения (без округления) для использования в data-exact-value и при печати
        $totalCartsValuesExact = $products->map(function ($product, $index) use ($totalCarts, $savedCarts) {
            $calculatedCarts = $totalCarts->values()->get($index) ? (float)$totalCarts->values()->get($index) : 0;
            $savedCartsValue = $savedCarts->values()->get($index) ? (float)$savedCarts->values()->get($index) : 0;
            $totalCartsValue = $calculatedCarts + $savedCartsValue;
            return $totalCartsValue > 0 ? $totalCartsValue : '';
        });

        // Округленные значения для отображения в поле (только если пользователь заполнил поле)
        $totalCartsValues = $products->map(function ($product, $index) use ($totalCarts, $savedCarts) {
            $calculatedCarts = $totalCarts->values()->get($index) ? (float)$totalCarts->values()->get($index) : 0;
            $savedCartsValue = $savedCarts->values()->get($index) ? (float)$savedCarts->values()->get($index) : 0;
            // Показываем значение только если пользователь заполнил поле (есть savedCarts)
            if ($savedCartsValue > 0) {
                $totalCartsValue = $calculatedCarts + $savedCartsValue;
                return $totalCartsValue > 0 ? round($totalCartsValue) : '';
            }
            return '';
        });

        // Рассчитываем итоговые значения: (рассчитанное из заказов + введенное пользователем + остатки хлеба в тележках) * pieces_per_cart
        // Остатки хлеба уже включены в totalCarts в пересчете на тележки
        // Округляем до целых чисел
        $finalTotals = $products->map(function ($product, $index) use ($totalCarts, $savedCarts, $piecesPerCarts) {
            $calculatedCarts = $totalCarts->values()->get($index) ? (float)$totalCarts->values()->get($index) : 0;
            $savedCartsValue = $savedCarts->values()->get($index) ? (float)$savedCarts->values()->get($index) : 0;
            $piecesPerCart = $piecesPerCarts->values()->get($index) ?? 1;
            $totalCartsValue = $calculatedCarts + $savedCartsValue;
            // Итоговое значение = общее количество тележек * pieces_per_cart
            $totalAmount = $totalCartsValue > 0 ? round($totalCartsValue * $piecesPerCart) : 0;
            return $totalAmount > 0 ? round($totalAmount) : '';
        });

        return view('admin.orders.index', compact('date', 'busesData', 'products', 'totalCarts', 'multipliedAmounts', 'piecesPerCarts', 'savedCarts', 'finalTotals', 'totalCartsValues', 'totalCartsValuesExact', 'breadRemains'));
    }

    /**
     * @param Request $request
     * @return BinaryFileResponse
     */
    public function exportToExcel(Request $request): BinaryFileResponse
    {
        $date = $request->input('date', date('Y-m-d', strtotime('+1 day')));
        $products = $this->getProducts();
        $sumMarkdowns = $this->getSumMarkdowns($date);
        $sumRealizations = $this->getSumRealizations($date);
        $sumRemainders = $this->getSumRemainders($date);

        return Excel::download(new OrderExport($date, $products, $sumMarkdowns, $sumRealizations, $sumRemainders), 'orders_' . date('d.m.Y', strtotime($date)) . '.xlsx');
    }

    /**
     * @param Request $request
     * @return JsonResponse
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

}
