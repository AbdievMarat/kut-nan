<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\StoreMarkdownRequest;
use App\Models\Bus;
use App\Models\BusProductPrice;
use App\Models\Markdown;
use App\Models\MarkdownItem;
use App\Models\Product;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MarkdownController extends Controller
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

        $markdown = Markdown::query()
            ->where('bus_id', '=', $busId)
            ->whereDate('date', $date)
            ->first();

        if (!$markdown) {
            $markdown = new Markdown();
            $markdown->bus_id = $busId;
            $markdown->date = $date;
            $markdown->save();
        }

        $products = BusProductPrice::query()
            ->select([
                'bus_product_prices.product_id as id',
                'products.name as name',
            ])
            ->from('bus_product_prices')
            ->where('bus_product_prices.bus_id', '=', $busId)
            ->where('products.is_active', '=', Product::IS_ACTIVE)
            ->join('products', 'bus_product_prices.product_id', '=', 'products.id')
            ->orderBy('products.sort')
            ->with('product')
            ->get();

        $itemAmounts = $markdown->items->keyBy('product_id');

        return view('client.markdowns.create', compact('licensePlate', 'markdown', 'products', 'itemAmounts'));
    }

    /**
     * @param StoreMarkdownRequest $request
     * @return RedirectResponse
     */
    public function store(StoreMarkdownRequest $request): RedirectResponse
    {
        $validatedData = $request->validated();

        $markdownId = $validatedData['id'];

        foreach ($validatedData['item_amounts'] as $productId => $amount) {
            if ($amount !== null) {
                $price = $validatedData['item_price'][$productId] ?? 0;

                MarkdownItem::query()->updateOrCreate(
                    ['markdown_id' => $markdownId, 'product_id' => $productId],
                    ['amount' => $amount, 'price' => $price]
                );
            }
        }

        $request->session()->forget('license_plate');

        return redirect()
            ->route('orders.enter_license_plate')
            ->with('success', ['text' => 'Данные успешно сохранены!']);
    }
}
