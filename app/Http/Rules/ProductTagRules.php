<?php
namespace App\Http\Rules;

use Illuminate\Database\Query\Builder;
use Illuminate\Validation\Rule;
use ProjectRules;

class ProductTagRules {

    public static function storeProductTagRules(int $projectId) {
        return [
            'name'=>[
                'required',
                'string',
                'max:60',
                Rule::unique('product_tags', 'name')
                    ->where('project_id', $projectId),
            ],
        ];
    }

    public static function productTagProductsRules(int $projectId) { 
        return [
            'products'=>'nullable|array',
            'products.*.id'=>'required',
            'products.*.gross_weight'=>'required|numeric|min:0.01',
            'products.*.net_weight'=>'required|numeric|min:0',
            'products.*.name'=>[
                'exclude_unless:products.*.id,0',
                'string',
                'max:60',
                'distinct:ignore_case',
                Rule::unique('products', 'name')
                    ->where('project_id', $projectId),
            ]
        ];
    }


    public static function getUpdateProductTagRules(int $id, int $projectId) {
        return [
            
            'id'=>[
                'required',
                Rule::exists('product_tags', 'id')
                    ->where('project_id', $projectId),
            ],
            'name'=>[
                'required',
                'string',
                'max:60',
                Rule::unique('product_tags', 'name')
                    ->where('project_id', $projectId)
                    ->ignore($id),
            ],
        ];
    }

}