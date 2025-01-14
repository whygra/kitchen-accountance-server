<?php
namespace App\Http\Resources\Dish;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DishIngredientWithPurchaseOptionsResource extends JsonResource
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
            'name' => $this->name,
            'type' => $this->type,
            'category' => $this->category,
            'group' => $this->group,
            'products' => IngredientProductWithPurchaseOptionsResource::collection($this->products),
            'source_weight' => $this->source_weight,
            'ingredient_amount' => $this->pivot->ingredient_amount,
            'item_weight' => $this->item_weight,
            'waste_percentage' => $this->pivot->waste_percentage,
        ];
    }
}