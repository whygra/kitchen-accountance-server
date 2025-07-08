<?php
namespace App\Http\Resources\Storage;

use App\Models\Storage\PurchaseActItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseActResource extends JsonResource
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
            'updated_by_user' => $this->updated_by_user()->first(),
            'updated_at' => $this->updated_at,
            'distributor' => $this->distributor,
            'date' => $this->date,
            'total_cost' => $this->total_cost,
            // связанные сущности
            'items' => PurchaseActItemResource::collection($this->items),
        ];
    }
}