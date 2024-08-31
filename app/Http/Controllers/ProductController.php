<?php

namespace App\Http\Controllers;

use App\Http\Requests\Product\DeleteProductRequest;
use App\Http\Requests\Product\GetProductWithPurchaseOptionsRequest;
use App\Models\Product\Product;
use App\Http\Requests\Product\StoreProductWithPurchaseOptionsRequest;
use App\Http\Requests\Product\UpdateProductWithPurchaseOptionsRequest;
use Exception;

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
        $all = Product::with('products_purchase_options.purchase_option')->all();
        return response()->json($all);
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
        $item = Product::with('products_purchase_options.purchase_option')->find($id);
        if (empty($item))
            return response()->json([
                'message' => '404'
            ], 404);

        return response()->json($item);
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
}
