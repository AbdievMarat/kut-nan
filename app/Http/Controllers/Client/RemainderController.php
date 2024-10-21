<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\StoreRemainderRequest;
use App\Models\Bus;
use App\Models\Product;
use App\Models\Remainder;
use App\Models\RemainderItem;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RemainderController extends Controller
{
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

        $remainder = Remainder::query()
            ->where('bus_id', '=', $busId)
            ->whereDate('date', $date)
            ->first();

        if (!$remainder) {
            $remainder = new Remainder();
            $remainder->bus_id = $busId;
            $remainder->date = $date;
            $remainder->save();

            $products = Product::query()
                ->where('is_active', '=', Product::IS_ACTIVE)
                ->get();

            if ($products) {
                $remainderItems = [];

                /** @var Product $product */
                foreach ($products as $product) {
                    $remainderItems[] = [
                        'remainder_id' => $remainder->id,
                        'product_id' => $product->id,
                        'price' => $product->price,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                RemainderItem::query()->insert($remainderItems);
            }
        }

        $remainder->load('items.product');

        return view('client.remainders.create', compact('licensePlate', 'remainder'));
    }

    /**
     * @param StoreRemainderRequest $request
     * @return RedirectResponse
     */
    public function store(StoreRemainderRequest $request): RedirectResponse
    {
        $validatedData = $request->validated();

        foreach ($validatedData['item_ids'] as $itemId) {
            $amount = $validatedData['item_amounts'][$itemId];

            $remainderItem = RemainderItem::find($itemId);
            $remainderItem->amount = $amount;
            $remainderItem->save();
        }

        $request->session()->forget('license_plate');

        return redirect()
            ->route('orders.enter_license_plate')
            ->with('success', ['text' => 'Данные успешно сохранены!']);
    }
}
