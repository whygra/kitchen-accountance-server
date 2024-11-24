<?php

namespace App\Http\Controllers;

use App\Http\Requests\Dish\DeleteDishRequest;
use App\Http\Requests\Dish\GetDishRequest;
use App\Http\Requests\Dish\StoreDishRequest;
use App\Http\Requests\Dish\StoreDishWithIngredientsRequest;
use App\Http\Requests\Dish\StoreDishWithProductsRequest;
use App\Http\Requests\Dish\UpdateDishRequest;
use App\Http\Requests\Dish\UpdateDishWithIngredientsRequest;
use App\Http\Requests\Dish\UpdateDishWithProductsRequest;
use App\Http\Requests\Dish\UploadDishImageRequest;
use App\Http\Resources\Dish\DishResource;
use App\Http\Resources\Dish\DishWithPurchaseOptionsResource;
use App\Models\Ingredient\Ingredient;
use App\Models\Dish\Dish;
use App\Models\Dish\DishCategory;
use App\Models\Dish\DishGroup;
use App\Models\Dish\DishIngredient;
use App\Models\Ingredient\SecondaryIngredient;
use App\Models\Product\Product;
use App\Models\Project;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
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
        $new = new Dish;
        $new->project_id = $request['project_id'];
        $new->name = $request->name;
        $new->save();
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
        $all = Project::find($project_id)->dishes()->with(
            'ingredients.type',
            'ingredients.category',
            'category'
        )->get();
        return response()->json(DishResource::collection($all));
    }

    public function show_loaded(GetDishRequest $request, $project_id, $id)
    {
        $item = Project::find($project_id)->dishes()->with(
            ['ingredients.type', 
            'ingredients.category',
            'ingredients.group',
            'category',
            'group']
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
        $item = new Dish;
        $project = Project::find($project_id);
        DB::transaction(function() use ($request, $item, $project) {

            // добавление данных блюда
            $item->project_id = $request['project_id'];
            
            $category = null;
            if($request['category']['id'])
                $category = $project->dish_categories()->find($request['category']['id']);
            else if(!empty($request['category']['name']))
                $category = $project->dish_categories()->create([
                    'name'=>$request['category']['name'],
                ]);
            $item->category()->associate($category);
            
            $group = null;
            if($request['group']['id'])
                $group = $project->dish_groups()->find($request['group']['id']);
            else if(!empty($request['group']['name']))
                $group = $project->dish_groups()->create([
                    'name'=>$request['group']['name'],
                ]);

            $item->group()->associate($group);

            $item->name = $request->name;

            $item->image_name = $request['image']['name'] ?? '';
    
            $item->save();
    
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

        if (empty($item->id))
            return response()->json([
                'message' => "Блюдо с id=$id не найдено"
            ], 404);


        DB::transaction(function() use ($request, $project, $item) {
            // обновление данных блюда
            $category = null;
            if($request['category']['id'])
                $category = $project->dish_categories()->find($request['category']['id']);
            else if(!empty($request['category']['name']))
                $category = $project->category()->create([
                    'name'=>$request['category']['name'],
                ]);

            $item->category()->associate($category);

            $group = null;
            if($request['group']['id'])
                $group = $project->dish_groups()->find($request['group']['id']);
            else if(!empty($request['group']['name']))
                $group = $project->dish_groups()->create([
                    'name'=>$request['group']['name'],
                ]);

            $item->group()->associate($group);
            
                
            $item->name = $request->name;
            if(!empty($request['image'])){
                if($item->image_name != '')
                    Storage::disk('public')->delete('images/dishes/'.$item->image_name);
                $item->image_name = $request['image']['name'] ?? '';
            }

            $item->save();
            
            $this->process_ingredients($item, $request);
        });

        return response()->json($item, 200);
    }


    private function process_ingredients(Dish $item, FormRequest $request) {
        $project = Project::find($request->project_id);
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
                'waste_percentage'=>$i['waste_percentage'],
                'ingredient_amount'=>$i['ingredient_amount']
            ];
        }
    
        $item->ingredients()->sync($ingredients);
    }

    // блюдо с позициями закупки
    public function index_with_purchase_options(GetDishRequest $request, $project_id)
    {
        $project = Project::find($project_id);
        
        $items = $project->dishes()->with(
            'ingredients.products.purchase_options.distributor',
            'ingredients.type',
            'ingredients.category',
            'category',
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
            'ingredients.category',
            'category',
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
