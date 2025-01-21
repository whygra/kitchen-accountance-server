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
            'project_id' => $this->project_id,
            'updated_by_user' => $this->updated_by_user()->first(),
            'name' => $this->name,
            'type' => $this->type,
            'category' => $this->category,
            'group' => $this->group,
            'products' => IngredientProductWithPurchaseOptionsResource::collection($this->products),
            'source_weight' => $this->source_weight,
            'avg_waste_percentage' => $this->avg_waste_percentage,
            'item_weight' => $this->item_weight,
        ];
    }
}