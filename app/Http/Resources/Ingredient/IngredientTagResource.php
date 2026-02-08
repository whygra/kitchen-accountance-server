<?php
namespace App\Http\Resources\Ingredient;

use App\Http\Resources\Dish\DishResource;
use App\Http\Resources\Dish\IngredientProductWithPurchaseOptionsResource;
use App\Http\Resources\Ingredient\IngredientResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IngredientTagResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'project_id' => $this->project_id,
            'updated_by_user' => $this->updated_by_user()->first(),
            'name' => $this->name,
            'ingredients' => IngredientResource::collection($this->ingredients),
        ];
    }
}