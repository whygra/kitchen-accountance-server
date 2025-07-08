<?php

namespace App\Models\Storage;

use App\Models\Dish\Dish;
use App\Models\Dish\DishIngredient;
use App\Models\Distributor\Distributor;
use App\Models\Storage\PurchaseActItem;
use App\Models\Storage\SaleActProduct;
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

class SaleAct extends Model
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
    protected $table = 'sale_acts';
    

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'date',
        'updated_by_user_id',
    ];

    protected $foreignKeys = [
        'updated_by_user' => 'updated_by_user_id',
        'project' => 'project_id',
    ];
    protected $appends = [
        'total_cost',
    ];
    
    protected function totalCost(): Attribute
    {
        return new Attribute(
            get: fn () => array_reduce(
                $this->items()->get()->toArray(),
                fn($total, $p)=>$total+$p['pivot']['price']*$p['pivot']['amount'],
                0
            ),
        );
    }
    
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
    
    public function items(): BelongsToMany
    {
        return $this->belongsToMany(Dish::class, 'sale_acts_items', 'sale_act_id', 'item_id')
            ->withPivot(['amount', 'price'])
            ->using(SaleActProduct::class);
    }

    public function updated_by_user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_user_id', 'id');
    }

}
