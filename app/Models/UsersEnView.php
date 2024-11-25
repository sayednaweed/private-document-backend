<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UsersEnView extends Model
{
    protected $table = 'users_en_view';

    // Since views usually don't have an id field or timestamps
    public $timestamps = false;
    protected $primaryKey = null;
}
