<?php

namespace App\Http\Controllers\Admin;

use App\Exports\OrderExport;
use App\Http\Controllers\Controller;
use App\Models\Bus;
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
        $sumRealizations = $this->getSumRealizations($date);
        $sumRemainders = $this->getSumRemainders($date);

        $busesData = $buses->map(function ($bus) use ($products, $sumRealizations, $sumRemainders) {
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
                'total_realization_sum' => $sumRealizations[$bus->id] ?? '',
                'total_remainder_sum' => $sumRemainders[$bus->id] ?? ''
            ];
        });

        return view('admin.orders.index', compact('date', 'busesData', 'products'));
    }

    /**
     * @param Request $request
     * @return BinaryFileResponse
     */
    public function exportToExcel(Request $request): BinaryFileResponse
    {
        $date = $request->input('date', date('Y-m-d', strtotime('+1 day')));
        $products = $this->getProducts();
        $sumRealizations = $this->getSumRealizations($date);
        $sumRemainders = $this->getSumRemainders($date);

        return Excel::download(new OrderExport($date, $products, $sumRealizations, $sumRemainders), 'orders_' . date('d.m.Y', strtotime($date)) . '.xlsx');
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
     * @return Collection
     */
    private function getProducts(): Collection
    {
        return Product::query()
            ->where('is_active', '=', Product::IS_ACTIVE)
            ->orderBy('sort')
            ->get();
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