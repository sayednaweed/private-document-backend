<?php

namespace App\Models;

use App\Contracts\Encryptable;
use App\Traits\template\Auditable;
use Illuminate\Database\Eloquent\Model;

class DocumentDestination extends Model implements Encryptable
{
    use Auditable;
    public static function getEncryptedFields(): array
    {
        return ['feedback'];  // List of fields to encrypt
    }
    protected $guarded = [];

    public function document()
    {
        return $this->belongsTo(Document::class);
    }
    public function destination()
    {
        return $this->belongsTo(Destination::class);
    }
}
