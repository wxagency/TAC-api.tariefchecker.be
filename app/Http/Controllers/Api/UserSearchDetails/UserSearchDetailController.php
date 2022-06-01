<?php

namespace App\Http\Controllers\Api\UserSearchDetails;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SearchDetails\SearchDetail;

class UserSearchDetailController extends Controller
{
    public function index(Request $request)
    {


         $pack_id = null;
         $eid = null;
         $gid = null;

        if ($request->type == 'dual') {
            $eid = $request->eid;
            $gid = $request->gid;
        } else {
            $eid = "";
            $gid = "";
        }

        if ($request->type == 'pack') {

            $pack_id = $request->proid;
        } else {
            $pack_id = null;
        }
        if ($request->type == 'electricity') {
            $eid = $request->proid;
        } else {
            $eid = null;
        }
        if ($request->type == 'gas') {
            $gid = $request->proid;
        } else {
            $gid = null;
        }

        if ($request->customer_group == 'residential') {

            $residential_professional = 0;
        } else {

            $residential_professional = 1;
        }

        if ($request->locale == 'nl') {
            $contactAffiliate = 'tariefchecker';
        } else {
            $contactAffiliate = 'veriftarif';
        }


            $IncludeG=1;
            $IncludeE=1;
        if($request->comparison_type=='pack'){

            $IncludeG=1;
            $IncludeE=1;

        }
        if($request->comparison_type=='electricity'){

            $IncludeG=0;
            $IncludeE=1;
            
        }
        if($request->comparison_type=='gas'){

            $IncludeG=1;
            $IncludeE=0;
            
        }

        if(SearchDetail::where('uuid', $request->uuid )->exists()){ 
            
            SearchDetail::where('uuid', $request->uuid )->delete();
            
        }
        SearchDetail::create([

            'uuid' => $request->uuid,
            'locale'=> $request->locale,
            'firstname' => $request->firstname,
            'lastname' => $request->lastname,
            'residential_professional' => $request->customer_group,
            'postalcode' => $request->postal_code,
            'region' => $request->region,
            'familysize' => 3,
            'comparison_type' => $request->comparison_type,
            'meter_type' => $request->meter_type,
            'pack_id' => $pack_id,
            'eid' => $eid,
            'gid' => $gid,
            'total_cost' => $request->active_value,

            'single' => $request->usage_single,
            'day' => $request->usage_day,
            'night' => $request->usage_night,
            'excl_night' => $request->usage_excl_night,
            'current_electric_supplier' => $request->curr_supplierE,

            'gas_consumption' => $request->usage_gas,
            'current_gas_supplier' => $request->curr_supplierG,
            'email' => $request->email,
            'data_from' => $request->req_from,
            
            'contact_type' => 'customer',
            'contact_affiliate' => $contactAffiliate,
            'contact_language' => $request->locale,
            'contact_source' => 'comporator App',
            'contact_campaign' => 'Campaign',
            'address_postalcode' => $request->postal_code,
            'address_region' => $request->region,
            'comparison_url' => $request->url,
            // 'comparison_fuel' => null,
            'comparison_current_supplier_e' => null,
            'comparison_current_supplier_g' => null,
            // 'comparison_filters' => null,
            'comparison_energy_cost_e' => $request->energycostE,
            // 'comparison_other_costs_e' => null,
            'comparison_energy_cost_g' => $request->energycostG,
            // 'comparison_other_costs_g' => null,
            'comparison_promo_amount_e' => $request->promoAmountE,
            'comparison_promo_amount_g' => $request->promoAmountG,
            'comparison_savings' => $request->savings,
            'contract_supplier' => $request->supplier,
            'contract_supplier_id' => $request->supplierID,
            'contract_tariff' => $request->tariff,
            'contract_tariff_id' => $request->tariffID,
            // 'contract_signup_url' => $request->signupURL,
            'contract_url_tariffcard_e' => $request->signupURLE,
            'contract_url_tariffcard_g' => $request->signupURLG,
            'contract_signdate' => $request->signdate,
            'contract_startdate' => $request->startdate,
            'contract_enddate' => $request->enddate,
            'contract_duration_db' => $request->durationdb,
            'contract_duration' => $request->duration,
            // 'contract_pricetype_e' => null,
            // 'contract_pricetype_g' => null,
            'contract_energy_cost_e' => $request->contract_energy_costE,
            'contract_energy_cost_g' => $request->contract_energy_costG,
            // 'contract_othercosts_e' => null,
            'contract_promo_amount_e' => $request->contract_promoamountE,
            // 'contract_othercosts_g' => null,
            'contract_promo_amount_g' => $request->contract_promoamountG,
            // 'contract_promo_conditions_duration' => null,
            // 'contract_promo_conditions_servicelevel' => null,
            // 'contract_promo_ids' => null,
            // 'contract_status_supplier' => null,
            // 'deal_source' => null,
            // 'deal_medium' => null,
            // 'deal_campaign' => null,
            // 'deal_value' => null,
            'includeG'=>$IncludeG,
            'includeE'=>$IncludeE,

        ]);
    }
}