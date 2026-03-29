<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Reports\ItemsInStorageRequest;
use App\Models\GrossProductArray;
use App\Models\Project;
use App\Models\Storage\SaleAct;

class ReportsController extends Controller
{
    public function items_in_storage(ItemsInStorageRequest $request, $project_id, $date)
    {
        $project = Project::query()->find($project_id);

        $inventory_act = $project->inventory_acts()->where('date', '<', $date)->orderBy('date', 'desc')->first();

        $purchase_acts = $project->purchase_acts()->whereBetween('date', [$inventory_act->date, $date])->get();

        $sale_acts = $project->sale_acts()->whereBetween('date', [$inventory_act->date, $date])->get();

        $write_off_acts = $project->write_off_acts()->whereBetween('date', [$inventory_act->date, $date])->get();

        $products = new GrossProductArray($inventory_act->raw_products);

        foreach ($purchase_acts as $p) {
            foreach ($p->raw_products as $p) {
                $products->addProduct($p);
            }
        }

        foreach ($sale_acts as $s) {
            foreach ($s->raw_products as $p) {
                $products->writeOffProduct($p);
            }
        }

        foreach ($write_off_acts as $w) {
            foreach ($w->raw_products as $p) {
                $products->writeOffProduct($p);
            }
        }

        return response()->json(array_values($products->get()), 200);
    }
}
