<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\LicensePlateRequest;
use App\Http\Requests\Client\StoreOrderRequest;
use App\Models\Bus;
use App\Models\Order;
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
        $licensePlate = $request->validated()['license_plate'];

        $request->session()->put('license_plate', $licensePlate);

        return redirect()->route('orders.create');
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

        return view('client.orders.create', compact('licensePlate', 'order'));
    }

    /**
     * @param StoreOrderRequest $request
     * @return RedirectResponse
     */
    public function store(StoreOrderRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $order = Order::query()->find($data['id']);
        $order->update($data);

        $request->session()->forget('license_plate');

        return redirect()
            ->route('orders.enter_license_plate')
            ->with('success', ['text' => 'Данные успешно сохранены!']);
    }
}