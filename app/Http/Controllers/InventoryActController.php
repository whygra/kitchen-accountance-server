<?php

namespace App\Http\Controllers;

use App\Http\Requests\InventoryAct\DeleteInventoryActRequest;
use App\Http\Requests\InventoryAct\GetInventoryActRequest;
use App\Http\Requests\InventoryAct\GetInventoryActWithItemsRequest;
use App\Http\Requests\InventoryAct\StoreInventoryActRequest;
use App\Http\Requests\InventoryAct\StoreInventoryActWithItemsRequest;
use App\Http\Requests\InventoryAct\UpdateInventoryActRequest;
use App\Http\Requests\InventoryAct\UpdateInventoryActWithItemsRequest;
use App\Http\Resources\Storage\InventoryActResource;
use App\Models\Ingredient\Ingredient;
use App\Models\Product\Product;
use App\Models\Project;
use App\Models\Storage\InventoryAct;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryActController extends Controller
{
    /**
     * Display a listing of the resource. 
     */
    public function index(GetInventoryActRequest $request, $project_id)
    {
        $project = Project::find($project_id);
        $all = $project->inventory_acts()->with(
            'updated_by_user'
            )->get();
        return response()->json($all);
    }
 
    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreInventoryActRequest $request, $project_id)
    {
        $project = Project::find($project_id);
        // if($project->freeDishSlots()<1)
        //     return response()->json([
        //         'message' => "Достигнут лимит количества ингредиентов."
        //     ], 400);

        $new = new InventoryAct;
        $new->date = $request->date;

        $project->inventory_acts()->save($new);
        return response()->json($new, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(GetInventoryActRequest $request, $project_id, $id)
    {
        $project = Project::find($project_id);
        $item = $project->inventory_acts()->find($id);
        if (empty($item))
            return response()->json([
                'message' => "Акт инвентаризации с id=$id не найден"
            ], 404);
            
        return response()->json($item);
    }

    /**
     * Update the specified resource in storage.
     */ 
    public function update(UpdateInventoryActRequest $request, $project_id, $id)
    {
        $project = Project::find($project_id);
        $item = $project->inventory_acts()->find($id);
        if (empty($item))
            return response()->json([
                'message' => "Акт инвентаризации с id=$id не найден"
            ], 404);
            
        $item->date = $request->date;
        $project->inventory_acts()->save($item);
        return response()->json($item, 200);
    }

    public function index_loaded(GetInventoryActWithItemsRequest $request, $project_id)
    {
        $project = Project::find($project_id);
        $all = $project->inventory_acts()->with(
            'updated_by_user'
            )->get();
        return response()->json(InventoryActResource::collection($all));
    }

    public function show_loaded(GetInventoryActRequest $request, $project_id, $id)
    {
        $project = Project::find($project_id);
        $item = $project->inventory_acts()->with([
            'updated_by_user'
            ])->find($id);
        if (empty($item))
            return response()->json([
                'message' => "Акт инвентаризации с id=$id не найден"
            ], 404); 
            
        return response()->json(new InventoryActResource($item));
    }

    // создание
    public function store_loaded(StoreInventoryActWithItemsRequest $request, $project_id)
    {
        $project = Project::find($project_id);
        // if($project->freeDishSlots()<1)
        //     return response()->json([
        //         'message' => "Достигнут лимит количества ингредиентов."
        //     ], 400);

        $item = new InventoryAct;
        DB::transaction(function() use ($request, $item, $project) {
            // сначала создаем ингредиент - потом связи
            $item->date = $request['date'];

            $project->inventory_acts()->save($item);

            if($request->products)
                $this->process_products($item, $request);
            if($request->ingredients)
                $this->process_ingredients($item, $request);
        });
        
        return response()->json($item, 201);
    }

    /**
     * Update the specified resource in storage.
     */ 
    public function update_loaded(UpdateInventoryActWithItemsRequest $request, $project_id, $id)
    {
        $project = Project::find($project_id);
        $item = InventoryAct::where('project_id', $request['project_id'])->find($id);

        if (empty($item))
            return response()->json([
                'message' => "Акт инвентаризации с id=$id не найден"
            ], 404);

        DB::transaction(function() use ($request, $project, $item) {
            if($request->products)
                $this->process_products($item, $request);
            if($request->ingredients)
                $this->process_ingredients($item, $request);

            // обновление данных компонента
            $item->date = $request->date;
        });

        return response()->json($item, 200);
    }
    
    private function process_products(InventoryAct $item, FormRequest $request) {
        $project = Project::find($request['project_id']);
        
        $nNewProducts = count(array_filter(
            $request['products'],
            fn($p)=>($p['id'] ?? 0)==0
        ));

        $freeSlots = $project->freeProductSlots();
        if($freeSlots<$nNewProducts)
            return response()->json([
                'message' => "Невозможно добавить $nNewProducts продуктов. Превышается лимит количества продуктов (осталось $freeSlots)."
            ], 400);

        $products = [];
        foreach($request->products as $p){
            $product = Product::findOrNew($p['id']);

            if(empty($product->id)){
                $product->name = $p['name'];
                $product->project_id = $request['project_id'];
                $product->save();
            }

            $products[$product->id] = [
                'amount'=>$p['amount'],
                'net_weight'=>$p['net_weight'],
            ];
        }
    
        $sync = $item->products()->sync($products);
        if(!empty($sync['attached'])||!empty($sync['detached'])||!empty($sync['updated']))
            $item->touch();

    }
    private function process_ingredients(InventoryAct $item, FormRequest $request) {
        $project = Project::find($request['project_id']);
        
        $nNewIngredients = count(array_filter(
            $request['ingredients'],
            fn($p)=>($p['id'] ?? 0)==0
        ));

        $freeSlots = $project->freeIngredientSlots();
        if($freeSlots<$nNewIngredients)
            return response()->json([
                'message' => "Невозможно добавить $nNewIngredients ингредиентов. Превышается лимит количества ингредиентов (осталось $freeSlots)."
            ], 400);

        $ingredients = [];
        foreach($request->ingredients as $p){
            $ingredient = Ingredient::findOrNew($p['id']);

            if(empty($ingredient->id)){
                $ingredient->name = $p['name'];
                $ingredient->project_id = $request['project_id'];
                $ingredient->save();
            }

            $ingredients[$ingredient->id] = [
                'amount'=>$p['amount'],
            ];
        }
    
        $sync = $item->ingredients()->sync($ingredients);
        if(!empty($sync['attached'])||!empty($sync['detached'])||!empty($sync['updated']))
            $item->touch();

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DeleteInventoryActRequest $request, $project_id, $id)
    {        
        $project = Project::find($project_id);
        $item = $project->inventory_acts()->find($id);
        if (empty($item))
            return response()->json([
                'message' => "Не удалось найти Акт инвентаризации с id=$id"
            ], 404);
            
        $item->delete();
        return response()->json($item);
    }
}
