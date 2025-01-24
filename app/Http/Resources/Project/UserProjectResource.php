<?php
namespace App\Http\Resources\Project;

use App\Http\Resources\User\RoleResource;
use App\Models\User\Role;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class UserProjectResource extends JsonResource
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
            'creator' => $this->creator()->first(),
            'updated_by_user' => $this->updated_by_user()->first(),
            'updated_at' => $this->updated_at,
            'logo' => [
                'url'=>$this->logo_name == '' ? '' 
                    : url()->to('/').Storage::url(
                        $this->getLogoDirectoryPath().'/'
                        .$this->logo_name
                    ),
                'name'=>$this->logo_name,
            ],
            'backdrop' => [
                'url'=>$this->backdrop_name == '' ? '' 
                    : url()->to('/')
                    .Storage::url(
                        $this->getBackdropDirectoryPath().'/'
                        .$this->backdrop_name
                    ),
                'name'=>$this->backdrop_name,
            ],
            'is_public' => $this->is_public,
            
            'num_distributors' => [
                'max' => $this->subscription_plan->max_num_distributors,
                'current' => $this->distributors->count(),
            ],
            'num_units' => [
                'max' => $this->subscription_plan->max_num_units,
                'current' => $this->units->count(),
            ],
            'num_products' => [
                'max' => $this->subscription_plan->max_num_products,
                'current' => $this->products->count(),
            ],
            'num_product_categories' => [
                'max' => $this->subscription_plan->max_num_product_categories,
                'current' => $this->product_categories->count(),
            ],
            'num_product_groups' => [
                'max' => $this->subscription_plan->max_num_product_categories,
                'current' => $this->product_groups->count(),
            ],
            'num_ingredients' => [
                'max' => $this->subscription_plan->max_num_ingredients,
                'current' => $this->ingredients->count(),
            ],
            'num_ingredient_categories' => [
                'max' => $this->subscription_plan->max_num_ingredient_categories,
                'current' => $this->ingredient_categories->count(),
            ],
            'num_ingredient_groups' => [
                'max' => $this->subscription_plan->max_num_ingredient_categories,
                'current' => $this->ingredient_groups->count(),
            ],
            'num_dishes' => [
                'max' => $this->subscription_plan->max_num_dishes,
                'current' => $this->dishes->count(),
            ],
            'num_dish_categories' => [
                'max' => $this->subscription_plan->max_num_dish_categories,
                'current' => $this->dish_categories->count(),
            ],
            'num_dish_groups' => [
                'max' => $this->subscription_plan->max_num_dish_categories,
                'current' => $this->dish_groups->count(),
            ],
            
            'role' => new RoleResource(Role::with('permissions')->find($this->pivot->role_id))
        ];
    }
}