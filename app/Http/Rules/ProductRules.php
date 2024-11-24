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

    public static function productCategoryRules(int $projectId) {
        return [
            'category.id'=>'required',
            'category.name'=>[
                'nullable',
                'exclude_unless:category.id,0',
                'string',
                'max:60',
                Rule::unique('product_categories', 'name')
                    ->where('project_id', $projectId),
            ]
        ];
    }

    public static function productGroupRules(int $projectId) {
        return [
            'group.id'=>'required',
            'group.name'=>[
                'nullable',
                'exclude_unless:group.id,0',
                'string',
                'max:60',
                Rule::unique('product_groups', 'name')
                    ->where('project_id', $projectId),
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
            'purchase_options.*.product_share'=>'required|numeric|min:1|max:100',
        ];
    }

}