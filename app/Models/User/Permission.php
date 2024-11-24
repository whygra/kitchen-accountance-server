<?php

namespace App\Models\User;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\Pivot;

class Permission extends Model
{
    use HasFactory;
    
    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'permissions';
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

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'roles_permissions')
            ->using(RolePermission::class);
    }

}
