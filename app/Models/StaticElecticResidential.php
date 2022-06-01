<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaticElecticResidential extends Model
{
    protected $connection = 'mysql2';
    protected $table = 'static_electric_residentials';

    protected $fillable = [
        'product_id', 
        'acticve', 
        'partner', 
        'supplier', 
        'product_name_NL', 
        'product_name_FR',
        'fuel',
        'duration',
        'fixed_indiable',
        'green_percentage',
        'origin',
        'segment',
        'VL',
        'NA',
        'BR',
        'service_level_payment',
        'service_level_invoicing',
        'service_level_contact',
        'FF_pro_rata',
        'inv_period',
        'customer_condition',
        'subscribe_url_NL',
        'info_NL',
        'tariff_description_NL',
        'terms_NL',
        'subscribe_url_FR',
        'info_FR',
        'tariff_description_FR',
        'terms_FR',
        'created_at',
        'updated_at',
 
    ];
}
