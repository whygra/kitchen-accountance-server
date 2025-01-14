<?php
namespace App\Http\Resources\Dish;

use App\Models\Ingredient\Ingredient;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IngredientProductWithPurchaseOptionsResource extends JsonResource
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
            'category' => $this->category,
            'group' => $this->group,
            'purchase_options' => ProductPurchaseOptionResource::collection($this->purchase_options),
            'raw_product_weight' => $this->pivot->raw_product_weight,
            'raw_content_percentage' => $this->pivot->raw_content_percentage,
            'waste_percentage' => $this->pivot->waste_percentage,
        ];
    }
}