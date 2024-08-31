<?php
namespace App\Http\Resources\Distributor;

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
            'name' => $this->name,
            'net_weight' => $this->net_weight,
            'price' => $this->price,
            'unit' => $this->unit,
            'products' => PurchaseOptionProductResource::collection($this->products),
        ];
    }
}