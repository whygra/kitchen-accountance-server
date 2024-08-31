<?php

namespace App\Http\Controllers;

use App\Models\Ingredient\IngredientType;
use App\Http\Requests\IngredientType\GetIngredientTypeRequest;
use App\Http\Requests\IngredientType\UpdateIngredientTypeRequest;
use Exception;

class IngredientTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(GetIngredientTypeRequest $request)
    {
        $all = IngredientType::all();
        return response()->json($all);
    }
    /**
     * Display the specified resource.
     */
    public function show(GetIngredientTypeRequest $request, $id)
    {
        $item = IngredientType::find($id);
        return response()->json($item);
    }

}
