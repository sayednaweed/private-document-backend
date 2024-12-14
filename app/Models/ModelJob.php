<?php

namespace App\Models;

use App\Traits\template\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ModelJob extends Model
{
    use HasFactory, Auditable;

    protected $guarded = [];

    public function translations()
    {
        return $this->morphMany(Translate::class, 'translable');
    }
}
