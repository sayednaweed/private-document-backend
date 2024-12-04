<?php

namespace App\Models;

use App\Traits\template\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ApiKey extends Model
{
    use HasFactory, Auditable;
    public static function getEncryptedFields(): array
    {
        return ['name', 'key', 'directorate'];  // List of fields to encrypt
    }
    protected $fillable = ['name', 'directorate', 'ip_address', 'key', 'hashed_key', 'is_active'];
    // protect column should be mention here
}
