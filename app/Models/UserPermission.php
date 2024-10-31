<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPermission extends Model
{
    use HasFactory;
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
