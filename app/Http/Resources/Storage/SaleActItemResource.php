<?php
namespace App\Http\Resources\Storage;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SaleActItemResource extends JsonResource
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
            'amount' => $this->pivot->amount,
            'price' => $this->pivot->price,
        ];
    }
}