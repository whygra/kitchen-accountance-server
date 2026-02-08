<?php

namespace App\Exports\ProjectExport\Sheets;

use App\Exports\ProjectExport\ProjectExport;
use App\Models\Dish\DishIngredient;
use App\Models\Distributor\Unit;
use App\Models\Ingredient\IngredientIngredient;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class IngredientsTagsSheet implements FromCollection, WithTitle, WithMapping
{
    private $project_id;

    public function __construct(int $id) {
        $this->project_id = $id;
    }

    public function collection()
    {
        $project_id = $this->project_id;

        return DB::table('ingredients_tags')
            ->join('ingredients', 'ingredients_tags.ingredient_id', 'ingredients.id')
            ->join('ingredient_tags', 'ingredients_tags.tag_id', 'ingredient_tags.id')
            ->where('ingredient_tags.project_id', $project_id)
            ->select(['ingredients.name as ingredient', 'ingredient_tags.name as tag'])
            ->get();
    }

    /**
    * @param Collection $ingredient_tag
    */
    public function map($ingredient_tag): array
    {
        return [
            [
                $ingredient_tag['ingredient'],
                $ingredient_tag['tag'],
            ]
        ];
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return ProjectExport::INGREDIENTS_TAGS_SHEET_TITLE;
    }
}