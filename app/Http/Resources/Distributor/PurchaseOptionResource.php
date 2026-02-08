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
            'updated_by_user' => $this->updated_by_user,
            'is_relevant' => $this->is_relevant,
            'code' => $this->code,
            'name' => $this->name,
            'net_weight' => $this->net_weight,
            'price' => $this->price,
            'unit' => new UnitResource($this->unit),
            'product' => new PurchaseOptionProductResource($this->product),
        ];
    }
}