<?php

namespace App\Models\SearchDetails;

use Illuminate\Database\Eloquent\Model;

class SearchDetail extends Model
{
    protected $connection = 'mysql2';
    protected $table = 'user_search_histories';
    protected $fillable = ['uuid','locale','firstname','lastname','residential_professional',
    'postalcode','region','familysize','comparison_type','meter_type','pack_id','eid','gid',
    'total_cost','single','day','night','excl_night','current_electric_supplier','gas_consumption',
    'current_gas_supplier','email','data_from', 'first_residence', 'decentralise_production',
    'capacity_decentalise','includeG','includeE','comparison_savings','contact_type','contact_affiliate',
    'contact_language','contact_source','contact_medium','contact_campaign','address_street','address_number',
    'address_box','address_postalcode','address_city','address_region','comparison_url','comparison_fuel',
    'comparison_current_supplier_e','comparison_current_supplier_g','comparison_filters','comparison_energy_cost_e',
    'comparison_other_costs_e','comparison_energy_cost_g','comparison_other_costs_g','comparison_promo_amount_e',
    'comparison_promo_amount_g','follow_up_first_enddate','follow_up_lastaction','follow_up_status','contract_supplier',
    'contract_supplier_id','contract_tariff','contract_tariff_id','contract_signup_url','contract_url_tariffcard_e',
    'contract_url_tariffcard_g','contract_signdate','contract_startdate','contract_enddate','contract_duration_db',
    'contract_duration','contract_pricetype_e','contract_pricetype_g','contract_energy_cost_e','contract_energy_cost_g',
    'contract_othercosts_e','contract_promo_amount_e','contract_othercosts_g','contract_promo_amount_g',
    'contract_promo_conditions_duration','contract_promo_conditions_servicelevel','contract_promo_ids','contract_status_supplier',
    'deal_source','deal_medium','deal_campaign','deal_value','feedback_unsubscribe_automation','phone',
    'feedback_aanvullende','account','tags','lists','automations',
    ];
}
