<?php

namespace App\Http\Controllers;

use App\Http\Requests\PurchaseOption\DeletePurchaseOptionRequest;
use App\Http\Requests\PurchaseOption\GetPurchaseOptionWithProductsRequest;
use App\Models\Distributor\PurchaseOption;
use App\Http\Requests\PurchaseOption\StorePurchaseOptionWithProductsRequest;
use App\Http\Requests\PurchaseOption\UpdatePurchaseOptionWithProductsRequest;
use App\Http\Resources\PurchaseOption\PurchaseOptionResource;
use App\Models\Distributor\Distributor;
use App\Models\Distributor\Unit;
use App\Models\Product\Product;
use App\Models\Product\ProductPurchaseOption;
use App\Models\Project;
use Carbon\Carbon;
use DateTime;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use function PHPUnit\Framework\isEmpty;

class PurchaseOptionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(GetPurchaseOptionWithProductsRequest $request, $project_id)
    {
        $all = PurchaseOption::whereHas('distributor', 
        function (Builder $query) use($project_id) {
            $query->where('project_id', $project_id);
        })->get();
        return response()->json($all);
    }
    public function index_loaded(GetPurchaseOptionWithProductsRequest $request, $project_id)
    {
        $all = PurchaseOption::whereHas('distributor', 
        function (Builder $query) use($project_id) {
            $query->where('project_id', $project_id);
        })->with([
            'unit', 
            'products', 
            'distributor'
        ])->get();
        return response()->json(PurchaseOptionResource::collection($all));
    }

    /**
     * Display the specified resource.
     */
    public function show(GetPurchaseOptionWithProductsRequest $request, $project_id, $id)
    {
        $item = PurchaseOption::whereHas('distributor', 
        function (Builder $query) use($project_id) {
            $query->where('project_id', $project_id);
        })->find($id);
        
        if (empty($item))
            return response()->json([
                'message' => "Не удалось найти позицию закупки с id=".$item->id
            ], 404);
        return response()->json($item);
    }
    public function show_loaded(GetPurchaseOptionWithProductsRequest $request, $project_id, $id)
    {
        $item = PurchaseOption::whereHas('distributor', 
        function (Builder $query) use($project_id) {
            $query->where('project_id', $project_id);
        })->with([
            'unit', 
            'product',
            'distributor',
        ])->find($id);
        
        if (empty($item))
            return response()->json([
                'message' => "Не удалось найти позицию закупки с id=".$id
            ], 404);
        return response()->json(new PurchaseOptionResource($item));
    }


    /**
     * Store a newly created resource in storage.
     */ 
    public function store(StorePurchaseOptionWithProductsRequest $request, $project_id)
    {
        $project = Project::find($project_id);
        $distributor = $project->distributors()->find($request->distributor_id);
        if($distributor->freePurchaseOptionSlots()<1)
            return response()->json([
                'message' => "Достигнут лимит количества позиций закупки."
            ], 400);
        $new = new PurchaseOption;
        $new->unit_id = $request->unit_id;
        $new->name = $request->name;
        $new->net_weight = $request->net_weight;
        $new->price = $request->price;
        if(!empty($request->is_relevant))
            $new->is_relevant = $request->is_relevant;

        $distributor->purchase_options()->save($new);
        return response()->json($new, 201);    
    }        

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePurchaseOptionWithProductsRequest $request, $project_id, $id)
    {
        $item = PurchaseOption::find($id);
        if(empty($item))
            return response()->json([
                'message' => ''
            ], 404);

        $item->unit_id = $request->unit_id;
        $item->name = $request->name;
        $item->net_weight = $request->net_weight;
        $item->price = $request->price;
        
        if(!empty($request->is_relevant))
            $item->is_relevant = $request->is_relevant;
        
        $distributor = Project::find($project_id)->distributors()->find($item->distributor_id);
        $distributor->purchase_options()->save($item);

        return response()->json($item, 200);
    }


    /**
     * Store a newly created resource in storage.
     */ 
    public function store_loaded(StorePurchaseOptionWithProductsRequest $request, $project_id)
    {
        $project = Project::find($project_id);
        $distributor = $project->distributors()->find($request['distributor']['id']);
        if($distributor->freePurchaseOptionSlots()<1)
            return response()->json([
                'message' => "Достигнут лимит количества позиций закупки."
            ], 400);
        if($request['product']['id']==0&&$project->freeProductSlots()<1)
            return response()->json([
                'message' => "Невозможно добавить продукт. Превышается лимит количества продуктов."
            ], 400);
        $item = new PurchaseOption;
        DB::transaction(function() use ($request, $project, $item) {
            $unit = $project->units()->findOrNew($request['unit']['id']);
            if(empty($unit->id)){
                $unit->long = $request['unit']['long'];
                $unit->short = $request['unit']['short'];
                $project->units()->save($unit);
            }
            $item->unit()->associate($unit);
            $item->name = $request->name;
            $item->code = $request->code;
            $item->net_weight = $request->net_weight;
            $item->price = $request->price;
            
            $item->is_relevant = $request['is_relevant'] ?? true;

            
            $product = $project->products()->findOrNew(
                $request['product']['id']
            );
            if(empty($product->id)){
                $product->name = $request['product']['name'];
                $project->products()->save($product);
            }
            $item->product()->associate($product->id);
            
            $distributor = $project->distributors()->find($request['distributor']['id']);
            $distributor->purchase_options()->save($item);

        });

        return response()->json($item, 201);    
    }        

    /**
     * Update the specified resource in storage.
     */
    public function update_loaded(UpdatePurchaseOptionWithProductsRequest $request, $project_id, $id)
    {
        $item = PurchaseOption::find($id);
        if(empty($item))
            return response()->json([
                'message' => ''
            ], 404);
        $project = Project::find($project_id);

        DB::transaction(function() use ($request, $project, $item) {
            $unit = $project->units()->findOrNew($request['unit']['id']);
            if(empty($unit->id)){
                $unit->long = $request['unit']['long'];
                $unit->short = $request['unit']['short'];
                $project->units()->save($unit);
            }
            $item->unit()->associate($unit);
            
            $item->name = $request->name;
            $item->code = $request->code;
            $item->net_weight = $request->net_weight;
            $item->distributor_id = $request['distributor']['id'];
            $item->price = $request->price;

            $item->is_relevant = $request['is_relevant'] ?? true;

            if($request['product']['id']==0&&$project->freeProductSlots()<1)
                return response()->json([
                    'message' => "Невозможно добавить продукт. Превышается лимит количества продуктов."
                ], 400);
                
            $product = $project->products()->findOrNew(
                $request['product']['id']
            );
            if(empty($product->id)){
                $product->name = $request['product']['name'];
                $project->products()->save($product);
            }
            $item->product()->associate($product->id);

            $distributor = $project->distributors()->find($item->distributor_id);
            $distributor->purchase_options()->save($item);
        });

        return response()->json($item, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DeletePurchaseOptionRequest $request, $project_id, $id)
    {
        $item = PurchaseOption::find($id);
        if(empty($item))
            return response()->json([
                'message' => ''
            ], 404);

        $item->delete();            
        return response()->json($item, 200);
    }
}
