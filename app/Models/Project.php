<?php

namespace App\Models;

use App\Models\Dish\Dish;
use App\Models\Dish\DishTag;
use App\Models\Distributor\Distributor;
use App\Models\Distributor\Unit;
use App\Models\Ingredient\Ingredient;
use App\Models\Ingredient\IngredientTag;
use App\Models\Product\Product;
use App\Models\Product\ProductTag;
use App\Models\Storage\InventoryAct;
use App\Models\Storage\PurchaseAct;
use App\Models\Storage\SaleAct;
use App\Models\Storage\WriteOffAct;
use App\Models\User\Role;
use App\Models\User\RoleNames;
use App\Models\User\SubscriptionPlan;
use App\Models\User\SubscriptionPlanNames;
use App\Models\User\User;
use App\Models\User\UserProject;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

use function PHPUnit\Framework\isEmpty;

class Project extends Model
{
    use HasFactory;

    protected $appends = [
        'is_public',
        'subscription_plan',
    ];

    protected function isPublic(): Attribute
    {
        return new Attribute(
            fn () => !empty($this->users()->find(User::guest()->id)),
        );
    }

    protected function subscriptionPlan(): Attribute
    {
        return new Attribute(
            fn () => 
                $this->creator()->first()->subscription_plan
                ?? SubscriptionPlan::where('name', SubscriptionPlanNames::NONE)->first(),
        );
    }
    
    protected static function booted(): void
    {
        static::creating(function ($model) {
            if(Auth::user()){
                $model->updated_by_user_id = Auth::user()->id;
                $model->creator_id = Auth::user()->id;
            }
        });
        static::created(function ($model) {
            if (Auth::user()){
                $model->users()->sync([
                    Auth::user()->id => [
                       'role_id'=>Role::where(
                        'name', RoleNames::ADMIN->value
                        )->first()->id
                    ]
                ]);
            }
        });
        static::updating(function ($model) {
            if(Auth::user()){
                $model->updated_by_user_id = Auth::user()->id;
            }
        });
        static::deleted(function ($model) {
            Storage::disk('public')->deleteDirectory($model->getDirectoryPath());
        });
    }
    
    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'projects';
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
        'logo_name',
        'backdrop_name',
        'updated_by_user_id',
    ];

    protected $foreignKeys = [
        'creator' => 'creator_id',
        'updated_by_user' => 'updated_by_user_id'
    ];

    public function freeDistributorSlots() : int {
        return $this->subscription_plan->max_num_distributors - $this->distributors()->count();
    }

    public function freeUnitSlots() : int {
        return $this->subscription_plan->max_num_units - $this->units()->count();
    }
    public function freeProductSlots() : int {
        return $this->subscription_plan->max_num_products - $this->products()->count();
    }
    public function freeProductTagSlots() : int {
        return $this->subscription_plan->max_num_tags - $this->product_tags()->count();
    }
    public function freeIngredientSlots() : int {
        return $this->subscription_plan->max_num_ingredients - $this->ingredients()->count();
    }
    public function freeIngredientTagSlots() : int {
        return $this->subscription_plan->max_num_tags - $this->ingredient_tags()->count();
    }
    public function freeDishSlots() : int {
        return $this->subscription_plan->max_num_dishes - $this->dishes()->count();
    }
    public function freeDishTagSlots() : int {
        return $this->subscription_plan->max_num_tags - $this->dish_tags()->count();
    }

    // путь к папке проекта в хранилище изображений
    public function getDirectoryPath() : string {
        return 'images/project_'.$this->id;
    }
    // путь к файлу логотиа
    public function getLogoDirectoryPath() : string {
        return 'images/project_'.$this->id.'/logo';
    }
    // путь к файлу фона
    public function getBackdropDirectoryPath() : string {
        return 'images/project_'.$this->id.'/backdrop';
    }

    public function uploadLogo($file) : string {
        Storage::disk('public')->deleteDirectory($this->getLogoDirectoryPath());
        $image_uploaded_path = $file->store($this->getLogoDirectoryPath(), 'public'); 
        $this->logo_name = basename($image_uploaded_path);
        $this->save();
        return $image_uploaded_path;
    }

    public function uploadBackdrop($file) : string {
        Storage::disk('public')->deleteDirectory($this->getBackdropDirectoryPath());
        $image_uploaded_path = $file->store($this->getBackdropDirectoryPath(), 'public'); 
        $this->backdrop_name = basename($image_uploaded_path);
        $this->save();
        return $image_uploaded_path;
    }

    public function creator() : BelongsTo {
        return $this->belongsTo(User::class, 'creator_id', 'id');
    }

    public function updated_by_user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_user_id', 'id');
    }
    
    public function users(): BelongsToMany{
        return $this->belongsToMany(User::class, 'users_projects')
            ->withPivot(['role_id'])
            ->using(UserProject::class);
    }

    public function distributors(): HasMany
    {
        return $this->hasMany(Distributor::class, 'project_id', 'id');
    }
    public function units(): HasMany
    {
        return $this->hasMany(Unit::class);
    }
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
    public function product_tags(): HasMany
    {
        return $this->hasMany(ProductTag::class);
    }

    public function ingredients(): HasMany
    {
        return $this->hasMany(Ingredient::class);
    }
    public function ingredient_tags(): HasMany
    {
        return $this->hasMany(IngredientTag::class);
    }
    public function dishes(): HasMany
    {
        return $this->hasMany(Dish::class);
    }
    public function dish_tags(): HasMany
    {
        return $this->hasMany(DishTag::class);
    }
    public function inventory_acts(): HasMany
    {
        return $this->hasMany(InventoryAct::class);
    }
    public function write_off_acts(): HasMany
    {
        return $this->hasMany(WriteOffAct::class);
    }
    public function purchase_acts(): HasMany
    {
        return $this->hasMany(PurchaseAct::class);
    }
    public function sale_acts(): HasMany
    {
        return $this->hasMany(SaleAct::class);
    }

    public function get_inventory_estimate($date) {
        $last = $this->inventory_acts->where('date', '<', $date)
            ->latest('date')->first();
        
        $estimate = new InventoryAct($last);

        $sales = $this->sale_acts()->where(fn($a)=>$a->date >= $last->date && $a->date < $date);
        $purchases = $this->purchase_acts()->where(fn($a)=>$a->date >= $last->date && $a->date < $date);
        $write_offs = $this->write_off_acts()->where(fn($a)=>$a->date >= $last->date && $a->date < $date);

        foreach($sales as $s) {
            foreach($s->items as $dish){
                foreach($dish->getRawProducts() as $p){
                    $key = array_find_key($estimate->products, fn($ep)=>$ep->name == $p->name);
                    if(isEmpty($key)){
                        array_push($estimate->products, $p);
                    }
                    else{
                        $estimate->products[$key]->amount = $p->amount;
                    }
                }

            }
        }

        foreach($purchases as $p) {
            foreach($p->items as $po){
                foreach($po->products as $product){
                    $key = array_find_key($estimate->products, fn($ep)=>$product->name == $p->name);
                    if(isEmpty($key)){
                        array_push($estimate->products, $p);
                    }
                    else{
                        $estimate->products[$key]->amount = $p->amount;
                    }
                }

            }
        }
    } 
}
