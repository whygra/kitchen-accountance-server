<?php
namespace App\Http\Resources\Ingredient;

use App\Http\Resources\Product\ProductPurchaseOptionResource;
use App\Models\Ingredient\Ingredient;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IncludedIngredientResource extends JsonResource
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
            'waste_percentage' => 100-($this->pivot->net_weight/$this->pivot->amount/$this->item_weight * 100),
            'item_weight' => $this->item_weight,
            'amount' => $this->pivot->amount,
            'net_weight' => $this->pivot->net_weight,
        ];
    }
}