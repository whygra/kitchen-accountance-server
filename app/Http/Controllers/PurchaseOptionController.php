<?php

namespace App\Http\Controllers;

use App\Http\Requests\PurchaseOption\DeletePurchaseOptionRequest;
use App\Http\Requests\PurchaseOption\GetPurchaseOptionWithProductsRequest;
use App\Models\Distributor\PurchaseOption;
use App\Http\Requests\PurchaseOption\StorePurchaseOptionWithProductsRequest;
use App\Http\Requests\PurchaseOption\UpdatePurchaseOptionWithProductsRequest;
use App\Http\Resources\PurchaseOption\PurchaseOptionResource;
use App\Models\Product\Product;
use App\Models\Product\ProductPurchaseOption;
use Exception;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;

class PurchaseOptionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(GetPurchaseOptionWithProductsRequest $request)
    {
        $all = PurchaseOption::with('unit', 'distributor')->get();
        return response()->json($all);
    }
    public function index_loaded(GetPurchaseOptionWithProductsRequest $request)
    {
        $all = PurchaseOption::with('unit', 'distributor', 'products')->get();
        return response()->json(PurchaseOptionResource::collection($all));
    }

    /**
     * Display the specified resource.
     */
    public function show(GetPurchaseOptionWithProductsRequest $request, $id)
    {
        $item = PurchaseOption::find($id);
        return response()->json($item);
    }
    public function show_loaded(GetPurchaseOptionWithProductsRequest $request, $id)
    {
        $item = PurchaseOption::with('unit', 'distributor', 'products')->find($id);
        return response()->json(new PurchaseOptionResource($item));
    }


    /**
     * Store a newly created resource in storage.
     */ 
    public function store(StorePurchaseOptionWithProductsRequest $request)
    {
        $new = new PurchaseOption;
        $new->unit_id = $request->unit_id;
        $new->name = $request->name;
        $new->net_weight = $request->net_weight;
        $new->distributor_id = $request->distributor_id;
        $new->price = $request->price;
        $new->save();
        return response()->json($new, 201);    
    }        

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePurchaseOptionWithProductsRequest $request, $id)
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

        $item->save();


        return response()->json($item, 200);
    }


    /**
     * Store a newly created resource in storage.
     */ 
    public function store_loaded(StorePurchaseOptionWithProductsRequest $request)
    {
        $item = new PurchaseOption;
        DB::transaction(function() use ($request, $item) {

            $item->unit_id = $request['unit']['id'];
            $item->name = $request->name;
            $item->code = $request->code;
            $item->net_weight = $request->net_weight;
            $item->distributor_id = $request['distributor']['id'];
            $item->price = $request->price;
            $item->save();
            $this->process_products($item, $request);
        });

        return response()->json($item, 201);    
    }        

    /**
     * Update the specified resource in storage.
     */
    public function update_loaded(UpdatePurchaseOptionWithProductsRequest $request, $id)
    {
        $item = PurchaseOption::find($id);
        if(empty($item))
            return response()->json([
                'message' => ''
            ], 404);

        DB::transaction(function() use ($request, $item) {
            $item->unit_id = $request['unit']['id'];
            $item->name = $request->name;
            $item->code = $request->code;
            $item->net_weight = $request->net_weight;
            $item->distributor_id = $request['distributor']['id'];
            $item->price = $request->price;

            $this->process_products($item, $request);

            $item->save();
        });

        return response()->json($item, 200);
    }

    private function process_products(PurchaseOption $item, FormRequest $request) {
        $products = [];
        foreach($request->products as $p){
            $product = Product::findOrNew($p['id']);
            if(empty($product->id))
            {
                $product->name = $p['name'];
                $product->save();
            }
            $products[$product->id] = ['product_share'=>$p['product_share']];
        }
    
        $item->products()->sync($products);
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DeletePurchaseOptionRequest $request, $id)
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
