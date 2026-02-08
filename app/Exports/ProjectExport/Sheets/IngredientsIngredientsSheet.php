<?php

namespace App\Exports\ProjectExport\Sheets;

use App\Exports\ProjectExport\ProjectExport;
use App\Models\Dish\DishIngredient;
use App\Models\Distributor\Unit;
use App\Models\Ingredient\IngredientIngredient;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class IngredientsIngredientsSheet implements FromCollection, WithTitle, WithMapping
{
    private $project_id;

    public function __construct(int $id) {
        $this->project_id = $id;
    }

    public function collection()
    {
        $project_id = $this->project_id;

        return IngredientIngredient::whereHas(
            'includer', 
            function (Builder $query) use($project_id) {
                $query->where('project_id', $project_id);
            }
        )->with(['includer', 'included'])->get();
    }

    /**
    * @param IngredientIngredient $ingredient_ingredient
    */
    public function map($ingredient_ingredient): array
    {
        return [
            [
                $ingredient_ingredient->includer->name,
                $ingredient_ingredient->included->name,
                $ingredient_ingredient->amount,
                $ingredient_ingredient->net_weight,

                // $ingredient_ingredient->waste_percentage,
            ]
        ];
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return ProjectExport::INGREDIENTS_INGREDIENTS_SHEET_TITLE;
    }
}