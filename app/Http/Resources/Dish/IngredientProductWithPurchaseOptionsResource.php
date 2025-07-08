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
            'waste_percentage' => 100-($this->pivot->net_weight/$this->pivot->gross_weight * 100),
            'gross_weight' => $this->pivot->gross_weight,
            'net_weight' => $this->pivot->net_weight,
        ];
    }
}