<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreBusRequest;
use App\Http\Requests\Admin\UpdateBusProductPriceRequest;
use App\Http\Requests\Admin\UpdateBusRequest;
use App\Models\Bus;
use App\Models\BusProductPrice;
use App\Models\Product;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\RedirectResponse;

class BusController extends Controller
{
    /**
     * @return Factory|Application|View|\Illuminate\Contracts\Foundation\Application
     */
    public function index(): Factory|Application|View|\Illuminate\Contracts\Foundation\Application
    {
        $buses = Bus::query()
            ->orderBy('sort')
            ->paginate(10)
            ->withQueryString();

        return view('admin.buses.index', compact('buses'));
    }

    /**
     * @return Factory|Application|View|\Illuminate\Contracts\Foundation\Application
     */
    public function create(): Factory|Application|View|\Illuminate\Contracts\Foundation\Application
    {
        return view('admin.buses.create');
    }

    /**
     * @param StoreBusRequest $request
     * @return RedirectResponse
     */
    public function store(StoreBusRequest $request): RedirectResponse
    {
        Bus::query()->create($request->validated());

        return redirect()
            ->route('admin.buses.index')
            ->with('success', ['text' => 'Бус успешно создан!']);
    }

    /**
     * @param Bus $bus
     * @return Factory|Application|View|\Illuminate\Contracts\Foundation\Application
     */
    public function edit(Bus $bus): Factory|Application|View|\Illuminate\Contracts\Foundation\Application
    {
        return view('admin.buses.edit', compact('bus'));
    }

    /**
     * @param UpdateBusRequest $request
     * @param Bus $bus
     * @return RedirectResponse
     */
    public function update(UpdateBusRequest $request, Bus $bus): RedirectResponse
    {
        $bus->update($request->validated());

        return redirect()
            ->route('admin.buses.index')
            ->with('success', ['text' => 'Бус успешно обновлен!']);
    }

    /**
     * @param Bus $bus
     * @return Factory|Application|View|\Illuminate\Contracts\Foundation\Application
     */
    public function editProductPrices(Bus $bus): Factory|Application|View|\Illuminate\Contracts\Foundation\Application
    {
        $products = Product::query()
            ->where('is_active', '=', Product::IS_ACTIVE)
            ->orderBy('sort')
            ->get();

        $prices = $bus->prices->keyBy('product_id');

        return view('admin.buses.edit_product_prices', compact('bus', 'products', 'prices'));
    }

    /**
     * @param UpdateBusProductPriceRequest $request
     * @param Bus $bus
     * @return RedirectResponse
     */
    public function updateProductPrices(UpdateBusProductPriceRequest $request, Bus $bus): RedirectResponse
    {
        $validatedData = $request->validated();

        foreach ($validatedData['product_prices'] as $productId => $price) {
            if ($price !== null) {
                BusProductPrice::query()->updateOrCreate(
                    ['bus_id' => $bus->id, 'product_id' => $productId],
                    ['price' => $price]
                );
            }
        }

        return redirect()
            ->route('admin.buses.index')
            ->with('success', ['text' => 'Данные успешно обновлены!']);
    }
}
