<?php

namespace App\Exports;

use App\Exports\Sheets\DishExport;
use App\Exports\Sheets\DishIngredientsSheet;
use App\Exports\Sheets\IngredientProductsSheet;
use App\Exports\Sheets\IngredientsSheet;
use App\Exports\Sheets\ProductPurchaseOptionsSheet;
use App\Exports\Sheets\ProductsSheet;
use App\Exports\Sheets\PurchaseOptionsSheet;
use App\Models\Dish\Dish;
use App\Models\Distributor\Distributor;
use App\Models\Distributor\PurchaseOption;
use App\Models\User;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ProjectExport implements WithMultipleSheets
{
    use Exportable;

    private $project_id;

    public function __construct(int $id) {
        $this->project_id = $id;
    }

    public const INGREDIENTS_SHEET_TITLE = 'Ingredients';
    public const DISHES_SHEET_TITLE = 'Dishes';
    public const PRODUCTS_SHEET_TITLE = 'Products';
    public const PURCHASE_OPTIONS_SHEET_TITLE = 'PurchaseOptions';
    public const DISH_INGREDIENTS_SHEET_TITLE = 'DishIngredients';
    public const INGREDIENT_PRODUCTS_SHEET_TITLE = 'IngredientProducts';
    public const PRODUCT_PURCHASE_OPTIONS_SHEET_TITLE = 'ProductPurchaseOptions';
    
    /**
     * @return array
     */
    public function sheets(): array
    {
        $sheets = [
            new ProductsSheet($this->project_id),
            new IngredientsSheet($this->project_id),
            new DishExport($this->project_id),
            new PurchaseOptionsSheet($this->project_id),

            new DishIngredientsSheet($this->project_id),
            new IngredientProductsSheet($this->project_id),
            new ProductPurchaseOptionsSheet($this->project_id),
        ];

        return $sheets;
    }
}