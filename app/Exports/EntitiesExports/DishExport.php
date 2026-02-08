<?php

namespace App\Exports\ProjectExport\ProjectExport\Sheets;

use App\Exports\ProjectExport\ProjectExport;
use App\Models\Dish\Dish;
use App\Models\Project;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DishExport implements WithMapping, WithStyles
{
    private $project_id;

    public function __construct(int $id) {
        $this->project_id = $id;
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1')
            ->applyFromArray(['font' => ['bold' => true]]);
        $sheet->getStyle('A3')
            ->applyFromArray(['font' => ['bold' => true]]);
        $sheet->mergeCells('A3:C3');
        $sheet->mergeCells('A4:C4');
        $sheet->mergeCells('A5:D5');
    }

    /**
    * @param Dish $dish
    */
    public function map($dish): array
    {
        $result = [
            [
                'Наименование',
            ],
            [
                $dish->name,
            ],
            [
                'Описание',
            ],
            [
                $dish->description,
            ],
            [
                'Ингредиенты',
            ],
            [
                'Наименование',
                'Тип',
                'Вес/Количество',
                'Процент отхода',
            ],
        ];

        foreach($dish->ingredients as $i)
            array_push($result, [
                $i->name,
                $i->type->name,
                $i->pivot->amount.($i->is_item_measured?'шт':'г'),
                $i->pivot->net_weight,
            ]);
        
        return $result;
    }

}