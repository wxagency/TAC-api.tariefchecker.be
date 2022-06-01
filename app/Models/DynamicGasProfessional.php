<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DynamicGasProfessional extends Model
{
    protected $connection = 'mysql2';
    protected $table = 'dynamic_gas_professionals';
    protected $fillable = ['product_id', 'date', 'valid_from', 'valid_till', 'supplier','product',
    'fuel','duration', 'fixed_indexed', 'segment', 'VL', 'WA', 'BR', 'volume_lower', 'volume_upper',
    'price_gas', 'ff', 'prices_url_nl', 'prices_url_fr', 'index_name', 'index_value', 'coeff', 'term'
    ];

    public function staticData()
    {
        return $this->belongsTo('App\Models\StaticGasProfessional','product_id','product_id' );
    }
    // public function staticPackProfessionals()
    // {
    //     return $this->belongsTo('App\Models\StaticPackProfessional','pro_id_G','product_id');
    // }
}

