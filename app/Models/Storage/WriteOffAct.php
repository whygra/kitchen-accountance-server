<?php

namespace App\Models\Storage;

use App\Models\Dish\Dish;
use App\Models\Dish\DishIngredient;
use App\Models\Ingredient\Ingredient;
use App\Models\Storage\InventoryActProduct;
use App\Models\Storage\WriteOffActProduct;
use App\Models\Product\Product;
use App\Models\Project;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Auth;

class WriteOffAct extends Model
{
    use HasFactory;
    
    protected static function booted(): void
    {
        static::creating(function ($model) {
            if(Auth::user())
                $model->updated_by_user_id = Auth::user()->id;
        });
        static::updating(function ($model) {
            if(Auth::user())
                $model->updated_by_user_id = Auth::user()->id;
        });
    }
    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'write_off_acts';
    

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $foreignKeys = [
        'updated_by_user' => 'updated_by_user_id',
        'project' => 'project_id'
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'write_off_acts_products')
            ->withPivot(['amount'])
            ->withPivot(['net_weight'])
            ->using(InventoryActProduct::class);
    }

    public function ingredients(): BelongsToMany
    {
        return $this->belongsToMany(Ingredient::class, 'write_off_acts_ingredients')
            ->withPivot(['amount'])
            ->using(InventoryActIngredient::class);
    }

    public function updated_by_user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_user_id', 'id');
    }

}
