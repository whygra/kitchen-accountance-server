<?php

namespace App\Exports\Sheets;

use App\Exports\ProjectExport;
use App\Models\Distributor\PurchaseOption;
use App\Models\Ingredient\Ingredient;
use App\Models\Product\Product;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class PurchaseOptionsSheet implements FromCollection, WithTitle, WithMapping
{
    private $project_id;

    public function __construct(int $id) {
        $this->project_id = $id;
    }
    
    public function collection()
    {
        $project_id = $this->project_id;

        return PurchaseOption::whereHas(
            'distributor', 
            function (Builder $query) use($project_id) {
                $query->where('project_id', $project_id);
            }
        )->with(['distributor', 'unit'])->get();
    }

    /**
    * @param PurchaseOption $purchase_option
    */
    public function map($purchase_option): array
    {
        return [
            [
                $purchase_option->code,
                $purchase_option->name,
                $purchase_option->net_weight,
                $purchase_option->price,
                $purchase_option->distributor->name,
                $purchase_option->unit?->long??''
            ]
        ];
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return ProjectExport::PURCHASE_OPTIONS_SHEET_TITLE;
    }
}