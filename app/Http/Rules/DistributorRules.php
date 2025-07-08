<?php
namespace App\Http\Rules;

use App\Models\Distributor\Distributor;
use App\Models\Project;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use ProjectRules;

class DistributorRules {
    
    public static function storeDistributorRules(int $projectId) {
        return [
            'name'=>[
                'required',
                'string',
                'max:60',
                Rule::unique('distributors', 'name')
                    ->where('project_id', $projectId),
            ],
        ];
    }
    
    public static function getUpdateDistributorRules(int $id, int $projectId) {
        return [
            'id'=>[
                'required',
                Rule::exists('distributors', 'id')
                    ->where('project_id', $projectId),
            ],
            'name'=>[
                'required',
                'string',
                'max:60',
                Rule::unique('distributors', 'name')
                    ->where('project_id', $projectId)
                    ->ignore($id),
            ],
        ];
    }

    public static function storeDistributorPurchaseOptionsRules(int $projectId) {
        return [
            'purchase_options'=>'nullable|array',
                // при добавлении данных поставщика позиции закупки только создаются
                'purchase_options.*.name'=>[
                    'required',
                    'string',
                    'max:60',
                ],
                'purchase_options.*.unit.id'=>'required',
                'purchase_options.*.unit.long'=>[
                    'exclude_unless:purchase_options.unit.id,0',
                    'string',
                    'max:120',
                    Rule::unique('units', 'long')
                        ->where('project_id', $projectId),
                    'distinct:ignore_case'
                ],
                'purchase_options.*.unit.short'=>[
                    'exclude_unless:purchase_options.unit.id,0',
                    'string',
                    'max:6',
                    Rule::unique('units', 'short')
                        ->where('project_id', $projectId),
                    'distinct:ignore_case'
                ],
                'purchase_options.*.code'=>[
                    'nullable',
                    'string',
                    'max:120',
                ],

                'purchase_options.*.products'=>'nullable|array',
                'purchase_options.*.products.*.id'=>'required|distinct',
                'purchase_options.*.products.*.name'=>[
                    'exclude_unless:purchase_options.*.products.*.id,0',
                    'string',
                    'max:60',
                    Rule::unique('products', 'name')
                        ->where('project_id', $projectId),
                    'distinct:ignore_case',
                ]
            ];
        }

    public static function getUpsertPurchaseOptionsRules(int $distributorId, int $projectId, Request $request) {
        $po_ids = $request->input('purchase_options.*.id');
        return [
            'purchase_options'=>'nullable|array',
            'purchase_options.*.id'=>'required',
            'purchase_options.*.name'=>[
                'exclude_unless:purchase_options.*.id,0',
                'string',
                'max:120',
                'distinct:ignore_case',
                Rule::unique('purchase_options', 'name')
                    ->whereNotIn('id', $po_ids)
                    ->where('distributor_id', $distributorId)
            ],
            'purchase_options.*.unit.id'=>'required',
            'purchase_options.*.unit.long'=>[
                'exclude_unless:purchase_options.*.unit.id,0',
                'string',
                'max:60',
                Rule::unique('units', 'long')
                    ->where('project_id', $projectId),
                'distinct:ignore_case'
            ],
            'purchase_options.*.unit.short'=>[
                'exclude_unless:purchase_options.*.unit.id,0',
                'string',
                'max:6',
                Rule::unique('units', 'short')
                    ->where('project_id', $projectId),
                'distinct:ignore_case'
            ],
            'purchase_options.*.code'=>[
                'nullable',
                'string',
                'max:120',
                'distinct:ignore_case',
                Rule::unique('purchase_options', 'code')
                    ->where('distributor_id', $distributorId)
                    ->whereNotIn('id', $po_ids)
                    ,
            ],

            'purchase_options.*.products'=>'nullable|array',
            'purchase_options.*.products.*.id'=>'required',
            'purchase_options.*.products.*.name'=>[
                'exclude_unless:purchase_options.*.products.*.id,0',
                'string',
                'max:60',
                'distinct:ignore_case',
                Rule::unique('products', 'name')
                    ->where('project_id', $projectId),
            ],
        ];
    }
    public static function getDeletePurchaseOptionsRules(int $distributorId) {
        return [
            'purchase_options_to_delete'=>'nullable|array',
            'purchase_options_to_delete.*.id'=>
                Rule::exists('purchase_options', 'id')
                    ->where('distributor_id', $distributorId),
        ];
    }
}