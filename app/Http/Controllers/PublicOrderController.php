<?php

namespace App\Http\Controllers;

use App\Models\Bus;
use App\Models\CartCount;
use App\Models\Order;
use App\Models\OrderChangeLog;
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
        // Переключаемся на следующий день только после 07:00 утра
        $currentHour = (int)date('H');
        if ($currentHour >= 7) {
            $date = date('Y-m-d', strtotime('+1 day'));
        } else {
            $date = date('Y-m-d');
        }

        $dateFormatted = date('d.m.Y H:i');

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

        // Получаем последние изменения для текущей даты
        $changeLogs = OrderChangeLog::query()
            ->whereDate('date', $date)
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy(function ($log) {
                return $log->bus_id . '_' . $log->product_id;
            })
            ->map(function ($logs) {
                return $logs->first(); // Берем последнее изменение для каждой комбинации bus_id + product_id
            });

        $busesData = $buses->map(function ($bus) use ($products, $changeLogs) {
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
                'products' => $products->map(function ($product) use ($orderAmounts, $changeLogs, $bus) {
                    $logKey = $bus->id . '_' . $product->id;
                    $changeLog = $changeLogs->get($logKey);

                    $changeType = null;
                    if ($changeLog) {
                        $oldAmount = $changeLog->old_amount ?? 0;
                        $newAmount = $changeLog->new_amount ?? 0;
                        if ($newAmount > $oldAmount) {
                            $changeType = 'increase';
                        } elseif ($newAmount < $oldAmount) {
                            $changeType = 'decrease';
                        }
                    }

                    return [
                        'product_id' => $product->id,
                        'order_amount' => $orderAmounts[$product->id] ?? '',
                        'change_type' => $changeType,
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

        // Загружаем сохраненные значения тележек из базы данных
        $savedCartCounts = CartCount::query()
            ->whereDate('date', $date)
            ->whereIn('product_id', $products->pluck('id'))
            ->get()
            ->keyBy('product_id');

        // Рассчитываем итоговое количество тележек (рассчитанное + введенное)
        // Округляем до целых чисел
        $totalCarts = $products->map(function ($product) use ($totalOrderAmounts, $savedCartCounts) {
            // Рассчитываем количество тележек из заказов
            $totalAmount = $totalOrderAmounts[$product->id] ?? 0;
            $orderMultiplier = $product->order_multiplier ?? 1;
            $piecesPerCart = $product->pieces_per_cart ?? 1;
            $multipliedAmount = $totalAmount * $orderMultiplier;
            $calculatedCarts = $multipliedAmount > 0 && $piecesPerCart > 0
                ? round($multipliedAmount / $piecesPerCart, 2)
                : 0;
            
            // Получаем введенное пользователем количество тележек
            $cartCount = $savedCartCounts->get($product->id);
            $savedCartsValue = $cartCount ? (float)$cartCount->carts : 0;
            
            // Итоговое количество тележек (рассчитанное + введенное), округляем до целых
            $totalCartsValue = $calculatedCarts + $savedCartsValue;
            return $totalCartsValue > 0 ? round($totalCartsValue) : '';
        });

        // Если это AJAX запрос, возвращаем JSON
        // Проверяем явный параметр ajax=1 для надежности (избегаем проблем при дублировании вкладки)
        if ($request->ajax() && $request->has('ajax')) {
            return response()->json([
                'date' => $date,
                'dateFormatted' => $dateFormatted,
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
