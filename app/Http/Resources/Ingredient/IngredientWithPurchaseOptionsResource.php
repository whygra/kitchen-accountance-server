<?php
namespace App\Http\Resources\Ingredient;

use App\Http\Resources\Dish\IngredientProductWithPurchaseOptionsResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IngredientWithPurchaseOptionsResource extends JsonResource
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
            'products' => IngredientProductWithPurchaseOptionsResource::collection($this->products),
            'item_weight' => $this->item_weight,
        ];
    }
}