<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductRequest;
use App\Http\Requests\Admin\UpdateProductRequest;
use App\Http\Requests\Admin\StoreProductIngredientsRequest;
use App\Models\Product;
use App\Models\Ingredient;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\RedirectResponse;

class ProductController extends Controller
{
    /**
     * @return Factory|Application|View|\Illuminate\Contracts\Foundation\Application
     */
    public function index(): Factory|Application|View|\Illuminate\Contracts\Foundation\Application
    {
        $products = Product::query()
            ->orderBy('sort')
            ->paginate(10)
            ->withQueryString();

        return view('admin.products.index', compact('products'));
    }

    /**
     * @return Factory|Application|View|\Illuminate\Contracts\Foundation\Application
     */
    public function create(): Factory|Application|View|\Illuminate\Contracts\Foundation\Application
    {
        return view('admin.products.create');
    }

    /**
     * @param StoreProductRequest $request
     * @return RedirectResponse
     */
    public function store(StoreProductRequest $request): RedirectResponse
    {
        Product::query()->create($request->validated());

        return redirect()
            ->route('admin.products.index')
            ->with('success', ['text' => 'Продукт успешно добавлен!']);
    }

    /**
     * @param Product $product
     * @return Factory|Application|View|\Illuminate\Contracts\Foundation\Application
     */
    public function edit(Product $product): Factory|Application|View|\Illuminate\Contracts\Foundation\Application
    {
        return view('admin.products.edit', compact('product'));
    }

    /**
     * @param UpdateProductRequest $request
     * @param Product $product
     * @return RedirectResponse
     */
    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $product->update($request->validated());

        return redirect()
            ->route('admin.products.index')
            ->with('success', ['text' => 'Продукт успешно обновлен!']);
    }

    /**
     * @param Product $product
     * @return Factory|Application|View|\Illuminate\Contracts\Foundation\Application
     */
    public function ingredients(Product $product): Factory|Application|View|\Illuminate\Contracts\Foundation\Application
    {
        $ingredients = Ingredient::query()
            ->where('is_active', Ingredient::IS_ACTIVE)
            ->orderBy('sort')
            ->get();

        $productIngredients = $product->ingredients()
            ->withPivot('formula')
            ->get()
            ->keyBy('id');

        return view('admin.products.ingredients', compact('product', 'ingredients', 'productIngredients'));
    }

    /**
     * @param StoreProductIngredientsRequest $request
     * @param Product $product
     * @return RedirectResponse
     */
    public function storeIngredients(StoreProductIngredientsRequest $request, Product $product): RedirectResponse
    {
        $ingredient_formula = $request->validated()['ingredient_formula'] ?? [];

        $syncData = [];
        foreach ($ingredient_formula as $ingredientId => $formula) {
            if (!empty($formula)) {
                $syncData[$ingredientId] = ['formula' => $formula];
            }
        }

        $product->ingredients()->sync($syncData);

        return redirect()
            ->route('admin.products.index')
            ->with('success', ['text' => 'Ингредиенты продукта успешно обновлены!']);
    }
}
