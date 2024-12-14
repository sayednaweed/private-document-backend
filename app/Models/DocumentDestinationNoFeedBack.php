<?php

namespace App\Models;

use App\Traits\template\Auditable;
use Illuminate\Database\Eloquent\Model;

class DocumentDestinationNoFeedBack extends Model
{
    use Auditable;
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
