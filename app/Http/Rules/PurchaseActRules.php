<?php
namespace App\Http\Rules;

use Illuminate\Database\Query\Builder;
use Illuminate\Validation\Rule;
use ProjectRules;

class PurchaseActRules {
    
    public static function storePurchaseActRules(int $projectId) {
        return [
            'distributor.id'=>[
                'required',
                Rule::exists('distributors', 'id')
                    ->where('project_id', $projectId),
            ],
            'date' => 'required|date',
        ];
    }

    public static function getUpdatePurchaseActRules(int $projectId) {
        return [
            'id'=>[
                'required',
                Rule::exists('purchase_acts', 'id')
                    ->where('project_id', $projectId),
            ],
            'distributor.id'=>[
                'required',
                Rule::exists('distributors', 'id')
                    ->where('project_id', $projectId),
            ],
            'date' => 'required|date',

        ];
    }

    public static function purchaseActItemsRules(int $projectId, int $distributorId, int $id) {
        return [
            'items'=>'nullable|array',
            'items.*.id'=>[
                'distinct',
                'required',
                'exclude_if:items.*.id,0',
                Rule::exists('purchase_options', 'id')
                    ->where('distributor_id', $distributorId),
            ],
            'items.*.amount'=>'required|numeric|min:1',
            'items.*.net_weight'=>'required|numeric|min:1',
            'items.*.price'=>'required|numeric|min:0',

            'items.*.unit.id'=>[
                'exclude_unless:items.*.id,0',
                'required',
                Rule::exists('units', 'id')
                    ->where('project_id', $projectId),
            ],
            'items.*.unit.long'=>[
                'exclude_unless:items.*.id,0',
                'exclude_unless:items.*.unit.id,0',
                'string',
                'max:60',
                'distinct:ignore_case',
                Rule::unique('units', 'long')
                    ->where('project_id', $projectId),
            ],
            'items.*.unit.short'=>[
                'exclude_unless:items.*.id,0',
                'exclude_unless:items.*.unit.id,0',
                'string',
                'max:60',
                'distinct:ignore_case',
                Rule::unique('units', 'short')
                    ->where('project_id', $projectId),
            ],

            'items.*.name'=>[
                'exclude_unless:items.*.id,0',
                'string',
                'max:60',
                'distinct:ignore_case',
                Rule::unique('purchase_options', 'name')
                    ->where('distributor_id', $distributorId)
                    ->ignore($id),
            ]
        ];
    }   

}