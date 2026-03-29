<?php

namespace App\Models\Dish;

use App\Models\Dish\DishTag;
use App\Models\GrossProductArray;
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
            if (Auth::user()) {
                $model->updated_by_user_id = Auth::user()->id;
            }
        });
        static::updating(function ($model) {

            if (Auth::user()) {
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
        'updated_by_user_id',
        'total_gross_weight',
        'total_net_weight',
        'avg_waste_percentage',
    ];

    protected $foreignKeys = [
        'updated_by_user' => 'updated_by_user_id',
        'project' => 'project_id',
    ];

    protected $casts = [
        'atr_total_gross_weight' => 'float',
        'atr_total_net_weight' => 'float',
        'total_gross_weight' => 'float',
        'total_net_weight' => 'float',
        'avg_waste_percentage' => 'float',
    ];

    protected $appends = [
        // 'atr_total_gross_weight',
        // 'atr_total_net_weight',
        // 'avg_waste_percentage',
    ];

    protected function atrTotalGrossWeight(): Attribute
    {
        return new Attribute(
            get: fn() => array_reduce(
                $this->ingredients()->get()->toArray(),
                fn($total, $i)
                => $total + ($i['pivot']['amount'] * $i['item_weight']),
                0
            ),
        );
    }

    protected function atrTotalNetWeight(): Attribute
    {
        return new Attribute(
            get: fn() => array_reduce(
                $this->ingredients()->get()->toArray(),
                fn($total, $i) => $total + $i['pivot']['net_weight'],
                0
            ),
        );
    }

    protected function getAtrAvgWastePercentage(): Attribute
    {
        return new Attribute(
            get: fn() => 100 - $this->total_net_weight / ($this->total_gross_weight == 0 ? 1 : $this->total_gross_weight) * 100,
        );
    }

    // путь к папке блюда в хранилище изображений
    public function getImageDirectoryPath(): string
    {
        return 'images/project_' . $this->project_id . '/dishes/' . $this->id;
    }

    public function uploadImage($file): string
    {
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
            ->withPivot('net_weight', 'amount')
            ->using(DishIngredient::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(DishTag::class, 'dishes_tags', 'dish_id', 'tag_id');
    }

    public function updated_by_user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_user_id', 'id');
    }

    // получить продукты под списание
    public function getRawProducts(int $amount = 1)
    {
        $gross_products = new GrossProductArray();

        foreach ($this->ingredients()->get() as $i) {
            foreach ($i->getRawProducts($i->pivot->amount * $i->item_weight * $amount) as $p) {
                $gross_products->addProduct($p);
            }
        }
        return $gross_products->get();
    }
}
