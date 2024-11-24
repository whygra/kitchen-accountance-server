<?php

namespace App\Models\Dish;

use App\Models\Project;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Facades\Auth;

class DishGroup extends Model
{
    use HasFactory;
    
    protected static function booted(): void
    {
        static::created(function ($model) {
            if(Auth::user())
                $model->updated_by_user_id = Auth::user()->id;
        });
        static::updated(function ($model) {
            if(Auth::user())
                $model->updated_by_user_id = Auth::user()->id;
        });
    }
    
    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'dish_groups';
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

    protected $foreignKeys = [
        'updated_by_user' => 'updated_by_user_id',
        'project' => 'project_id'
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function dishes(): HasMany
    {
        return $this->hasMany(Dish::class, 'group_id', 'id');
    }

    public function updated_by_user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_user_id', 'id');
    }

}
