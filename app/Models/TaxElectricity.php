<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaxElectricity extends Model
{
	protected $connection = 'mysql2';
    protected $table = 'tax_electricities';
    protected $fillable = ['date', 'valid_from', 'valid_till', 'dgo','dgo_electrabelname',
    'fuel','segment', 'VL', 'WA', 'BR', 'volume_lower', 'volume_upper','energy_contribution', 
    'federal_contribution', 'connection_fee', 'contribution_public_services', 'fixed_tax_first_res',
    'fixed_tax_not_first_res',
    ];
}
