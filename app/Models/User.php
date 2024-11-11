<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use OwenIt\Auditing\Contracts\Auditable;
use ESolution\DBEncryption\Traits\EncryptedAttribute;
use Illuminate\Support\Arr;
use OwenIt\Auditing\Models\Audit;

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
    public function destination()
    {
        return $this->belongsTo(Destination::class, 'destination_id', "id");
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
    public function audit()
    {


        return $this->hasMany(Audit::class, 'user_id', 'id');
    }
    public function transformAudit(array $data): array
    {
        // Check if 'role_id' was changed
        if (Arr::has($data, 'new_values.role_id')) {
            $oldRole = $this->role()->find($this->getOriginal('role_id'));
            $newRole = $this->role()->find($this->getAttribute('role_id'));

            $data['old_values']['RoleName'] = $oldRole ? $oldRole->name : 'Unknown';
            $data['new_values']['RoleName'] = $newRole ? $newRole->name : 'Unknown';
        }

        // Check if 'contact_id' was changed
        if (Arr::has($data, 'new_values.contact_id')) {
            $oldContact = $this->contact()->find($this->getOriginal('contact_id'));
            $newContact = $this->contact()->find($this->getAttribute('contact_id'));

            $data['old_values']['Contact'] = $oldContact ? $oldContact->value : 'No Contact';
            $data['new_values']['Contact'] = $newContact ? $newContact->value : 'No Contact';
        }
        // Check if 'email_id' was changed
        if (Arr::has($data, 'new_values.email_id')) {
            $oldemail = $this->email()->find($this->getOriginal('email_id'));
            $newemail = $this->email()->find($this->getAttribute('email_id'));

            $data['old_values']['Email'] = $oldemail ? $oldemail->value : 'No Email';
            $data['new_values']['Email'] = $newemail ? $newemail->value : 'No Email';
        }
        if (Arr::has($data, 'new_values.job_id')) {
            $oldjob = $this->job()->find($this->getOriginal('job_id'));
            $newjob = $this->job()->find($this->getAttribute('job_id'));

            $data['old_values']['Job'] = $oldjob ? $oldjob->name : 'No Job';
            $data['new_values']['Job'] = $newjob ? $newjob->name : 'No Job';
        }


        return $data;
    }
}
