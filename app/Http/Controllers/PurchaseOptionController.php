<?php

namespace App\Http\Controllers;

use App\Models\Distributor\PurchaseOption;
use App\Http\Requests\PurchaseOption\StorePurchaseOptionRequest;
use App\Http\Requests\PurchaseOption\UpdatePurchaseOptionRequest;
use App\Models\Product\Product;
use App\Models\Product\ProductPurchaseOption;
use Exception;
use Illuminate\Foundation\Http\FormRequest;

class PurchaseOptionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $all = PurchaseOption::all();
        return response()->json($all);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $item = PurchaseOption::find($id);
        return response()->json($item);
    }


    /**
     * Store a newly created resource in storage.
     */ 
    public function store(StorePurchaseOptionRequest $request)
    {
        $new = new PurchaseOption;
        $new->product_id = $request->product_id;
        $new->unit_id = $request->unit_id;
        $new->name = $request->name;
        $new->net_weight = $request->net_weight;
        $new->distributor_id = $request->distributor_id;
        $new->declared_price = $request->declared_price;
        $new->save();
        return response()->json([
            'message' => "201"
        ], 201);    
    }        

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePurchaseOptionRequest $request, $id)
    {
        $item = PurchaseOption::find($id);
        if(empty($item))
            return response()->json([
                'message' => ''
            ], 404);

        $item->product_id = $request->product_id;
        $item->unit_id = $request->unit_id;
        $item->name = $request->name;
        $item->net_weight = $request->net_weight;
        $item->declared_price = $request->declared_price;

        $item->save();


        return response()->json([
            'message' => ''
        ], 200);
    }


    /**
     * Store a newly created resource in storage.
     */ 
    public function store_loaded(StorePurchaseOptionRequest $request)
    {
        $new = new PurchaseOption;
        $new->product_id = $request->product_id;
        $new->unit_id = $request->unit_id;
        $new->name = $request->name;
        $new->net_weight = $request->net_weight;
        $new->distributor_id = $request->distributor_id;
        $new->declared_price = $request->declared_price;
        $this->process_products($new, $request);
        $new->save();
        return response()->json([
            'message' => "201"
        ], 201);    
    }        

    /**
     * Update the specified resource in storage.
     */
    public function update_loaded(UpdatePurchaseOptionRequest $request, $id)
    {
        $item = PurchaseOption::find($id);
        if(empty($item))
            return response()->json([
                'message' => ''
            ], 404);

        $item->product_id = $request->product_id;
        $item->unit_id = $request->unit_id;
        $item->name = $request->name;
        $item->net_weight = $request->net_weight;
        $item->distributor_id = $request->distributor_id;
        $item->declared_price = $request->declared_price;

        $this->process_products($item, $request);

        $item->save();

        return response()->json([
            'message' => ''
        ], 200);
    }

    private function process_products(PurchaseOption $item, FormRequest $request) {
        $products = [];
        foreach($request->products as $p){
            $product = Product::findOrNew($p['id']);
            $product->name = $p['name'];
            $product->save();
            $products[$product->id] = ['product_share'=>$p['product_share']];
        }
    
        $item->products()->sync($products);
    }


    /**
     * Display the specified resource.
     */

     public function index_loaded()
     {
         $all = PurchaseOption::with('purchase_options_products')->all();
         return response()->json($all);
     }
     

    public function show_loaded($id)
    {
        $item = PurchaseOption::with('purchase_options_products')->find($id);
        if(empty($item))
            return response()->json([
                'message' => '404'
            ], 404);
        return response()->json($item);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $item = PurchaseOption::find($id);
        if(empty($item))
            return response()->json([
                'message' => ''
            ], 404);

        $item->save();
        return response()->json($item, 200);
    }
}
