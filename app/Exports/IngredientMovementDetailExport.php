<?php

namespace App\Exports;

use App\Models\Ingredient;
use App\Models\IngredientUsage;
use App\Models\ProductBatch;
use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class IngredientMovementDetailExport implements FromArray, WithHeadings, WithEvents
{
    protected $date;

    public function __construct($date)
    {
        $this->date = $date;
    }

    public function array(): array
    {
        $ingredients = Ingredient::query()
            ->where('is_active', Ingredient::IS_ACTIVE)
            ->orderBy('sort')
            ->get();

        // Получаем производственные партии за указанную дату
        $productBatches = ProductBatch::query()
            ->with([
                'product',
                'productBatchIngredients.ingredient'
            ])
            ->where('date', $this->date)
            ->whereHas('product', function ($query) {
                $query->where('is_active', Product::IS_ACTIVE);
            })
            ->get();

        // Получаем приход ингредиентов за указанную дату
        $ingredientUsages = IngredientUsage::query()
            ->where('date', '=', $this->date)
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

            foreach ($batch->productBatchIngredients as $batchIngredient) {
                $tableData[$batch->product_id]['ingredients'][$batchIngredient->ingredient_id] = $batchIngredient->amount;
            }
        }

        $data = [];

        // Добавляем данные по продуктам
        foreach ($tableData as $productId => $productData) {
            $row = [
                $productData['product']->name,
                number_format($productData['quantity_cart'], 2),
                $productData['quantity_total']
            ];

            foreach ($ingredients as $ingredient) {
                $ingredientAmount = $productData['ingredients'][$ingredient->id] ?? null;
                $row[] = $ingredientAmount ? number_format($ingredientAmount, 2) : '';
            }

            // Рассчитываем себестоимость продукта
            $ingredientsCost = 0;
            foreach ($ingredients as $ingredient) {
                if (isset($productData['ingredients'][$ingredient->id])) {
                    $ingredientsCost += $productData['ingredients'][$ingredient->id] * $ingredient->price;
                }
            }
            $productionCost = $productData['product']->production_cost * $productData['quantity_total'];
            $totalCost = $ingredientsCost + $productionCost;

            $row[] = $totalCost > 0 ? number_format($totalCost, 2) : '';

            $data[] = $row;
        }

        // Пустая строка для разделения
        $emptyRow = array_fill(0, 4 + count($ingredients), '');
        $data[] = $emptyRow;

        // Расход на хлеб - итого
        $totalRow = ['ИТОГО:', '', ''];
        $totalCarts = array_sum(array_column($tableData, 'quantity_cart'));
        $totalPieces = array_sum(array_column($tableData, 'quantity_total'));
        $totalRow[1] = number_format($totalCarts, 2);
        $totalRow[2] = $totalPieces;

        foreach ($ingredients as $ingredient) {
            $totalUsage = 0;
            foreach ($tableData as $productData) {
                if (isset($productData['ingredients'][$ingredient->id])) {
                    $totalUsage += $productData['ingredients'][$ingredient->id];
                }
            }
            $totalRow[] = $totalUsage > 0 ? number_format($totalUsage, 2) : '';
        }

        // Рассчитываем общую себестоимость всех продуктов
        $totalCostAllProducts = 0;
        foreach ($tableData as $productData) {
            $ingredientsCost = 0;
            foreach ($ingredients as $ingredient) {
                if (isset($productData['ingredients'][$ingredient->id])) {
                    $ingredientsCost += $productData['ingredients'][$ingredient->id] * $ingredient->price;
                }
            }
            $productionCost = $productData['product']->production_cost * $productData['quantity_total'];
            $totalCostAllProducts += $ingredientsCost + $productionCost;
        }
        $totalRow[] = $totalCostAllProducts > 0 ? number_format($totalCostAllProducts, 2) : '';

        $data[] = $totalRow;

        // Пустая строка
        $data[] = $emptyRow;

        // ПРОЧИЕ РАСХОДЫ
        $otherExpensesHeader = ['ПРОЧИЕ РАСХОДЫ'];
        // Заполняем оставшиеся столбцы пустыми значениями
        for ($i = 1; $i < 4 + count($ingredients); $i++) {
            $otherExpensesHeader[] = '';
        }
        $data[] = $otherExpensesHeader;

        // Не хватает
        $missingRow = ['Не хватает:', '', ''];
        foreach ($ingredients as $ingredient) {
            $usage = $ingredientUsages[$ingredient->id] ?? null;
            $missingRow[] = ($usage && $usage->usage_missing > 0) ? number_format($usage->usage_missing, 2) : '';
        }
        $missingRow[] = ''; // Пустая ячейка для колонки себестоимости
        $data[] = $missingRow;

        // Забрали со склада
        $takenRow = ['Забрали со склада:', '', ''];
        foreach ($ingredients as $ingredient) {
            $usage = $ingredientUsages[$ingredient->id] ?? null;
            $takenRow[] = ($usage && $usage->usage_taken_from_stock > 0) ? number_format($usage->usage_taken_from_stock, 2) : '';
        }
        $takenRow[] = ''; // Пустая ячейка для колонки себестоимости
        $data[] = $takenRow;

        // Кухня
        $kitchenRow = ['Кухня:', '', ''];
        foreach ($ingredients as $ingredient) {
            $usage = $ingredientUsages[$ingredient->id] ?? null;
            $kitchenRow[] = ($usage && $usage->usage_kitchen > 0) ? number_format($usage->usage_kitchen, 2) : '';
        }
        $kitchenRow[] = ''; // Пустая ячейка для колонки себестоимости
        $data[] = $kitchenRow;

        // Пустая строка
        $data[] = $emptyRow;

        // ИТОГО РАСХОД
        $totalExpensesHeader = ['ИТОГО РАСХОД'];
        // Заполняем оставшиеся столбцы пустыми значениями
        for ($i = 1; $i < 4 + count($ingredients); $i++) {
            $totalExpensesHeader[] = '';
        }
        $data[] = $totalExpensesHeader;

        $grandTotalRow = ['Итого:', '', ''];
        foreach ($ingredients as $ingredient) {
            $totalUsage = 0;
            foreach ($tableData as $productData) {
                if (isset($productData['ingredients'][$ingredient->id])) {
                    $totalUsage += $productData['ingredients'][$ingredient->id];
                }
            }

            $usage = $ingredientUsages[$ingredient->id] ?? null;
            $totalOtherUsage = 0;
            if ($usage) {
                $totalOtherUsage = $usage->usage_missing + $usage->usage_taken_from_stock + $usage->usage_kitchen;
            }

            $grandTotal = $totalUsage + $totalOtherUsage;
            $grandTotalRow[] = $grandTotal > 0 ? number_format($grandTotal, 2) : '';
        }
        $grandTotalRow[] = ''; // Пустая ячейка для колонки себестоимости
        $data[] = $grandTotalRow;

        // Пустая строка
        $data[] = $emptyRow;

        // ПРИХОД
        $incomeHeader = ['ПРИХОД'];
        // Заполняем оставшиеся столбцы пустыми значениями
        for ($i = 1; $i < 4 + count($ingredients); $i++) {
            $incomeHeader[] = '';
        }
        $data[] = $incomeHeader;

        $incomeRow = ['Итого:', '', ''];
        foreach ($ingredients as $ingredient) {
            $usage = $ingredientUsages[$ingredient->id] ?? null;
            $incomeRow[] = ($usage && $usage->income > 0) ? number_format($usage->income, 2) : '';
        }
        $incomeRow[] = ''; // Пустая ячейка для колонки себестоимости
        $data[] = $incomeRow;

        return $data;
    }

    public function headings(): array
    {
        $ingredients = Ingredient::query()
            ->where('is_active', Ingredient::IS_ACTIVE)
            ->orderBy('sort')
            ->get();

        $headings = ['Продукт', 'Тележек', 'Штук'];

        foreach ($ingredients as $ingredient) {
            $headings[] = $ingredient->short_name . ' (' . $ingredient->unit . ')';
        }

        $headings[] = 'Себестоимость';

        return $headings;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $ingredients = Ingredient::query()
                    ->where('is_active', Ingredient::IS_ACTIVE)
                    ->orderBy('sort')
                    ->get();

                $ingredientCount = $ingredients->count();
                $lastColumnIndex = 4 + $ingredientCount; // +1 для колонки себестоимости
                $lastColumn = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($lastColumnIndex);
                $rowCount = $sheet->getHighestRow();

                // Применяем границы ко всей таблице
                $sheet->getStyle('A1:' . $lastColumn . $rowCount)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ],
                    ],
                ]);

                // Стиль для заголовков
                $sheet->getStyle('A1:' . $lastColumn . '1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => [
                            'rgb' => 'E9ECEF'
                        ]
                    ]
                ]);

                // Найдем строки с итогами и разделами для стилизации
                for ($row = 2; $row <= $rowCount; $row++) {
                    $cellValue = $sheet->getCell('A' . $row)->getValue();

                    // Стили для разделов
                    if (in_array($cellValue, ['ПРОЧИЕ РАСХОДЫ', 'ИТОГО РАСХОД', 'ПРИХОД'])) {
                        $sheet->getStyle('A' . $row . ':' . $lastColumn . $row)->applyFromArray([
                            'font' => [
                                'bold' => true,
                                'size' => 12
                            ],
                            'alignment' => [
                                'horizontal' => Alignment::HORIZONTAL_CENTER,
                            ],
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => [
                                    'rgb' => 'D1ECF1'
                                ]
                            ],
                            'borders' => [
                                'allBorders' => [
                                    'borderStyle' => Border::BORDER_THIN,
                                ],
                            ],
                        ]);
                    }

                    // Стили для итоговых строк
                    if (strpos($cellValue, 'ИТОГО:') !== false || strpos($cellValue, 'Итого:') !== false) {
                        $sheet->getStyle('A' . $row . ':' . $lastColumn . $row)->applyFromArray([
                            'font' => [
                                'bold' => true,
                            ],
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => [
                                    'rgb' => 'F8F9FA'
                                ]
                            ],
                            'borders' => [
                                'allBorders' => [
                                    'borderStyle' => Border::BORDER_THIN,
                                ],
                            ],
                        ]);
                    }
                }

                // Автоширина столбцов
                for ($i = 1; $i <= $lastColumnIndex; $i++) {
                    $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i);
                    $sheet->getColumnDimension($columnLetter)->setAutoSize(true);
                }

                // Заморозка заголовков
                $sheet->freezePane('A2');
            },
        ];
    }
}
