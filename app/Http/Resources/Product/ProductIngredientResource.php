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
            'group' => $this->group,
            'total_gross_weight' => $this->total_gross_weight,
            'item_weight' => $this->item_weight,
            'is_item_measured' => $this->is_item_measured,
            'dishes' => IngredientDishResource::collection($this->dishes),
            'waste_percentage' => 100 - $this->pivot->net_weight/($this->pivot->gross_weight==0?1:$this->pivot->gross_weight)*100,
            'gross_weight' => $this->pivot->gross_weight,
            'gross_share' => $this->pivot->gross_weight/$this->total_gross_weight,
            'net_weight' => $this->pivot->net_weight,
        ];
    }
}