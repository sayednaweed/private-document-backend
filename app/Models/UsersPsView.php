<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsersPsView extends Model
{
    protected $table = 'users_ps_view';

    // Since views usually don't have an id field or timestamps
    public $timestamps = false;
    protected $primaryKey = null;
}
