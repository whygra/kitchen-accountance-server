<?php

namespace App\Models\Distributor;

use App\Models\Product\Product;
use App\Models\Product\ProductPurchaseOption;
use App\Models\Storage\PurchaseAct;
use App\Models\Storage\PurchaseActItem;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Support\Facades\Auth;

class PurchaseOption extends Model
{
    use HasFactory;
    
    protected static function booted(): void
    {
        static::creating(function ($model) {
            if(Auth::user())
                $model->updated_by_user()->associate(Auth::user()->id);
        });
        static::updating(function ($model) {
            if(Auth::user())
                $model->updated_by_user()->associate(Auth::user()->id);
        });
    }
    
    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'purchase_options';
    /**
     * Indicates if the model should be timestamped.
     * @var bool
     */
    public $timestamps = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'distributor_id',
        'unit_id',
        'code',
        'name',
        'net_weight',
        'price',
        'updated_by_user_id',
    ];

    protected $foreignKeys = [
        'unit' => 'unit_id', 
        'distributor' => 'distributor_id', 
        'updated_by_user' => 'updated_by_user_id'
    ];

    protected $casts = [
        'price' => 'float',
        'code' => 'string',
    ];

    protected $touches = ['distributor'];

    
    protected $appends = [
        'project_id',
    ];
    
    protected function projectId(): Attribute
    {
        return new Attribute(
            get: fn () => $this->distributor?->project_id ?? 0
        );
    }

    public function purchases(): BelongsToMany
    {
        return $this->belongsToMany(PurchaseAct::class, 'purchase_acts_items', 'item_id', 'purchase_act_id')
            ->withPivot(['amount', 'price', 'net_weight'])
            ->using(PurchaseActItem::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'products_purchase_options', 'purchase_option_id', 'product_id')
            ->withPivot('product_share')
            ->using(ProductPurchaseOption::class);
    }

        public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'unit_id', 'id');
    }

    public function distributor(): BelongsTo
    {
        return $this->belongsTo(Distributor::class, 'distributor_id', 'id');
    }

    public function updated_by_user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_user_id', 'id');
    }
}
