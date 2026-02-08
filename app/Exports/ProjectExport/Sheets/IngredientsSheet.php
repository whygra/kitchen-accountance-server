<?php

namespace App\Exports\ProjectExport\Sheets;

use App\Exports\ProjectExport\ProjectExport;
use App\Models\Distributor\PurchaseOption;
use App\Models\Ingredient\Ingredient;
use App\Models\Project;
use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class IngredientsSheet implements FromCollection, WithTitle, WithMapping
{
    private $project_id;

    public function __construct(int $id) {
        $this->project_id = $id;
    }
    public function collection()
    {
        return Project::find($this->project_id)->ingredients()->with(['type'])->get();
    }

    /**
    * @param Ingredient $ingredient
    */
    public function map($ingredient): array
    {
        return [
            [
                $ingredient->name,
                $ingredient->type->name,
                $ingredient->is_item_measured,
                $ingredient->item_weight,
                $ingredient->description,
                $ingredient->total_gross_weight,
                $ingredient->total_net_weight,
            ]
        ];
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return ProjectExport::INGREDIENTS_SHEET_TITLE;
    }
}