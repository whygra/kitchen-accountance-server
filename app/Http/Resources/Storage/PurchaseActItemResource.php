<?php
namespace App\Http\Resources\Storage;

use App\Http\Resources\Distributor\PurchaseOptionProductResource;
use App\Http\Resources\Distributor\PurchaseOptionResource;
use App\Http\Resources\Distributor\UnitResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseActItemResource extends JsonResource
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
            'unit' => new UnitResource($this->unit),
            'name' => $this->name,
            'code' => $this->code,
            'amount' => $this->pivot->amount,
            'net_weight' => $this->pivot->net_weight,
            'price' => $this->pivot->price,
            'products' => PurchaseOptionProductResource::collection($this->products)
        ];
    }
}