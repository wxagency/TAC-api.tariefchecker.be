<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfirmedUser extends Model
{
    protected $connection = 'mysql2';
    protected $table='confirm_users';
    protected $fillable = [
        'con_user_id','request','sync','created_at' 
    ];
}