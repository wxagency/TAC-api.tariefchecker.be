<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaxGas extends Model
{
	protected $connection = 'mysql2';
    protected $table = 'tax_gas';
    protected $fillable = ['date', 'valid_from', 'valid_till', 'dgo','dgo_electrabelname',
    'fuel','segment', 'VL', 'WA', 'BR', 'volume_lower', 'volume_upper','energy_contribution', 
    'federal_contribution','contribution_protected_customers', 'connection_fee', 
    'contribution_public_services', 'fixed_tax',
    ];
}
