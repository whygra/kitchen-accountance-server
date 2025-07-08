<?php

namespace App\Models\Storage;

use App\Models\Dish\Dish;
use App\Models\Dish\DishIngredient;
use App\Models\Distributor\Distributor;
use App\Models\Distributor\PurchaseOption;
use App\Models\Storage\PurchaseActItem;
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

class PurchaseAct extends Model
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
    protected $table = 'purchase_acts';
    

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'date',
        'updated_by_user_id',
        'distributor_id',
    ];

    protected $foreignKeys = [
        'updated_by_user' => 'updated_by_user_id',
        'project' => 'project_id',
        'distributor' => 'distributor_id'
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
    
    public function distributor(): BelongsTo
    {
        return $this->belongsTo(Distributor::class);
    }

    public function items(): BelongsToMany
    {
        return $this->belongsToMany(PurchaseOption::class, 'purchase_acts_items', 'purchase_act_id', 'item_id')
            ->withPivot(['amount', 'price', 'net_weight'])
            ->using(PurchaseActItem::class);
    }

    public function updated_by_user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_user_id', 'id');
    }

}
