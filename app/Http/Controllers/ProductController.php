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
use Exception;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Calculation\Category;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(GetProductWithPurchaseOptionsRequest $request)
    {
        $all = Product::all();
        return response()->json($all);
    }
    public function index_with_purchase_options(GetProductWithPurchaseOptionsRequest $request)
    {
        $all = Product::with('purchase_options.distributor', 'category')->get();
        return response()->json(ProductResource::collection($all));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductWithPurchaseOptionsRequest $request)
    {
        $new = new Product;
        $new->name = $request->name;
        $new->category_id = $request->category_id;
        $new->save();
        return response()->json($new, 201);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store_with_purchase_options(StoreProductWithPurchaseOptionsRequest $request)
    {
        $item = new Product;

        DB::transaction(function() use ($request, $item) {
            // обновление данных поставщика
            $item->name = $request->name;

            $category = null;
            if($request['category']['id'] !== 0)
                $category = ProductCategory::find($request['category']['id']);
            else if(!empty($request['category']['name']))
                $category = ProductCategory::create(['name'=>$request['category']['name']]);

            // if(!empty($category))
                $item->category()->associate($category);

            $item->save();
            
            $this->process_purchase_options($request, $item);
        });
        $item->save();
        return response()->json($item, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(GetProductWithPurchaseOptionsRequest $request, $id)
    {
        $item = Product::find($id);
        if (empty($item))
            return response()->json([
                'message' => '404'
            ], 404);

        return response()->json($item);
    }
    public function show_with_purchase_options(GetProductWithPurchaseOptionsRequest $request, $id)
    {
        $item = Product::with('purchase_options.distributor', 'category')->find($id);
        if (empty($item))
            return response()->json([
                'message' => '404'
            ], 404);

        return response()->json(new ProductResource($item));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductWithPurchaseOptionsRequest $request, $id)
    {
        $item = Product::find($id);
        if (empty($item))
            return response()->json([
                'message' => '404'
            ], 404);

        $item->name = $request->name;
        $item->category_id = $request->category_id;
        $item->save();
        return response()->json($item, 200);
    }

    
    public function update_with_purchase_options(UpdateProductWithPurchaseOptionsRequest $request, $id)
    {
        $item = Product::find($id);
        if (empty($item))
            return response()->json([
                'message' => '404'
            ], 404);

        DB::transaction(function() use ($request, $item) {
            // обновление данных поставщика
            $item->name = $request->name;

            $category = ProductCategory::findOrNew($request['category']['id']);
            if(empty($category->id)&&!empty($request['category']['name'])){
                $category->name = $request['category']['name'];
                $category->save();
                $item->category()->associate($category);
            }       
            $item->save();
            
            $this->process_purchase_options($request, $item);
        });

        return response()->json($item, 201);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DeleteProductRequest $request, $id)
    {
        $item = Product::find($id);
        if (empty($item))
            return response()->json([
                'message' => '404'
            ], 404);

        $item->delete();
        return response()->json($item, 204);
    }

    
    private function process_purchase_options(FormRequest $request, Product $item) {
        $purchaseOptions = [];
        foreach($request->purchase_options as $o){
            $option = PurchaseOption::find($o['id']);
            $purchaseOptions[$option->id] = ['product_share'=>$o['product_share']];
        }
    
        $item->purchase_options()->sync($purchaseOptions);

    }
}
