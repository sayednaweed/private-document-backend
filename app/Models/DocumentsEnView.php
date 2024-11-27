<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentsEnView extends Model
{
    protected $table = 'documents_en_view';
    // Since views usually don't have an id field or timestamps
    public $timestamps = false;
    protected $primaryKey = null;
}
