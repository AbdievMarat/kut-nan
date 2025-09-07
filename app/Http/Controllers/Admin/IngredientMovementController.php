<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\IngredientMovementRequest;
use App\Models\Ingredient;
use App\Models\IngredientUsage;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Exports\IngredientMovementExport;
use App\Exports\IngredientMovementDetailExport;
use Carbon\Carbon;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class IngredientMovementController extends Controller
{
    /**
     * Отображение списка производственных партий
     *
     * @param Request $request
     * @return Factory|Application|View|\Illuminate\Contracts\Foundation\Application
     */
    public function index(Request $request): Factory|Application|View|\Illuminate\Contracts\Foundation\Application
    {
        // Получаем параметры фильтрации дат из запроса
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

        // Если даты не переданы, используем последние 30 дней из таблицы IngredientUsage
        if (!$dateFrom || !$dateTo) {
            $latestDate = IngredientUsage::query()
                ->orderByDesc('date')
                ->value('date');

            if ($latestDate) {
                $dateTo = $latestDate;
                $dateFrom = date('Y-m-d', strtotime($latestDate . ' -30 days'));
            } else {
                $dateTo = date('Y-m-d');
                $dateFrom = date('Y-m-d', strtotime('-30 days'));
            }
        }

        $ingredients = Ingredient::query()
            ->where('is_active', Ingredient::IS_ACTIVE)
            ->orderBy('sort')
            ->get();

        // Получаем записи движения ингредиентов с фильтрацией по датам
        $ingredientUsages = IngredientUsage::query()
            ->with('ingredient')
            ->whereBetween('date', [$dateFrom, $dateTo])
            ->orderByDesc('date')
            ->get()
            ->groupBy('date');

        // Формируем структуру данных для таблицы
        $tableData = [];
        foreach ($ingredientUsages as $date => $usages) {
            $tableData[$date] = [];

            /** @var IngredientUsage $usage */
            foreach ($usages as $usage) {
                $tableData[$date][$usage->ingredient_id] = [
                    'income' => $usage->income,
                    'usage' => $usage->usage,
                    'stock' => $usage->stock,
                ];
            }
        }

        // Добавляем информацию о возможности редактирования для каждой даты
        $editableDates = [];
        foreach ($tableData as $date => $data) {
            $editableDates[$date] = $this->isDateEditable($date);
        }

        // Проверяем, есть ли записи за сегодняшний день
        $today = date('Y-m-d');
        $hasTodayRecords = IngredientUsage::query()
            ->where('date', '=', $today)
            ->exists();

        return view('admin.ingredient_movements.index', [
            'ingredients' => $ingredients,
            'tableData' => $tableData,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'hasTodayRecords' => $hasTodayRecords,
            'editableDates' => $editableDates,
        ]);
    }

    /**
     * Получение активных продуктов и ингредиентов
     *
     * @return Factory|Application|View|\Illuminate\Contracts\Foundation\Application|RedirectResponse
     */
    public function create(): Factory|Application|View|\Illuminate\Contracts\Foundation\Application|RedirectResponse
    {
        $products = Product::query()
            ->where('is_active', '=', Product::IS_ACTIVE)
            ->where('pieces_per_cart', '>', '1')
            ->orderBy('sort')
            ->get();

        $ingredients = Ingredient::query()
            ->where('is_active', '=', Ingredient::IS_ACTIVE)
            ->orderBy('sort')
            ->get();

        return view('admin.ingredient_movements.create', [
            'products' => $products,
            'ingredients' => $ingredients,
        ]);
    }

    /**
     * Сохранение данные о производстве и расходе ингредиентов
     *
     * @param IngredientMovementRequest $request
     * @return RedirectResponse
     */
    public function store(IngredientMovementRequest $request): RedirectResponse
    {
        // Валидация на уровне контроллера
        $request->validate([
            'date' => [
                function ($attribute, $value, $fail) {
                    $hasRecords = IngredientUsage::query()
                        ->where('date', '=', $value)
                        ->exists();

                    if ($hasRecords) {
                        $fail('Запись за ' . date('d.m.Y', strtotime($value)) . ' уже существует.');
                    }
                }
            ]
        ]);

        DB::transaction(function () use ($request) {
            $date = $request->input('date');

            // Сохранение партий продуктов
            $productBatches = $this->saveProductBatches($request, $date);

            // Расчет и сохранение движения ингредиентов
            $this->processIngredientMovements($request, $date, $productBatches);

            // Пересчитываем остатки для всех последующих дат
            $this->recalculateStocksAfterDate($date);
        });

        return redirect()
            ->route('admin.ingredient-movements.index')
            ->with('success', ['text' => 'Запись успешно создана']);
    }

    /**
     * Отображение детальной информации по производству на конкретную дату
     *
     * @param string $date
     * @return Factory|Application|View|\Illuminate\Contracts\Foundation\Application
     */
    public function show(string $date): Factory|Application|View|\Illuminate\Contracts\Foundation\Application
    {
        // Получаем все активные ингредиенты для отображения в столбцах
        $ingredients = Ingredient::query()
            ->where('is_active', Ingredient::IS_ACTIVE)
            ->orderBy('sort')
            ->get();

        // Получаем производственные партии за указанную дату с загрузкой связанных данных
        $productBatches = ProductBatch::query()
            ->with([
                'product',
                'productBatchIngredients.ingredient'
            ])
            ->where('date', $date)
            ->whereHas('product', function ($query) {
                $query->where('is_active', Product::IS_ACTIVE);
            })
            ->get();

        // Получаем приход ингредиентов за указанную дату
        $ingredientUsages = IngredientUsage::query()
            ->where('date', '=', $date)
            ->get()
            ->keyBy('ingredient_id');

        // Формируем структуру данных для таблицы
        $tableData = [];
        foreach ($productBatches as $batch) {
            $tableData[$batch->product_id] = [
                'product' => $batch->product,
                'quantity_cart' => $batch->quantity_cart,
                'quantity_total' => $batch->quantity_total,
                'ingredients' => []
            ];
            // Заполняем данные по ингредиентам для каждого продукта

            foreach ($batch->productBatchIngredients as $batchIngredient) {
                $tableData[$batch->product_id]['ingredients'][$batchIngredient->ingredient_id] = $batchIngredient->amount;
            }
        }

        return view('admin.ingredient_movements.show', [
            'date' => $date,
            'ingredients' => $ingredients,
            'tableData' => $tableData,
            'ingredientUsages' => $ingredientUsages,
        ]);
    }

    /**
     * Редактирование записи движения ингредиентов по дате
     *
     * @param string|null $date
     * @return Factory|Application|View|\Illuminate\Contracts\Foundation\Application|RedirectResponse
     */
    public function edit(?string $date = null): Factory|Application|View|\Illuminate\Contracts\Foundation\Application|RedirectResponse
    {
        // Если дата не передана, используем сегодняшнюю дату
        if (!$date) {
            $date = date('Y-m-d');
        }

        // Проверяем, есть ли записи за указанную дату
        $hasRecords = IngredientUsage::query()
            ->where('date', '=', $date)
            ->exists();

        if (!$hasRecords) {
            return redirect()
                ->route('admin.ingredient-movements.index')
                ->with('error', ['text' => 'Записи за ' . date('d.m.Y', strtotime($date)) . ' не найдены']);
        }

        // Проверяем, доступна ли дата для редактирования
        if (!$this->isDateEditable($date)) {
            return redirect()
                ->route('admin.ingredient-movements.index')
                ->with('error', ['text' => 'Редактирование записи за ' . date('d.m.Y', strtotime($date)) . ' недоступно. Можно редактировать только записи за последние 10 дней.']);
        }

        $products = Product::query()
            ->where('is_active', '=', Product::IS_ACTIVE)
            ->where('pieces_per_cart', '>', '1')
            ->orderBy('sort')
            ->get();

        $ingredients = Ingredient::query()
            ->where('is_active', '=', Ingredient::IS_ACTIVE)
            ->orderBy('sort')
            ->get();

        // Получаем существующие партии продуктов на эту дату
        $existingProductBatches = ProductBatch::query()
            ->where('date', $date)
            ->get()
            ->keyBy('product_id');

        // Получаем существующие движения ингредиентов на эту дату
        $existingIngredientUsages = IngredientUsage::query()
            ->where('date', $date)
            ->get()
            ->keyBy('ingredient_id');

        return view('admin.ingredient_movements.edit', [
            'products' => $products,
            'ingredients' => $ingredients,
            'date' => $date,
            'existingProductBatches' => $existingProductBatches,
            'existingIngredientUsages' => $existingIngredientUsages,
        ]);
    }

    /**
     * Обновление записи движения ингредиентов
     *
     * @param IngredientMovementRequest $request
     * @param string $date
     * @return RedirectResponse
     * @throws Throwable
     */
    public function update(IngredientMovementRequest $request, string $date): RedirectResponse
    {
        DB::transaction(function () use ($request, $date) {
            // Удаляем существующие записи за эту дату
            ProductBatch::query()->where('date', $date)->delete();
            IngredientUsage::query()->where('date', $date)->delete();

            // Сохранение партий продуктов
            $productBatches = $this->saveProductBatches($request, $date);

            // Расчет и сохранение движения ингредиентов
            $this->processIngredientMovements($request, $date, $productBatches);

            // Пересчитываем остатки для всех последующих дат
            $this->recalculateStocksAfterDate($date);
        });

        return redirect()
            ->route('admin.ingredient-movements.index')
            ->with('success', ['text' => 'Запись успешно обновлена']);
    }

    /**
     * Сохранение партий продуктов
     *
     * @param IngredientMovementRequest $request
     * @param string $date
     * @return array
     */
    private function saveProductBatches(IngredientMovementRequest $request, string $date): array
    {
        $productBatches = [];

        if ($request->has('products')) {
            foreach ($request->input('products') as $productId => $productData) {
                $quantityCart = (float) ($productData['quantity_cart'] ?? 0);
                $piecesPerCart = (int) ($productData['pieces_per_cart'] ?? 0);

                if ($quantityCart > 0 && $piecesPerCart > 0) {
                    $quantityTotal = $quantityCart * $piecesPerCart;

                    $productBatch = new ProductBatch();
                    $productBatch->product_id = $productId;
                    $productBatch->date = $date;
                    $productBatch->quantity_cart = $quantityCart;
                    $productBatch->quantity_total = $quantityTotal;
                    $productBatch->save();

                    $productBatches[$productId] = $productBatch;
                }
            }
        }

        return $productBatches;
    }

    /**
     * Обработка движения ингредиентов
     *
     * @param IngredientMovementRequest $request
     * @param string $date
     * @param array $productBatches
     * @return void
     */
    private function processIngredientMovements(IngredientMovementRequest $request, string $date, array $productBatches): void
    {
        $ingredients = Ingredient::query()
            ->with(['products'])
            ->where('is_active', Ingredient::IS_ACTIVE)
            ->orderBy('sort')
            ->get();

        /** @var Ingredient $ingredient */
        foreach ($ingredients as $ingredient) {
            $calculatedUsage = $this->calculateIngredientUsage($ingredient, $productBatches);
            $income = (float) ($request->input("ingredients.{$ingredient->id}.income") ?? 0);
            $usageMissing = (float) ($request->input("ingredients.{$ingredient->id}.usage_missing") ?? 0);
            $usageTakenFromStock = (float) ($request->input("ingredients.{$ingredient->id}.usage_taken_from_stock") ?? 0);
            $usageKitchen = (float) ($request->input("ingredients.{$ingredient->id}.usage_kitchen") ?? 0);

            $this->saveIngredientUsage(
                $ingredient,
                $date,
                $calculatedUsage,
                $income,
                $usageMissing,
                $usageTakenFromStock,
                $usageKitchen
            );
        }
    }

    /**
     * Расчет расхода ингредиента
     *
     * @param Ingredient $ingredient
     * @param array $productBatches
     * @return float
     */
    private function calculateIngredientUsage(Ingredient $ingredient, array $productBatches): float
    {
        $calculatedUsage = 0;

        /** @var Product $product */
        foreach ($ingredient->products as $product) {
            if (isset($productBatches[$product->id]) && !empty($product->pivot->formula)) {
                /** @var ProductBatch $productBatch */
                $productBatch = $productBatches[$product->id];

                $ingredientFormula = $product->pivot->formula;

                $processedFormula = str_replace('$quantity', $productBatch->quantity_total, $ingredientFormula);
                $processedFormula = str_replace('$portion', $product->pieces_per_cart, $processedFormula);

                $calculatedIngredientAmount = eval('return ' . $processedFormula . ';');

                // Если 1 и 2 сорт муки, то значит мешок 50 кг
                if ($ingredient->id === 1 || $ingredient->id === 2) {
                    $calculatedIngredientAmount = $calculatedIngredientAmount / 50;
                }

                $productBatch->productBatchIngredients()->create([
                    'ingredient_id' => $ingredient->id,
                    'amount' => $calculatedIngredientAmount,
                ]);

                $calculatedUsage += $calculatedIngredientAmount;
            }
        }

        return $calculatedUsage;
    }

    /**
     * Сохранение записи движения ингредиента
     *
     * @param Ingredient $ingredient
     * @param string $date
     * @param float $income
     * @param float $calculatedUsage
     * @param float $usageMissing
     * @param float $usageTakenFromStock
     * @param float $usageKitchen
     * @return void
     */
    private function saveIngredientUsage(
        Ingredient $ingredient,
        string $date,
        float $calculatedUsage,
        float $income = 0,
        float $usageMissing = 0,
        float $usageTakenFromStock = 0,
        float $usageKitchen = 0
    ): void
    {
        // Получаем последний остаток для расчета нового остатка
        $lastUsage = IngredientUsage::query()
            ->where('ingredient_id', $ingredient->id)
            ->where('date', '<', $date)
            ->orderByDesc('date')
            ->first();

        $previousStock = $lastUsage ? $lastUsage->stock : 0;

        // Рассчитываем общий расход (рассчитанный + прочие расходы)
        $totalUsage = $calculatedUsage + $usageMissing + $usageTakenFromStock + $usageKitchen;
        $newStock = $previousStock + $income - $totalUsage;

        $ingredientUsage = new IngredientUsage();
        $ingredientUsage->ingredient_id = $ingredient->id;
        $ingredientUsage->date = $date;
        $ingredientUsage->income = $income;
        $ingredientUsage->usage = $calculatedUsage;
        $ingredientUsage->usage_missing = $usageMissing;
        $ingredientUsage->usage_taken_from_stock = $usageTakenFromStock;
        $ingredientUsage->usage_kitchen = $usageKitchen;
        $ingredientUsage->stock = $newStock;
        $ingredientUsage->save();
    }

    /**
     * Пересчет остатков ингредиентов после указанной даты
     *
     * @param string $date
     * @return void
     */
    private function recalculateStocksAfterDate(string $date): void
    {
        $ingredients = Ingredient::all();

        foreach ($ingredients as $ingredient) {
            $usages = IngredientUsage::query()
                ->where('ingredient_id', $ingredient->id)
                ->where('date', '>', $date)
                ->orderBy('date')
                ->get();

            /** @var IngredientUsage $usage */
            foreach ($usages as $usage) {
                $lastUsage = IngredientUsage::query()
                    ->where('ingredient_id', $ingredient->id)
                    ->where('date', '<', $usage->date)
                    ->orderByDesc('date')
                    ->first();

                $previousStock = $lastUsage ? $lastUsage->stock : 0;

                // Рассчитываем общий расход (рассчитанный + прочие расходы)
                $totalUsage = $usage->usage + $usage->usage_missing + $usage->usage_taken_from_stock + $usage->usage_kitchen;
                $newStock = $previousStock + $usage->income - $totalUsage;

                $usage->stock = $newStock;
                $usage->save();
            }
        }
    }

    /**
     * Экспорт данных о движении ингредиентов в Excel
     *
     * @param Request $request
     * @return BinaryFileResponse
     */
    public function exportToExcel(Request $request): BinaryFileResponse
    {
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

        // Если даты не переданы, используем последние 30 дней
        if (!$dateFrom || !$dateTo) {
            $latestDate = IngredientUsage::query()
                ->orderByDesc('date')
                ->value('date');

            if ($latestDate) {
                $dateTo = $latestDate;
                $dateFrom = date('Y-m-d', strtotime($latestDate . ' -30 days'));
            } else {
                $dateTo = date('Y-m-d');
                $dateFrom = date('Y-m-d', strtotime('-30 days'));
            }
        }

        return Excel::download(
            new IngredientMovementExport($dateFrom, $dateTo),
            'ingredient_movements_' . date('d.m.Y', strtotime($dateFrom)) . '-' . date('d.m.Y', strtotime($dateTo)) . '.xlsx'
        );
    }

    /**
     * Экспорт детализированных данных о производстве на конкретную дату в Excel
     *
     * @param string $date
     * @return BinaryFileResponse
     */
    public function exportShowToExcel(string $date): BinaryFileResponse
    {
        return Excel::download(
            new IngredientMovementDetailExport($date),
            'production_details_' . date('d.m.Y', strtotime($date)) . '.xlsx'
        );
    }

    /**
     * Получение детализации себестоимости для продукта
     *
     * @param Request $request
     * @return JsonResponse
     * @throws Throwable
     */
    public function getCostDetails(Request $request): JsonResponse
    {
        $productId = $request->get('product_id');
        $date = $request->get('date');

        $product = Product::findOrFail($productId);

        // Получаем производственную партию за указанную дату
        $productBatch = ProductBatch::query()
            ->with([
                'product',
                'productBatchIngredients.ingredient'
            ])
            ->where('product_id', '=', $productId)
            ->where('date', '=', $date)
            ->first();

        if (!$productBatch) {
            return response()->json(['error' => 'Партия продукта не найдена'], 404);
        }

        // Собираем данные по ингредиентам
        $ingredientsData = [];
        $totalIngredientsCost = 0;

        foreach ($productBatch->productBatchIngredients as $batchIngredient) {
            $ingredient = $batchIngredient->ingredient;
            $cost = $batchIngredient->amount * $ingredient->price;

            $ingredientsData[] = [
                'name' => $ingredient->name,
                'amount' => $batchIngredient->amount,
                'unit' => $ingredient->unit,
                'price' => $ingredient->price,
                'cost' => $cost
            ];

            $totalIngredientsCost += $cost;
        }

        // Расчет производственных расходов
        $productionCostPerUnit = $product->production_cost;
        $totalProductionCost = $productionCostPerUnit * $productBatch->quantity_total;

        // Общая себестоимость
        $totalCost = $totalIngredientsCost + $totalProductionCost;

        $costData = [
            'product_name' => $product->name,
            'quantity_total' => $productBatch->quantity_total,
            'production_cost_per_unit' => $productionCostPerUnit,
            'ingredients' => $ingredientsData,
            'total_ingredients_cost' => $totalIngredientsCost,
            'total_production_cost' => $totalProductionCost,
            'total_cost' => $totalCost
        ];

        $costDetailsHtml = view('admin.ingredient_movements.cost_detail', [
            'costData' => $costData
        ])->render();

        return response()->json([
            'success' => true,
            'costDetails' => $costDetailsHtml
        ]);
    }

    /**
     * Проверка, доступна ли дата для редактирования
     *
     * @param string $date
     * @return bool
     */
    private function isDateEditable(string $date): bool
    {
        $today = now();
        $recordDate = Carbon::parse($date);

        // Для неадминистраторов - только сегодняшний день
        if (!auth()->user()->hasRole('admin')) {
            return $recordDate->format('Y-m-d') === $today->format('Y-m-d');
        }

        // Для администраторов - последние 10 дней
        return $recordDate->diffInDays($today) <= 10;
    }
}
