<?php
namespace App\Http\Rules;

use Illuminate\Database\Query\Builder;
use Illuminate\Validation\Rule;
use ProjectRules;

class IngredientTagRules {

    public static function storeIngredientTagRules(int $projectId) {
        return [
            'name'=>[
                'required',
                'string',
                'max:60',
                Rule::unique('ingredient_tags', 'name')
                    ->where('project_id', $projectId),
            ],
        ];
    }

    public static function ingredientTagIngredientsRules(int $projectId) {
        return [
            'ingredients'=>'nullable|array',
            'ingredients.*.id'=>[
                'required',
                'exclude_if:ingredients.*.id,0',
                Rule::exists('ingredients')->where(function(Builder $query) use($projectId){
                    return $query->where('project_id', $projectId);
                })
            ],
            'ingredients.*.type.id'=>'exclude_unless:ingredients.*.id,null|required|exists:ingredient_types,id',
            'ingredients.*.amount'=>'required|numeric|min:0.01',
            'ingredients.*.net_weight'=>'required|numeric|min:0',
            'ingredients.*.name'=>[
                'exclude_unless:ingredients.*.id,null',
                'string',
                'max:60',
                Rule::unique('ingredients', 'name')
                    ->where('project_id', $projectId),
                'distinct:ignore_case',
            ],
        ];
    }

    public static function getUpdateIngredientTagRules(int $id, int $projectId) {
        return [
            'id'=>[
                'required',
                Rule::exists('ingredient_tags', 'id')
                    ->where('project_id', $projectId),
            ],
            'name'=>[
                'required',
                'string',
                'max:60',
                Rule::unique('ingredient_tags', 'name')
                    ->where('project_id', $projectId)
                    ->ignore($id),
            ],        
        ];
    }

}