<?php

namespace App\Http\Controllers;

use App\Models\Bus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class PublicOrderController extends Controller
{
    /**
     * Публичная страница для отображения заказов на большом экране
     * @param Request $request
     * @return Factory|Application|View|\Illuminate\Contracts\Foundation\Application|JsonResponse
     */
    public function display(Request $request): Factory|Application|View|\Illuminate\Contracts\Foundation\Application|JsonResponse
    {
        $date = $request->input('date', date('Y-m-d'));

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

        $busesData = $buses->map(function ($bus) use ($products) {
            $orderAmounts = [];

            /** @var Order $order */
            foreach ($bus->orders as $order) {
                /** @var OrderItem $item */
                foreach ($order->items as $item) {
                    $orderAmounts[$item->product_id] = $item->amount;
                }
            }

            // Проверяем, есть ли хотя бы один заказ
            $hasOrders = false;
            foreach ($orderAmounts as $amount) {
                if (!empty($amount) && $amount > 0) {
                    $hasOrders = true;
                    break;
                }
            }

            // Если нет заказов, возвращаем null (будет отфильтровано)
            if (!$hasOrders) {
                return null;
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
            ];
        })->filter(); // Убираем автобусы без заказов

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
            $piecesPerCart = $product->pieces_per_cart ?? 1;
            return $totalAmount > 0 && $piecesPerCart > 0
                ? round($totalAmount / $piecesPerCart, 1)
                : '';
        });

        // Если это AJAX запрос, возвращаем JSON
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'date' => $date,
                'dateFormatted' => date('d.m.Y H:i', strtotime($date)),
                'busesData' => $busesData->values()->toArray(),
                'products' => $products->map(function ($product) {
                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                    ];
                })->toArray(),
                'totalCarts' => $totalCarts->values()->toArray()
            ]);
        }

        return view('public.orders.display', compact('date', 'dateFormatted', 'busesData', 'products', 'totalCarts'));
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
}
