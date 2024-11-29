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
use App\Models\Product\ProductCategory;
use App\Models\Product\ProductGroup;
use App\Models\Project;
use Exception;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Calculation\Category;

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
        $all = $project->products()->with('purchase_options.distributor', 'category')->get();
        return response()->json(ProductResource::collection($all));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductWithPurchaseOptionsRequest $request, $project_id)
    {
        $project = Project::find($project_id);
        $new = new Product;
        $new->name = $request->name;
        $new->category_id = $request->category_id;
        $new->group_id = $request->group_id;
        $project->products()->save($new);
        return response()->json($new, 201);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store_with_purchase_options(StoreProductWithPurchaseOptionsRequest $request, $project_id)
    {
        $project = Project::find($project_id);
        $item = new Product;

        DB::transaction(function() use ($request, $project, $item) {
            // обновление данных поставщика
            $item->name = $request->name;

            $group = null;
            if($request['group']['id'] !== 0)
                $group = $project->product_groups()->find($request['group']['id']);
            else if(!empty($request['group']['name']))
                $group = $project->products()->create([
                    'name'=>$request['group']['name'],
                ]);
            $item->group()->associate($group);

            $category = null;
            if($request['category']['id'] !== 0)
                $category = $project->product_categories()->find($request['category']['id']);
            else if(!empty($request['category']['name']))
                $category = $project->product_categories()->create([
                    'name'=>$request['category']['name'],
                ]);
            $item->category()->associate($category);

            $item->save();
            
            $this->process_purchase_options($request, $project->id, $item);
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
        $item = $project->products()->with('purchase_options.distributor', 'category')->find($id);
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
        $item->category_id = $request->category_id;
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
            
            $group = null;
            if($request['group']['id'] !== 0)
                $group = $project->product_groups()->find($request['group']['id']);
            else if(!empty($request['group']['name']))
                $group = $project->product_groups()->create([
                    'name'=>$request['group']['name'],
                ]);

            $item->group()->associate($group);

            $category = null;
            if($request['category']['id'] !== 0)
                $category = $project->product_categories()->find($request['category']['id']);
            else if(!empty($request['category']['name']))
                $category = $project->product_categories()->create([
                    'name'=>$request['category']['name'],
                ]);

            $item->category()->associate($category);

            $project->products()->save($item);
            
            $this->process_purchase_options($request, $project->id, $item);
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
        $purchaseOptions = [];
        foreach($request->purchase_options as $o){
            $option = PurchaseOption::with([
                'distributor' => function($query)use($request){
                    $query->where('project_id', $request['project_id']);
                }
            ])->find($o['id']);
            $purchaseOptions[$option->id] = ['product_share'=>$o['product_share']];
        }
    
        $sync = $item->purchase_options()->sync($purchaseOptions);
        if(!empty($sync['attached'])||!empty($sync['detached'])||!empty($sync['updated']))
            $item->touch();

    }
}
