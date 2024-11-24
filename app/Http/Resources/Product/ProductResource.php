<?php
namespace App\Http\Resources\Product;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
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
            'updated_by_user' => $this->updated_by_user(),
            'name' => $this->name,
            'category' => $this->category,
            'group' => $this->group,
            'purchase_options' => PurchaseOptionResource::collection($this->purchase_options),
            'ingredients' => ProductIngredientResource::collection($this->ingredients),
        ];
    }
}