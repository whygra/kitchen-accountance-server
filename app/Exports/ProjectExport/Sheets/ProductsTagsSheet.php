<?php

namespace App\Exports\ProjectExport\Sheets;

use App\Exports\ProjectExport\ProjectExport;
use App\Models\Dish\DishProduct;
use App\Models\Distributor\Unit;
use App\Models\Product\ProductProduct;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class ProductsTagsSheet implements FromCollection, WithTitle, WithMapping
{
    private $project_id;

    public function __construct(int $id) {
        $this->project_id = $id;
    }

    public function collection()
    {
        $project_id = $this->project_id;

        return DB::table('products_tags')
            ->join('products', 'products_tags.product_id', 'products.id')
            ->join('product_tags', 'products_tags.tag_id', 'product_tags.id')
            ->where('product_tags.project_id', $project_id)
            ->select(['products.name as product', 'product_tags.name as tag'])
            ->get();
    }

    /**
    * @param Collection $product_tag
    */
    public function map($product_tag): array
    {
        return [
            [
                $product_tag['product'],
                $product_tag['tag'],
            ]
        ];
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return ProjectExport::PRODUCTS_TAGS_SHEET_TITLE;
    }
}