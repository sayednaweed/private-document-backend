<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use ESolution\DBEncryption\Traits\EncryptedAttribute;
use OwenIt\Auditing\Contracts\Auditable;

class ApiKey extends Model implements Auditable
{
    use HasFactory, EncryptedAttribute;
    use \OwenIt\Auditing\Auditable;

    protected $fillable = ['name', 'directorate', 'ip_address', 'key', 'hashed_key', 'is_active'];
    // protect column should be mention here
    protected $encryptable = [
        'name',
        'key',
        'directorate',

    ];
}
