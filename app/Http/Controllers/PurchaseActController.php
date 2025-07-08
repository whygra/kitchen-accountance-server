<?php

namespace App\Http\Controllers;

use App\Http\Requests\PurchaseAct\DeletePurchaseActRequest;
use App\Http\Requests\PurchaseAct\GetPurchaseActRequest;
use App\Http\Requests\PurchaseAct\GetPurchaseActWithProductsRequest;
use App\Http\Requests\PurchaseAct\StorePurchaseActRequest;
use App\Http\Requests\PurchaseAct\StorePurchaseActWithProductsRequest;
use App\Http\Requests\PurchaseAct\UpdatePurchaseActRequest;
use App\Http\Requests\PurchaseAct\UpdatePurchaseActWithProductsRequest;
use App\Http\Resources\Storage\PurchaseActResource;
use App\Models\Distributor\PurchaseOption;
use App\Models\Product\Product;
use App\Models\Project;
use App\Models\Storage\PurchaseAct;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseActController extends Controller
{
    /**
     * Display a listing of the resource. 
     */
    public function index(GetPurchaseActRequest $request, $project_id)
    {
        $project = Project::find($project_id);
        $all = $project->purchase_acts()->get();
        return response()->json($all);
    }
 
    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePurchaseActRequest $request, $project_id)
    {
        $project = Project::find($project_id);
        // if($project->freeDishSlots()<1)
        //     return response()->json([
        //         'message' => "Достигнут лимит количества ингредиентов."
        //     ], 400);

        $new = new PurchaseAct;
        $new->date = $request->date;

        $project->purchase_acts()->save($new);
        return response()->json($new, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(GetPurchaseActRequest $request, $project_id, $id)
    {
        $project = Project::find($project_id);
        $item = $project->purchase_acts()->find($id);
        if (empty($item))
            return response()->json([
                'message' => "Акт закупки с id=$id не найден"
            ], 404);
            
        return response()->json($item);
    }

    /**
     * Update the specified resource in storage.
     */ 
    public function update(UpdatePurchaseActRequest $request, $project_id, $id)
    {
        $project = Project::find($project_id);
        $item = $project->purchase_acts()->find($id);
        if (empty($item))
            return response()->json([
                'message' => "Акт закупки с id=$id не найден"
            ], 404);
            
        $item->date = $request->date;
        $project->purchase_acts()->save($item);
        return response()->json($item, 200);
    }

    public function index_loaded(GetPurchaseActWithProductsRequest $request, $project_id)
    {
        $project = Project::find($project_id);
        $all = $project->purchase_acts()->with(
            'updated_by_user',
            'distributor',
            )->get();
        return response()->json(PurchaseActResource::collection($all));
    }

    public function show_loaded(GetPurchaseActRequest $request, $project_id, $id)
    {
        $project = Project::find($project_id);
        $item = $project->purchase_acts()->with([
            'updated_by_user',
            'distributor',
            ])->find($id);
        if (empty($item))
            return response()->json([
                'message' => "Акт закупки с id=$id не найден"
            ], 404); 
            
        return response()->json(new PurchaseActResource($item));
    }

    // создание
    public function store_loaded(StorePurchaseActWithProductsRequest $request, $project_id)
    {
        $project = Project::find($project_id);
        // if($project->freeDishSlots()<1)
        //     return response()->json([
        //         'message' => "Достигнут лимит количества ингредиентов."
        //     ], 400);

        $item = new PurchaseAct;
        DB::transaction(function() use ($request, $item, $project) {
            // сначала создаем ингредиент - потом связи
            $item->date = $request['date'];
            $item->distributor_id = $request['distributor']['id'];

            $project->purchase_acts()->save($item);

            $this->process_items($item, $request);
        });
        
        return response()->json($item, 201);
    }

    /**
     * Update the specified resource in storage.
     */ 
    public function update_loaded(UpdatePurchaseActWithProductsRequest $request, $project_id, $id)
    {
        $project = Project::find($project_id);
        $item = $project->purchase_acts()->find($id);

        if (empty($item))
            return response()->json([
                'message' => "Компонент с id=$id не найден"
            ], 404);

        DB::transaction(function() use ($request, $item) {
            $this->process_items($item, $request);
            $item->distributor_id = $request['distributor']['id'];

            // обновление данных компонента
            $item->date = $request->date;

            $item->save();
        });

        return response()->json($item, 200);
    }
    
    private function process_items(PurchaseAct $item, FormRequest $request) {
        $project = Project::find($request['project_id']);
        $distributor = $project->distributors()->find($request['distributor']['id']);
        
        $nNewPurchaseOptions = count(array_filter(
            $request['items'],
            fn($o)=>($o['id'] ?? 0)==0
        ));

        $freeSlots = $distributor->freePurchaseOptionSlots();
        if($freeSlots<$nNewPurchaseOptions)
            return response()->json([
                'message' => "Невозможно добавить $nNewPurchaseOptions позиций закупки. Превышается лимит (осталось $freeSlots)."
            ], 400);

        $items = [];
        foreach($request->items as $o){
            $purchase_option = PurchaseOption::findOrNew($o['id']);

            if(empty($purchase_option->id)){
                $purchase_option->name = $o['name'];
                $purchase_option->distributor_id = $request['distributor']['id'];
                $purchase_option->net_weight = $o['net_weight'];
                $purchase_option->price = $o['price'];
                $unit = $project->units()->find($o['unit']['id']);
                if(empty($unit->id))
                {
                    $unit->short = $o['unit']['short'];
                    $unit->long = $o['unit']['long'];
                    $project->units()->save($unit);
                }
                $purchase_option->unit()->associate($unit);
                $purchase_option->save();
            }

            $items[$purchase_option->id] = [
                'amount'=>$o['amount'],
                'price'=>$o['price'],
                'net_weight'=>$o['net_weight'],
            ];
        }
    
        $sync = $item->items()->sync($items);
        if(!empty($sync['attached'])||!empty($sync['detached'])||!empty($sync['updated']))
            $item->touch();

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DeletePurchaseActRequest $request, $project_id, $id)
    {        
        $project = Project::find($project_id);
        $item = $project->purchase_acts()->find($id);
        if (empty($item))
            return response()->json([
                'message' => "Не удалось найти Акт закупки с id=$id"
            ], 404);
            
        $item->delete();
        return response()->json($item);
    }
}
