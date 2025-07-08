<?php
namespace App\Http\Rules;

use Illuminate\Database\Query\Builder;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;
use ProjectRules;

class InventoryActRules {
    
    public static function storeInventoryActRules(int $projectId) {
        return [
            'project_id' => 'required|exists:projects,id',
            'date' => [
                'required',
                'date',
                Rule::unique('inventory_acts', 'date')
                ->where('project_id', $projectId)
            ],
        ];
    }

    public static function getUpdateInventoryActRules(int $id, int $projectId) {
        return [
            'id'=>[
                'required',
                Rule::exists('inventory_acts', 'id')
                    ->where('project_id', $projectId),
            ],
            'date' => [
                'required',
                'date',
                Rule::unique('inventory_acts', 'date')
                    ->where('project_id', $projectId)
                    ->ignore($id)
            ],
        ];
    }

    public static function inventoryActItemsRules(int $projectId, int $id) {
        return [
            'products'=>'nullable|array',
            'products.*.id'=>[
                'required',
                'distinct',
                Rule::exists('products', 'id')
                    ->where('project_id', $projectId),
            ],
            'products.*.amount'=>'required|numeric|min:1',
            'products.*.net_weight'=>'required|numeric|min:1',
            'products.*.name'=>[
                'exclude_unless:products.*.id,0',
                'string',
                'max:60',
                'distinct:ignore_case',
                Rule::unique('products', 'name')
                    ->where('project_id', $projectId)
                    ->ignore($id),
            ],
            
            'ingredients'=>'nullable|array',
            'ingredients.*.id'=>[
                'required',
                'distinct',
                Rule::exists('ingredients', 'id')
                    ->where('project_id', $projectId),
            ],
            'ingredients.*.amount'=>'required|numeric|min:1',
            'ingredients.*.name'=>[
                'exclude_unless:ingredients.*.id,0',
                'string',
                'max:60',
                'distinct:ignore_case',
                Rule::unique('ingredients', 'name')
                    ->where('project_id', $projectId)
                    ->ignore($id),
            ]
        ];
    }   

}