<?php

namespace App\Exports\Sheets;

use App\Exports\ProjectExport;
use App\Models\Dish\Dish;
use App\Models\Distributor\PurchaseOption;
use App\Models\Ingredient\Ingredient;
use App\Models\Project;
use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class DishesSheet implements FromCollection, WithTitle, WithMapping
{
    private $project_id;

    public function __construct(int $id) {
        $this->project_id = $id;
    }
    
    public function collection()
    {
        return Project::find($this->project_id)->dishes()->with(['category', 'group'])->get();
    }

    /**
    * @param Dish $dish
    */
    public function map($dish): array
    {
        return [
            [
                $dish->name,
                $dish->category?->name,
                $dish->group?->name,
                $dish->description,
            ]
        ];
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return ProjectExport::DISHES_SHEET_TITLE;
    }
}