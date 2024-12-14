<?php

namespace App\Models;

use App\Traits\template\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserPermission extends Model
{
    use HasFactory, Auditable;

    protected $guarded = [];
    protected $casts = [
        'view' => 'boolean',
        'edit' => 'boolean',
        'delete' => 'boolean',
        'add' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
