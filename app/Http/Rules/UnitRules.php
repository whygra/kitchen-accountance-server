<?php
namespace App\Http\Rules;

use Illuminate\Database\Query\Builder;
use Illuminate\Validation\Rule;
use ProjectRules;

class UnitRules {

    public static function storeUnitRules(int $projectId) {
        return [
            'long'=>[
                'required',
                'string',
                'max:20',
                Rule::unique('units', 'long')
                    ->where('project_id', $projectId),
            ],
            'short'=>[
                'required',
                'string',
                'max:6',
                Rule::unique('units', 'short')
                    ->where('project_id', $projectId),
            ],
        ];
    }


    public static function getUpdateUnitRules(int $id, int $projectId) {
        return [
            'id'=>[
                'required',
                Rule::exists('units', 'id')
                    ->where('project_id', $projectId),
            ],
            'long'=>[
                'required',
                'string',
                'max:20',
                Rule::unique('units', 'long')
                    ->where('project_id', $projectId)
                    ->ignore($id),
            ],
            'short'=>[
                'required',
                'string',
                'max:6',
                Rule::unique('units', 'short')
                    ->where('project_id', $projectId)
                    ->ignore($id),
            ],
        ];
    }

}