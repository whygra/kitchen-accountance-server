<?php

namespace App\Models\User;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\Pivot;

class Role extends Model
{
    use HasFactory;

    public static function project_manager() {
        return Role::where('name', RoleNames::PROJECT_MANAGER)->first();
    }

    public static function admin() {
        return Role::where('name', RoleNames::ADMIN)->first();
    }

    public static function user_manager() {
        return Role::where('name', RoleNames::USER_MANAGER)->first();
    }

    public static function chef() {
        return Role::where('name', RoleNames::CHEF)->first();
    }

    public static function viewer() {
        return Role::where('name', RoleNames::VIEWER)->first();
    }

    public static function guest() {
        return Role::where('name', RoleNames::GUEST)->first();
    }
    
    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'roles';
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

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'roles_permissions')
            ->using(RolePermission::class);
    }

}
