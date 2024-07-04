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

    public function __construct($date)
    {
        $this->date = $date;
    }

    public function query()
    {
        return Bus::with(['orders' => function ($query) {
            $query->whereDate('date', $this->date);
        }])->orderBy('sort');
    }

    public function headings(): array
    {
        return [
            'Автобус',
            'Москва',
            'Москва уп',
            'Солдат',
            'Отруб',
            'Налив',
            'Тостер',
            'Тостер кара',
            'Мини тостер',
            'Гречневый',
            'Зерновой',
            'Багет',
            'Без дрожж',
            'Чемпион',
            'Абсолют',
            'Кукурузный',
            'Уп. Бород',
            'Уп. Батон отруб',
            'Уп. Батон серый',
            'Уп. Батон белый',
            'Баатыр',
            'Обама отруб',
            'Обама ржан',
            'Обама серый',
            'Уп. Моск',
            'Гамбургер',
            'Тартин',
            'Тартин зерновой',
            'Тартин ржаной',
            'Тартин с луком',
        ];
    }

    /**
     * @param $row
     * @return array
     */
    public function map($row): array
    {
        $mapped = [];

        if ($row->orders->isEmpty()) {
            $mapped[] = [
                $row->license_plate . ' ' . $row->serial_number,
                null, null, null, null, null, null, null, null, null,
                null, null, null, null, null, null, null, null, null, null,
                null, null, null, null, null, null, null, null, null,
            ];
        } else {
            foreach ($row->orders as $order) {
                $mapped[] = [
                    $row->license_plate . ' ' . $row->serial_number,
                    $order->product_1,
                    $order->product_2,
                    $order->product_3,
                    $order->product_4,
                    $order->product_5,
                    $order->product_6,
                    $order->product_7,
                    $order->product_8,
                    $order->product_9,
                    $order->product_10,
                    $order->product_11,
                    $order->product_12,
                    $order->product_13,
                    $order->product_14,
                    $order->product_15,
                    $order->product_16,
                    $order->product_17,
                    $order->product_18,
                    $order->product_19,
                    $order->product_20,
                    $order->product_21,
                    $order->product_22,
                    $order->product_23,
                    $order->product_24,
                    $order->product_25,
                    $order->product_26,
                    $order->product_27,
                    $order->product_28,
                    $order->product_29,
                ];
            }
        }

        return $mapped;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $sheet->getStyle('A1:AD1')->applyFromArray([
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
            },
        ];
    }
}
