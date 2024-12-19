<?php

namespace App\Models;

use App\Models\Dish\Dish;
use App\Models\Dish\DishCategory;
use App\Models\Dish\DishGroup;
use App\Models\Distributor\Distributor;
use App\Models\Distributor\Unit;
use App\Models\Ingredient\Ingredient;
use App\Models\Ingredient\IngredientCategory;
use App\Models\Ingredient\IngredientGroup;
use App\Models\Product\Product;
use App\Models\Product\ProductCategory;
use App\Models\Product\ProductGroup;
use App\Models\User\Role;
use App\Models\User\RoleNames;
use App\Models\User\SubscriptionPlan;
use App\Models\User\SubscriptionPlanNames;
use App\Models\User\User;
use App\Models\User\UserProject;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class Project extends Model
{
    use HasFactory;
    
    protected static function booted(): void
    {
        static::creating(function ($model) {
            if(Auth::user()){
                $model->updated_by_user_id = Auth::user()->id;
                $model->creator_id = Auth::user()->id;
            }
        });
        static::created(function ($model) {
            $model->users()->sync([
                Auth::user()->id => [
                    'role_id'=>Role::where(
                        'name', RoleNames::ADMIN->value
                    )->first()->id
                ]
            ]);
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

    public function getSubscriptionPlan() {
        return $this->creator()->first()->getSubscriptionPlan() 
            ?? SubscriptionPlan::where('name', SubscriptionPlanNames::NONE)->first();
    }

    public function freeDistributorSlots() : int {
        return $this->getSubscriptionPlan()->max_num_distributors - $this->distributors()->count();
    }

    public function freeUnitSlots() : int {
        return $this->getSubscriptionPlan()->max_num_units - $this->units()->count();
    }
    public function freeProductSlots() : int {
        return $this->getSubscriptionPlan()->max_num_products - $this->products()->count();
    }
    public function freeProductCategorySlots() : int {
        return $this->getSubscriptionPlan()->max_num_product_categories - $this->product_categories()->count();
    }
    public function freeProductGroupSlots() : int {
        return $this->getSubscriptionPlan()->max_num_product_categories - $this->product_groups()->count();
    }
    public function freeIngredientSlots() : int {
        return $this->getSubscriptionPlan()->max_num_ingredients - $this->ingredients()->count();
    }
    public function freeIngredientCategorySlots() : int {
        return $this->getSubscriptionPlan()->max_num_ingredient_categories - $this->ingredient_categories()->count();
    }
    public function freeIngredientGroupSlots() : int {
        return $this->getSubscriptionPlan()->max_num_ingredient_categories - $this->ingredient_groups()->count();
    }
    public function freeDishSlots() : int {
        return $this->getSubscriptionPlan()->max_num_dishes - $this->dishes()->count();
    }
    public function freeDishCategorySlots() : int {
        return $this->getSubscriptionPlan()->max_num_dish_categories - $this->dish_categories()->count();
    }
    public function freeDishGroupSlots() : int {
        return $this->getSubscriptionPlan()->max_num_dish_categories - $this->dish_groups()->count();
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
    public function product_categories(): HasMany
    {
        return $this->hasMany(ProductCategory::class);
    }
    public function product_groups(): HasMany
    {
        return $this->hasMany(ProductGroup::class);
    }

    public function ingredients(): HasMany
    {
        return $this->hasMany(Ingredient::class);
    }
    public function ingredient_categories(): HasMany
    {
        return $this->hasMany(IngredientCategory::class);
    }
    public function ingredient_groups(): HasMany
    {
        return $this->hasMany(IngredientGroup::class);
    }
    public function dishes(): HasMany
    {
        return $this->hasMany(Dish::class);
    }
    public function dish_categories(): HasMany
    {
        return $this->hasMany(DishCategory::class);
    }
    public function dish_groups(): HasMany
    {
        return $this->hasMany(DishGroup::class);
    }
}
