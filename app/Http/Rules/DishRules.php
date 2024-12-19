<?php
namespace App\Http\Rules;

use Illuminate\Database\Query\Builder;
use Illuminate\Validation\Rule;
use ProjectRules;

class DishRules {
    
    public static function storeDishRules(int $projectId){
        return [
            'name'=>[
                'required',
                'string',
                'max:60',
                Rule::unique('dishes', 'name')
                    ->where('project_id', $projectId)
            ],
            'description'=>[
                'nullable',
                'string',
                'max:255',
            ],
        ];
    }
    public static function getUpdateDishRules(int $id, int $projectId) {
        return [
            'id'=>[
                'required',
                Rule::exists('dishes', 'id')
                    ->where('project_id', $projectId),
            ],
            'name'=>[
                'required',
                'string',
                'max:60',
                Rule::unique('dishes', 'name')
                    ->where('project_id', $projectId)
                    ->ignore($id)
            ],
            'description'=>[
                'nullable',
                'string',
                'max:255',
            ],
        ];
    
    }
    public static function uploadDishImageRules(int $projectId) {
        return [
            'file'=>[
                'required', 
                'image:jpeg,png,jpg,gif,svg',
                'max:2048'
            ],    
        ];
    }

    public static function getDishCategoryRules(int $projectId){
        return [
            'category.id'=>'required',
            'category.name'=>[
                'nullable',
                'exclude_unless:category.id,0',
                'nullable',
                'string',
                'max:60',
                Rule::unique('dish_categories', 'name')
                    ->where('project_id', $projectId)
            ],
        ];
    }

    public static function dishGroupRules(int $projectId) 
    {
        return [
            'group.id'=>'required',
            'group.name'=>[
                'nullable',
                'exclude_unless:group.id,0',
                'nullable',
                'string',
                'max:60',
                Rule::unique('dish_groups', 'name')
                    ->where('project_id', $projectId)
            ],
        ];
    }
    public static function dishIngredientsRules(int $projectId) {
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
            'ingredients.*.ingredient_amount'=>'required|numeric|min:1',
            'ingredients.*.waste_percentage'=>'required|numeric|min:0|max:100',
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

}