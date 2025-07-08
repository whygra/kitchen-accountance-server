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
            'waste_percentage' => 100 - $this->pivot->net_weight/($this->item_weight!=0?$this->item_weight*$this->pivot->ingredient_amount:1)*100,
            'total_gross_weight' => $this->total_gross_weight,
            'total_net_weight' => $this->total_net_weight,
            'ingredient_amount' => $this->pivot->ingredient_amount,
            'item_weight' => $this->item_weight,
            'net_weight' => $this->pivot->net_weight,
            'avg_waste_percentage' => $this->avg_waste_percentage,
        ];
    }
}