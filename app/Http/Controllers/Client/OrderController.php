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

            $products = Product::query()
                ->where('is_active', '=', Product::IS_ACTIVE)
                ->where('is_in_report', '=', Product::IS_IN_REPORT)
                ->orderBy('sort')
                ->get();

            if ($products) {
                $orderItems = [];

                /** @var Product $product */
                foreach ($products as $product) {
                    $busProductPrice = $product->prices()
                        ->where('bus_id', '=', $busId)
                        ->first();

                    /** @var BusProductPrice $busProductPrice */
                    $price = $busProductPrice ? $busProductPrice->price : 0;

                    $orderItems[] = [
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'price' => $price,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                OrderItem::query()->insert($orderItems);
            }
        }

        $order->load('items.product');

        return view('client.orders.create', compact('licensePlate', 'order'));
    }

    /**
     * @param StoreOrderRequest $request
     * @return RedirectResponse
     */
    public function store(StoreOrderRequest $request): RedirectResponse
    {
        $validatedData = $request->validated();

        foreach ($validatedData['item_ids'] as $itemId) {
            $amount = $validatedData['item_amounts'][$itemId];

            $orderItem = OrderItem::find($itemId);
            $orderItem->amount = $amount;
            $orderItem->save();
        }

        $request->session()->forget('license_plate');

        return redirect()
            ->route('orders.enter_license_plate')
            ->with('success', ['text' => 'Данные успешно сохранены!']);
    }
}