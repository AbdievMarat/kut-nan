<?php

namespace App\Http\Controllers\Admin;

use App\Exports\OrderExport;
use App\Http\Controllers\Controller;
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

        // Рассчитываем количество тележек для итоговой строки
        $totalCarts = $products->map(function ($product) use ($totalOrderAmounts) {
            $totalAmount = $totalOrderAmounts[$product->id] ?? 0;
            $orderMultiplier = $product->order_multiplier ?? 1;
            $piecesPerCart = $product->pieces_per_cart ?? 1;
            $multipliedAmount = $totalAmount * $orderMultiplier;
            return $multipliedAmount > 0 && $piecesPerCart > 0
                ? round($multipliedAmount / $piecesPerCart, 1)
                : '';
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

        // Рассчитываем итоговые значения тележек (рассчитанное + введенное)
        $totalCartsValues = $products->map(function ($product, $index) use ($totalCarts, $savedCarts) {
            $calculatedCarts = $totalCarts->values()->get($index) ? (float)$totalCarts->values()->get($index) : 0;
            $savedCartsValue = $savedCarts->values()->get($index) ? (float)$savedCarts->values()->get($index) : 0;
            $totalCartsValue = $calculatedCarts + $savedCartsValue;
            return $totalCartsValue > 0 ? round($totalCartsValue) : '';
        });

        // Рассчитываем итоговые значения: (рассчитанное из заказов + введенное пользователем) * pieces_per_cart
        // Округляем до целых чисел
        $finalTotals = $products->map(function ($product, $index) use ($totalCarts, $savedCarts, $piecesPerCarts) {
            $calculatedCarts = $totalCarts->values()->get($index) ? (float)$totalCarts->values()->get($index) : 0;
            $savedCartsValue = $savedCarts->values()->get($index) ? (float)$savedCarts->values()->get($index) : 0;
            $piecesPerCart = $piecesPerCarts->values()->get($index) ?? 1;
            $totalCartsValue = $calculatedCarts + $savedCartsValue;
            return $totalCartsValue > 0 ? round($totalCartsValue * $piecesPerCart) : '';
        });

        return view('admin.orders.index', compact('date', 'busesData', 'products', 'totalCarts', 'multipliedAmounts', 'piecesPerCarts', 'savedCarts', 'finalTotals', 'totalCartsValues'));
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

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function updateOrderAmount(Request $request): JsonResponse
    {
        $date = $request->input('date');
        $busId = $request->input('bus_id');
        $productId = $request->input('product_id');
        $amount = $request->input('amount');

        // Найти или создать Order
        $order = Order::query()
            ->where('bus_id', $busId)
            ->whereDate('date', $date)
            ->first();

        if (!$order) {
            $order = new Order();
            $order->bus_id = $busId;
            $order->date = $date;
            $order->save();
        }

        // Получить цену продукта для автобуса
        $busProductPrice = BusProductPrice::query()
            ->where('bus_id', $busId)
            ->where('product_id', $productId)
            ->first();

        $price = $busProductPrice ? $busProductPrice->price : 0;

        // Найти или создать OrderItem
        $orderItem = OrderItem::query()
            ->where('order_id', $order->id)
            ->where('product_id', $productId)
            ->first();

        $oldAmount = null;
        if ($orderItem) {
            $oldAmount = $orderItem->amount;
        }

        if (!$orderItem) {
            $orderItem = new OrderItem();
            $orderItem->order_id = $order->id;
            $orderItem->product_id = $productId;
            $orderItem->price = $price;
        }

        $newAmount = $amount ? (int)$amount : null;
        $orderItem->amount = $newAmount;
        $orderItem->save();

        // Записать изменение в лог, если значение изменилось
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

        // Пересчитать totalCarts
        $products = $this->getProducts();
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

        $totalOrderAmounts = [];
        foreach ($buses as $bus) {
            foreach ($bus->orders as $busOrder) {
                foreach ($busOrder->items as $item) {
                    $productIdKey = $item->product_id;
                    $orderAmount = $item->amount ?: 0;
                    $totalOrderAmounts[$productIdKey] = ($totalOrderAmounts[$productIdKey] ?? 0) + $orderAmount;
                }
            }
        }

        $totalCarts = $products->map(function ($product) use ($totalOrderAmounts) {
            $totalAmount = $totalOrderAmounts[$product->id] ?? 0;
            $orderMultiplier = $product->order_multiplier ?? 1;
            $piecesPerCart = $product->pieces_per_cart ?? 1;
            $multipliedAmount = $totalAmount * $orderMultiplier;
            return $multipliedAmount > 0 && $piecesPerCart > 0
                ? round($multipliedAmount / $piecesPerCart, 2)
                : '';
        });

        $multipliedAmounts = $products->map(function ($product) use ($totalOrderAmounts) {
            $totalAmount = $totalOrderAmounts[$product->id] ?? 0;
            $orderMultiplier = $product->order_multiplier ?? 1;
            $multipliedAmount = $totalAmount * $orderMultiplier;
            return $multipliedAmount > 0 ? $multipliedAmount : '';
        });

        $piecesPerCarts = $products->map(function ($product) {
            return $product->pieces_per_cart ?? 1;
        });

        // Загружаем сохраненные значения тележек для расчета итого
        $savedCartCounts = CartCount::query()
            ->whereDate('date', $date)
            ->whereIn('product_id', $products->pluck('id'))
            ->get()
            ->keyBy('product_id');

        $savedCarts = $products->map(function ($product) use ($savedCartCounts) {
            $cartCount = $savedCartCounts->get($product->id);
            return $cartCount ? $cartCount->carts : null;
        });

        // Рассчитываем итоговые значения тележек (рассчитанное + введенное)
        $totalCartsValues = $products->map(function ($product, $index) use ($totalCarts, $savedCarts) {
            $calculatedCarts = $totalCarts->values()->get($index) ? (float)$totalCarts->values()->get($index) : 0;
            $savedCartsValue = $savedCarts->values()->get($index) ? (float)$savedCarts->values()->get($index) : 0;
            $totalCartsValue = $calculatedCarts + $savedCartsValue;
            return $totalCartsValue > 0 ? round($totalCartsValue) : '';
        });

        // Рассчитываем итоговые значения: (рассчитанное из заказов + введенное пользователем) * pieces_per_cart
        // Округляем до целых чисел
        $finalTotals = $products->map(function ($product, $index) use ($totalCarts, $savedCarts, $piecesPerCarts) {
            $calculatedCarts = $totalCarts->values()->get($index) ? (float)$totalCarts->values()->get($index) : 0;
            $savedCartsValue = $savedCarts->values()->get($index) ? (float)$savedCarts->values()->get($index) : 0;
            $piecesPerCart = $piecesPerCarts->values()->get($index) ?? 1;
            $totalCartsValue = $calculatedCarts + $savedCartsValue;
            return $totalCartsValue > 0 ? round($totalCartsValue * $piecesPerCart) : '';
        });

        return response()->json([
            'success' => true,
            'totalCarts' => $totalCarts->values()->toArray(),
            'multipliedAmounts' => $multipliedAmounts->values()->toArray(),
            'piecesPerCarts' => $piecesPerCarts->values()->toArray(),
            'finalTotals' => $finalTotals->values()->toArray(),
            'totalCartsValues' => $totalCartsValues->values()->toArray()
        ]);
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
     * @param Request $request
     * @return JsonResponse
     */
    public function updateCartCount(Request $request): JsonResponse
    {
        $date = $request->input('date');
        $productId = $request->input('product_id');
        $carts = $request->input('carts');

        $product = Product::find($productId);
        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Продукт не найден'], 404);
        }

        $cartCount = CartCount::query()
            ->whereDate('date', $date)
            ->where('product_id', $productId)
            ->first();

        if (!$cartCount) {
            $cartCount = new CartCount();
            $cartCount->date = $date;
            $cartCount->product_id = $productId;
        }

        // Сохраняем только carts (введенное пользователем количество тележек)
        $cartCount->carts = $carts !== null && $carts !== '' ? (float)$carts : null;
        $cartCount->save();

        // Рассчитываем итого динамически: (рассчитанное из заказов + введенное) * pieces_per_cart
        // Для этого нужно получить рассчитанное значение из заказов
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

        $totalOrderAmounts = [];
        foreach ($buses as $bus) {
            foreach ($bus->orders as $busOrder) {
                foreach ($busOrder->items as $item) {
                    if ($item->product_id == $productId) {
                        $orderAmount = $item->amount ?: 0;
                        $totalOrderAmounts[$productId] = ($totalOrderAmounts[$productId] ?? 0) + $orderAmount;
                    }
                }
            }
        }

        $totalAmount = $totalOrderAmounts[$productId] ?? 0;
        $orderMultiplier = $product->order_multiplier ?? 1;
        $piecesPerCart = $product->pieces_per_cart ?? 1;
        $multipliedAmount = $totalAmount * $orderMultiplier;
        $calculatedCarts = $multipliedAmount > 0 && $piecesPerCart > 0
            ? round($multipliedAmount / $piecesPerCart, 2)
            : 0;

        // Итого = (рассчитанное из заказов + введенное пользователем) * pieces_per_cart
        // Округляем до целых чисел
        $savedCartsValue = $cartCount->carts ?? 0;
        $totalCartsValue = $calculatedCarts + $savedCartsValue;
        $calculatedTotal = $totalCartsValue > 0 ? round($totalCartsValue * $piecesPerCart) : 0;

        return response()->json([
            'success' => true,
            'carts' => $cartCount->carts,
            'calculated_total' => $calculatedTotal,
            'calculated_carts' => $calculatedCarts,
            'total_carts_value' => $totalCartsValue > 0 ? $totalCartsValue : 0, // Возвращаем точное значение, не округленное
        ]);
    }

}
