<?php
namespace App\Http\Resources\Product;

use App\Http\Resources\Ingredient\IngredientDishResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductIngredientResource extends JsonResource
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
            'item_weight' => $this->item_weight,
            'is_item_measured' => $this->is_item_measured,
            'dishes' => IngredientDishResource::collection($this->dishes),
            'raw_content_percentage' => $this->pivot->raw_content_percentage,
            'waste_percentage' => $this->pivot->waste_percentage,
        ];
    }
}