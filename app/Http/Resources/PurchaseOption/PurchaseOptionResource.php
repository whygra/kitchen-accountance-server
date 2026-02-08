<?php
namespace App\Http\Resources\PurchaseOption;

use App\Http\Resources\Distributor\PurchaseOptionProductResource;
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
            'is_relevant' => $this->is_relevant,
            'project_id' => $this->project_id,
            'updated_by_user' => $this->updated_by_user,
            'updated_at' => $this->updated_at,
            'code' => $this->code,
            'name' => $this->name,
            'net_weight' => $this->net_weight,
            'price' => $this->price,
            'distributor' => $this->distributor,
            'unit' => [
                'id'=>$this->unit?->id,
                'long'=>$this->unit?->long,
                'short'=>$this->unit?->short,
            ],
            'product' => new PurchaseOptionProductResource($this->product),
        ];
    }
}