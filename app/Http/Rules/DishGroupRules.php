<?php
namespace App\Http\Rules;

use Illuminate\Database\Query\Builder;
use Illuminate\Validation\Rule;
use ProjectRules;

class DishGroupRules {

    public static function storeDishGroupRules(int $projectId) {
        return [
            'name'=>[
                'required',
                'string',
                'max:60',
                Rule::unique('dish_groups', 'name')
                    ->where('project_id', $projectId)
            ],
        ];  
    }

    public static function dishGroupDishes(int $projectId){
        return [
            'dishes'=>'nullable|array',
            'dishes.*.id'=>'required',
            'dishes.*.name'=>[
                'exclude_unless:dishes.*.id,0',
                'string',
                'max:60',
                'distinct:ignore_case',
                Rule::unique('dishes', 'name')
                ->where('project_id', $projectId)
            ],
            'dishes.*.category.id'=>[
                'nullable',
                Rule::exists('dish_categories', 'id')
                    ->where('project_id', $projectId),
            ],
        ];
    } 


    public static function getUpdateDishGroupRules(int $id, int $projectId) {
        return [
            'id'=>[
                'required',
                Rule::exists('dish_groups', 'id')
                    ->where('project_id', $projectId),
            ],
            'name'=>[
                'required',
                'string',
                'max:60',
                Rule::unique('dish_groups', 'name')
                    ->where('project_id', $projectId)
                    ->ignore($id)
            ],
        ];
    }

}