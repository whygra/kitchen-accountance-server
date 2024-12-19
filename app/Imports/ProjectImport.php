<?php

namespace App\Imports;

use App\Exports\ProjectExport;
use App\Imports\SheetImports\DishesImport;
use App\Imports\SheetImports\DishIngredientsImport;
use App\Imports\SheetImports\IngredientProductsImport;
use App\Imports\SheetImports\IngredientsImport;
use App\Imports\SheetImports\ProductPurchaseOptionsImport;
use App\Imports\SheetImports\ProductsImport;
use App\Imports\SheetImports\PurchaseOptionsImport;
use App\Models\Distributor\Distributor;
use App\Models\Distributor\PurchaseOption;
use App\Models\Distributor\Unit;
use App\Models\Project;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Symfony\Component\HttpKernel\Exception\HttpException;

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
            ProjectExport::DISH_INGREDIENTS_SHEET_TITLE => new DishIngredientsImport($this->project_id),
            ProjectExport::INGREDIENT_PRODUCTS_SHEET_TITLE => new IngredientProductsImport($this->project_id),
            ProjectExport::PRODUCT_PURCHASE_OPTIONS_SHEET_TITLE => new ProductPurchaseOptionsImport($this->project_id),
        ];
    }
}