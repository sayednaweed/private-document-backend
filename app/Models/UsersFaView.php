<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UsersFaView extends Model
{
    protected $table = 'users_fa_view';

    // Since views usually don't have an id field or timestamps
    public $timestamps = false;
    protected $primaryKey = null;
}
