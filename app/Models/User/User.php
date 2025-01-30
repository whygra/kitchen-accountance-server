<?php

namespace App\Models\User;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Models\Dish\Dish;
use App\Models\Distributor\Distributor;
use App\Models\Distributor\PurchaseOption;
use App\Models\Ingredient\Ingredient;
use App\Models\Product\Product;
use App\Models\Project;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    public const GUEST_NAME = 'guest';
    public static function guest() {
        return User::where('name', User::GUEST_NAME)->first();
    }
    public static function superuser() {
        return User::where('name', env('SUPERUSER_NAME'))->first();
    }
    
    protected $table = 'users';

    protected $guard_name = 'web';
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'subscription_plan_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected $foreignKeys = [
        'subscription_plan' => 'subscription_plan_id',
    ];

    public function subscription_plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id', 'id');
    }

    public function getSubscriptionPlan() {
        return $this->subscription_plan()->first() ?? SubscriptionPlan::where('name', SubscriptionPlanNames::NONE)->first();
    }

    public function freeProjectSlots() : int {
        return $this->getSubscriptionPlan()->max_num_projects - $this->created_projects()->count();
    }

    public function projects(): BelongsToMany{
        return $this->belongsToMany(Project::class, 'users_projects')
            ->withPivot(['role_id'])
            ->using(UserProject::class);
    }

    public function created_projects() : HasMany {
        return $this->hasMany(Project::class, 'creator_id', 'id');
    }

    public function last_updated_projects(): HasMany
    {
        return $this->hasMany(Project::class, 'updated_by_user_id', 'id');
    }

    public function last_updated_distributors(): HasMany
    {
        return $this->hasMany(Distributor::class, 'updated_by_user_id', 'id');
    }

    public function last_updated_purchase_options(): HasMany
    {
        return $this->hasMany(PurchaseOption::class, 'updated_by_user_id', 'id');
    }

    public function last_updated_products(): HasMany
    {
        return $this->hasMany(Product::class, 'updated_by_user_id', 'id');
    }

    public function last_updated_ingredients(): HasMany
    {
        return $this->hasMany(Ingredient::class, 'updated_by_user_id', 'id');
    }

    public function last_updated_dishes(): HasMany
    {
        return $this->hasMany(Dish::class, 'updated_by_user_id', 'id');
    }

    public function hasAnyPermission(int $projectId, array $permissions) : bool {

        $project = Project::find($projectId);     
        // проекта не существует
        if(empty($project))
            return false;

        $user = $project->users()->find($this->id) ?? (
            ($project->is_public 
                ?$project->users()->find(User::guest()->id)
                :null
            )
        );
        // авторизованный пользователь ?? гость не участвуют в проекте
        if(empty($user))
            return false;

        // авторизованный пользователь является создателем проекта
        // и затребовано разрешение EDIT_PROJECT
        if($project->creator_id == $this->id && array_search(PermissionNames::EDIT_PROJECT->value, $permissions))
            return true;

        $role = Role::find($user->pivot->role_id);

        if(empty($role)) return false;
        foreach ($role->permissions()->get() as $perm){
            // авторизованный пользователь ?? гость имеет требуемые права
            if(in_array($perm->name, $permissions)){
                return true;
            }
        }
        return false;
    }

}
