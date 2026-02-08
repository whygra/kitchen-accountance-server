<?php

namespace App\Http\Controllers;

use App\Http\Requests\Product\DeleteProductRequest;
use App\Http\Requests\Product\GetProductWithPurchaseOptionsRequest;
use App\Http\Requests\ProductTag\StoreProductTagWithProductsRequest;
use App\Http\Requests\ProductTag\UpdateProductTagWithProductsRequest;
use App\Models\Product\Product;
use App\Models\Product\ProductTag;
use App\Models\Product\ProductProduct;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductTagController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(GetProductWithPurchaseOptionsRequest $request, $project_id)
    {
        $project = Project::find($project_id);
        $all = $project->product_tags()->get();
        return response()->json($all);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductTagWithProductsRequest $request, $project_id)
    {
        $project = Project::find($project_id);
        if($project->freeProductTagSlots()<1)
            return response()->json([
                'message' => "Достигнут лимит количества групп продуктов."
            ], 400);
        $new = new ProductTag;
        $new->name = $request->name;
        $new->project_id = $request->project_id;
        $new->save();
        return response()->json($new, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(GetProductWithPurchaseOptionsRequest $request, $project_id, $id)
    {
        $project = Project::find($project_id);
        $item = $project->product_tags()->find($id);
        if(empty($item))
            return response()->json([
                'message' => "Не удалось найти группу продуктов с id=".$item->id
            ], 404);
        return response()->json($item);
    }
    
    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductTagWithProductsRequest $request, $project_id, $id)
    {
        $project = Project::find($project_id);
        $item = $project->product_tags()->find($id);
        if(empty($item))
            return response()->json([
                'message' => "Не удалось найти группу продуктов с id=".$item->id
            ], 404);
        $item->name = $request->name;
        $project->product_tags()->save($item);
        return response()->json($item, 200);
    }

    /**
     * Display the specified resource.
     */

    public function index_loaded(GetProductWithPurchaseOptionsRequest $request, $project_id)
    {
        $project = Project::find($project_id);
        $all = $project->product_tags()->with('products')->get();
        return response()->json($all);
    }
     

    public function show_loaded(GetProductWithPurchaseOptionsRequest $request, $project_id, $id)
    {
        $project = Project::find($project_id);
        $item = $project->product_tags()->with(['products', 'updated_by_user'])->find($id);
        if(empty($item))
            return response()->json([
                'message' => "Не удалось найти группу продуктов с id=".$item->id
            ], 404);
        return response()->json($item);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store_loaded(StoreProductTagWithProductsRequest $request, $project_id)
    {
        $project = Project::find($project_id);
        if($project->freeProductTagSlots()<1)
            return response()->json([
                'message' => "Достигнут лимит количества групп продуктов."
            ], 400);

        $new = new ProductTag;
        DB::transaction(function() use($request, $project, $new){
            $new->name = $request->name;
            $project->product_tags()->save($new);
            $this->process_products($new, $request);
        });
        
        return response()->json($new, 201);
    }
    
    /**
     * Update the specified resource in storage.
     */
    public function update_loaded(UpdateProductTagWithProductsRequest $request, $project_id, $id)
    {
        $project = Project::find($project_id);
        $item = $project->product_tags()->find($id);
        if(empty($item))
            return response()->json([
                'message' => "Не удалось найти группу продуктов с id=".$item->id
            ], 404);
        
        DB::transaction(function() use($request, $project, $item){
            $item->name = $request->name;
            $project->product_tags()->save($item);
            $this->process_products($item, $request);
        });

        return response()->json($item, 200);
    }
    
    // удаление
    public function destroy(DeleteProductRequest $request, $project_id, $id) 
    {
        $project = Project::find($project_id);
        $item = $project->product_tags()->find($id);
        if (empty($item))
            return response()->json([
                'message' => "Не удалось найти категорию продуктов с id=$id"
            ], 404);
            
            
        $item->delete();
        return response()->json($item);
    }

    private function process_products(ProductTag $item, Request $request){
                
        $nNewProducts = count(array_filter(
            $request['products'],
            fn($p)=>($p['id'] ?? 0)==0
        ));

        $project = Project::find($request['project_id']);
        $freeSlots = $project->freeProductSlots();
        if($freeSlots<$nNewProducts)
            return response()->json([
                'message' => "Невозможно добавить $nNewProducts продуктов. Превышается лимит (осталось $freeSlots)."
            ], 400);

        $products = [];
        foreach($request->products as $p){
            $p = Product::findOrNew($p['id']);

            if(empty($p->id)){
                $p->name = $p['name'];
                $p->project_id = $request['project_id'];
                $p->save();
            }

            $products[$p->id] = [];
        }
    
        $sync = $item->products()->sync($products);
        if(!empty($sync['attached'])||!empty($sync['detached'])||!empty($sync['updated']))
            $item->touch();
    }
}
