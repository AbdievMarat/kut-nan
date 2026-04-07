<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\StoreInvoiceRequest;
use App\Models\Bus;
use App\Models\Invoice;
use App\Models\InvoiceShop;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class InvoiceController extends Controller
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

        $invoice = Invoice::query()
            ->where('bus_id', '=', $busId)
            ->whereDate('date', $date)
            ->first();

        /** @var Invoice $invoice */
        if ($invoice) {
            $invoiceShops = InvoiceShop::query()
                ->where('invoice_id', '=', $invoice->id)
                ->get();

            if ($invoiceShops->count() === 0) {
                $latestInvoiceId = Invoice::query()
                    ->where('bus_id', '=', $busId)
                    ->where('id', '!=', $invoice->id)
                    ->orderBy('date', 'desc')
                    ->value('id');

                if ($latestInvoiceId) {
                    $invoiceShops = InvoiceShop::query()
                        ->where('invoice_id', '=', $latestInvoiceId)
                        ->get();

                    $invoiceShops->each(function ($shop) {
                        $shop->id = 0;
                    });
                }
            }
        } else {
            $latestInvoiceId = Invoice::query()
                ->where('bus_id', '=', $busId)
                ->orderBy('date', 'desc')
                ->value('id');

            $invoice = new Invoice();
            $invoice->bus_id = $busId;
            $invoice->date = $date;
            $invoice->save();

            if ($latestInvoiceId) {
                $invoiceShops = InvoiceShop::query()
                    ->where('invoice_id', '=', $latestInvoiceId)
                    ->get();

                $invoiceShops->each(function ($shop) {
                    $shop->id = 0;
                });
            }
        }

        return view('client.invoices.create', [
            'licensePlate' => $licensePlate,
            'invoice' => $invoice,
            'invoiceShops' => $invoiceShops ?? [],
        ]);
    }

    /**
     * @param StoreInvoiceRequest $request
     * @return RedirectResponse
     */
    public function store(StoreInvoiceRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $invoiceId = $data['invoice_id'];
        $invoiceShopIds = $data['invoice_shop_id'];
        $shops = $data['shop'];
        $amounts = $data['amount'];

        $existingShops = InvoiceShop::query()
            ->where('invoice_id', '=', $invoiceId)
            ->get();

        foreach ($existingShops as $existingShop) {
            if (!in_array($existingShop->id, $invoiceShopIds)) {
                $existingShop->delete();
            }
        }

        foreach ($shops as $index => $shop) {
            $invoiceShopId = $invoiceShopIds[$index];
            $amount = $amounts[$index];

            if ($invoiceShopId > 0) {
                $invoiceShop = InvoiceShop::find($invoiceShopId);
                if ($invoiceShop) {
                    $invoiceShop->invoice_id = $invoiceId;
                    $invoiceShop->shop = $shop;
                    $invoiceShop->amount = $amount;
                    $invoiceShop->save();
                }
            } else {
                $invoiceShop = new InvoiceShop();
                $invoiceShop->invoice_id = $invoiceId;
                $invoiceShop->shop = $shop;
                $invoiceShop->amount = $amount;
                $invoiceShop->save();
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
            'item' => view('client.invoices.shop_item', [
                'id' => 0,
                'shop' => '',
                'amount' => '',
                'index' => null,
            ])->render()
        ]);
    }
}
