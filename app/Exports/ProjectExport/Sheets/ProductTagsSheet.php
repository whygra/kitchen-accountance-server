<?php

namespace App\Exports\ProjectExport\Sheets;

use App\Exports\ProjectExport\ProjectExport;
use App\Models\Product\ProductTag;
use App\Models\Distributor\PurchaseOption;
use App\Models\Product\Product;
use App\Models\Project;
use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class ProductTagsSheet implements FromCollection, WithTitle, WithMapping
{
    private $project_id;

    public function __construct(int $id) {
        $this->project_id = $id;
    }
    
    public function collection()
    {
        return Project::find($this->project_id)->product_tags()->get();
    }

    /**
    * @param ProductTag $product_tag
    */
    public function map($product_tag): array
    {
        return [
            [
                $product_tag->name,
            ]
        ];
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return ProjectExport::PRODUCT_TAGS_SHEET_TITLE;
    }
}