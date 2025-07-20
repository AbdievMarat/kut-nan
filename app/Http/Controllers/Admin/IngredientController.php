<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreIngredientRequest;
use App\Http\Requests\Admin\UpdateIngredientRequest;
use App\Models\Ingredient;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\RedirectResponse;

class IngredientController extends Controller
{
    /**
     * @return Factory|Application|View|\Illuminate\Contracts\Foundation\Application
     */
    public function index(): Factory|Application|View|\Illuminate\Contracts\Foundation\Application
    {
        $ingredients = Ingredient::query()
            ->orderBy('sort')
            ->paginate(20)
            ->withQueryString();

        return view('admin.ingredients.index', compact('ingredients'));
    }

    /**
     * @return Factory|Application|View|\Illuminate\Contracts\Foundation\Application
     */
    public function create(): Factory|Application|View|\Illuminate\Contracts\Foundation\Application
    {
        return view('admin.ingredients.create');
    }

    /**
     * @param StoreIngredientRequest $request
     * @return RedirectResponse
     */
    public function store(StoreIngredientRequest $request): RedirectResponse
    {
        Ingredient::query()->create($request->validated());

        return redirect()
            ->route('admin.ingredients.index')
            ->with('success', ['text' => 'Ингредиент успешно добавлен!']);
    }

    /**
     * @param Ingredient $ingredient
     * @return Factory|Application|View|\Illuminate\Contracts\Foundation\Application
     */
    public function edit(Ingredient $ingredient): Factory|Application|View|\Illuminate\Contracts\Foundation\Application
    {
        return view('admin.ingredients.edit', compact('ingredient'));
    }

    /**
     * @param UpdateIngredientRequest $request
     * @param Ingredient $ingredient
     * @return RedirectResponse
     */
    public function update(UpdateIngredientRequest $request, Ingredient $ingredient): RedirectResponse
    {
        $ingredient->update($request->validated());

        return redirect()
            ->route('admin.ingredients.index')
            ->with('success', ['text' => 'Ингредиент успешно обновлен!']);
    }
}
