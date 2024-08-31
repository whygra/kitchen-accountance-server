<?php
namespace App\Http\Resources\Ingredient;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IngredientProductResource extends JsonResource
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
            'category' => $this->category,
            'raw_content_percentage' => $this->pivot->raw_content_percentage,
            'waste_percentage' => $this->pivot->waste_percentage,
        ];
    }
}