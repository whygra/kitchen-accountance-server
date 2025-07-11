<?php

namespace App\Models\Dish;

use App\Models\Ingredient\Ingredient;
use App\Models\MenuItem\MenuItem;
use App\Models\Project;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class Dish extends Model
{
    use HasFactory;
    
    protected static function booted(): void
    {
        static::creating(function ($model) {
            if(Auth::user())
                $model->updated_by_user_id = Auth::user()->id;
        });
        static::updating(function ($model) {
            
            if(Auth::user()){
                $model->updated_by_user_id = Auth::user()->id;
            }
        });
        static::deleted(function ($model) {
            Storage::disk('public')->deleteDirectory($model->getImageDirectoryPath());
        });
    }

    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'dishes';
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
        'description',
        'image_name',
        'category_id',
        'group_id',
        'updated_by_user_id',
    ];

    protected $foreignKeys = [
        'category' => 'category_id',
        'group' => 'group_id',
        'updated_by_user' => 'updated_by_user_id',
        'project' => 'project_id',
    ];

    protected $casts = [
        'total_gross_weight' => 'float',
        'total_net_weight' => 'float',
        'avg_waste_percentage' => 'float',
    ];

    protected $appends = [
        'total_gross_weight',
        'total_net_weight',
        'avg_waste_percentage',
    ];
    
    protected function totalGrossWeight(): Attribute
    {
        return new Attribute(
            get: fn () => array_reduce(
                $this->ingredients()->get()->toArray(),
                fn($total, $i)=>
                    $total+($i['pivot']['ingredient_amount']*$i['item_weight']),
                    0
            ),
        );
    }
    
    protected function totalNetWeight(): Attribute
    {
        return new Attribute(
            get: fn () => array_reduce(
                $this->ingredients()->get()->toArray(),
                fn($total, $i)=>$total+$i['pivot']['net_weight'],
                0
            ),
        );
    }
    
    protected function avgWastePercentage(): Attribute
    {
        return new Attribute(
            get: fn () => 100 - $this->total_net_weight/($this->total_gross_weight==0?1:$this->total_gross_weight)*100,
        );
    }

    // путь к папке блюда в хранилище изображений
    public function getImageDirectoryPath() : string {
        return 'images/project_'.$this->project_id.'/dishes/'.$this->id;
    }

    public function uploadImage($file) : string {
        // очистка папки
        $dishPath = $this->getImageDirectoryPath();
        Storage::disk('public')->deleteDirectory($dishPath);
        
        $image_uploaded_path = $file->store($dishPath, 'public'); 
        $this->image_name = basename($image_uploaded_path);
        $this->save();
        return $image_uploaded_path;
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id', 'id');
    }

    // связи M-N с ингредиентами
    public function ingredients(): BelongsToMany
    {
        return $this->belongsToMany(Ingredient::class, 'dishes_ingredients')
            ->withPivot('net_weight', 'ingredient_amount')
            ->using(DishIngredient::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(DishCategory::class, 'category_id', 'id');
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(DishGroup::class, 'group_id', 'id');
    }

    public function updated_by_user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_user_id', 'id');
    }
}