<?php
namespace App\Http\Rules;

use Illuminate\Database\Query\Builder;
use Illuminate\Validation\Rule;
use ProjectRules;

class ProductCategoryRules {

    public static function storeProductCategoryRules(int $projectId) {
        return [
            'name'=>[
                'required',
                'string',
                'max:60',
                Rule::unique('product_categories', 'name')
                    ->where('project_id', $projectId),
            ],
        ];
    }

    public static function productCategoryProductsRules(int $projectId) {
        return [
            'products'=>'array|nullable',
            'products.*.id'=>'required',
            'products.*.name'=>[
                'exclude_unless:products.*.id,0',
                'string',
                'max:60',
                Rule::unique('products', 'name')
                    ->where('project_id', $projectId),
                'distinct:ignore_case',
            ],
            'products.*.group.id'=>[
                'nullable',
                Rule::exists('product_groups', 'id')
                    ->where('project_id', $projectId),
            ],
        ];
    }


    public static function getUpdateProductCategoryRules(int $id, int $projectId) {
        return [
            
            'id'=>[
                'required',
                Rule::exists('product_categories', 'id')
                    ->where('project_id', $projectId),
            ],
            'name'=>[
                'required',
                'string',
                'max:60',
                Rule::unique('product_categories', 'name')
                    ->where('project_id', $projectId)
                    ->ignore($id),
            ],
        ];
    }

}