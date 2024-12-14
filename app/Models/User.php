<?php

namespace App\Models;

use Illuminate\Support\Arr;
use Laravel\Sanctum\HasApiTokens;
use OwenIt\Auditing\Models\Audit;
use App\Traits\template\Auditable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens, Auditable;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [];
    // protect column should be mention here

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];
    protected $casts = [
        'grant_permission' => 'boolean',
        'status' => 'boolean',
        'created_at' => 'datetime',
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
    public function contact()
    {
        return $this->belongsTo(Contact::class, 'contact_id');
    }
    public function email()
    {
        return $this->belongsTo(Email::class, 'email_id');
    }
    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id', "id");
    }
    public function destination()
    {
        return $this->belongsTo(Destination::class, 'destination_id', "id");
    }
    public function job()
    {
        return $this->belongsTo(ModelJob::class, 'job_id', "id");
    }
    public function userPermissions()
    {
        return $this->hasMany(UserPermission::class, 'user_id', 'id');
    }
    // Define the relationship to the Permission model
    public function permissions()
    {
        return $this->hasManyThrough(Permission::class, UserPermission::class, 'user_id', 'name', 'id', 'permission');
    }

    public function hasPermission($permission)
    {
        return $this->permissions()->where('permission', $permission)->exists();
        // Optionally cache the permissions if needed
        // $permissions = $this->getCachedPermissions();

        // return in_array($permission, $permissions);
    }

    // private function getCachedPermissions()
    // {
    //     // Use cache to store user permissions
    //     return Cache::remember("user_permissions_{$this->id}", 60, function () {
    //         return $this->permissions()->pluck('permission')->toArray();
    //     });
    // }
}
