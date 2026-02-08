<?php
namespace App\Http\Rules;

use Illuminate\Database\Query\Builder;
use Illuminate\Validation\Rule;
use ProjectRules;

class DishTagRules {
    
    public static function storeDishTagRules(int $projectId){
        return [
            'name'=>[
                'required',
                'string',
                'max:60',
                Rule::unique('dish_tags', 'name')
                    ->where('project_id', $projectId)
            ],
        ];
    }
    public static function getUpdateDishTagRules(int $id, int $projectId) {
        return [
            'id'=>[
                'required',
                Rule::exists('dish_tags', 'id')
                    ->where('project_id', $projectId),
            ],
            'name'=>[
                'required',
                'string',
                'max:60',
                Rule::unique('dish_tags', 'name')
                    ->where('project_id', $projectId)
                    ->ignore($id)
            ],
        ];
    
    }

    public static function dishTagDishes(int $projectId) {
        return [
            'dishes'=>'nullable|array',
            'dishes.*.id'=>[
                'required',
                'exclude_if:dishes.*.id,0',
                Rule::exists('dishes')->where(function(Builder $query) use($projectId){
                    return $query->where('project_id', $projectId);
                })
            ],
            'dishes.*.name'=>[
                'exclude_unless:dishes.*.id,null',
                'string',
                'max:60',
                Rule::unique('dishes', 'name')
                    ->where('project_id', $projectId),
                'distinct:ignore_case',
            ],
        ];
    }

}