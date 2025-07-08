<?php
namespace App\Http\Resources\Dish;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class DishWithPurchaseOptionsResource extends JsonResource
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
            'updated_by_user' => $this->updated_by_user(),
            'name' => $this->name,
            'category' => $this->category,
            'group' => $this->group,
            'total_net_weight' => $this->total_net_weight,
            'total_gross_weight' => $this->total_gross_weight,
            'ingredients' => DishIngredientWithPurchaseOptionsResource::collection($this->ingredients),
            'image' => [
                'url'=>$this->image_name == '' ? '' : url()->to('/').Storage::url($this->getImageDirectoryPath().'/'.$this->image_name),
                'name'=>$this->image_name,
            ]
        ];
    }
}