<?php

namespace App\Exports\Sheets;

use App\Exports\ProjectExport;
use App\Models\Distributor\PurchaseOption;
use App\Models\Ingredient\Ingredient;
use App\Models\Ingredient\IngredientProduct;
use App\Models\Product\ProductPurchaseOption;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class ProductPurchaseOptionsSheet implements FromCollection, WithTitle, WithMapping
{
    private $project_id;

    public function __construct(int $id) {
        $this->project_id = $id;
    }

    public function collection()
    {
        $project_id = $this->project_id;

        return ProductPurchaseOption::whereHas(
            'product', 
            function (Builder $query) use($project_id) {
                $query->where('project_id', $project_id);
            }
        )->with(['product', 'purchase_option'])->get();
    }

    /**
    * @param ProductPurchaseOption $product_purchase_option
    */
    public function map($product_purchase_option): array
    {
        return [
            [
                $product_purchase_option->product->name,
                $product_purchase_option->purchase_option->name,
                $product_purchase_option->product_share,
            ]
        ];
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return ProjectExport::PRODUCT_PURCHASE_OPTIONS_SHEET_TITLE;
    }
}