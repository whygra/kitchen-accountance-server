<?php
namespace App\Http\Resources\Product;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseOptionResource extends JsonResource
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
            'code' => $this->code,
            'name' => $this->name,
            'net_weight' => $this->net_weight,
            'price' => $this->price,
            'unit' => $this->unit,
            'distributor' => new DistributorResource($this->distributor)
        ];
    }
}