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
            'description'=>[
                'nullable',
                'string',
                'max:1000',
            ],
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
            'description'=>[
                'nullable',
                'string',
                'max:1000',
            ],
        ];
    }

    
    public static function ingredientTagsRules(int $projectId) {
        return [
            'tags'=>'nullable|array',
            'tags.*.id'=>[
                'required',
                'exclude_if:tags.*.id,0',
                'distinct',
                Rule::exists('ingredient_tags')->where(function(Builder $query) use($projectId){
                    return $query->where('project_id', $projectId);
                })
            ],
            'tags.*.name'=>[
                'exclude_unless:tags.*.id,null',
                'string',
                'max:60',
                Rule::unique('ingredient_tags', 'name')
                    ->where('project_id', $projectId),
                'distinct:ignore_case',
            ],
        ];
    }

    public static function ingredientProductRules(int $projectId) {
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

    public static function ingredientIngredientRules(int $projectId, int $id=-1) {
        return [
            'ingredients'=>'nullable|array',
            'ingredients.*.id'=>'required|not_in:'.$id,
            'ingredients.*.amount'=>'required|numeric|min:0.01',
            'ingredients.*.type.id'=>'required|exclude_unless:ingredients.*.id,0|exists:ingredient_types,id',
            'ingredients.*.net_weight'=>'required|numeric|min:0',
            'ingredients.*.name'=>[
                'exclude_unless:ingredients.*.id,0',
                'string',
                'max:60',
                'distinct:ignore_case',
                Rule::unique('ingredients', 'name')
                    ->where('project_id', $projectId),
            ]
        ];
    }   

}