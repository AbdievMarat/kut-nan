<?php

namespace App\Exports;

use App\Models\Ingredient;
use App\Models\IngredientUsage;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class IngredientMovementExport implements FromArray, WithHeadings, WithEvents
{
    protected $dateFrom;
    protected $dateTo;

    public function __construct($dateFrom, $dateTo)
    {
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
    }

    public function array(): array
    {
        $ingredients = Ingredient::query()
            ->where('is_active', Ingredient::IS_ACTIVE)
            ->orderBy('sort')
            ->get();

        // Получаем записи движения ингредиентов с фильтрацией по датам
        $ingredientUsages = IngredientUsage::query()
            ->with('ingredient')
            ->whereBetween('date', [$this->dateFrom, $this->dateTo])
            ->orderByDesc('date')
            ->get()
            ->groupBy('date');

        // Формируем структуру данных для таблицы
        $tableData = [];
        foreach ($ingredientUsages as $date => $usages) {
            $tableData[$date] = [];

            foreach ($usages as $usage) {
                $tableData[$date][$usage->ingredient_id] = [
                    'income' => $usage->income,
                    'usage' => $usage->usage,
                    'stock' => $usage->stock,
                ];
            }
        }

        $data = [];

        // Добавляем заголовки подстолбцов как вторую строку
        $subHeaders = [''];
        foreach ($ingredients as $ingredient) {
            $subHeaders[] = 'Приход';
            $subHeaders[] = 'Расход';
            $subHeaders[] = 'Остаток';
        }
        $data[] = $subHeaders;

        // Добавляем данные
        foreach ($tableData as $date => $ingredientsData) {
            $row = [date('d.m.Y', strtotime($date))];

            foreach ($ingredients as $ingredient) {
                $ingredientData = $ingredientsData[$ingredient->id] ?? null;

                $row[] = $ingredientData && isset($ingredientData['income']) && $ingredientData['income'] > 0 ? number_format($ingredientData['income'], 2) : '';
                $row[] = $ingredientData && isset($ingredientData['usage']) && $ingredientData['usage'] > 0 ? number_format($ingredientData['usage'], 2) : '';
                $row[] = $ingredientData && isset($ingredientData['stock']) ? number_format($ingredientData['stock'], 2) : '';
            }

            $data[] = $row;
        }

        return $data;
    }

    public function headings(): array
    {
        $ingredients = Ingredient::query()
            ->where('is_active', Ingredient::IS_ACTIVE)
            ->orderBy('sort')
            ->get();

        $headings = ['Дата'];

        foreach ($ingredients as $ingredient) {
            $headings[] = $ingredient->name . ' (' . $ingredient->unit . ')';
            $headings[] = '';
            $headings[] = '';
        }

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

                if ($ingredientCount === 0) {
                    return;
                }

                // Объединяем ячейки для названий ингредиентов
                $columnIndex = 2; // Начинаем с колонки B (1=A, 2=B)
                foreach ($ingredients as $ingredient) {
                    $startColumn = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($columnIndex);
                    $endColumn = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($columnIndex + 2);

                    $sheet->mergeCells($startColumn . '1:' . $endColumn . '1');
                    $columnIndex += 3;
                }

                // Объединяем ячейку "Дата" для двух строк заголовков
                $sheet->mergeCells('A1:A2');

                // Стили для заголовков
                $lastColumnIndex = 1 + ($ingredientCount * 3); // A + количество ингредиентов * 3 столбца
                $lastColumn = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($lastColumnIndex);

                // Стиль для первой строки заголовков (названия ингредиентов)
                $sheet->getStyle('A1:' . $lastColumn . '1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ],
                    ],
                ]);

                // Стиль для второй строки заголовков (подзаголовки)
                $sheet->getStyle('A2:' . $lastColumn . '2')->applyFromArray([
                    'font' => [
                        'bold' => true,
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ],
                    ],
                ]);

                // Цветовое кодирование для подзаголовков
                $columnIndex = 2; // Начинаем с колонки B
                foreach ($ingredients as $ingredient) {
                    // Приход - зеленый
                    $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($columnIndex);
                    $sheet->getStyle($columnLetter . '2')->getFill()
                        ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                        ->getStartColor()->setRGB('28a745');
                    $sheet->getStyle($columnLetter . '2')->getFont()->getColor()->setRGB('FFFFFF');

                    // Расход - красный
                    $columnIndex++;
                    $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($columnIndex);
                    $sheet->getStyle($columnLetter . '2')->getFill()
                        ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                        ->getStartColor()->setRGB('dc3545');
                    $sheet->getStyle($columnLetter . '2')->getFont()->getColor()->setRGB('FFFFFF');

                    // Остаток - синий
                    $columnIndex++;
                    $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($columnIndex);
                    $sheet->getStyle($columnLetter . '2')->getFill()
                        ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                        ->getStartColor()->setRGB('17a2b8');
                    $sheet->getStyle($columnLetter . '2')->getFont()->getColor()->setRGB('FFFFFF');

                    $columnIndex++;
                }

                // Автоширина столбцов
                for ($i = 1; $i <= $lastColumnIndex; $i++) {
                    $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i);
                    $sheet->getColumnDimension($columnLetter)->setAutoSize(true);
                }

                // Заморозка заголовков
                $sheet->freezePane('A3');
            },
        ];
    }
}
