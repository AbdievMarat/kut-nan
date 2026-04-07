<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\StoreInvoiceReturnRequest;
use App\Models\Bus;
use App\Models\InvoiceReturn;
use App\Models\InvoiceReturnShop;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class InvoiceReturnController extends Controller
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

        $invoiceReturn = InvoiceReturn::query()
            ->where('bus_id', '=', $busId)
            ->whereDate('date', $date)
            ->first();

        /** @var InvoiceReturn $invoiceReturn */
        if ($invoiceReturn) {
            $invoiceReturnShops = InvoiceReturnShop::query()
                ->where('invoice_return_id', '=', $invoiceReturn->id)
                ->get();

            if ($invoiceReturnShops->count() === 0) {
                $latestInvoiceReturnId = InvoiceReturn::query()
                    ->where('bus_id', '=', $busId)
                    ->where('id', '!=', $invoiceReturn->id)
                    ->orderBy('date', 'desc')
                    ->value('id');

                if ($latestInvoiceReturnId) {
                    $invoiceReturnShops = InvoiceReturnShop::query()
                        ->where('invoice_return_id', '=', $latestInvoiceReturnId)
                        ->get();

                    $invoiceReturnShops->each(function ($shop) {
                        $shop->id = 0;
                    });
                }
            }
        } else {
            $latestInvoiceReturnId = InvoiceReturn::query()
                ->where('bus_id', '=', $busId)
                ->orderBy('date', 'desc')
                ->value('id');

            $invoiceReturn = new InvoiceReturn();
            $invoiceReturn->bus_id = $busId;
            $invoiceReturn->date = $date;
            $invoiceReturn->save();

            if ($latestInvoiceReturnId) {
                $invoiceReturnShops = InvoiceReturnShop::query()
                    ->where('invoice_return_id', '=', $latestInvoiceReturnId)
                    ->get();

                $invoiceReturnShops->each(function ($shop) {
                    $shop->id = 0;
                });
            }
        }

        return view('client.invoice-returns.create', [
            'licensePlate' => $licensePlate,
            'invoiceReturn' => $invoiceReturn,
            'invoiceReturnShops' => $invoiceReturnShops ?? [],
        ]);
    }

    /**
     * @param StoreInvoiceReturnRequest $request
     * @return RedirectResponse
     */
    public function store(StoreInvoiceReturnRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $invoiceReturnId = $data['invoice_return_id'];
        $invoiceReturnShopIds = $data['invoice_return_shop_id'];
        $shops = $data['shop'];
        $amounts = $data['amount'];

        $existingShops = InvoiceReturnShop::query()
            ->where('invoice_return_id', '=', $invoiceReturnId)
            ->get();

        foreach ($existingShops as $existingShop) {
            if (!in_array($existingShop->id, $invoiceReturnShopIds)) {
                $existingShop->delete();
            }
        }

        foreach ($shops as $index => $shop) {
            $invoiceReturnShopId = $invoiceReturnShopIds[$index];
            $amount = $amounts[$index];

            if ($invoiceReturnShopId > 0) {
                $invoiceReturnShop = InvoiceReturnShop::find($invoiceReturnShopId);
                if ($invoiceReturnShop) {
                    $invoiceReturnShop->invoice_return_id = $invoiceReturnId;
                    $invoiceReturnShop->shop = $shop;
                    $invoiceReturnShop->amount = $amount;
                    $invoiceReturnShop->save();
                }
            } else {
                $invoiceReturnShop = new InvoiceReturnShop();
                $invoiceReturnShop->invoice_return_id = $invoiceReturnId;
                $invoiceReturnShop->shop = $shop;
                $invoiceReturnShop->amount = $amount;
                $invoiceReturnShop->save();
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
            'item' => view('client.invoice-returns.shop_item', [
                'id' => 0,
                'shop' => '',
                'amount' => '',
                'index' => null,
            ])->render()
        ]);
    }
}
