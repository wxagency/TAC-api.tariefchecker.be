<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DynamicElectricResidential extends Model
{
    protected $connection = 'mysql2';
    protected $table = 'dynamic_electric_residentials';

    protected $fillable = ['backupdate', 'product_id', 'date', 'valid_from', 'valid_till', 'supplier','product',
    'fuel','duration', 'fixed_indexed', 'customer_segment', 'VL', 'WA', 'BR', 'volume_lower', 'volume_upper',
    'price_single', 'price_day', 'price_night', 'price_excl_night', 'ff_single', 'ff_day_night', 
    'ff_excl_night', 'gsc_vl', 'wkc_vl', 'gsc_wa', 'gsc_br', 'prices_url_nl', 'prices_url_fr',
    'index_name', 'index_value', 'coeff_single', 'term_single', 'coeff_day', 'term_day', 'coeff_night',
    'term_night', 'coeff_excl', 'term_excl'
    ]; 
    public function staticData()
    {
        return $this->belongsTo('App\Models\StaticElecticResidential','product_id','product_id' );
    } 

  
}
