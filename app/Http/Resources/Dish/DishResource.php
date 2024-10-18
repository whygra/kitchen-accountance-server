<?php
namespace App\Http\Resources\Dish;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class DishResource extends JsonResource
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
            'ingredients' => DishIngredientResource::collection($this->ingredients),
            'image' => [
                'url'=>$this->image_path == '' ? '' : url()->to('/').Storage::url('images/dishes/'.$this->image_path),
                'name'=>$this->image_path,
            ]
        ];
    }
}