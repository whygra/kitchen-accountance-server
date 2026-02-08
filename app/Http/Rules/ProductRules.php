<?php
namespace App\Http\Rules;

use Illuminate\Database\Query\Builder;
use Illuminate\Validation\Rule;
use ProjectRules;

class ProductRules {
    
    public static function storeProductRules(int $projectId) {
        return [
            'name'=>[
                'required',
                'string',
                'max:60',
                Rule::unique('products', 'name')
                    ->where('project_id', $projectId),
            ],
        ];
    }

    public static function getUpdateProductRules(int $id, int $projectId) {
        return [
            'id'=>[
                'required',
                Rule::exists('products', 'id')
                    ->where('project_id', $projectId),
            ], 
            'name'=>[
                'required',
                'string',
                'max:60',
                Rule::unique('products', 'name')
                    ->where('project_id', $projectId)
                    ->ignore($id),
            ],
        ];
    }

    public static function productTagsRules(int $projectId) {
        return [
            'tags'=>'nullable|array',
            'tags.*.id'=>[
                'required',
                'exclude_if:tags.*.id,0',
                'distinct',
                Rule::exists('product_tags')->where(function(Builder $query) use($projectId){
                    return $query->where('project_id', $projectId);
                })
            ],
            'tags.*.name'=>[
                'exclude_unless:tags.*.id,null',
                'string',
                'max:60',
                Rule::unique('product_tags', 'name')
                    ->where('project_id', $projectId),
                'distinct:ignore_case',
            ],
        ];
    }

    public static function productPurchaseOption(int $projectId) {
        return [
            'purchase_options'=>'nullable|array',
            'purchase_options.*.id'=>[
                'required',
                Rule::exists('purchase_options', 'id')
                    // ->where('project_id', $projectId)
                    ,
                'distinct',
            ],
            // 'purchase_options.*.product_share'=>'required|numeric|min:1|max:100',
        ];
    }

}