<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\StoreRealizationRequest;
use App\Models\Bus;
use App\Models\Realization;
use App\Models\RealizationShop;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RealizationController extends Controller
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

        $realization = Realization::query()
            ->where('bus_id', '=', $busId)
            ->whereDate('date', $date)
            ->first();

        /** @var Realization $realization */
        if ($realization) {
            $realizationShops = RealizationShop::query()
                ->where('realization_id', '=', $realization->id)
                ->get();

            $realizationShopsCount = $realizationShops->count();

            if ($realizationShopsCount === 0) {
                $latestRealizationId = Realization::query()
                    ->where('bus_id', '=', $busId)
                    ->where('id', '!=', $realization->id)
                    ->orderBy('date', 'desc')
                    ->value('id');

                if ($latestRealizationId) {
                    $realizationShops = RealizationShop::query()
                        ->where('realization_id', '=', $latestRealizationId)
                        ->get();

                    // Пробегаем по коллекции и заменяем id на 0
                    $realizationShops->each(function ($shop) {
                        $shop->id = 0;
                    });
                }
            }
        } else {
            // берем прошлую реализацию данного буса
            $latestRealizationId = Realization::query()
                ->where('bus_id', '=', $busId)
                ->orderBy('date', 'desc')
                ->value('id');

            $realization = new Realization();
            $realization->bus_id = $busId;
            $realization->date = $date;
            $realization->save();

            if ($latestRealizationId) {
                $realizationShops = RealizationShop::query()
                    ->where('realization_id', '=', $latestRealizationId)
                    ->get();

                // Пробегаем по коллекции и заменяем id на 0
                $realizationShops->each(function ($shop) {
                    $shop->id = 0;
                });
            }
        }

        return view('client.realizations.create', [
            'licensePlate' => $licensePlate,
            'realization' => $realization,
            'realizationShops' => $realizationShops ?? [],
        ]);
    }

    /**
     * @param StoreRealizationRequest $request
     * @return RedirectResponse
     */
    public function store(StoreRealizationRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $realizationId = $data['realization_id'];
        $realizationShopIds = $data['realization_shop_id'];
        $shops = $data['shop'];
        $amounts = $data['amount'];

        $existingShops = RealizationShop::query()
            ->where('realization_id', '=', $realizationId)
            ->get();

        foreach ($existingShops as $existingShop) {
            // Если ID текущего магазина не пришел из формы, удаляем его
            if (!in_array($existingShop->id, $realizationShopIds)) {
                $existingShop->delete();
            }
        }

        foreach ($shops as $index => $shop) {
            $realizationShopId = $realizationShopIds[$index];
            $amount = $amounts[$index];

            if ($realizationShopId > 0) {
                $realizationShop = RealizationShop::find($realizationShopId);
                if ($realizationShop) {
                    $realizationShop->realization_id = $realizationId;
                    $realizationShop->shop = $shop;
                    $realizationShop->amount = $amount;
                    $realizationShop->save();
                }
            } else {
                $realization = new RealizationShop();
                $realization->realization_id = $realizationId;
                $realization->shop = $shop;
                $realization->amount = $amount;
                $realization->save();
            }
        }

        $request->session()->forget('license_plate');

        return redirect()
            ->route('orders.enter_license_plate')
            ->with('success', ['text' => 'Данные успешно сохранены!']);
    }

    /**
     * @return JsonResponse
     */
    public function addShop(): JsonResponse
    {
        return response()->json([
            'item' => view('client.realizations.shop_item', [
                'id' => 0,
                'shop' => '',
                'amount' => '',
                'index' => null,
            ])->render()
        ]);
    }
}
