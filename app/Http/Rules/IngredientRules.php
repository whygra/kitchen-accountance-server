<?php
namespace App\Http\Rules;

use Illuminate\Database\Query\Builder;
use Illuminate\Validation\Rule;
use ProjectRules;

class IngredientRules {
    
    public static function storeIngredientRules(int $projectId) {
        return [
            'type.id'=>'required|exists:ingredient_types,id',
            'name'=>[
                'required',
                'string',
                'max:60',
                Rule::unique('ingredients', 'name')
                    ->where('project_id', $projectId),
            ],
            'is_item_measured' => 'required|boolean',
            'item_weight' => 'exclude_if:is_item_measured,false|numeric|min:1',
        ];
    }

    public static function getUpdateIngredientRules(int $id, int $projectId) {
        return [
            'id'=>[
                'required',
                Rule::exists('ingredients', 'id')
                    ->where('project_id', $projectId),
            ],
            'is_item_measured' => 'required|boolean',
            'item_weight' => 'exclude_if:is_item_measured,false|numeric|min:1',
            'name'=>[
                'required',
                'string',
                'max:60',
                Rule::unique('ingredients', 'name')
                    ->where('project_id', $projectId)
                    ->ignore($id),
            ],
            'type.id' => 'required|exists:ingredient_types,id',
        ];
    }

    public static function ingredientCategoryRules(int $projectId, int $id=0) {
        return [
            'category.id'=>'required',
            'category.name'=>[
                'nullable',
                'exclude_unless:category.id,0',
                'string',
                'max:60',
                Rule::unique('ingredient_categories', 'name')
                    ->where('project_id', $projectId)
                    ->ignore($id),
            ],
        ];
    }

    public static function ingredientGroupRules(int $projectId, int $id=0) {
        return [
            'group.id'=>'required',
            'group.name'=>[
                'nullable',
                'exclude_unless:group.id,0',
                'string',
                'max:60',
                Rule::unique('ingredient_groups', 'name')
                    ->where('project_id', $projectId)
                    ->ignore($id),
            ],
        ];
    }

    public static function ingredientProductRules(int $projectId, int $id=0) {
        return [
            'products'=>'nullable|array',
            'products.*.id'=>'required',
            'products.*.raw_content_percentage'=>'required|numeric|min:0|max:100',
            'products.*.waste_percentage'=>'required|numeric|min:0|max:100',
            'products.*.name'=>[
                'exclude_unless:products.*.id,0',
                'string',
                'max:60',
                'distinct:ignore_case',
                Rule::unique('products', 'name')
                    ->where('project_id', $projectId)
                    ->ignore($id),
            ]
        ];
    }   

}