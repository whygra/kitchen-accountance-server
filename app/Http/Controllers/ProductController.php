<?php

namespace App\Http\Controllers;

use App\Models\Product\Product;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use Exception;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $all = Product::all();
        return response()->json($all);
    }
    public function index_with_purchase_options()
    {
        $all = Product::with('products_purchase_options.purchase_option')->all();
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
     * Store a newly created resource in storage.
     */
    public function store(StoreProductRequest $request)
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
    public function show($id)
    {
        $item = Product::find($id);
        if (empty($item))
            return response()->json([
                'message' => '404'
            ], 404);

        return response()->json($item);
    }
    public function show_with_purchase_options($id)
    {
        $item = Product::with('products_purchase_options.purchase_option')->find($id);
        if (empty($item))
            return response()->json([
                'message' => '404'
            ], 404);

        return response()->json($item);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductRequest $request, $id)
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
    public function destroy($id)
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
