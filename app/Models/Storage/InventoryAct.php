<?php

namespace App\Models\Storage;

use App\Models\Dish\Dish;
use App\Models\Dish\DishIngredient;
use App\Models\Ingredient\Ingredient;
use App\Models\Storage\InventoryActProduct;
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

class InventoryAct extends Model
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
    protected $table = 'inventory_acts';
    

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
        'project' => 'project_id'
    ];

    protected $appends = [
        'raw_products'
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'inventory_acts_products')
            ->withPivot(['amount'])
            ->withPivot(['net_weight'])
            ->using(InventoryActProduct::class);
    }

    public function ingredients(): BelongsToMany
    {
        return $this->belongsToMany(Ingredient::class, 'inventory_acts_ingredients')
            ->withPivot(['amount'])
            ->using(InventoryActIngredient::class);
    }

    protected function rawProducts(): Attribute
    {
        return new Attribute(
            function (){
                $raw_products = $this->products;
                foreach($this->ingredients->all() as $i){
                    foreach($i->getRawProducts() as $p){
                        $key = array_find_key($raw_products, fn($rp)=>$rp->name==$p->name);
                        $raw_products[$key]->amount += $p->pivot->gross_weight;
                    }
                }
                return $raw_products;
            }
        );
    }

    public function updated_by_user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_user_id', 'id');
    }

    public function previous(): InventoryAct|null {
        
        $previous = $this->project->inventory_acts()
            ->where('date', '<', $this->date)
            ->latest('date')->first();
            
        return $previous;
    }

}
