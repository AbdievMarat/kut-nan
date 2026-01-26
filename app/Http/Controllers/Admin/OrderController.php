<?php

namespace App\Http\Controllers\Admin;

use App\Exports\OrderExport;
use App\Http\Controllers\Controller;
use App\Models\Bus;
use App\Models\BusProductPrice;
use App\Models\Markdown;
use App\Models\Order;
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

        return view('admin.orders.index', compact('date', 'busesData', 'products', 'totalCarts', 'multipliedAmounts', 'piecesPerCarts'));
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

        if (!$orderItem) {
            $orderItem = new OrderItem();
            $orderItem->order_id = $order->id;
            $orderItem->product_id = $productId;
            $orderItem->price = $price;
        }

        $orderItem->amount = $amount ? (int)$amount : null;
        $orderItem->save();

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

        return response()->json([
            'success' => true,
            'totalCarts' => $totalCarts->values()->toArray(),
            'multipliedAmounts' => $multipliedAmounts->values()->toArray(),
            'piecesPerCarts' => $piecesPerCarts->values()->toArray()
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

}
