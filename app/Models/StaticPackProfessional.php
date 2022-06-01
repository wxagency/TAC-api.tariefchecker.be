<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaticPackProfessional extends Model
{
    protected $connection = 'mysql2';
    protected $table = 'static_pack_professionals';

    protected $fillable = [
        'pack_id', 
        'pack_name_NL', 
        'pack_name_FR', 
        'active', 
        'partner', 
        'pro_id_E',
        'pro_id_G',
        'URL_NL',
        'info_NL',
        'tariff_description_NL',
        'URL_FR',
        'info_FR',
        'tariff_description_FR',
        'check_elec',
        'check_gas',      
        'created_at',
        'updated_at'
 
    ];

    // public function staticDataE()
    // {
    //     return $this->belongsTo('App\Models\DynamicElectricProfessional','pro_id_E','product_id');
    // } 
    
    public function staticElectricDetails()
    {
        return $this->hasOne('App\Models\StaticElectricProfessional','product_id','pro_id_E');
    } 



    // public function staticDataG()
    // {
    //     return $this->belongsTo('App\Models\DynamicGasProfessional','pro_id_G','product_id');
    // }

    public function staticGasDetails()
    {
        return $this->hasOne('App\Models\StaticGasProfessional','product_id','pro_id_G');
    }

 


}
