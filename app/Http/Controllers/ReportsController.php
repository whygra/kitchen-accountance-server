<?php

use App\Http\Controllers\Controller;
use App\Http\Requests\PurchaseOption\ItemsInStorageRequest;
use App\Models\Project;

class ReportsController extends Controller
{
    public function items_in_storage(ItemsInStorageRequest $request, $project_id, $date) {
        $project = Project::find($project_id);

        $inventory_act = $project->inventory_acts()->where('date', '>', $date)->first();
        
        $ingredients = array_map(
            (fn($i)=>
                $inventory_act->ingredients()->find($i->id)??[...$i, 'pivot'=>['amount'=>0]]),
                $project->ingredients
            );
        
        $products = array_map(
            (fn($i)=>
                $inventory_act->products()->find($i->id)??[...$i, 'pivot'=>['amount'=>0, 'net_weight'=>0]]),
                $project->products
            );
            
        return response()->json($item, 200);
    }
}