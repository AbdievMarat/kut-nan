<?php

namespace App\Exports;

use App\Models\Bus;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class OrderExport implements FromQuery, WithHeadings, WithMapping, WithEvents
{
    protected $date;
    protected $products;
    protected $sumMarkdowns;
    protected $sumRealizations;
    protected $sumRemainders;

    public function __construct($date, $products, $sumMarkdowns, $sumRealizations, $sumRemainders)
    {
        $this->date = $date;
        $this->products = $products;
        $this->sumMarkdowns = $sumMarkdowns;
        $this->sumRealizations = $sumRealizations;
        $this->sumRemainders = $sumRemainders;
    }

    public function query()
    {
        return Bus::query()
            ->with([
                'orders' => function ($query) {
                    $query->whereDate('date', $this->date)
                        ->with(['items']);
                }
            ])
            ->where('is_active', '=', Bus::IS_ACTIVE)
            ->orderBy('sort');
    }

    public function headings(): array
    {
        $productNames = $this->products->pluck('name')->toArray();

        return array_merge(
            ['Автобус'],
            $productNames,
            ['Сумма', 'Уценка', 'Реализации', 'Остаток']
        );
    }

    /**
     * @param $row
     * @return array
     */
    public function map($row): array
    {
        $orderAmounts = [];

        // Подсчет количества продуктов в заказах
        foreach ($row->orders as $order) {
            foreach ($order->items as $item) {
                $orderAmounts[$item->product_id] = $item->amount;
            }
        }

        // Формирование строки данных
        $mappedRow = [
            $row->license_plate . ' ' . $row->serial_number,
        ];

        // Добавляем количество для каждого продукта
        foreach ($this->products as $product) {
            $mappedRow[] = $orderAmounts[$product->id] ?? '';
        }

        // Добавляем сумму реализаций и остатков
        $mappedRow[] = ''; // Сумма
        $mappedRow[] = $this->sumMarkdowns[$row->id] ?? '';
        $mappedRow[] = $this->sumRealizations[$row->id] ?? '';
        $mappedRow[] = $this->sumRemainders[$row->id] ?? '';

        return $mappedRow;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $sheet->getStyle('A1:AH1')->applyFromArray([
                    'alignment' => [
                        'textRotation' => 90,
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                    ],
                ]);

                // Устанавливаем высоту строки заголовков
                $sheet->getRowDimension(1)->setRowHeight(100);

                // Устанавливаем ширину столбцов вручную
                $sheet->getColumnDimension('A')->setWidth(15);
                $sheet->getColumnDimension('B')->setWidth(5);
                $sheet->getColumnDimension('C')->setWidth(5);
                $sheet->getColumnDimension('D')->setWidth(5);
                $sheet->getColumnDimension('E')->setWidth(5);
                $sheet->getColumnDimension('F')->setWidth(5);
                $sheet->getColumnDimension('G')->setWidth(5);
                $sheet->getColumnDimension('H')->setWidth(5);
                $sheet->getColumnDimension('I')->setWidth(5);
                $sheet->getColumnDimension('J')->setWidth(5);
                $sheet->getColumnDimension('K')->setWidth(5);
                $sheet->getColumnDimension('L')->setWidth(5);
                $sheet->getColumnDimension('M')->setWidth(5);
                $sheet->getColumnDimension('N')->setWidth(5);
                $sheet->getColumnDimension('O')->setWidth(5);
                $sheet->getColumnDimension('P')->setWidth(5);
                $sheet->getColumnDimension('Q')->setWidth(5);
                $sheet->getColumnDimension('R')->setWidth(5);
                $sheet->getColumnDimension('S')->setWidth(5);
                $sheet->getColumnDimension('T')->setWidth(5);
                $sheet->getColumnDimension('U')->setWidth(5);
                $sheet->getColumnDimension('V')->setWidth(5);
                $sheet->getColumnDimension('W')->setWidth(5);
                $sheet->getColumnDimension('X')->setWidth(5);
                $sheet->getColumnDimension('Y')->setWidth(5);
                $sheet->getColumnDimension('Z')->setWidth(5);
                $sheet->getColumnDimension('AA')->setWidth(5);
                $sheet->getColumnDimension('AB')->setWidth(5);
                $sheet->getColumnDimension('AC')->setWidth(5);
                $sheet->getColumnDimension('AD')->setWidth(5);
                $sheet->getColumnDimension('AE')->setWidth(5);
                $sheet->getColumnDimension('AF')->setWidth(5);
            },
        ];
    }
}
