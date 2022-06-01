<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Netcostgs extends Model
{
    protected $connection = 'mysql2';
    protected $table = 'netcostg';

    protected $fillable = [
        'date', 
        'valid_from', 
        'valid_till', 
        'dgo', 
        'dgo_electrabelname', 
        'fuel', 
        'segment',
        'VL',
        'WA',
        'BR',
        'volume_lower',
        'volume_upper',       
        'fixed_term',
        'variable_term',
        'reading_meter_yearly',
        'reading_meter_monthly',
        'transport',       
        'created_at',
        'updated_at',
 
    ];
}
