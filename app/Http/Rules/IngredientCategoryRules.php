<?php
namespace App\Http\Rules;

use Illuminate\Database\Query\Builder;
use Illuminate\Validation\Rule;
use ProjectRules;

class IngredientCategoryRules {

    public static function storeIngredientCategory(int $projectId) {
        return [
            'name'=>[
                'required',
                'string',
                'max:60',
                Rule::unique('ingredient_categories', 'name')
                    ->where('project_id', $projectId),
            ],
        ];
    }

    public static function ingredientCategoryIngredients(int $projectId) {
        return [
            'ingredients'=>'nullable|array',
            'ingredients.*.id'=>'required',
            'ingredients.*.name'=>[
                'exclude_unless:ingredients.*.id,0',
                'string',
                'max:60',
                Rule::unique('ingredients', 'name')
                    ->where('project_id', $projectId),
                'distinct:ignore_case',
            ],
            'ingredients.*.is_item_measured'=>[
                'exclude_unless:ingredients.*.id,0',
                'boolean',
            ],
            'ingredients.*.item_weight'=>[
                'exclude_unless:ingredients.*.id,0',
                'numeric',
                'min:1',
            ],
            'ingredients.*.type.id'=>[
                'required',
                Rule::exists('ingredient_types', 'id'),
            ],
            'ingredients.*.group.id'=>[
                'nullable',
                Rule::exists('ingredient_groups', 'id')
                    ->where('project_id', $projectId),
            ],
        ];
    }

    public static function getUpdateIngredientCategoryRules(int $id, int $projectId) {
        return [
            'id'=>[
                'required',
                Rule::exists('ingredient_categories', 'id')
                    ->where('project_id', $projectId),
            ],
            'name'=>[
                'required',
                'string',
                'max:60',
                Rule::unique('ingredient_categories', 'name')
                    ->where('project_id', $projectId)
                    ->ignore($id),
            ],        
        ];
    }

}