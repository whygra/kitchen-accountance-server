<?php

namespace App\Models\User;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Models\Dish\Dish;
use App\Models\Distributor\Distributor;
use App\Models\Distributor\PurchaseOption;
use App\Models\Ingredient\Ingredient;
use App\Models\Product\Product;
use App\Models\Project;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class SubscriptionPlan extends Model
{
    use HasApiTokens, HasFactory, Notifiable;
    protected $table = 'subscription_plans';

    protected $guard_name = 'web';
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'max_num_projects',
        'max_num_distributors',
        'max_num_purchase_options',
        'max_num_products',
        'max_num_ingredients',
        'max_num_dishes',
        'max_num_units',
        'max_num_product_categories',
        'max_num_ingredient_categories',
        'max_num_dish_categories',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'max_num_projects' => 'integer',
            'max_num_distributors' => 'integer',
            'max_num_purchase_options' => 'integer',
            'max_num_products' => 'integer',
            'max_num_ingredients' => 'integer',
            'max_num_dishes' => 'integer',
        ];
    }

    public function subscription_plan(): HasMany
    {
        return $this->hasMany(Role::class, 'subscription_plan_id', 'id');
    }


}
