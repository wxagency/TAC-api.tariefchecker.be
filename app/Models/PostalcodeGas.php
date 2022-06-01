<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostalcodeGas extends Model
{
    protected $connection = 'mysql2';
    protected $table = 'postalcode_dgo_gases';

    protected $fillable = [
        'distribution_id', 
        'netadmin_zip', 
        'netadmin_city', 
        'netadmin_subcity', 
        'product', 
        'grid_operational', 
        'gas_H_L',
        'DNB',
        'netadmin_website',
        'TNB',
        'language_code',
        'region',       
        'created_at',
        'updated_at',
 
    ];
}
