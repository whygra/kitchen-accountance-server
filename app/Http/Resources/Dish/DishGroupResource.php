<?php
namespace App\Http\Resources\Dish;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DishGroupResource extends JsonResource
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
            'dishes' => DishResource::collection($this->dishes),
        ];
    }
}