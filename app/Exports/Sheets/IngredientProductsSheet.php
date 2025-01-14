<?php

namespace App\Exports\Sheets;

use App\Exports\ProjectExport;
use App\Models\Distributor\PurchaseOption;
use App\Models\Ingredient\Ingredient;
use App\Models\Ingredient\IngredientProduct;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class IngredientProductsSheet implements FromCollection, WithTitle, WithMapping
{
    private $project_id;

    public function __construct(int $id) {
        $this->project_id = $id;
    }

    public function collection()
    {
        $project_id = $this->project_id;

        return IngredientProduct::whereHas(
            'ingredient', 
            function (Builder $query) use($project_id) {
                $query->where('project_id', $project_id);
            }
        )->with(['ingredient', 'product'])->get();
    }

    /**
    * @param IngredientProduct $ingredient_product
    */
    public function map($ingredient_product): array
    {
        return [
            [
                $ingredient_product->ingredient->name,
                $ingredient_product->product->name,
                $ingredient_product->raw_product_weight,
                $ingredient_product->waste_percentage,
            ]
        ];
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return ProjectExport::INGREDIENT_PRODUCTS_SHEET_TITLE;
    }
}