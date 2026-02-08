<?php
namespace App\Http\Resources\Dish;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductPurchaseOptionResource extends JsonResource
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
            'is_relevant' => $this->is_relevant,
            'name' => $this->name,
            'net_weight' => $this->net_weight,
            'price' => $this->price,
            'distributor' => $this->distributor,
            'unit' => $this->unit,
        ];
    }
}