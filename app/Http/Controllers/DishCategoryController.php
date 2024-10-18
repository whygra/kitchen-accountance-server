<?php

namespace App\Http\Controllers;

use App\Http\Requests\DishCategory\DeleteDishCategoryRequest;
use App\Http\Requests\DishCategory\GetDishCategoryRequest;
use App\Http\Requests\DishCategory\StoreDishCategoryWithDishesRequest;
use App\Http\Requests\DishCategory\UpdateDishCategoryWithDishesRequest;
use App\Models\Dish\DishCategory;
use App\Models\Dish\DishIngredient;

class DishCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(GetDishCategoryRequest $request)
    {
        $all = DishCategory::all();
        return response()->json($all);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDishCategoryWithDishesRequest $request)
    {
        $new = new DishCategory;
        $new->name = $request->name;
        $new->save();
        return response()->json($new, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(GetDishCategoryRequest $request, $id)
    {
        $item = DishCategory::find($id);
        if(empty($item))
            return response()->json([
                'message' => '404'
            ], 404);
        return response()->json($item);
    }
    
    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDishCategoryWithDishesRequest $request, $id)
    {
        $item = DishCategory::find($id);
        $item->name = $request->name;
        if(empty($item))
            return response()->json([
                'message' => '404'
            ], 404);
        $item->name = $request->name;
        $item->save();
            return response()->json($item, 200);
    }


    /**
     * Display the specified resource.
     */

     public function index_loaded(GetDishCategoryRequest $request)
     {
         $all = DishCategory::with('dishes')->all();
         return response()->json($all);
     }


    public function show_loaded(GetDishCategoryRequest $request, $id)
    {
        $item = DishCategory::with('dishes')->find($id);
        if(empty($item))
            return response()->json([
                'message' => '404'
            ], 404);
        return response()->json($item);
    }

    // удаление
    public function destroy(DeleteDishCategoryRequest $request, $id) 
    {
        $item = DishCategory::find($id);
        if (empty($item))
            return response()->json([
                'message' => "Не удалось найти категорию блюда с id=$id"
            ], 404);

        $item->delete();
        return response()->json($item);
    }
}
