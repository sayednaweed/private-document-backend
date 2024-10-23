<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DestinationType extends Model
{
    //



    protected $fillable = ['name'];

    // One destination type has many destinations
    public function destination()
    {
        return $this->hasMany(Destination::class);
    }

    
}
