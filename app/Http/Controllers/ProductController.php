<?php

namespace App\Http\Controllers;

use App\Http\Requests\Product\DeleteProductRequest;
use App\Http\Requests\Product\GetProductWithPurchaseOptionsRequest;
use App\Http\Resources\Product\ProductResource;
use App\Models\Product\Product;
use App\Http\Requests\Product\StoreProductWithPurchaseOptionsRequest;
use App\Http\Requests\Product\UpdateProductWithPurchaseOptionsRequest;
use App\Models\Distributor\PurchaseOption;
use App\Models\Distributor\Unit;
use App\Models\Product\ProductTag;
use App\Models\Project;
use Exception;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(GetProductWithPurchaseOptionsRequest $request, $project_id)
    {
        $project = Project::find($project_id);
        $all = $project->products()->get();
        return response()->json($all);
    }
    public function index_with_purchase_options(GetProductWithPurchaseOptionsRequest $request, $project_id)
    {
        $project = Project::find($project_id);
        $all = $project->products()->with('purchase_options.distributor', 'tags')->get();
        return response()->json(ProductResource::collection($all));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductWithPurchaseOptionsRequest $request, $project_id)
    {
        $project = Project::find($project_id);
        if($project->freeProductSlots()<1)
            return response()->json([
                'message' => "Достигнут лимит количества продуктов."
            ], 400);
        $new = new Product;
        $new->name = $request->name;
        $project->products()->save($new);
        return response()->json($new, 201);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store_with_purchase_options(StoreProductWithPurchaseOptionsRequest $request, $project_id)
    {
        $project = Project::find($project_id);
        if($project->freeProductSlots()<1)
            return response()->json([
                'message' => "Достигнут лимит количества продуктов."
            ], 400);

        $item = new Product;

        DB::transaction(function() use ($request, $project, $item) {
            // обновление данных поставщика
            $item->name = $request->name;

            $item->save();
            
            $this->process_purchase_options($request, $project->id, $item);
            $this->process_tags($item, $request);
        });
        $project->products()->save($item);
        return response()->json($item, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(GetProductWithPurchaseOptionsRequest $request, $project_id, $id)
    {
        $project = Project::find($project_id);
        $item = $project->products()->find($id);
        if (empty($item))
            return response()->json([
                'message' => "Не удалось найти продукт с id=".$item->id
            ], 404);

        return response()->json($item);
    }
    public function show_with_purchase_options(GetProductWithPurchaseOptionsRequest $request, $project_id, $id)
    {
        $project = Project::find($project_id);
        $item = $project->products()->with('purchase_options.distributor', 'tags')->find($id);
        if (empty($item))
            return response()->json([
                'message' => "Не удалось найти продукт с id=".$item->id
            ], 404);

        return response()->json(new ProductResource($item));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductWithPurchaseOptionsRequest $request, $project_id, $id)
    {
        $project = Project::find($project_id);
        $item = $project->products()->find($id);
        if (empty($item))
            return response()->json([
                'message' => "Не удалось найти продукт с id=".$item->id
            ], 404);

        $item->name = $request->name;
        $project->products()->save($item);
        return response()->json($item, 200);
    }

    
    public function update_with_purchase_options(UpdateProductWithPurchaseOptionsRequest $request, $project_id, $id)
    {
        $project = Project::find($project_id);
        $item = $project->products()->find($id);
        if (empty($item))
            return response()->json([
                'message' => "Не удалось найти продукт с id=".$item->id
            ], 404);

        DB::transaction(function() use ($request, $project, $item) {
            // обновление данных поставщика
            $item->name = $request->name;

            $project->products()->save($item);
            
            $this->process_purchase_options($request, $project->id, $item);
            $this->process_tags($item, $request);
        });

        return response()->json($item, 201);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DeleteProductRequest $request, $project_id, $id)
    {
        $item = Product::where('project_id', $request['project_id'])->find($id);
        if (empty($item))
            return response()->json([
                'message' => "Не удалось найти продукт с id=".$item->id
            ], 404);

        $item->delete();
        return response()->json($item, 204);
    }

    
    private function process_purchase_options(FormRequest $request, $project_id, Product $item) {
        $sync = $item->purchase_options()->update(['product_id' => null]);
        foreach($request->purchase_options as $o){
            $option = PurchaseOption::with([
                'distributor' => function($query)use($project_id){
                    $query->where('project_id', $project_id);
                }
            ])->find($o['id']);
            $item->purchase_options()->save($option);
        }
    

            $item->touch();

    }

    
    // теги
    private function process_tags(Product $item, FormRequest $request) {
        $project = Project::find($request->project_id);
        
        $nNewTags = count(array_filter(
            $request['tags'],
            fn($p)=>($p['id'] ?? 0)==0
        ));

        $freeSlots = $project->freeDishTagSlots();
        if($freeSlots<$nNewTags)
            return response()->json([
                'message' => "Невозможно добавить $nNewTags тэгов. Превышается лимит (осталось $freeSlots)."
            ], 400);
            
        $tags = [];
            
        foreach($request->tags as $t){
            $tag = $project->product_tags()->where('name', $t['name'])->first();

            if(empty($tag)){
                $tag = $project->product_tags()->create();
                $tag->name = $t['name'];
                $tag->save();
            }

            array_push($tags, $tag->id);
        }

        $item->tags()->sync($tags);
    
    }

}
