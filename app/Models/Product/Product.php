<?php

namespace App\Models\Product;

use App\Models\Distributor\Purchase;
use App\Models\Distributor\PurchaseOption;
use App\Models\Ingredient\Ingredient;
use App\Models\Ingredient\IngredientCategory;
use App\Models\Ingredient\IngredientProduct;
use App\Models\Project;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class Product extends Model
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
    protected $table = 'products';
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
        'name',
        'category_id',
        'group_id',
        'item_cost',
        'item_weight',
        'updated_by_user_id',
    ];

    protected $foreignKeys = [
        'category' => 'category_id',
        'group' => 'group_id',
        'updated_by_user' => 'updated_by_user_id',
        'project' => 'project_id'
    ];

    public function ingredients(): BelongsToMany
    {
        return $this->belongsToMany(Ingredient::class, 'ingredients_products')
            ->withPivot('raw_content_percentage', 'waste_percentage')
            ->using(IngredientProduct::class);
    }

    public function purchase_options(): BelongsToMany
    {
        return $this->belongsToMany(PurchaseOption::class, 'products_purchase_options', 'product_id', 'purchase_option_id')
            ->withPivot('product_share')
            ->using(ProductPurchaseOption::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'category_id', 'id');
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(ProductGroup::class, 'group_id', 'id');
    }

    public function updated_by_user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_user_id', 'id');
    }

}
