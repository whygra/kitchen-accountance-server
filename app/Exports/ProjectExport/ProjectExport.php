<?php

namespace App\Exports\ProjectExport;

use App\Exports\ProjectExport\Sheets\DishesSheet;
use App\Exports\ProjectExport\Sheets\DishesTagsSheet;
use App\Exports\ProjectExport\Sheets\DishesIngredientsSheet;
use App\Exports\ProjectExport\Sheets\DishTagsSheet;
use App\Exports\ProjectExport\Sheets\IngredientsIngredientsSheet;
use App\Exports\ProjectExport\Sheets\IngredientsProductsSheet;
use App\Exports\ProjectExport\Sheets\IngredientsSheet;
use App\Exports\ProjectExport\Sheets\IngredientsTagsSheet;
use App\Exports\ProjectExport\Sheets\IngredientTagsSheet;
use App\Exports\ProjectExport\Sheets\ProductsPurchaseOptionsSheet;
use App\Exports\ProjectExport\Sheets\ProductsSheet;
use App\Exports\ProjectExport\Sheets\ProductsTagsSheet;
use App\Exports\ProjectExport\Sheets\ProductTagsSheet;
use App\Exports\ProjectExport\Sheets\PurchaseOptionsSheet;
use App\Models\Dish\Dish;
use App\Models\Distributor\Distributor;
use App\Models\Distributor\PurchaseOption;
use App\Models\Ingredient\Ingredient;
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
    public const INGREDIENT_TAGS_SHEET_TITLE = 'IngredientTags';
    public const DISH_TAGS_SHEET_TITLE = 'DishTags';
    public const PRODUCT_TAGS_SHEET_TITLE = 'ProductTags';
    public const INGREDIENTS_TAGS_SHEET_TITLE = 'IngredientsTags';
    public const DISHES_TAGS_SHEET_TITLE = 'DishesTags';
    public const PRODUCTS_TAGS_SHEET_TITLE = 'ProductsTags';
    public const PURCHASE_OPTIONS_SHEET_TITLE = 'PurchaseOptions';
    public const DISHES_INGREDIENTS_SHEET_TITLE = 'DishesIngredients';
    public const INGREDIENTS_PRODUCTS_SHEET_TITLE = 'IngredientsProducts';
    public const INGREDIENTS_INGREDIENTS_SHEET_TITLE = 'IngredientsIngredients';
    
    /**
     * @return array
     */
    public function sheets(): array
    {
        $sheets = [
            
            new ProductsSheet($this->project_id),
            new IngredientsSheet($this->project_id),
            new DishesSheet($this->project_id),
            new PurchaseOptionsSheet($this->project_id),
            
            new DishesIngredientsSheet($this->project_id),
            new IngredientsIngredientsSheet($this->project_id),
            new IngredientsProductsSheet($this->project_id),

            new ProductTagsSheet($this->project_id),
            new IngredientTagsSheet($this->project_id),
            new DishTagsSheet($this->project_id),

            new ProductsTagsSheet($this->project_id),
            new IngredientsTagsSheet($this->project_id),
            new DishesTagsSheet($this->project_id),
        ];

        return $sheets;
    }
}