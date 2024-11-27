<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentsPsView extends Model
{
    protected $table = 'documents_ps_view';
    // Since views usually don't have an id field or timestamps
    public $timestamps = false;
    protected $primaryKey = null;
}
