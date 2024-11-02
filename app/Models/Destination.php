<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Destination extends Model
{
    //


    protected $fillable = ['name','color'];


    public function destinationType()
    {
        return $this->belongsTo(DestinationType::class);
    }
}
