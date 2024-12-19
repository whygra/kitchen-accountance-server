<?php

namespace App\Exports\Sheets;

use App\Exports\ProjectExport;
use App\Models\Distributor\PurchaseOption;
use App\Models\Ingredient\Ingredient;
use App\Models\Product\Product;
use App\Models\Project;
use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class ProductsSheet implements FromCollection, WithTitle, WithMapping
{

    private $project_id;

    public function __construct(int $id) {
        $this->project_id = $id;
    }

    public function collection()
    {
        return Project::find($this->project_id)->products()->with(['category', 'group'])->get();
    }

    /**
    * @param Product $product
    */
    public function map($product): array
    {
        return [
            [
                $product->name,
                $product->category?->name,
                $product->group?->name,
            ]
        ];
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return ProjectExport::PRODUCTS_SHEET_TITLE;
    }
}