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
                $model->users()->sync([
                    Auth::user()->id => [
                        'role_id'=>Role::where(
                            'name', RoleNames::ADMIN->value
                        )->first()->id
                    ]
                ]);
                $model->updated_by_user_id = Auth::user()->id;
                $model->creator_id = Auth::user()->id;
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

    // путь к папке проекта в хранилище изображений
    public function getDirectoryPath() : string {
        return 'images/project_'.$this->id;
    }
    // путь к папке проекта в хранилище изображений
    public function getLogoDirectoryPath() : string {
        return 'images/project_'.$this->id.'/logo';
    }
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
