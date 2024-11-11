<?php

namespace App\Models;

use App\Models\Translate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Destination extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function type()
    {
        return $this->belongsTo(DestinationType::class, 'destination_type_id');
    }
    // In the Destination model
    public function translations()
    {
        return $this->morphMany(Translate::class, 'translable');
    }
}
