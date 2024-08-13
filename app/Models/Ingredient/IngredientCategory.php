<?php

namespace App\Models\Ingredient;

use App\Models\DeletionAllowableModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\Pivot;

class IngredientCategory extends DeletionAllowableModel
{
    use HasFactory;
    
    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'ingredient_categories';
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
    ];

    public function deletionAllowed() :bool {
        // удаление разрешено, если нет неудаляемых связанных продуктов
        return 
            empty(
                array_filter(
                    $this->ingredients()->get()->all(), 
                    fn($item)=>!($item->deletionAllowed())
                ));
    }

    public function ingredients(): HasMany
    {
        return $this->hasMany(Ingredient::class, 'category_id', 'id');
    }

}
