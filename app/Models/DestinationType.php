<?php

namespace App\Models;

use App\Models\Translate;
use App\Traits\template\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DestinationType extends Model
{
    use HasFactory, Auditable;

    protected $guarded = [];

    // One destination type has many destinations
    public function destination()
    {
        return $this->hasMany(Destination::class);
    }
    public function translations()
    {
        return $this->morphMany(Translate::class, 'translable');
    }
}
