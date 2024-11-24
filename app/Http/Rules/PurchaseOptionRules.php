<?php
namespace App\Http\Rules;

use Illuminate\Database\Query\Builder;
use Illuminate\Validation\Rule;
use ProjectRules;

class PurchaseOptionRules {
    
    static public function getStorePurchaseOptionRules(int $distributorId, int $projectId) {
        return [
            'distributor.id'=>[
                'required',
                Rule::exists('distributors', 'id')
                    ->where('project_id', $projectId)
            ],
            'name' => [
                'required',
                'string',
                'max:120',
                Rule::unique('purchase_options', 'name')->where('distributor_id', $distributorId),
            ],
            'net_weight'=>'required|numeric|min:1',
            'price'=>'required|numeric|min:0',
        ];
    }
    static public function getUpdatePurchaseOptionRules(int $distributorId, int $id) {
        return [
            'id'=> [
                'required',
                Rule::exists('purchase_options', 'id')
                    ->where('distributor_id', $distributorId),
            ],
            'name'=>[
                'required',
                'string',
                'max:120',
                Rule::unique('purchase_options', 'name')
                    ->where('distributor_id', $distributorId)
                    ->ignore($id),
            ],
            'net_weight'=>'required|numeric|min:1',
            'price'=>'required|numeric|min:0',
        ];
    }

    public static function purchaseOptionUnitRules(int $projectId){
        return [
            'unit.id'=>'required',
            'unit.long'=>[
                'exclude_unless:purchase_options.unit.id,0',
                'string',
                'max:60',
                Rule::unique('units', 'long')
                    ->where('project_id', $projectId),
            ],
            'unit.short'=>[
                'exclude_unless:purchase_options.unit.id,0',
                'string',
                'max:6',
                Rule::unique('units', 'short')
                    ->where('project_id', $projectId),
            ]
        ];
    }

    public static function purchaseOptionProductsRules(int $projectId) {
        return [
            'products'=>'nullable|array',
            'products.*.id'=>'required',
            'products.*.product_share'=>'required|numeric|min:1|max:100',
            'products.*.name'=>[
                'exclude_unless:products.*.id,0',
                'string',
                'max:60',
                Rule::unique('products', 'name')
                    ->where('project_id', $projectId),
                'distinct:ignore_case',
            ]
        ];
    }

}