<?php

namespace App\Http\Controllers;

use App\Http\Requests\Dish\DeleteDishRequest;
use App\Http\Requests\Dish\GetDishRequest;
use App\Http\Requests\Dish\StoreDishRequest;
use App\Http\Requests\Dish\StoreDishWithIngredientsRequest;
use App\Http\Requests\Dish\UpdateDishRequest;
use App\Http\Requests\Dish\UpdateDishWithIngredientsRequest;
use App\Http\Requests\Dish\UploadDishImageRequest;
use App\Http\Resources\Dish\DishResource;
use App\Http\Resources\Dish\DishWithPurchaseOptionsResource;
use App\Http\Resources\Dish\MinDishResource;
use App\Models\Dish\Dish;
use App\Models\Dish\DishTag;
use App\Models\Project;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DishController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(GetDishRequest $request, $project_id)
    {
        $all = Project::find($project_id)->dishes()->get();
        return response()->json($all);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDishRequest $request, $project_id)
    {
        $project = Project::find($project_id);
        if($project->freeDishSlots()<1)
            return response()->json([
                'message' => "Достигнут лимит количества блюд."
            ], 400);
        $new = new Dish;
        $new->name = $request->name;
        $project->dishes()->save($new);
        return response()->json($new, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(GetDishRequest $request, $project_id, $id)
    {
        $item = Project::find($project_id)->dishes()->find($id);
        if(empty($item))
            return response()->json([
                'message' => "Не удалось найти блюдо с id=".$item->id
            ], 404);
        return response()->json($item);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDishRequest $request, $project_id, $id)
    {
        $item = Project::find($project_id)->dishes()->findOrNew($id);
        if(empty($item->id))
            return response()->json([
                'message' => "Не удалось найти блюдо с id=".$item->id
            ], 404);
        $item->name = $request->name;
        $item->save();
            return response()->json($item, 200);
    }
    
    public function index_loaded(GetDishRequest $request, $project_id)
    {
        $all = Project::find($project_id)->dishes()->get();
        return response()->json(MinDishResource::collection($all));
    }

    public function show_loaded(GetDishRequest $request, $project_id, $id)
    {
        $item = Project::find($project_id)->dishes()->with(
            ['ingredients.type', 
            'tags']
        )->find($id);

        if (empty($item))
            return response()->json([
                'message' => "не удалось найти блюдо с id=".$id
            ], 404);
            
        return response()->json(new DishResource($item));
    }

    public function upload_image(UploadDishImageRequest $request, $project_id, $id){

        $file = $request->file('file');
        $item = Project::find($project_id)->dishes()->find($id);
        
        $image_uploaded_path = $item->uploadImage($file);

        return response()->json([
            "name" => basename($image_uploaded_path),
            "url" => Storage::url($image_uploaded_path),
        ], 201);
    }

    public function store_loaded(StoreDishWithIngredientsRequest $request, $project_id)
    {
        $project = Project::find($project_id);
        if($project->freeDishSlots()<1)
            return response()->json([
                'message' => "Достигнут лимит количества блюд."
            ], 400);

        $item = new Dish;
        DB::transaction(function() use ($request, $item, $project) {

            // добавление данных блюда
            $item->project_id = $request['project_id'];
            $item->description = $request['description'];

            $item->name = $request->name;

            $item->image_name = $request['image']['name'] ?? '';
    
            $item->save();
    
            $this->process_tags($item, $request);
            $this->process_ingredients($item, $request);
        });
	
        return response()->json($item, 201);
        
    }

    /**
     * Update the specified resource in storage.
     */ 
    public function update_loaded(UpdateDishWithIngredientsRequest $request, $project_id, $id)
    {
        $project = Project::find($project_id);
        $item = $project->dishes()->findOrNew($id);
        $item->description = $request['description'];

        if (empty($item->id))
            return response()->json([
                'message' => "Блюдо с id=$id не найдено"
            ], 404);


        DB::transaction(function() use ($request, $project, $item) {
            // обновление данных блюда
            $item->name = $request->name;
            
            $this->process_ingredients($item, $request);
            $this->process_tags($item, $request);
            $item->save();
            
        });

        return response()->json($item, 200);
    }


    private function process_ingredients(Dish $item, FormRequest $request) {
        $project = Project::find($request->project_id);
        
        $nNewIngredients = count(array_filter(
            $request['ingredients'],
            fn($p)=>($p['id'] ?? 0)==0
        ));

        $freeSlots = $project->freeIngredientSlots();
        if($freeSlots<$nNewIngredients)
            return response()->json([
                'message' => "Невозможно добавить $nNewIngredients ингредиентов. Превышается лимит количества ингредиентов (осталось $freeSlots)."
            ], 400);
            
        $ingredients = [];
        foreach($request->ingredients as $i){
            $ingredient = Project::find( $project->id)->ingredients()->findOrNew($i['id']??0);

            if(empty($ingredient->id)){
                $ingredient->type_id = $i['type']['id'];
                $ingredient->name = $i['name'];
                $ingredient->item_weight = $i['item_weight'];
                $project->ingredients()->save($ingredient);
            }

            $ingredients[$ingredient->id] = [
                'net_weight'=>$i['net_weight'],
                'amount'=>$i['amount'],
            ];
        }
    
        $sync = $item->ingredients()->sync($ingredients);
        
        $item->total_gross_weight = $item->atr_total_gross_weight;
        $item->total_net_weight = $item->atr_total_net_weight;
        // $item->avg_waste_percentage = 100 - $item->total_net_weight/($item->total_gross_weight==0?1:$item->total_gross_weight)*100;

        if(!empty($sync['detached'])||!empty($sync['attached'])||!empty($sync['updated']))
            $item->touch();

    }

    // теги
    private function process_tags(Dish $item, FormRequest $request) {
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
            
        foreach($request['tags'] as $t){
            $tag = $project->dish_tags()->where('name', $t['name'])->first();

            // если тега нет - создаем
            if(empty($tag)){

                $tag = $project->dish_tags()->create(['name'=>$t['name']]);
            }

            array_push($tags, $tag->id);
        }

        // синхронизировать теги
        $item->tags()->sync($tags);
    
    }

    // блюдо с позициями закупки
    public function index_with_purchase_options(GetDishRequest $request, $project_id)
    {
        $project = Project::find($project_id);
        
        $items = $project->dishes()->with(
            'ingredients.products.purchase_options.distributor',
            'ingredients.type',
            'tags',
            )->get();
            
        return response()->json(DishWithPurchaseOptionsResource::collection($items));
    }

    // блюдо с позициями закупки
    public function show_with_purchase_options(GetDishRequest $request, $project_id, $id)
    {
        $project = Project::find($project_id);
        $item = $project->dishes()->with(
            'ingredients.products.purchase_options.distributor',
            'ingredients.type',
            'tags',
            )->find($id);
        if (empty($item))
            return response()->json([
                'message' => "Блюдо с id=$id не найдено"
            ], 404);
            
        return response()->json(new DishWithPurchaseOptionsResource($item));
    }

    // удаление блюда и связей блюдо-компонент
    public function destroy(DeleteDishRequest $request, $project_id, $id)
    {
        $project = Project::find($project_id);
        $item = $project->dishes()->find($id);
        if (empty($item))
            return response()->json([
                'message' => "Не удалось найти Блюдо с id=$id"
            ], 404);
            
            
        $item->delete();
        return response()->json($item);
    }
}
