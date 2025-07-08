<?php
namespace App\Http\Resources\Storage;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WriteOffActResource extends JsonResource
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
            'date' => $this->date,
            // связанные сущности
            'items' => InventoryActIngredientResource::collection($this->items),
        ];
    }
}