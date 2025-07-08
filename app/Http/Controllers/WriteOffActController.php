<?php

namespace App\Http\Controllers;

use App\Http\Requests\WriteOffAct\DeleteWriteOffActRequest;
use App\Http\Requests\WriteOffAct\GetWriteOffActRequest;
use App\Http\Requests\WriteOffAct\GetWriteOffActWithItemsRequest;
use App\Http\Requests\WriteOffAct\StoreWriteOffActRequest;
use App\Http\Requests\WriteOffAct\StoreWriteOffActWithItemsRequest;
use App\Http\Requests\WriteOffAct\UpdateWriteOffActRequest;
use App\Http\Requests\WriteOffAct\UpdateWriteOffActWithItemsRequest;
use App\Http\Resources\Storage\InventoryActResource;
use App\Http\Resources\Storage\WriteOffActResource;
use App\Models\Ingredient\Ingredient;
use App\Models\Product\Product;
use App\Models\Project;
use App\Models\Storage\WriteOffAct;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WriteOffActController extends Controller
{
    /**
     * Display a listing of the resource. 
     */
    public function index(GetWriteOffActRequest $request, $project_id)
    {
        $project = Project::find($project_id);
        $all = $project->write_off_acts()->get();
        return response()->json($all);
    }
 
    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreWriteOffActRequest $request, $project_id)
    {
        $project = Project::find($project_id);
        // if($project->freeDishSlots()<1)
        //     return response()->json([
        //         'message' => "Достигнут лимит количества ингредиентов."
        //     ], 400);

        $new = new WriteOffAct;
        $new->date = $request->date;

        $project->write_off_acts()->save($new);
        return response()->json($new, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(GetWriteOffActRequest $request, $project_id, $id)
    {
        $project = Project::find($project_id);
        $item = $project->write_off_acts()->find($id);
        if (empty($item))
            return response()->json([
                'message' => "Акт списания с id=$id не найден"
            ], 404);
            
        return response()->json($item);
    }

    /**
     * Update the specified resource in storage.
     */ 
    public function update(UpdateWriteOffActRequest $request, $project_id, $id)
    {
        $project = Project::find($project_id);
        $item = $project->write_off_acts()->find($id);
        if (empty($item))
            return response()->json([
                'message' => "Акт списания с id=$id не найден"
            ], 404);
            
        $item->date = $request->date;
        $project->write_off_acts()->save($item);
        return response()->json($item, 200);
    }

    public function index_loaded(GetWriteOffActWithItemsRequest $request, $project_id)
    {
        $project = Project::find($project_id);
        $all = $project->write_off_acts()->with(
            'updated_by_user'
            )->get();
        return response()->json(InventoryActResource::collection($all));
    }

    public function show_loaded(GetWriteOffActRequest $request, $project_id, $id)
    {
        $project = Project::find($project_id);
        $item = $project->write_off_acts()->with([
            'updated_by_user'
            ])->find($id);
        if (empty($item))
            return response()->json([
                'message' => "Акт списания с id=$id не найден"
            ], 404); 
            
        return response()->json(new InventoryActResource($item));
    }

    // создание
    public function store_loaded(StoreWriteOffActWithItemsRequest $request, $project_id)
    {
        $project = Project::find($project_id);
        // if($project->freeDishSlots()<1)
        //     return response()->json([
        //         'message' => "Достигнут лимит количества ингредиентов."
        //     ], 400);

        $item = new WriteOffAct;
        DB::transaction(function() use ($request, $item, $project) {
            // сначала создаем ингредиент - потом связи
            $item->date = $request['date'];

            $project->write_off_acts()->save($item);

            $this->process_products($item, $request);
            $this->process_ingredients($item, $request);
        });
        
        return response()->json($item, 201);
    }

    /**
     * Update the specified resource in storage.
     */ 
    public function update_loaded(UpdateWriteOffActWithItemsRequest $request, $project_id, $id)
    {
        $project = Project::find($project_id);
        $item = WriteOffAct::where('project_id', $request['project_id'])->find($id);

        if (empty($item))
            return response()->json([
                'message' => "Компонент с id=$id не найден"
            ], 404);

        DB::transaction(function() use ($request, $project, $item) {
            $this->process_products($item, $request);
            $this->process_ingredients($item, $request);

            // обновление данных компонента
            $item->date = $request->date;
        });

        return response()->json($item, 200);
    }
    
    private function process_products(WriteOffAct $item, FormRequest $request) {
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
    
    private function process_ingredients(WriteOffAct $item, FormRequest $request) {
        $project = Project::find($request['project_id']);
        
        $nNewIngredients = count(array_filter(
            $request['ingredients'],
            fn($p)=>($p['id'] ?? 0)==0
        ));

        $freeSlots = $project->freeIngredientSlots();
        if($freeSlots<$nNewIngredients)
            return response()->json([
                'message' => "Невозможно добавить $nNewIngredients продуктов. Превышается лимит количества продуктов (осталось $freeSlots)."
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
    public function destroy(DeleteWriteOffActRequest $request, $project_id, $id)
    {        
        $project = Project::find($project_id);
        $item = $project->write_off_acts()->find($id);
        if (empty($item))
            return response()->json([
                'message' => "Не удалось найти Акт списания с id=$id"
            ], 404);
            
        $item->delete();
        return response()->json($item);
    }
}
