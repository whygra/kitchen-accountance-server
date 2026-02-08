<?php

namespace App\Imports\ProjectImport;

use App\Exports\ProjectExport\ProjectExport;
use App\Imports\ProjectImport\SheetImports\DishesImport;
use App\Imports\ProjectImport\SheetImports\DishesIngredientsImport;
use App\Imports\ProjectImport\SheetImports\DishesTagsImport;
use App\Imports\ProjectImport\SheetImports\DishTagsImport;
use App\Imports\ProjectImport\SheetImports\IngredientsIngredientsImport;
use App\Imports\ProjectImport\SheetImports\IngredientsProductsImport;
use App\Imports\ProjectImport\SheetImports\IngredientsImport;
use App\Imports\ProjectImport\SheetImports\IngredientsTagsImport;
use App\Imports\ProjectImport\SheetImports\IngredientTagsImport;
use App\Imports\ProjectImport\SheetImports\ProductsPurchaseOptionsImport;
use App\Imports\ProjectImport\SheetImports\ProductsImport;
use App\Imports\ProjectImport\SheetImports\ProductsTagsImport;
use App\Imports\ProjectImport\SheetImports\ProductTagsImport;
use App\Imports\ProjectImport\SheetImports\PurchaseOptionsImport;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ProjectImport implements WithMultipleSheets
{
    // use Importable;
    private int $project_id = 0;

    public function __construct(int $project_id) {
        $this->project_id = $project_id;
    }

    public function sheets(): array
    {
        return [
            ProjectExport::DISHES_SHEET_TITLE => new DishesImport($this->project_id),
            ProjectExport::INGREDIENTS_SHEET_TITLE => new IngredientsImport($this->project_id),
            ProjectExport::PRODUCTS_SHEET_TITLE => new ProductsImport($this->project_id),
            ProjectExport::PURCHASE_OPTIONS_SHEET_TITLE => new PurchaseOptionsImport($this->project_id),

            ProjectExport::DISHES_INGREDIENTS_SHEET_TITLE => new DishesIngredientsImport($this->project_id),
            ProjectExport::INGREDIENTS_PRODUCTS_SHEET_TITLE => new IngredientsProductsImport($this->project_id),
            ProjectExport::INGREDIENTS_INGREDIENTS_SHEET_TITLE => new IngredientsIngredientsImport($this->project_id),

            ProjectExport::DISH_TAGS_SHEET_TITLE => new DishTagsImport($this->project_id),
            ProjectExport::INGREDIENT_TAGS_SHEET_TITLE => new IngredientTagsImport($this->project_id),
            ProjectExport::PRODUCT_TAGS_SHEET_TITLE => new ProductTagsImport($this->project_id),

            ProjectExport::DISHES_TAGS_SHEET_TITLE => new DishesTagsImport($this->project_id),
            ProjectExport::INGREDIENTS_TAGS_SHEET_TITLE => new IngredientsTagsImport($this->project_id),
            ProjectExport::PRODUCTS_TAGS_SHEET_TITLE => new ProductsTagsImport($this->project_id),
        ];
    }
}