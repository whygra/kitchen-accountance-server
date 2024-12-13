<?php

namespace App\Http\Controllers;

use App\Http\Requests\Product\DeleteProductRequest;
use App\Http\Requests\Product\GetProductWithPurchaseOptionsRequest;
use App\Http\Requests\ProductGroup\StoreProductGroupWithProductsRequest;
use App\Http\Requests\ProductGroup\UpdateProductGroupWithProductsRequest;
use App\Models\Product\ProductGroup;
use App\Models\Product\ProductProduct;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductGroupController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(GetProductWithPurchaseOptionsRequest $request, $project_id)
    {
        $project = Project::find($project_id);
        $all = $project->product_groups()->get();
        return response()->json($all);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductGroupWithProductsRequest $request)
    {
        $new = new ProductGroup;
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
        $item = $project->product_groups()->find($id);
        if(empty($item))
            return response()->json([
                'message' => "Не удалось найти категорию продуктов с id=".$item->id
            ], 404);
        return response()->json($item);
    }
    
    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductGroupWithProductsRequest $request, $project_id, $id)
    {
        $project = Project::find($project_id);
        $item = $project->product_groups()->find($id);
        if(empty($item))
            return response()->json([
                'message' => "Не удалось найти категорию продуктов с id=".$item->id
            ], 404);
        $item->name = $request->name;
        $project->product_groups()->save($item);
        return response()->json($item, 200);
    }

    /**
     * Display the specified resource.
     */

    public function index_loaded(GetProductWithPurchaseOptionsRequest $request, $project_id)
    {
        $project = Project::find($project_id);
        $all = $project->product_groups()->with('products')->get();
        return response()->json($all);
    }
     

    public function show_loaded(GetProductWithPurchaseOptionsRequest $request, $project_id, $id)
    {
        $project = Project::find($project_id);
        $item = $project->product_groups()->with(['products', 'updated_by_user'])->find($id);
        if(empty($item))
            return response()->json([
                'message' => "Не удалось найти категорию продуктов с id=".$item->id
            ], 404);
        return response()->json($item);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store_loaded(StoreProductGroupWithProductsRequest $request, $project_id)
    {
        $new = new ProductGroup;
        DB::transaction(function() use($request, $project_id, $new){
            $project = Project::find($project_id);
            $new->name = $request->name;
            $project->product_groups()->save($new);
            $this->process_products($new, $request);
        });
        
        return response()->json($new, 201);
    }
    
    /**
     * Update the specified resource in storage.
     */
    public function update_loaded(UpdateProductGroupWithProductsRequest $request, $project_id, $id)
    {
        $project = Project::find($project_id);
        $item = $project->product_groups()->find($id);
        if(empty($item))
            return response()->json([
                'message' => "Не удалось найти категорию продуктов с id=".$item->id
            ], 404);
        
        DB::transaction(function() use($request, $project, $item){
            $item->name = $request->name;
            $project->product_groups()->save($item);
            $this->process_products($item, $request);
        });

        return response()->json($item, 200);
    }
    
    // удаление
    public function destroy(DeleteProductRequest $request, $project_id, $id) 
    {
        $project = Project::find($project_id);
        $item = $project->product_groups()->find($id);
        if (empty($item))
            return response()->json([
                'message' => "Не удалось найти категорию продуктов с id=$id"
            ], 404);
            
            
        $item->delete();
        return response()->json($item);
    }

    private function process_products(ProductGroup $item, Request $request){
        $item->products()->update(['group_id' => null]);
        $project = Project::find($request->project_id);
        foreach($request['products'] as $i){
            $product = $project->products()->findOrNew($i['id']);
            if(empty($product->id)){
                $product->name = $i['name'];
            }
                
            $item->products()->save($product);
        }
    }
}
