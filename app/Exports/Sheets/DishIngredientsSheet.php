<?php

namespace App\Exports\Sheets;

use App\Exports\ProjectExport;
use App\Models\Dish\DishIngredient;
use App\Models\Distributor\Unit;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class DishIngredientsSheet implements FromCollection, WithTitle, WithMapping
{
    private $project_id;

    public function __construct(int $id) {
        $this->project_id = $id;
    }

    public function collection()
    {
        $project_id = $this->project_id;

        return DishIngredient::whereHas(
            'dish', 
            function (Builder $query) use($project_id) {
                $query->where('project_id', $project_id);
            }
        )->with(['dish', 'ingredient'])->get();
    }

    /**
    * @param DishIngredient $dish_ingredient
    */
    public function map($dish_ingredient): array
    {
        return [
            [
                $dish_ingredient->dish->name,
                $dish_ingredient->ingredient->name,
                $dish_ingredient->ingredient_amount,
                $dish_ingredient->waste_percentage,
            ]
        ];
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return ProjectExport::DISH_INGREDIENTS_SHEET_TITLE;
    }
}