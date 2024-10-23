<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentDestination extends Model
{
    //


    
    public function document()
    {
        return $this->belongsTo(Document::class);
    }
    public function destination()
    {
        return $this->belongsTo(Destination::class);
    }

    
}
