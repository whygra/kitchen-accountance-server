<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDishWithComponentsRequest;
use App\Http\Requests\StoreDishWithProductsRequest;
use App\Http\Requests\UpdateDishWithComponentsRequest;
use App\Http\Requests\UpdateDishWithProductsRequest;
use App\Models\Component;
use App\Models\ComponentType;
use App\Models\Dish;
use App\Models\DishComponent;
use App\Models\Product;
use Illuminate\Http\Request;

class DishWithComponentsController extends Controller
{
    public function index()
    {
        $all = Dish::with('dishes_components.component.type')->get();
        return response()->json($all);
    }

    public function show($id)
    {
        $item = Dish::with('dishes_components.component.type')->find($id);
        if (empty($item))
            return response()->json([
                'message' => "404"
            ], 404);
            
        return response()->json($item);
    }

    public function store(StoreDishWithComponentsRequest $request)
    {
        
        $item = new Dish;

        // обновление данных компонента
        $item->name = $request->name;
        $item->image_path = $request->image_path ?? "";
        $item->save();
        
        // для каждой связи блюдо-компонент
        foreach ($request->dishes_components as $dishComponentData){

            $component_id = $dishComponentData['component_id'];

            // создание, обновление компонента
            switch($dishComponentData['component_data_action']){
                case 'create':
                    // создание компонента
                    $component = new Component;
                    $component->name = $dishComponentData['component_name'] ?? '';
                    $type = ComponentType::find($dishComponentData['component_type_id'] ?? 1);
                    $component->type()->associate($type);
                    $component->save();
                    break;
                case 'update':
                    // обновление компонента
                    $component = Component::find($component_id);
                    $component->name = $dishComponentData['component_name'] ?? '';
                    $type = ComponentType::find($dishComponentData['component_type_id'] ?? 1);
                    $component->type()->associate($type);
                    $component->save();
                    break;
                case 'none':
                    // получение компонента
                    $component = Component::find($component_id);
                    break;
                default:
                    continue 2;
            }

            // создание связи
            $dishComponent = new DishComponent;
            $dishComponent->component_raw_weight = $dishComponentData['component_raw_weight'];
            $dishComponent->waste_percentage = $dishComponentData['waste_percentage'];
            
            $item->dishes_components()->save($dishComponent->component()->associate($component));
            $dishComponent->save();
        }
        
        return response()->json($item->with('dishes_components.component', 'type'), 201);
    }

    /**
     * Update the specified resource in storage.
     */ 
    public function update(UpdateDishWithComponentsRequest $request, $id)
    {
        $item = Dish::find($id);
        if (empty($item))
            return response()->json([
                'message' => "Компонент с id=$id не найден"
            ], 404);

        // для каждой связи блюдо-компонент
        foreach ($request->dishes_components as $dishComponentData){

            $component_id = $dishComponentData['component_id'];
            $dish_component_id = $dishComponentData['id'];            

            // создание, обновление связи
            switch($dishComponentData['data_action']){
                case 'create':
                    // создание связи
                    $dishComponent = new DishComponent;
                    $dishComponent->waste_percentage = $dishComponentData['waste_percentage'];
                    $dishComponent->weight = $dishComponentData['weight'];
                    break;
                case 'update':
                    // обновление связи
                    $dishComponent = DishComponent::find($dish_component_id);
                    $dishComponent->waste_percentage = $dishComponentData['waste_percentage'];
                    $dishComponent->component_raw_weight = $dishComponentData['component_raw_weight'];
                    break;
                case 'none':
                    // получение связи
                    $dishComponent = DishComponent::find($dish_component_id);
                    break;
                case 'delete':
                    // удаление связи
                    $dishComponent = DishComponent::find($dish_component_id);
                    $dishComponent->delete();
                default:
                    continue 2;
            }

            // создание, обновление продукта
            switch($dishComponentData['product_data_action']){
                case 'create':
                    // создание продукта
                    $component = new Component;
                    $component->name = $dishComponentData['component_name'] ?? '';
                    $component->type_id = $dishComponentData['component_type_id'];
                    $component->save();
                    break;
                case 'update':
                    // обновление продукта
                    $component = Component::find($component_id);
                    $component->name = $dishComponentData['component_name'] ?? '';
                    $component->type_id = $dishComponentData['component_type_id'] ?? 1;
                    $component->save();
                    break;
                case 'none':
                    // получение продукта
                    $component = Component::find($component_id);
                    break;
                default:
                    continue 2;
            }

            $item->dishes_components()->save($dishComponent->component()->associate($component)->dish()->associate($item));
        }

        // обновление данных компонента
        $item->name = $request->name;
        $item->type_id = $request->type_id;
        $item->image_path = $request->image_path;
        $item->save();

        return response()->json($item->with('dishes_components.component', 'type'), 200);
    }
}
