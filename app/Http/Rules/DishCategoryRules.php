<?php
namespace App\Http\Rules;

use Illuminate\Database\Query\Builder;
use Illuminate\Validation\Rule;
use ProjectRules;

class DishCategoryRules {

    public static function getStoreDishCategoryRules(int $projectId) {
        return [
            'name'=>[
                'required',
                'string',
                'max:60',
                Rule::unique('dish_categories', 'name')
                    ->where('project_id', $projectId)
            ],
        ];
    }

    public static function dishCategoryDishesRules(int $projectId) {
        return [
            'dishes'=>'nullable|array',
            'dishes.*.id'=>'required',
            'dishes.*.name'=>[
                'exclude_unless:dishes.*.id,0',
                'string',
                'max:60',
                Rule::unique('dishes', 'name')
                ->where('project_id', $projectId),
                'distinct:ignore_case',
            ],
            'dishes.*.group.id'=>[
                'nullable',
                Rule::exists('dish_groups', 'id')
                    ->where('project_id', $projectId),
            ],
        ];
    }


    public static function updateDishCategoryRules(int $id, int $projectId) {
        return [
            'id'=>[
                'required',
                Rule::exists('dish_categories', 'id')
                    ->where('project_id', $projectId),
            ],
            'name'=>[
                'required',
                'string',
                'max:60',
                Rule::unique('dish_categories', 'name')
                    ->where('project_id', $projectId)
                    ->ignore($id)
            ],
        ];
    }

    public static function filterDataRules(int $id, int $projectId) {
        return [
            'id'=>[
                'required',
                Rule::exists('dish_categories', 'id')
                    ->where('project_id', $projectId),
            ],
            'name'=>[
                'required',
                'string',
                'max:60',
                Rule::unique('dish_categories', 'name')
                    ->where('project_id', $projectId)
                    ->ignore($id)
            ],
        ];
    }

}