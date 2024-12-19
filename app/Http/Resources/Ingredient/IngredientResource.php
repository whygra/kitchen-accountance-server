<?php
namespace App\Http\Resources\Ingredient;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IngredientResource extends JsonResource
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
            'updated_at' => $this->updated_at,
            'name' => $this->name,
            'description' => $this->description,
            'type' => $this->type,
            'category' => $this->category,
            'group' => $this->group,
            'item_weight' => $this->item_weight,
            'is_item_measured' => $this->is_item_measured,
            'products' => IngredientProductResource::collection($this->products),
            'dishes' => IngredientDishResource::collection($this->dishes),
        ];
    }
}