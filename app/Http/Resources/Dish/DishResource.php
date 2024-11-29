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
            'project_id' => $this->project_id,
            'updated_by_user' => $this->updated_by_user()->first(),
            'updated_at' => $this->updated_at,
            'name' => $this->name,
            'category' => $this->category,
            'group' => $this->group,
            'ingredients' => DishIngredientResource::collection($this->ingredients),
            'image' => [
                'url'=>$this->image_name == '' ? '' : url()->to('/').Storage::url($this->getImageDirectoryPath().'/'.$this->image_name),
                'name'=>$this->image_name,
            ]
        ];
    }
}