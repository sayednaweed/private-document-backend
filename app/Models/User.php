<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use OwenIt\Auditing\Contracts\Auditable;
use ESolution\DBEncryption\Traits\EncryptedAttribute;
use Illuminate\Support\Facades\Auth;

class User extends Authenticatable implements Auditable
{
    use HasFactory, Notifiable, HasApiTokens, EncryptedAttribute;
    use \OwenIt\Auditing\Auditable;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [];
    // protect column should be mention here
    protected $encryptable = [
        'name',
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
    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id', "id");
    }
    public function job()
    {
        return $this->belongsTo(ModelJob::class, 'job_id', "id");
    }
    public function permissions()
    {
        return $this->hasMany(UserPermission::class, 'user_id', 'id');
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
