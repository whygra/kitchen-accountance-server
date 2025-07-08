<?php
namespace App\Http\Resources\Storage;

use App\Models\Storage\InventoryActProduct;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InventoryActResource extends JsonResource
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
            'updated_by_user' => $this->updated_by_user,
            'updated_at' => $this->updated_at,
            'date' => $this->date,
            // связанные сущности
            'products' => InventoryActProductResource::collection($this->products),
            'ingredients' => InventoryActIngredientResource::collection($this->ingredients),
        ];
    }
}