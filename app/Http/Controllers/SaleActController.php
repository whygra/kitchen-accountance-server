<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaleAct\DeleteSaleActRequest;
use App\Http\Requests\SaleAct\GetSaleActRequest;
use App\Http\Requests\SaleAct\GetSaleActWithProductsRequest;
use App\Http\Requests\SaleAct\StoreSaleActRequest;
use App\Http\Requests\SaleAct\StoreSaleActWithProductsRequest;
use App\Http\Requests\SaleAct\UpdateSaleActRequest;
use App\Http\Requests\SaleAct\UpdateSaleActWithProductsRequest;
use App\Http\Resources\Storage\SaleActResource;
use App\Models\Dish\Dish;
use App\Models\Product\Product;
use App\Models\Project;
use App\Models\Storage\SaleAct;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleActController extends Controller
{
    /**
     * Display a listing of the resource. 
     */
    public function index(GetSaleActRequest $request, $project_id)
    {
        $project = Project::find($project_id);
        $all = $project->sale_acts()->get();
        return response()->json($all);
    }
 
    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSaleActRequest $request, $project_id)
    {
        $project = Project::find($project_id);
        // if($project->freeDishSlots()<1)
        //     return response()->json([
        //         'message' => "Достигнут лимит количества ингредиентов."
        //     ], 400);

        $new = new SaleAct;
        $new->date = $request->date;

        $project->sale_acts()->save($new);
        return response()->json($new, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(GetSaleActRequest $request, $project_id, $id)
    {
        $project = Project::find($project_id);
        $item = $project->sale_acts()->find($id);
        if (empty($item))
            return response()->json([
                'message' => "Акт закупки с id=$id не найден"
            ], 404);
            
        return response()->json($item);
    }

    /**
     * Update the specified resource in storage.
     */ 
    public function update(UpdateSaleActRequest $request, $project_id, $id)
    {
        $project = Project::find($project_id);
        $item = $project->sale_acts()->find($id);
        if (empty($item))
            return response()->json([
                'message' => "Акт закупки с id=$id не найден"
            ], 404);
            
        $item->date = $request->date;
        $project->sale_acts()->save($item);
        return response()->json($item, 200);
    }

    public function index_loaded(GetSaleActWithProductsRequest $request, $project_id)
    {
        $project = Project::find($project_id);
        $all = $project->sale_acts()->with(
            'updated_by_user'
            )->get();
        return response()->json(SaleActResource::collection($all));
    }

    public function show_loaded(GetSaleActRequest $request, $project_id, $id)
    {
        $project = Project::find($project_id);
        $item = $project->sale_acts()->with([
            'updated_by_user'
            ])->find($id);
        if (empty($item))
            return response()->json([
                'message' => "Акт закупки с id=$id не найден"
            ], 404); 
            
        return response()->json(new SaleActResource($item));
    }

    // создание
    public function store_loaded(StoreSaleActWithProductsRequest $request, $project_id)
    {
        $project = Project::find($project_id);
        // if($project->freeDishSlots()<1)
        //     return response()->json([
        //         'message' => "Достигнут лимит количества ингредиентов."
        //     ], 400);

        $item = new SaleAct;
        DB::transaction(function() use ($request, $item, $project) {
            // сначала создаем ингредиент - потом связи
            $item->date = $request['date'];

            $project->sale_acts()->save($item);

            $this->process_items($item, $request);
        });
        
        return response()->json($item, 201);
    }

    /**
     * Update the specified resource in storage.
     */ 
    public function update_loaded(UpdateSaleActWithProductsRequest $request, $project_id, $id)
    {
        $project = Project::find($project_id);
        $item = $project->sale_acts()->find($id);

        if (empty($item))
            return response()->json([
                'message' => "Акт продажи с id=$id не найден"
            ], 404);

        DB::transaction(function() use ($request, $item) {
            $this->process_items($item, $request);

            // обновление данных компонента
            $item->date = $request->date;
        });

        return response()->json($item, 200);
    }
    
    private function process_items(SaleAct $item, FormRequest $request) {
        $project = Project::find($request['project_id']);

        $items = [];
        foreach($request->items as $i){
            $dish = Dish::findOrNew($i['id']);

            if(empty($dish->id)){
                $dish->name = $i['name'];
                $dish->project_id = $request['project_id'];
                $dish->save();
            }

            $items[$dish->id] = [
                'amount'=>$i['amount'],
                'price'=>$i['price'],
            ];
        }
    
        $sync = $item->items()->sync($items);
        if(!empty($sync['attached'])||!empty($sync['detached'])||!empty($sync['updated']))
            $dish->touch();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DeleteSaleActRequest $request, $project_id, $id)
    {        
        $project = Project::find($project_id);
        $item = $project->sale_acts()->find($id);
        if (empty($item))
            return response()->json([
                'message' => "Не удалось найти Акт закупки с id=$id"
            ], 404);
            
        $item->delete();
        return response()->json($item);
    }
}
