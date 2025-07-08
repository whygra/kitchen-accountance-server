<?php
namespace App\Http\Rules;

use Illuminate\Database\Query\Builder;
use Illuminate\Validation\Rule;
use ProjectRules;

class SaleActRules {
    
    public static function storeSaleActRules() {
        return [
            'date' => 'required|date',
        ];
    }

    public static function getUpdateSaleActRules(int $projectId) {
        return [
            'id'=>[
                'required',
                Rule::exists('sale_acts', 'id')
                    ->where('project_id', $projectId),
            ],
            'date' => 'required|date',
        ];
    }

    public static function saleActItemsRules(int $projectId, int $id) {
        return [
            'items'=>'nullable|array',
            'items.*.id'=>[
                'exclude_if:items.*.id,0',
                'distinct',
                'required',
                Rule::exists('dishes', 'id')
                    ->where('project_id', $projectId),
            ],
            'items.*.amount'=>'required|numeric|min:1',
            'items.*.price'=>'required|numeric|min:0',
            'items.*.name'=>[
                'exclude_unless:items.*.id,0',
                'string',
                'max:60',
                'distinct:ignore_case',
                Rule::unique('dishes', 'name')
                    ->where('project_id', $projectId)
                    ->ignore($id),
            ]
        ];
    }   

}