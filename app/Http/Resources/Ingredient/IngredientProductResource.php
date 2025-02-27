<?php
namespace App\Http\Resources\Ingredient;

use App\Http\Resources\Product\ProductPurchaseOptionResource;
use App\Models\Ingredient\Ingredient;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IngredientProductResource extends JsonResource
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
            'raw_product_weight' => $this->pivot->raw_product_weight,
            'raw_content_percentage' => $this->pivot->raw_content_percentage,
            'waste_percentage' => $this->pivot->waste_percentage,
            'purchase_options' => ProductPurchaseOptionResource::collection($this->purchase_options)
        ];
    }
}