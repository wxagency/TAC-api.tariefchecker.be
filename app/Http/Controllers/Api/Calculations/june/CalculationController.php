<?php

namespace App\Http\Controllers\Api\Calculations\june;

use App\Models\Api\Calculation;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\StaticElecticResidential;
use App\Models\StaticGasResidential;
use App\Models\StaticElectricProfessional;
use App\Models\StaticGasProfessional;
use App\Models\PostalcodeElectricity;
use App\Models\PostalcodeGas;
use App\Models\StaticPackResidential;
use App\Models\StaticPackProfessional;
use App\Models\Netcostes;
use App\Models\Netcostgs;
use App\Models\TaxElectricity;
use App\Models\TaxGas;
use App\Models\DynamicElectricProfessional;
use App\Models\DynamicGasProfessional;
use App\Models\DynamicElectricResidential;
use App\Models\DynamicGasResidential;
use App\Models\Discount;
use App\Models\Supplier;
use App\Models\SearchDetails\SearchDetail;
use App\Models\SupplierPopularity;
use DB;
use Response;
use Validator;

class CalculationController extends Controller
{
            /**
            * Display a listing of the resource.
            *
            * @return \Illuminate\Http\Response
            */

            function generateUUID($length = 36) 
            {
                    $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
                    $charactersLength = strlen($characters);
                    $randomString1 = '';
                    $randomString2 = '';
                    $randomString3 = '';
                    $randomString4 = '';
                    $randomString5 = '';
                    for ($i = 0; $i < 8; $i++) {
                    $randomString1 .= $characters[rand(0, $charactersLength - 1)];
                    }
                    for ($i = 0; $i < 4; $i++) {
                    $randomString2 .= $characters[rand(0, $charactersLength - 1)];
                    }
                    for ($i = 0; $i < 4; $i++) {
                    $randomString3 .= $characters[rand(0, $charactersLength - 1)];
                    }
                    for ($i = 0; $i < 4; $i++) {
                    $randomString4 .= $characters[rand(0, $charactersLength - 1)];
                    }
                    for ($i = 0; $i < 12; $i++) {
                    $randomString5 .= $characters[rand(0, $charactersLength - 1)];
                    }
                    return $randomString1.'-'.$randomString2.'-'.$randomString3.'-'.$randomString4.'-'.$randomString5;
            }

            public function juneCalculation(Request $request)
            {
            $this->validate($request,[
            'locale'=>'required',
            'postal_code'=>'required',
            'customer_type'=>'required',        
            ]);

            $locale=$request->locale; //fr
            $postalCode=$request->postal_code;
            $customerGroup=$request->customer_type; //professional 
            $FirstResidence=3;  
            $uuid=$this->generateUUID();
            $IncludeG=false;
            $IncludeE=false;
            $first_residence=false;
            $decentralise_production=false;
            $capacity_decentalise=0;
            $currentSupplierE=null;
            $currentSupplierG=null;

            if(isset($request->usage['electricity']['single'])){
            $registerNormal=$request->usage['electricity']['single'];
            }else{
            $registerNormal=0;
            }

            if(isset($request->usage['electricity']['day'])){
            $registerDay=$request->usage['electricity']['day'];
            }else{
            $registerDay=0;
            }

            if(isset($request->usage['electricity']['night'])){
            $registerNight=$request->usage['electricity']['night'];
            }else{
            $registerNight=0;
            }

            if(isset($request->usage['electricity']['excl_night'])){
            $registerExclNight=$request->usage['electricity']['excl_night'];
            }else{
            $registerExclNight=0;
            }

            if(isset($request->usage['gas'])){
            $registerG=$request->usage['gas'];
            $IncludeG=true;
            }else{
            $registerG=0;
            }

            $sumRegisters=$registerNormal+$registerDay+$registerNight+$registerExclNight;

            if($registerNormal!=0 || $registerDay!=0 || $registerNight!=0 || $registerExclNight!=0){

            $IncludeE=true;

            }



            // meter-type

            if($registerNormal!=0 && $registerDay==0 && $registerNight==0 && $registerExclNight==0){

            $Metertype='single';

            }

            if($registerNormal==0 && $registerDay!=0 && $registerNight!=0 && $registerExclNight==0){

            $Metertype='double';

            }

            if($registerNormal!=0 && $registerDay==0 && $registerNight==0 && $registerExclNight!=0){

            $Metertype='single_excl_night';

            }

            if($registerNormal==0 && $registerDay!=0 && $registerNight!=0 && $registerExclNight!=0){

            $Metertype='double_excl_night';

            }

            // end-meter type








            $sumRegisters=$registerNormal+$registerDay+$registerNight+$registerExclNight;
            if(isset($request->capacity_decen_pro)){
            $capacityDecentrelisedProduction=$request->capacity_decen_pro;
            }else{
            $capacityDecentrelisedProduction=0;
            }




            //   calculation-start


            /**
            * Electricity
            */




            if($sumRegisters!=0 && $registerG==0)
            {

                $res['postalE']=PostalcodeElectricity::select('distribution_id','DNB','region')->where('netadmin_zip',$postalCode)->orderBy('DNB', 'asc')->first();
                $dnbE=$res['postalE']->DNB;
                $region=$res['postalE']->region;
                $distribution_id=$res['postalE']->distribution_id;


                    if($customerGroup=='professional')
                    {
                            $customer='PRO';
                            $currentDate=date("Y/m/d");
                            $result['electricity']['Netcostes']=Netcostes::where('dgo',$dnbE)->where('segment',$customer)
                            ->where('volume_lower','<=',$sumRegisters)->where('volume_upper','>=',$sumRegisters)
                            ->first();
                            $result['electricity']['tax']=TaxElectricity::where('dgo',$dnbE)->where('segment',$customer)->first();
                            $result['products'] = DynamicElectricProfessional::whereHas('staticData', function($q) {
                            $q->where('acticve', 'Y');
                            })->where($region,'=','Y')->get();
                            $comparisonType='electricity';
                    }else
                    {
                            $customer='RES';
                            $currentDate=date("Y/m/d");
                            $result['electricity']['Netcostes']=Netcostes::where('dgo',$dnbE)->where('segment',$customer)
                            ->where('volume_lower','<=',$sumRegisters)->where('volume_upper','>=',$sumRegisters)
                            ->first();
                            $result['electricity']['tax']=TaxElectricity::where('dgo',$dnbE)->where('segment',$customer)->first();
                            $result['products'] = DynamicElectricResidential::whereHas('staticData', function($q) {
                            $q->where('acticve', 'Y');
                            })->where($region,'=','Y')->get();
                            $comparisonType='electricity';
                    }
            }

            if($sumRegisters==0 && $registerG!=0)
            {

                $res['postalG']=PostalcodeGas::select('distribution_id','DNB','region')->where('netadmin_zip',$postalCode)->orderBy('DNB', 'asc')->first();
                $dnbG=$res['postalG']->DNB;
                $region=$res['postalG']->region;
                $distribution_id=$res['postalG']->distribution_id;

                if($customerGroup=='professional')
                {
                    $customer='PRO';
                    $currentDate=date("Y/m/d");
                    $result['gas']['Netcostes']=Netcostgs::where('dgo',$dnbG)->where('segment',$customer)
                    ->where('volume_lower','<=',$registerG)->where('volume_upper','>=',$registerG)
                    ->first();
                    $result['gas']['tax']=TaxGas::where('dgo',$dnbG)->where('segment',$customer)->first();
                    $result['products'] = DynamicGasProfessional::whereHas('staticData', function($q) {
                    $q->where('acticve', 'Y');
                    })->where($region,'=','Y')->get();
                    $comparisonType='gas';
                }else
                {
                    $customer='RES';
                    $currentDate=date("Y/m/d");
                    $result['gas']['Netcostes']=Netcostgs::where('dgo',$dnbG)->where('segment',$customer)
                    ->where('volume_lower','<=',$registerG)->where('volume_upper','>=',$registerG)
                    ->first();
                    $result['gas']['tax']=TaxGas::where('dgo',$dnbG)->where('segment',$customer)->first();
                    $result['products'] = DynamicGasResidential::whereHas('staticData', function($q) {
                    $q->where('acticve', 'Y');
                    })->where($region,'=','Y')->get();
                    $comparisonType='gas';
                }

            }


            if($sumRegisters!=0 && $registerG!=0){



            $res['postalE']=PostalcodeElectricity::select('DNB','region')->where('netadmin_zip',$postalCode)
            ->orderBy('DNB', 'asc')
            ->first();

            $res['postalG']=PostalcodeGas::select('distribution_id','DNB','region')->where('netadmin_zip',$postalCode)
            ->orderBy('DNB', 'asc')
            ->first();


            $dnbE=$res['postalE']->DNB;
            $dnbG=$res['postalG']->DNB;


            $distribution_id=$res['postalG']->distribution_id;

            $regionE=$res['postalE']->region;
            $region=$regionG=$res['postalG']->region;


            if($customerGroup=='professional'){



            $customer='PRO';
            $currentDate=date("Y/m/d");

            $result['gas']['Netcostes']=Netcostgs::where('dgo',$dnbG)->where('segment',$customer)
            ->where('volume_lower','<=',$registerG)->where('volume_upper','>=',$registerG)
            ->first();



            $result['electricity']['Netcostes']=Netcostes::where('dgo',$dnbE)->where('segment',$customer)
            ->where('volume_lower','<=',$sumRegisters)->where('volume_upper','>=',$sumRegisters)
            ->first();

            $result['electricity']['tax']=TaxElectricity::where('dgo',$dnbE)->where('segment',$customer)
            ->where('volume_lower','<=',$sumRegisters)->where('volume_upper','>=',$sumRegisters)
            ->first();


            $result['gas']['tax']=TaxGas::where('dgo',$dnbG)->where('segment',$customer)
            ->where('volume_lower','<=',$sumRegisters)->where('volume_upper','>=',$sumRegisters)
            ->first();



            $result['products']=StaticPackProfessional::select(
            'static_pack_professionals.*',
            'dynamic_electric_professionals.product_id as product_idE','dynamic_electric_professionals.date as dateE','dynamic_electric_professionals.valid_from as valid_fromE','dynamic_electric_professionals.valid_till as valid_tillE','dynamic_electric_professionals.supplier as supplierE','dynamic_electric_professionals.product as productE','dynamic_electric_professionals.fuel as fuelE','dynamic_electric_professionals.duration as durationE','dynamic_electric_professionals.fixed_indexed as fixed_indiableE','dynamic_electric_professionals.fixed_indexed as fixed_indiableE','dynamic_electric_professionals.customer_segment as segmentE','dynamic_electric_professionals.VL as VLE','dynamic_electric_professionals.WA as WAE','dynamic_electric_professionals.BR as BRE','dynamic_electric_professionals.volume_lower as volume_lowerE','dynamic_electric_professionals.volume_upper as volume_upperE','dynamic_electric_professionals.price_single as price_singleE','dynamic_electric_professionals.price_day as price_dayE','dynamic_electric_professionals.price_night as price_nightE','dynamic_electric_professionals.price_excl_night as price_excl_nightE','dynamic_electric_professionals.ff_single as ff_singleE','dynamic_electric_professionals.ff_day_night as ff_day_nightE','dynamic_electric_professionals.ff_excl_night as ff_excl_nightE','dynamic_electric_professionals.gsc_vl as gsc_vlE','dynamic_electric_professionals.wkc_vl as wkc_vlE','dynamic_electric_professionals.gsc_wa as gsc_waE','dynamic_electric_professionals.gsc_br as gsc_brE','dynamic_electric_professionals.prices_url_nl as prices_url_nlE','dynamic_electric_professionals.prices_url_fr as prices_url_frE','dynamic_electric_professionals.index_name as indexE','dynamic_electric_professionals.index_value as waardeE','dynamic_electric_professionals.coeff_single as coeff_singleE','dynamic_electric_professionals.term_single as term_singleE','dynamic_electric_professionals.coeff_day as coeff_dayE','dynamic_electric_professionals.term_day as term_dayE','dynamic_electric_professionals.coeff_night as coeff_nightE','dynamic_electric_professionals.term_night as term_nightE','dynamic_electric_professionals.coeff_excl as coeff_exclE','dynamic_electric_professionals.term_excl as term_exclE',
            'dynamic_gas_professionals.product_id as product_idG','dynamic_gas_professionals.date as dateG','dynamic_gas_professionals.valid_from as valid_fromG','dynamic_gas_professionals.valid_till as valid_tillG','dynamic_gas_professionals.supplier as supplierG','dynamic_gas_professionals.product as productG','dynamic_gas_professionals.fuel as fuelG','dynamic_gas_professionals.duration as durationG','dynamic_gas_professionals.fixed_indexed as fixed_indiableG','dynamic_gas_professionals.fixed_indexed as fixed_indiableG','dynamic_gas_professionals.segment as segmentG','dynamic_gas_professionals.VL as VLG','dynamic_gas_professionals.WA as WAG','dynamic_gas_professionals.BR as BRG','dynamic_gas_professionals.volume_lower as volume_lowerG','dynamic_gas_professionals.volume_upper as volume_upperG','dynamic_gas_professionals.price_gas as price_gasG','dynamic_gas_professionals.ff  as ffG','dynamic_gas_professionals.prices_url_nl as prices_url_nlG','dynamic_gas_professionals.prices_url_fr as prices_url_frG','dynamic_gas_professionals.index_name as indexG','dynamic_gas_professionals.index_value as waardeG','dynamic_gas_professionals.coeff as coeffG','dynamic_gas_professionals.term as term')            
            ->Join('dynamic_electric_professionals','dynamic_electric_professionals.product_id','=','static_pack_professionals.pro_id_E')
            ->Join('dynamic_gas_professionals','dynamic_gas_professionals.product_id','=','static_pack_professionals.pro_id_G')  
            ->where('dynamic_gas_professionals.'.$region.'','=','Y')
            //->whereDate('dynamic_gas_professionals.valid_from','<=',$currentDate)->whereDate('dynamic_gas_professionals.valid_till','>=',$currentDate)
            //->whereDate('dynamic_electric_professionals.valid_from','<=',$currentDate)->whereDate('dynamic_electric_professionals.valid_till','>=',$currentDate)
            ->where('static_pack_professionals.active','Y')
            ->get();

            $comparisonType='pack';



            }else{



            $customer='RES';
            $currentDate=date("Y/m/d");

            $result['gas']['Netcostes']=Netcostgs::where('dgo',$dnbG)->where('segment',$customer)
            ->where('volume_lower','<=',$registerG)->where('volume_upper','>=',$registerG)
            ->first();


            $result['electricity']['Netcostes']=Netcostes::where('dgo',$dnbE)->where('segment',$customer)
            ->where('volume_lower','<=',$sumRegisters)->where('volume_upper','>=',$sumRegisters)
            ->first();

            $result['electricity']['tax']=TaxElectricity::where('dgo',$dnbE)->where('segment',$customer)
            ->where('volume_lower','<=',$sumRegisters)
            ->where('volume_upper','>=',$sumRegisters)
            ->where($region,'Y')
            ->first(); 

            $result['gas']['tax']=TaxGas::where('dgo',$dnbG)->where('segment',$customer)
            ->where('volume_lower','<=',$sumRegisters)->where('volume_upper','>=',$sumRegisters)
            ->first();

            $result['products']=StaticPackResidential::select(
            'static_pack_residentials.*',
            'dynamic_electric_residentials.product_id as product_idE','dynamic_electric_residentials.date as dateE','dynamic_electric_residentials.valid_from as valid_fromE','dynamic_electric_residentials.valid_till as valid_tillE','dynamic_electric_residentials.supplier as supplierE','dynamic_electric_residentials.product as productE','dynamic_electric_residentials.fuel as fuelE','dynamic_electric_residentials.duration as durationE','dynamic_electric_residentials.fixed_indexed as fixed_indiableE','dynamic_electric_residentials.fixed_indexed as fixed_indiableE','dynamic_electric_residentials.customer_segment as segmentE','dynamic_electric_residentials.VL as VLE','dynamic_electric_residentials.WA as WAE','dynamic_electric_residentials.BR as BRE','dynamic_electric_residentials.volume_lower as volume_lowerE','dynamic_electric_residentials.volume_upper as volume_upperE','dynamic_electric_residentials.price_single as price_singleE','dynamic_electric_residentials.price_day as price_dayE','dynamic_electric_residentials.price_night as price_nightE','dynamic_electric_residentials.price_excl_night as price_excl_nightE','dynamic_electric_residentials.ff_single as ff_singleE','dynamic_electric_residentials.ff_day_night as ff_day_nightE','dynamic_electric_residentials.ff_excl_night as ff_excl_nightE','dynamic_electric_residentials.gsc_vl as gsc_vlE','dynamic_electric_residentials.wkc_vl as wkc_vlE','dynamic_electric_residentials.gsc_wa as gsc_waE','dynamic_electric_residentials.gsc_br as gsc_brE','dynamic_electric_residentials.prices_url_nl as prices_url_nlE','dynamic_electric_residentials.prices_url_fr as prices_url_frE','dynamic_electric_residentials.index_name as indexE','dynamic_electric_residentials.index_value as waardeE','dynamic_electric_residentials.coeff_single as coeff_singleE','dynamic_electric_residentials.term_single as term_singleE','dynamic_electric_residentials.coeff_day as coeff_dayE','dynamic_electric_residentials.term_day as term_dayE','dynamic_electric_residentials.coeff_night as coeff_nightE','dynamic_electric_residentials.term_night as term_nightE','dynamic_electric_residentials.coeff_excl as coeff_exclE','dynamic_electric_residentials.term_excl as term_exclE',
            'dynamic_gas_residentials.product_id as product_idG','dynamic_gas_residentials.date as dateG','dynamic_gas_residentials.valid_from as valid_fromG','dynamic_gas_residentials.valid_till as valid_tillG','dynamic_gas_residentials.supplier as supplierG','dynamic_gas_residentials.product as productG','dynamic_gas_residentials.fuel as fuelG','dynamic_gas_residentials.duration as durationG','dynamic_gas_residentials.fixed_indexed as fixed_indiableG','dynamic_gas_residentials.fixed_indexed as fixed_indiableG','dynamic_gas_residentials.segment as segmentG','dynamic_gas_residentials.VL as VLG','dynamic_gas_residentials.WA as WAG','dynamic_gas_residentials.BR as BRG','dynamic_gas_residentials.volume_lower as volume_lowerG','dynamic_gas_residentials.volume_upper as volume_upperG',
            'dynamic_gas_residentials.ff as ffG',
            'dynamic_gas_residentials.price_gas as price_gasG','dynamic_gas_residentials.prices_url_nl as prices_url_nlG','dynamic_gas_residentials.prices_url_fr as prices_url_frG','dynamic_gas_residentials.index_name as indexG','dynamic_gas_residentials.index_value as waardeG','dynamic_gas_residentials.coeff as coeffG','dynamic_gas_residentials.term as term')            
            ->Join('dynamic_electric_residentials','dynamic_electric_residentials.product_id','=','static_pack_residentials.pro_id_E')
            ->Join('dynamic_gas_residentials','dynamic_gas_residentials.product_id','=','static_pack_residentials.pro_id_G')   
            ->where('dynamic_gas_residentials.'.$region.'','=','Y') 
            //->whereDate('dynamic_gas_residentials.valid_from','<=',$currentDate)->whereDate('dynamic_gas_residentials.valid_till','>=',$currentDate)
            //->whereDate('dynamic_electric_residentials.valid_from','<=',$currentDate)->whereDate('dynamic_electric_residentials.valid_till','>=',$currentDate)
            ->where('static_pack_residentials.active','Y')
            ->get();

            $comparisonType='pack';

            }

            }



            /**
            * Electricity-end
            */

            //**json output -start */


            // dd($result['products']['electricity']);

            $pro = [];
            $products = [];
            // parameter-start


            //dd($result['products']);
            unset($pro); $pro=array(); 
            unset($products); $products=array(); 

            if(isset($dnbE)){
            $dnbE=$dnbE;
            }else{
            $dnbE=null;

            }
            if(isset($dnbG)){
            $dnbG=$dnbG;
            }else{
            $dnbG=null;

            }

            if(isset($regionE)){
            $regionE=$regionE;
            }else{
            $regionE=null;

            }
            if(isset($zip)){
            $zip=$zip;
            }else{
            $zip=null;

            }

            if(isset($CurrentSupplierE)){
            $CurrentSupplierE=$CurrentSupplierE;
            }else{

            $CurrentSupplierE=null;
            }

            if(isset($CurrentsupplierG)){
            $CurrentsupplierG=$CurrentsupplierG;
            }else{

            $CurrentsupplierG=null;
            }
            if(isset($Metertype)){
            $Metertype=$Metertype;

            }else{

            $Metertype=null;
            }

            if(isset($locale)){

            $locale=$locale;
            }else{
            $locale=null;
            }

            if(isset($email)){

            $email=$email;

            }else{

            $email=null;
            }

            if($locale=='nl'){

            $lng='NL';
            }else{
            $lng='FR';
            }


            if(isset($request->residents)){
            $residents=$request->residents;

            }else{
            $residents=1;
            }

            if(isset($request->first_residence)){
            $first_residence=$request->first_residence;
            }else{

            $first_residence=0;
            }

            if(isset($request->dec_pro)){
            $decentralise_production=$request->dec_pro;
            }else{

            $decentralise_production="";
            }

            if(isset($request->capacity_decen_pro)){
            $capacity_decentalise=$request->capacity_decen_pro;
            }else{

            $capacity_decentalise="";

            }

            if($comparisonType=='pack'){

            $currentSupplierG=$currentSupplierE;
            $IncludeG=1;
            $IncludeE=1;
            }


            foreach($result['products'] as $getProducts){



            $products['parameters'] = [
            'uuid' => $uuid        
            ];
            $products['parameters']['values']=[
            'customer_group' => $customerGroup,
            'location_id' =>$distribution_id,
            'region' => $regionE,
            'dgo_id_electricity' => $dnbE,
            'dgo_id_gas' => $dnbG,
            'residents' =>$residents ,
            'current_payment_amount' => 0,
            'current_supplier_name_gas' => $currentSupplierG,
            'current_supplier_name_electricity' => $currentSupplierE,
            'current_supplier_id_gas' => null,
            'current_supplier_id_electricity' => null,
            'usage_single' => $registerNormal,
            'usage_day' => $registerDay,
            'usage_night' => $registerNight,
            'usage_excl_night' => $registerExclNight,
            'usage_gas' => $registerG,
            'current_payment_amount_gas' => 0,
            'email' => $email,
            'postal_code' => $postalCode,
            'comparison_type' => $comparisonType,
            'meter_type' => $Metertype,
            'locale' => $locale,

            'includeG' => $IncludeG,
            'includeE' => $IncludeE,
            'first_residence' => $first_residence,
            'decentralise_production' => $decentralise_production,
            'capacity_decentalise' => $capacity_decentalise


            ];
            // parameter-end

            // product-start 

            if($sumRegisters!="0" && $registerG=="0"){  



            if($locale=='nl'){
            if(isset($getProducts->staticData->product_name_NL)){ $name=utf8_encode($getProducts->staticData->product_name_NL); }
            if(isset($getProducts->staticData->info_NL)){ $description=utf8_encode($getProducts->staticData->info_NL); }

            if(isset($getProducts->staticData->tariff_description_NL)){ $tariff_description_NL=utf8_encode($getProducts->staticData->tariff_description_NL); }
            if(isset($getProducts->staticData->subscribe_url_NL)){ $subscribe_url_NL=utf8_encode($getProducts->staticData->subscribe_url_NL); }
            if(isset($getProducts->staticData->terms_NL)){ $terms_NL=utf8_encode($getProducts->staticData->terms_NL); }
            if(isset($getProducts->prices_url_nl)){ $terms_dynamic=utf8_encode($getProducts->prices_url_nl); }
            }else{


            if(isset($getProducts->staticData->product_name_FR)){ $name=utf8_encode($getProducts->staticData->product_name_FR); }
            if(isset($getProducts->staticData->info_FR)){ $description=utf8_encode($getProducts->staticData->info_FR); }

            if(isset($getProducts->staticData->tariff_description_FR)){ $tariff_description_NL=utf8_encode($getProducts->staticData->tariff_description_FR); }
            if(isset($getProducts->staticData->subscribe_url_FR)){ $subscribe_url_NL=utf8_encode($getProducts->staticData->subscribe_url_FR); }
            if(isset($getProducts->staticData->terms_FR)){ $terms_NL=utf8_encode($getProducts->staticData->terms_FR); }
            if(isset($getProducts->prices_url_fr)){ $terms_dynamic=utf8_encode($getProducts->prices_url_fr); }


            }


            if(isset($getProducts->staticData->duration)){ $duration=$getProducts->staticData->duration; }
            if(isset($getProducts->staticData->service_level_payment)){ $service_level_payment=utf8_encode($getProducts->staticData->service_level_payment); }
            if(isset($getProducts->staticData->service_level_invoicing)){ $service_level_invoicing=utf8_encode($getProducts->staticData->service_level_invoicing); }
            if(isset($getProducts->staticData->service_level_contact)){ $service_level_contact=utf8_encode($getProducts->staticData->service_level_contact); }
            if(isset($getProducts->staticData->customer_condition)){ $customer_condition=utf8_encode($getProducts->staticData->customer_condition); }
            if(isset($getProducts->staticData->customer_condition)){ $customer_condition=utf8_encode($getProducts->staticData->customer_condition); }
            if(isset($getProducts->staticData->green_percentage)){ 
            $green_percentage=utf8_encode($getProducts->staticData->green_percentage); 

            if($green_percentage=="100%"){

            $gp=100;
            }else{

            $gp=0;
            }
            }

            if(isset($getProducts->staticData->FF_pro_rata)){ $FF_pro_rata=utf8_encode($getProducts->staticData->FF_pro_rata); }
            if(isset($getProducts->staticData->inv_period)){ $inv_period=utf8_encode($getProducts->staticData->inv_period); }
            if(isset($getProducts->staticData->origin)){ 
            $origin=utf8_encode($getProducts->staticData->origin); 
            }

            if(isset($getProducts->fixed_indexed)){ 
            $priceType=utf8_encode($getProducts->fixed_indexed); 

            if($priceType=='Fix'){

            $pt='fixed';
            }else{
            $pt='variable';

            }
            }


            $product_popularity_count=SupplierPopularity::where('product_id',$getProducts->id)->where('customer_group',$customerGroup)->where('comparison_type',$comparisonType)->get();
            $product_popularity_total=SupplierPopularity::where('customer_group',$customerGroup)->where('comparison_type',$comparisonType)->get();
            $total=0;
            foreach($product_popularity_total as $product_popularity_total){

            $total=$product_popularity_total->popularity+$total;
            }

            $pcount=0;
            foreach($product_popularity_count as $product_popularity_count){
            $pcount=$product_popularity_count->popularity;
            }
            if($total!=0 && $pcount!=0){
            $popularity=round(($pcount/$total)*100);
            }else{
            $popularity=0;   
            }

            $pack_url=$subscribe_url_NL;
            $products['product'] = [
            '_id' => $getProducts->id,
            'id' => $getProducts->product_id,
            'name' =>$name ,
            'description' => $description,
            'tariff_description' => $tariff_description_NL,
            'type' => utf8_encode('electricity'),
            'contract_duration' => $duration,
            'service_level_payment' => strtolower($service_level_payment),
            'service_level_invoicing' => strtolower($service_level_invoicing),
            'service_level_contact' => strtolower($service_level_contact),
            'customer_condition' => $customer_condition,
            'origin' => $origin,
            'pricing_type' => utf8_encode($pt),
            'green_percentage' => $gp,
            'subscribe_url' => $subscribe_url_NL,
            'terms_url' => $terms_NL,
            'terms_url_dynamic'=> $terms_dynamic,
            'ff_pro_rata' => $FF_pro_rata,
            'inv_period' => $inv_period,
            'popularity_score' => $popularity
            ];
            }





            if($sumRegisters=="0" && $registerG!="0"){ 

            if($locale=='nl'){

            if(isset($getProducts->staticData->product_name_NL)){ $name=utf8_encode($getProducts->staticData->product_name_NL); }
            if(isset($getProducts->staticData->info_NL)){ $description=$D_name=iconv("cp1252", "utf-8//TRANSLIT",$getProducts->staticData->info_NL); }

            if(isset($getProducts->staticData->tariff_description_NL)){ $tariff_description_NL=utf8_encode($getProducts->staticData->tariff_description_NL); }

            if(isset($getProducts->staticData->subscribe_url_NL)){ $subscribe_url_NL=utf8_encode($getProducts->staticData->subscribe_url_NL); }
            if(isset($getProducts->staticData->terms_NL)){ $terms_NL=utf8_encode($getProducts->staticData->terms_NL); }
            if(isset($getProducts->prices_url_nl)){ $terms_dynamic=utf8_encode($getProducts->prices_url_nl); }

            }else{

            if(isset($getProducts->staticData->product_name_FR)){ $name=utf8_encode($getProducts->staticData->product_name_FR); }
            if(isset($getProducts->staticData->info_FR)){ $description=iconv("cp1252", "utf-8//TRANSLIT",$getProducts->staticData->info_FR); }

            if(isset($getProducts->staticData->tariff_description_FR)){ $tariff_description_NL=utf8_encode($getProducts->staticData->tariff_description_FR); }

            if(isset($getProducts->staticData->subscribe_url_FR)){ $subscribe_url_NL=utf8_encode($getProducts->staticData->subscribe_url_FR); }
            if(isset($getProducts->staticData->terms_FR)){ $terms_NL=utf8_encode($getProducts->staticData->terms_FR); }
            if(isset($getProducts->prices_url_fr)){ $terms_dynamic=utf8_encode($getProducts->prices_url_fr); }





            }
            if(isset($getProducts->staticData->duration)){ $duration=$getProducts->staticData->duration; }
            if(isset($getProducts->staticData->service_level_payment)){ $service_level_payment=utf8_encode($getProducts->staticData->service_level_payment); }
            if(isset($getProducts->staticData->service_level_invoicing)){ $service_level_invoicing=utf8_encode($getProducts->staticData->service_level_invoicing); }
            if(isset($getProducts->staticData->service_level_contact)){ $service_level_contact=utf8_encode($getProducts->staticData->service_level_contact); }
            if(isset($getProducts->staticData->customer_condition)){ $customer_condition=utf8_encode($getProducts->staticData->customer_condition); }
            if(isset($getProducts->staticData->customer_condition)){ $customer_condition=utf8_encode($getProducts->staticData->customer_condition); }


            if(isset($getProducts->staticData->FF_pro_rata)){ $FF_pro_rata=utf8_encode($getProducts->staticData->FF_pro_rata);


            if($getProducts->staticData->FF_pro_rata=="N"){

            $FF=false;
            }
            if($getProducts->staticData->FF_pro_rata==""){

            $FF=null;
            }
            if($getProducts->staticData->FF_pro_rata=="Y"){

            $FF=true;
            }
            }
            if(isset($getProducts->staticData->inv_period)){ $inv_period=utf8_encode($getProducts->staticData->inv_period); }
            if(isset($getProducts->staticData->origin)){ 
            $origin=utf8_encode($getProducts->staticData->origin); 
            }

            if(isset($getProducts->fixed_indexed)){ 
            $priceType=utf8_encode($getProducts->fixed_indexed); 

            if($priceType=='Fix'){

            $pt='fixed';
            }else{
            $pt='variable';

            }
            }

            $product_popularity_count=SupplierPopularity::where('product_id',$getProducts->id)->where('customer_group',$customerGroup)->where('comparison_type',$comparisonType)->get();
            $product_popularity_total=SupplierPopularity::where('customer_group',$customerGroup)->where('comparison_type',$comparisonType)->get();
            $total=0;
            foreach($product_popularity_total as $product_popularity_total){

            $total=$product_popularity_total->popularity+$total;
            }

            $pcount=0;
            foreach($product_popularity_count as $product_popularity_count){
            $pcount=$product_popularity_count->popularity;
            }
            if($total!=0 && $pcount!=0){
            $popularity=round(($pcount/$total)*100);
            }else{
            $popularity=0;   
            }

            $pack_url=$subscribe_url_NL;
            $products['product'] = [
            '_id' => $getProducts->id,
            'id' => $getProducts->product_id,
            'name' =>$name ,
            'description' => $description,
            'tariff_description' => $tariff_description_NL,
            'type' => utf8_encode('gas'),
            'contract_duration' => $duration,
            'service_level_payment' => strtolower($service_level_payment),
            'service_level_invoicing' => strtolower($service_level_invoicing),
            'service_level_contact' => strtolower($service_level_contact),
            'customer_condition' => $customer_condition,
            'origin' => null,
            'pricing_type' => utf8_encode($pt),
            'green_percentage' => 0,
            'subscribe_url' => $subscribe_url_NL,
            'terms_url' => $terms_NL,
            'terms_url_dynamic'=> $terms_dynamic,
            'ff_pro_rata' =>$FF,
            'inv_period' => $inv_period,
            'popularity_score' => $popularity
            ];
            }






            // product-underlying products-electricity
            if($sumRegisters!="0" && $registerG!="0"){                  

            if(isset($getProducts->staticElectricDetails->origin)){
            $origin=$getProducts->staticElectricDetails->origin;
            }else{

            $origin=null;
            }

            if(isset($getProducts->staticElectricDetails->fixed_indiable)){ 
            $priceType=$getProducts->staticElectricDetails->fixed_indiable; 

            if($priceType=='Fix'){

            $pt='fixed';
            }else{
            $pt='variable';

            }
            }

            if(isset($getProducts->staticElectricDetails->green_percentage)){

            if($getProducts->staticElectricDetails->green_percentage=='100%'){

            $gP=100;
            }else{

            $gP=0;
            }
            }


            if(isset($getProducts->staticElectricDetails->FF_pro_rata)){ $FF_pro_rata=$getProducts->staticElectricDetails->FF_pro_rata;


            if($getProducts->staticElectricDetails->FF_pro_rata=="N"){

            $FF=false;
            }
            if($getProducts->staticElectricDetails->FF_pro_rata==""){

            $FF=null;
            }
            if($getProducts->staticElectricDetails->FF_pro_rata=="Y"){

            $FF=true;
            }
            }


            if($locale=='nl'){
            $E_name=utf8_encode($getProducts->staticElectricDetails->product_name_NL);
            $E_info=iconv("cp1252", "utf-8//TRANSLIT",$getProducts->staticElectricDetails->info_NL);
            $E_desc=utf8_encode($getProducts->staticElectricDetails->info_NL);
            $E_url=utf8_encode($getProducts->staticElectricDetails->subscribe_url_NL);
            $E_term=utf8_encode($getProducts->staticElectricDetails->terms_NL);
            if(isset($getProducts->prices_url_nlE)){ $terms_dynamic=utf8_encode($getProducts->prices_url_nlE); }

            }else{

            $E_name=utf8_encode($getProducts->staticElectricDetails->product_name_FR);
            $E_info=iconv("cp1252", "utf-8//TRANSLIT",$getProducts->staticElectricDetails->info_FR);
            $E_desc=utf8_encode($getProducts->staticElectricDetails->info_FR);
            $E_url=utf8_encode($getProducts->staticElectricDetails->subscribe_url_FR);
            $E_term=utf8_encode($getProducts->staticElectricDetails->terms_FR);
            if(isset($getProducts->prices_url_nlE)){ $terms_dynamic=utf8_encode($getProducts->prices_url_frE); }

            }

            $product_popularity_count=SupplierPopularity::where('product_id',$getProducts->staticElectricDetails->product_id)->where('customer_group',$customerGroup)->where('comparison_type',$comparisonType)->get();
            $product_popularity_total=SupplierPopularity::where('customer_group',$customerGroup)->where('comparison_type',$comparisonType)->get();
            $total=0;
            foreach($product_popularity_total as $product_popularity_total){

            $total=$product_popularity_total->popularity+$total;
            }

            $pcount=0;
            foreach($product_popularity_count as $product_popularity_count){
            $pcount=$product_popularity_count->popularity;
            }
            if($total!=0 && $pcount!=0){
            $popularity=round(($pcount/$total)*100);
            }else{
            $popularity=0;   
            }
            $pack_url=$E_url;
            $products['product']['underlying_products']['electricity'] = [
            '_id' =>$getProducts->staticGasDetails->id,
            'id' => utf8_encode($getProducts->staticElectricDetails->product_id),
            'name' =>  $E_name,
            'description' =>$E_info,
            'tariff_description' => $E_desc ,
            'type' => utf8_encode('electricity'),
            'contract_duration' => $getProducts->staticElectricDetails->duration,
            'service_level_payment' => utf8_encode(strtolower($getProducts->staticElectricDetails->service_level_payment)),
            'service_level_invoicing' => utf8_encode(strtolower($getProducts->staticElectricDetails->service_level_invoicing)),
            'service_level_contact' => utf8_encode(strtolower($getProducts->staticElectricDetails->service_level_contact)),
            'customer_condition' => utf8_encode($getProducts->staticElectricDetails->customer_condition),
            'origin' => utf8_encode(strtolower($origin)),
            'pricing_type' => $pt,
            'green_percentage' => $gP,
            'subscribe_url' => $E_url,
            'terms_url' => $E_term,
            'terms_url_dynamic'=> $terms_dynamic,
            'ff_pro_rata' => $FF,
            'inv_period' => $getProducts->staticElectricDetails->inv_period,
            'popularity_score' => $popularity                                    
            ];

            }




            // product-underlying products-electricity-end

            // product-underlying products-gas
            if($sumRegisters!="0" && $registerG!="0"){

            if(isset($getProducts->staticGasDetails->origin)){
            $origin=$getProducts->staticGasDetails->origin;
            }else{

            $origin=null;
            }

            if(isset($getProducts->staticGasDetails->fixed_indiable)){ 
            $priceType=$getProducts->staticGasDetails->fixed_indiable; 

            if($priceType=='Fix'){

            $pt='fixed';
            }else{
            $pt='variable';

            }
            }




            if(isset($getProducts->staticGasDetails->FF_pro_rata)){ $FF_pro_rata=$getProducts->staticGasDetails->FF_pro_rata;


            if($getProducts->staticGasDetails->FF_pro_rata=="N"){

            $FF=false;
            }
            if($getProducts->staticGasDetails->FF_pro_rata==""){

            $FF=null;
            }
            if($getProducts->staticGasDetails->FF_pro_rata=="Y"){

            $FF=true;
            }
            }


            if($locale=='nl'){
            $G_name=utf8_encode($getProducts->staticGasDetails->product_name_NL);
            $G_info=iconv("cp1252", "utf-8//TRANSLIT",$getProducts->staticGasDetails->info_NL);
            $G_desc=utf8_encode($getProducts->staticGasDetails->tariff_description_NL);
            $G_url=utf8_encode($getProducts->staticGasDetails->subscribe_url_NL);
            $G_term=utf8_encode($getProducts->staticGasDetails->terms_NL);
            if(isset($getProducts->prices_url_nlG)){ $terms_dynamic=utf8_encode($getProducts->prices_url_nlG); }

            }else{

            $G_name=utf8_encode($getProducts->staticGasDetails->product_name_FR);
            $G_info=iconv("cp1252", "utf-8//TRANSLIT",$getProducts->staticGasDetails->info_FR);
            $G_desc=utf8_encode($getProducts->staticGasDetails->tariff_description_FR);
            $G_url=utf8_encode($getProducts->staticGasDetails->subscribe_url_FR);
            $G_term=utf8_encode($getProducts->staticGasDetails->terms_FR);
            if(isset($getProducts->prices_url_frG)){ $terms_dynamic=utf8_encode($getProducts->prices_url_frG); }

            }

            $pack_url=$pack_url;
            $products['product']['underlying_products']['gas'] = [
            '_id' => $getProducts->staticGasDetails->id,
            'id' => utf8_encode($getProducts->staticGasDetails->product_id),
            'name' => $G_name,
            'description' => $G_info,
            'tariff_description' => $G_desc,
            'type' => utf8_encode('gas'),
            'contract_duration' => $getProducts->staticGasDetails->duration,
            'service_level_payment' => utf8_encode(strtolower($getProducts->staticGasDetails->service_level_payment)),
            'service_level_invoicing' => utf8_encode(strtolower($getProducts->staticGasDetails->service_level_invoicing)),
            'service_level_contact' => utf8_encode(strtolower($getProducts->staticGasDetails->service_level_contact)),
            'customer_condition' => utf8_encode($getProducts->staticGasDetails->customer_condition),
            'origin' => utf8_encode(strtolower($origin)),
            'pricing_type' => $pt,
            'green_percentage' => 0,
            'subscribe_url' => $G_url,
            'terms_url' => $G_term,
            'terms_url_dynamic'=> $terms_dynamic,
            'ff_pro_rata' => $FF,
            'inv_period' => $getProducts->staticGasDetails->inv_period,
            'popularity_score' =>0                                 
            ];
            }
            // product-underlying products-gas-end


            if($sumRegisters!="0" && $registerG!="0"){ 



            if(isset($getProducts->staticElectricDetails->origin)){ $origin=utf8_encode($getProducts->staticElectricDetails->origin); }else{
            $origin=null;


            }
            if(isset($getProducts->staticElectricDetails->FF_pro_rata)){ $FF_pro_rata=utf8_encode($getProducts->staticElectricDetails->FF_pro_rata);


            if($getProducts->staticElectricDetails->FF_pro_rata=="N"){

            $FF=false;
            }
            if($getProducts->staticElectricDetails->FF_pro_rata==""){

            $FF=null;
            }
            if($getProducts->staticElectricDetails->FF_pro_rata=="Y"){

            $FF=true;
            }
            }
            if(isset($getProducts->staticData->inv_period)){ $inv_period=utf8_encode($getProducts->staticData->inv_period); }
            if(isset($getProducts->staticData->origin)){ 
            $origin=utf8_encode($getProducts->staticData->origin); 
            }

            if(isset($getProducts->fixed_indexed)){ 
            $priceType=utf8_encode($getProducts->fixed_indexed); 

            if($priceType=='Fix'){

            $pt='fixed';
            }else{
            $pt='variable';

            }
            }


            if($getProducts->staticElectricDetails->green_percentage=="100%"){

            $gp=100;
            }else{

            $gp=0;
            }


            if($locale=='nl'){
            $pack_name=utf8_encode($getProducts->pack_name_NL);
            $pack_info=iconv("cp1252", "utf-8//TRANSLIT",$getProducts->info_NL);
            $pack_desc=utf8_encode($getProducts->tariff_description_NL);
            $pack_url=utf8_encode($getProducts->URL_NL);
            $pack_term=utf8_encode($getProducts->staticElectricDetails->terms_NL);

            }else{

            $pack_name=utf8_encode($getProducts->pack_name_FR);
            $pack_info=iconv("cp1252", "utf-8//TRANSLIT",$getProducts->info_FR);
            $pack_desc=utf8_encode($getProducts->tariff_description_FR);
            $pack_url=utf8_encode($getProducts->URL_FR);
            $pack_term=utf8_encode($getProducts->staticElectricDetails->terms_FR);

            }

            $product_popularity_count=SupplierPopularity::where('product_id',$getProducts->pack_id)->where('customer_group',$customerGroup)->where('comparison_type',$comparisonType)->get();
            $product_popularity_total=SupplierPopularity::where('customer_group',$customerGroup)->where('comparison_type',$comparisonType)->get();

            $total=0;
            foreach($product_popularity_total as $product_popularity_total){

            $total=$product_popularity_total->popularity+$total;
            }

            $pcount=0;
            foreach($product_popularity_count as $product_popularity_count){
            $pcount=$product_popularity_count->popularity;
            }
            if($total!=0 && $pcount!=0){
            $popularity=round(($pcount/$total)*100);
            }else{
            $popularity=0;   
            }
            if(isset($getProducts->staticElectricDetails->fixed_indiable)){ 
            $priceType=utf8_encode($getProducts->staticElectricDetails->fixed_indiable); 

            if($priceType=='Fix'){

            $pt='fixed';
            }else{
            $pt='variable';

            }
            }



            $products['product'] = [
            '_id' => $getProducts->id,
            'id' => utf8_encode($getProducts->pack_id),
            'name' => $pack_name,
            'description' => $pack_info,
            'tariff_description' => $pack_desc,
            'type' => utf8_encode('pack'),
            'contract_duration' => $getProducts->durationE,
            'service_level_payment' => utf8_encode(strtolower($getProducts->staticElectricDetails->service_level_payment)),
            'service_level_invoicing' => utf8_encode(strtolower($getProducts->staticElectricDetails->service_level_invoicing)),
            'service_level_contact' => utf8_encode(strtolower($getProducts->staticElectricDetails->service_level_contact)),
            'customer_condition' => utf8_encode($getProducts->staticElectricDetails->customer_condition),
            'origin' => utf8_encode($getProducts->staticElectricDetails->origin),
            'pricing_type' => $pt,
            'green_percentage' => $gp,
            'subscribe_url' => $pack_url,
            'terms_url' => $pack_term,
            'ff_pro_rata' => $FF,
            'inv_period' => $getProducts->staticElectricDetails->inv_period,
            'popularity_score' => $popularity
            ];


            }              



            // product-end

            // supplier-start

            if($comparisonType=='pack'){

            $supplier=$getProducts->staticGasDetails->supplier;
            }else{

            $supplier=$getProducts->supplier;
            }

            $supplier=Supplier::where('commercial_name',$supplier)->first();

            $products['supplier'] = [
            'id' => utf8_encode($supplier->supplier_id),
            'name' => utf8_encode($supplier->commercial_name),
            'logo' => utf8_encode('https://engineapi.wx.agency/uploads/supplier/'.$supplier->logo_large),
            'url' => utf8_encode('http://'),
            'origin' => utf8_encode($supplier->origin),
            'customer_rating' => utf8_encode($supplier->customer_rating),
            'greenpeace_rating' => utf8_encode($supplier->greenpeace_rating),
            'type' => utf8_encode($supplier->suppliertype)
            ];

            // supplier-end

            // price-breakdown-electricity               

            if($sumRegisters!="0" && $registerG=="0"){


            if($region=='VL'){
            $certificate=(($getProducts->gsc_vl+$getProducts->wkc_vl)*$sumRegisters)/100;
            }
            if($region=='WA'){
            $certificate=(($getProducts->gsc_wa)*$sumRegisters)/100;
            }
            if($region=='BR'){
            $certificate=(($getProducts->gsc_br)*$sumRegisters)/100;
            }



            if($request->meterType=='single' || $Metertype=='single'){
            $fixedFee=$getProducts->ff_single;
            }
            if($request->meterType=='double' ||$Metertype=='double'){

            $fixedFee=$getProducts->ff_day_night;
            }

            if($request->meterType=='single_excl_night' ||$Metertype=='single_excl_night'){
            $fixedFee=$getProducts->ff_single+$getProducts->ff_excl_night;
            }
            if($request->meterType=='double_excl_night' || $Metertype=='double_excl_night'){
            $fixedFee=$getProducts->ff_day_nightE+$getProducts->ff_excl_night;
            }

            $products['price']['breakdown']['electricity']['energy_cost'] = [
            'fixed_fee' =>$fixedFee= str_replace(',', '', number_format($fixedFee*100, 2, '.', ',')),
            'certificates' =>str_replace(',', '', number_format($certificate*100, 1, '.', ',')) ,
            'single' =>$single=str_replace(',', '', number_format($getProducts->price_single*$registerNormal, 2, '.', ',')) ,
            'day' =>$day=str_replace(',', '', number_format($getProducts->price_day*$registerDay, 2, '.', ',')) ,
            'night' =>$night=str_replace(',', '', number_format($getProducts->price_night*$registerNight, 2, '.', ',')) ,
            'excl_night' =>$excl_night=str_replace(',', '', number_format($getProducts->price_excl_night*$registerExclNight, 2, '.', ','))                                    
            ];
            $ff=str_replace(',', '', number_format($fixedFee*100, 1, '.', ','));
            $cert=str_replace(',', '', number_format($certificate*100, 1, '.', ','));


            $distribution=$result['electricity']['Netcostes']->reading_meter+($registerNormal*$result['electricity']['Netcostes']->price_single+$result['electricity']['Netcostes']->price_day*$registerDay+$result['electricity']['Netcostes']->price_night*$registerNight+$result['electricity']['Netcostes']->price_excl_night*$registerExclNight)/100+($result['electricity']['Netcostes']->prosumers*$capacityDecentrelisedProduction);  
            $transport=($sumRegisters*$result['electricity']['Netcostes']->transport)/100; 

            $products['price']['breakdown']['electricity']['distribution_and_transport'] = [
            'distribution' => $distribution*100,
            'transport' => str_replace(',', '', number_format($transport*100, 2, '.', ','))

            ];

            if($FirstResidence==true){
            $fixed_tax=$result['electricity']['tax']->fixed_tax_first_res;

            }elseif($FirstResidence==false){
            $fixed_tax=$result['electricity']['tax']->fixed_tax_not_first_res;

            }else{

            $fixed_tax=$result['electricity']['tax']->fixed_tax_first_res;
            }


            $tax=$fixed_tax+(($result['electricity']['tax']->energy_contribution+$result['electricity']['tax']->federal_contribution+$result['electricity']['tax']->connection_fee+$result['electricity']['tax']->contribution_public_services)*$sumRegisters)/100;

            $products['price']['breakdown']['electricity']['taxes'] = [
            'tax' =>str_replace(',', '', number_format($tax*100, 5, '.', ',')) 

            ];


            $total=(float)($fixedFee)+$certificate*100+($getProducts->price_single*$registerNormal)+($getProducts->price_day*$registerDay)+($getProducts->price_night*$registerNight)+($getProducts->price_excl_night*$registerExclNight);

            $energyCost=($total/$sumRegisters)*100;
            $products['price']['breakdown']['electricity']['unit_cost'] = [
            'energy_cost' =>str_replace(',', '', number_format((((($getProducts->price_single*$registerNormal+$getProducts->price_dayE*$registerDay+$getProducts->price_night*$registerNight+$getProducts->price_excl_night*$registerExclNight)+$fixedFee+$cert)/$sumRegisters)), 4, '.', ',')), //$energyCost,
            'total' =>$total/10000 ,

            ]; 
            }



            if($sumRegisters!="0" && $registerG!="0"){



            if($region=='VL'){
            $val=$getProducts->gsc_vlE+$getProducts->wkc_vlE;
            $certificate=(($val)*$sumRegisters)/100;
            }

            if($region=='WA'){
            $certificate=(($getProducts->gsc_waE)*$sumRegisters)/100;
            }
            if($region=='BR'){
            $certificate=(($getProducts->gsc_brE)*$sumRegisters)/100;
            }



            if($registerNormal!=0 && $registerDay==0 && $registerNight==0 && $registerExclNight==0){
            $fixedFee=$getProducts->ff_singleE;
            }
            if($registerNormal==0 && $registerDay!=0 && $registerNight!=0 && $registerExclNight==0){
            $fixedFee=$getProducts->ff_day_nightE;
            }
            if($registerNormal!=0 && $registerDay==0 && $registerNight==0 && $registerExclNight!=0){
            $fixedFee=$getProducts->ff_singleE+$getProducts->ff_excl_nightE;
            }
            if($registerNormal==0 && $registerDay!=0 && $registerNight!=0 && $registerExclNight!=0){
            $fixedFee=$getProducts->ff_day_nightE+$getProducts->ff_excl_nightE;
            }

            $fixedFeeE=$fixedFee*100;
            $certificateE=$certificate*100;
            $single=$getProducts->price_singleE*$registerNormal;
            $day=$getProducts->price_dayE*$registerDay;
            $night=$getProducts->price_nightE*$registerNight;
            $excl_night=$getProducts->price_excl_night*$registerExclNight;


            $products['price']['breakdown']['electricity']['energy_cost'] = [
            'fixed_fee' => str_replace(',', '', number_format($fixedFee*100, 3, '.', ',')) ,
            'certificates' =>  str_replace(',', '', number_format($certificate*100, 3, '.', ',')),
            'single' =>str_replace(',', '', number_format($getProducts->price_singleE*$registerNormal, 3, '.', ',')) ,
            'day' =>str_replace(',', '', number_format($getProducts->price_dayE*$registerDay, 3, '.', ',')) ,
            'night' =>str_replace(',', '', number_format($getProducts->price_nightE*$registerNight, 3, '.', ',')) ,
            'excl_night' =>str_replace(',', '', number_format($getProducts->price_excl_nightE*$registerExclNight, 3, '.', ','))                                    
            ];



            $distribution=$result['electricity']['Netcostes']->reading_meter+($registerNormal*$result['electricity']['Netcostes']->price_single+$result['electricity']['Netcostes']->price_day*$registerDay+$result['electricity']['Netcostes']->price_night*$registerNight+$result['electricity']['Netcostes']->price_excl_night*$registerExclNight)/100+($result['electricity']['Netcostes']->prosumers*$capacityDecentrelisedProduction); 
            $transport=($sumRegisters*$result['electricity']['Netcostes']->transport)/100; 
            $transportE=$transport*100;
            $products['price']['breakdown']['electricity']['distribution_and_transport'] = [
            'distribution' => $distributionE=$distribution*100,
            'transport' =>str_replace(',', '', number_format($transport*100, 3, '.', ','))            
            ];

            if($FirstResidence==true){
            $fixed_tax=$result['electricity']['tax']->fixed_tax_first_res;

            }elseif($FirstResidence==false){
            $fixed_tax=$result['electricity']['tax']->fixed_tax_not_first_res;

            }else{

            $fixed_tax=$result['electricity']['tax']->fixed_tax_first_res;
            }



            $tax=$fixed_tax+(($result['electricity']['tax']->energy_contribution+$result['electricity']['tax']->federal_contribution+$result['electricity']['tax']->connection_fee+$result['electricity']['tax']->contribution_public_services)*$sumRegisters)/100;
            $taxE=$tax*100;
            $products['price']['breakdown']['electricity']['taxes'] = [
            'tax' =>str_replace(',', '', number_format($tax*100, 5, '.', ',')) 

            ];


            $total=$fixedFee+$certificate+($getProducts->price_singleE*$registerNormal+$getProducts->price_dayE*$registerDay+$getProducts->price_nightE*$registerNight+$getProducts->price_excl_nightE*$registerExclNight)/100;
            $products['price']['breakdown']['electricity']['unit_cost'] = [
            'energy_cost' =>str_replace(',', '', number_format(($total/$sumRegisters), 5, '.', ',')) ,
            'total' =>$total ,

            ]; 

            $totalE=$distribution+$transport+$tax;

                        // gas
            $usg=($registerG*$getProducts->price_gasG);
            $products['price']['breakdown']['gas']['energy_cost'] = [
            'fixed_fee' =>str_replace(',', '', number_format(($getProducts->ffG)*100, 3, '.', ','))  ,
            'usage' => str_replace(',', '', number_format($usg, 3, '.', ','))                                                    
            ];

            $transport=($registerG*$result['gas']['Netcostes']->transport)/100;
            $distribution=($result['gas']['Netcostes']->fixed_term+$result['gas']['Netcostes']->reading_meter_yearly)+(($registerG*$result['gas']['Netcostes']->variable_term)/100);
            $transportG=$transport*100;  
            $products['price']['breakdown']['gas']['distribution_and_transport'] = [
            'distribution' =>$distributionG= $distribution*100,
            'transport' =>str_replace(',', '', number_format($transport*100, 3, '.', ','))             
            ];




            $tax=$result['gas']['tax']->contribution_public_services+(($result['gas']['tax']->energy_contribution+$result['gas']['tax']->federal_contribution+$result['gas']['tax']->contribution_protected_customers+$result['gas']['tax']->connection_fee)*$registerG)/100;
            $taxG=$tax*100;
            $products['price']['breakdown']['gas']['taxes'] = [
            'tax' =>str_replace(',', '', number_format($tax*100, 5, '.', ','))             
            ];


            $total=$getProducts->ffG+(($getProducts->price_gasG*$registerG)/100);
            $energyCost=$total/($registerG*100);
            $products['price']['breakdown']['gas']['unit_cost'] = [
            'energy_cost' =>str_replace(',', '', number_format(($energyCost*100), 4, '.', ','))  ,
            'total' =>$total ,            
            ]; 

            $totalG=$distribution+$transport+$tax;




            }

            // price-breakdown-gas
            if($sumRegisters=="0" && $registerG!="0"){
            $usgG=$registerG*$getProducts->price_gas;
            $products['price']['breakdown']['gas']['energy_cost'] = [
            'fixed_fee' => str_replace(',', '', number_format($getProducts->ff*100, 3, '.', ',')), 
            'usage' => str_replace(',', '', number_format(($registerG*$getProducts->price_gas), 3, '.', ','))                                                     
            ];

            $distribution=($result['gas']['Netcostes']->fixed_term+$result['gas']['Netcostes']->reading_meter_yearly)+(($registerG*$result['gas']['Netcostes']->variable_term))/100;  


            $products['price']['breakdown']['gas']['distribution_and_transport'] = [
            'distribution' => $distribution*100,
            'transport' =>str_replace(',', '', number_format(($registerG*$result['gas']['Netcostes']->transport), 3, '.', ',')) 

            ];

            $tax=$result['gas']['tax']->contribution_public_services+(($result['gas']['tax']->energy_contribution+$result['gas']['tax']->federal_contribution+$result['gas']['tax']->contribution_protected_customers+$result['gas']['tax']->connection_fee)*$registerG)/100;
            $products['price']['breakdown']['gas']['taxes'] = [
            'tax' =>str_replace(',', '', number_format($tax*100, 3, '.', ',')) 

            ];
            $total=$getProducts->ff+($getProducts->price_gas*($registerG/100));
            $products['price']['breakdown']['gas']['unit_cost'] = [
            'energy_cost' => str_replace(',', '', number_format((($total/$registerG)*100), 3, '.', ',')),
            'total' =>$total ,

            ]; 

            } 


            // price-breakdown-gas


            // price-breakdown-electricity



            // price-discount-start
            $discountCoupenCodeE=null;
            $discountCoupenCodeG=null;
            $discountCoupenCodeP=null;

            $servicelevel=false;
            $promo=false;
            $loyalty=false;

            $discountcodeE=null;
            $discountcodeG=null;
            $discountcodeP=null;
            $promoD=0; $promoED=0; $promoGD=0; $promoAD=0; $slD=0; $loyaltyD=0; 

            if($sumRegisters!=0 && $registerG==0){

            if($currentSupplierE){

            $getdiscount=Discount::where('productId',$getProducts->product_id)                
            ->whereDate('startdate','<=',$currentDate)->whereDate('enddate','>=',$currentDate)
            ->where('volume_lower','<=',$sumRegisters)
            ->where('volume_upper','>=',$sumRegisters)                           
            ->where('supplier','!=',$currentSupplierE)
            ->where('comparisonType','!=',"Packs only")
            ->where('applicableForExistingCustomers','FALSE')
            ->orWhere('applicableForExistingCustomers','TRUE')
            ->where('productId',$getProducts->product_id)
            ->groupBy('discountId')
            ->get();


            }else{

            $getdiscount=Discount::where('productId',$getProducts->product_id)
            ->whereDate('startdate','<=',$currentDate)->whereDate('enddate','>=',$currentDate)
            ->where('volume_lower','<=',$sumRegisters)
            ->where('volume_upper','>=',$sumRegisters)
            ->where('comparisonType','!=',"Packs only")
            ->where(function($q) {
            $q->where('fuelType','electricity')
            ->orWhere('fuelType', 'all');
            })
            //->where('fuelType','=','electricity')
            //->orWhere('fuelType','=','all')
            //->where('applicableForExistingCustomers','FALSE')
            ->groupBy('discountId')
            ->get();

            }









            unset($discountArray); $discountArray=array(); 
            $totaldE=0;                   
            foreach($getdiscount as $discount){

            if($discount->fuelType=='electricity'){
            $calFixed=$discount->value;
            $CalcUsageEurocent=$discount->value;
            $CalcUsagePct=(($getProducts->price_single*$registerNormal)+($getProducts->price_day*$registerDay)+($getProducts->price_night*$registerNight)+($getProducts->price_excl_night*$registerExclNight));
            }
            // if($discount->fuelType=='gas'){

            //     $calFixed=$discount->value;
            //     $CalcUsageEurocent=($registerG*$discount->value)/100;
            //     $CalcUsagePct=($getProducts->price_gas*$registerG)*$discount->value;
            // }


            $totCunsum=$sumRegisters;
            if($discount->unit=='euro'){

            if($discount->minimumSupplyCondition==0){
            $disc =$discount->value;
            }else{

            $disc=0; 
            }
            }
            if($discount->unit=='eurocent'){

            if($discount->minimumSupplyCondition==0){
            $disc =($discount->value*$totCunsum)/100;
            }else{

            $disc=0; 
            }
            }
            if($discount->unit=='pct'){
            if($discount->minimumSupplyCondition==0){
            $disc =(($discount->value)*$CalcUsagePct)/100;
            }else{

            $disc=0;

            }
            }

            if($locale=='nl'){
            // $D_name=utf8_encode($discount->nameNl);
            $D_name=iconv("cp1252", "utf-8//TRANSLIT",$discount->nameNl);
            // $D_info=utf8_encode($discount->descriptionNl);
            $D_info=iconv("cp1252", "utf-8//TRANSLIT",$discount->descriptionNl);


            }else{

            // $D_name=utf8_encode($discount->nameFR);
            $D_name=iconv("cp1252", "utf-8//TRANSLIT",$discount->nameFr);
            // $D_info=utf8_encode($discount->descriptionFR);
            $D_info=iconv("cp1252", "utf-8//TRANSLIT",$discount->descriptionFr);

            }

            $totaldE=$totaldE+$disc;

            if($discount->discountcodeE){
            $discountCoupenCodeE = $discount->discountcodeE;
            }
            if($discount->discountcodeP){
            $discountCoupenCodeP = $discount->discountcodeP;
            }

            $dataArray = [
            '_id'=>$discount->id,
            'id' => $discount->discountId,
            'name' => $D_name,
            'description' => $D_info,
            'type' => utf8_encode($discount->discountType),
            'included_in_calculation' => true,
            'amount'=>$disc,
            'validity_period'=>[
            'start' => $discount->startdate,
            'end' => $discount->enddate
            ],
            'parameters'=>[
            'volume_lower' => $discount->volume_lower,
            'volume_upper' => $discount->volume_upper,
            'fuel_type' => utf8_encode($discount->fuelType),
            'applicability' => utf8_encode($discount->applicability),
            'applicable_for_existing_customers' => utf8_encode($discount->applicableForExistingCustomers),
            'value_type' => utf8_encode($discount->valueType),
            'value' => $discount->value,
            'unit' => $discount->unit,
            'channel' => utf8_encode($discount->channel),
            'application_v_contract_duration' => utf8_encode($discount->applicationVContractDuration),
            'minimum_supply_condition' => utf8_encode($discount->minimumSupplyCondition),
            'duration' => $discount->duration,
            'service_level_payment' => utf8_encode(strtolower($discount->serviceLevelPayment)),
            'service_level_invoicing' => utf8_encode(strtolower($discount->serviceLevelInvoicing)),
            'service_level_contact' => utf8_encode(strtolower($discount->serviceLevelContact)),
            'discount_type' => utf8_encode(strtolower($discount->discountType))
            ]
            ];
            $discountArray[]=$dataArray;

            $serviceLevelPayment=$discount->serviceLevelPayment;
            $serviceLevelInvoicing=$discount->serviceLevelInvoicing;
            $serviceLevelContact=$discount->serviceLevelContact;
            $discountType=$discount->discountType;
            $minimumSupplyCondition=$discount->minimumSupplyCondition;


            if($discount->discountcodeE){
            $discountcodeE=$discount->discountcodeE;
            }

            if($discount->discountcodeG){
            $discountcodeG=$discount->discountcodeG;
            }

            if($discount->discountcodeP){
            $discountcodeP=$discount->discountcodeP;
            }




            if($discountType=='servicelevel' && $minimumSupplyCondition=0){
            $servicelevel=='domi';
            }

            if($discountType=='promo'){
            $promo='promo';
            }

            if($discountType=='loyalty' && $minimumSupplyCondition=0){
            $loyalty='email';
            }



            }

            $products['price']['breakdown']['discounts']=$discountArray;


            }



            if($sumRegisters==0 && $registerG!=0){

            if($currentSupplierG){
            $getdiscount=Discount::where('productId',$getProducts->product_id)
            ->whereDate('startdate','<=',$currentDate)->whereDate('enddate','>=',$currentDate)                
            ->where('volume_lower','<=',$registerG)
            ->where('volume_upper','>=',$registerG)               
            ->where('supplier','!=',$currentSupplierG)
            ->where('comparisonType','!=',"Packs only")
            ->where('applicableForExistingCustomers','FALSE')
            ->orWhere('applicableForExistingCustomers','TRUE')
            ->where('productId',$getProducts->product_id)
            ->groupBy('discountId')
            ->get();
            }else{

            $getdiscount=Discount::where('productId',$getProducts->product_id)
            ->whereDate('startdate','<=',$currentDate)->whereDate('enddate','>=',$currentDate)
            ->where('volume_lower','<=',$registerG)
            ->where('volume_upper','>=',$registerG)
            ->where('comparisonType','!=',"Packs only")
            ->where(function($q) {
            $q->where('fuelType','gas')
            ->orWhere('fuelType', 'all');
            })
            //->where('fuelType','=','electricity')
            //->orWhere('fuelType','=','all')
            //->where('applicableForExistingCustomers','FALSE')
            ->groupBy('discountId')
            ->get();


            }


            $totCunsum=$registerG;





            unset($discountgArray); $discountgArray=array();    
            $totaldG=0; 

            foreach($getdiscount as $discount){


            $calFixed=$discount->value;
            $CalcUsageEurocent=$discount->value;
            $CalcUsagePct=$getProducts->price_gas*$registerG;

            if($discount->unit=='euro'){

            if($discount->minimumSupplyCondition==0){
            $disc =$discount->value;
            }else{

            $disc=0; 
            }
            }
            if($discount->unit=='eurocent'){

            if($discount->minimumSupplyCondition==0){
            $disc =($discount->value*$totCunsum)/100;
            }else{

            $disc=0; 
            }
            }
            if($discount->unit=='pct'){
            if($discount->minimumSupplyCondition==0){
            $disc =(($discount->value)*$CalcUsagePct)/100;
            }else{

            $disc=0;

            }
            }

            if($locale=='nl'){
            // $D_name=utf8_encode($discount->nameNl);
            $D_name=iconv("cp1252", "utf-8//TRANSLIT",$discount->nameNl);
            // $D_info=utf8_encode($discount->descriptionNl);
            $D_info=iconv("cp1252", "utf-8//TRANSLIT",$discount->descriptionNl);


            }else{

            // $D_name=utf8_encode($discount->nameFR);
            $D_name=iconv("cp1252", "utf-8//TRANSLIT",$discount->nameFr);
            // $D_info=utf8_encode($discount->descriptionFR);
            $D_info=iconv("cp1252", "utf-8//TRANSLIT",$discount->descriptionFr);

            }
            if($discount->discountcodeG){
            $discountCoupenCodeG = $discount->discountcodeG;
            }
            if($discount->discountcodeP){
            $discountCoupenCodeP = $discount->discountcodeP;
            }


            $totaldG=$totaldG+$disc;
            $datagArray = [
            '_id'=>$discount->id,
            'id' => $discount->discountId,
            'name' => $D_name,
            'description' => $D_info,
            'type' => utf8_encode($discount->discountType),
            'included_in_calculation' => true,
            'amount'=>$disc,
            'validity_period'=>[
            'start' => $discount->startdate,
            'end' => $discount->enddate
            ],
            'parameters'=>[
            'volume_lower' => $discount->volume_lower,
            'volume_upper' => $discount->volume_upper,
            'fuel_type' => utf8_encode($discount->fuelType),
            'applicability' => utf8_encode($discount->applicability),
            'applicable_for_existing_customers' => utf8_encode($discount->applicableForExistingCustomers),
            'value_type' => utf8_encode($discount->valueType),
            'value' => $discount->value,
            'unit' => utf8_encode($discount->unit),
            'channel' => utf8_encode($discount->channel),
            'application_v_contract_duration' => $discount->applicationVContractDuration,
            'minimum_supply_condition' => utf8_encode($discount->minimumSupplyCondition),
            'duration' => $discount->duration,
            'service_level_payment' => strtolower($discount->serviceLevelPayment),
            'service_level_invoicing' => strtolower($discount->serviceLevelInvoicing),
            'service_level_contact' => strtolower($discount->serviceLevelContact),
            'discount_type' => utf8_encode(strtolower($discount->discountType))
            ]
            ];
            $discountgArray[]=$datagArray;

            $serviceLevelPayment=$discount->serviceLevelPayment;
            $serviceLevelInvoicing=$discount->serviceLevelInvoicing;
            $serviceLevelContact=$discount->serviceLevelContact;
            $discountType=$discount->discountType;
            $minimumSupplyCondition=$discount->minimumSupplyCondition;


            if($discount->discountcodeE){
            $discountcodeE=$discount->discountcodeE;
            }

            if($discount->discountcodeG){
            $discountcodeG=$discount->discountcodeG;
            }

            if($discount->discountcodeP){
            $discountcodeP=$discount->discountcodeP;
            }


            if($discountType=='servicelevel' && $minimumSupplyCondition=0){
            $servicelevel='domi';
            }

            if($discountType=='promo'){
            $promo='promo';
            }

            if($discountType=='loyalty' && $minimumSupplyCondition=0){
            $loyalty='email';
            }




            }
            $products['price']['breakdown']['discounts']=$discountgArray;

            }


            if($sumRegisters!=0 && $registerG!=0){


            $currentSupplierG="";
            if($currentSupplierE){
            $getdiscountE=Discount::where('productId',$getProducts->product_idE)                
            ->whereDate('startdate','<=',$currentDate)->whereDate('enddate','>=',$currentDate)
            ->where('volume_lower','<=',$sumRegisters)
            ->where('volume_upper','>=',$sumRegisters)                           
            ->where('supplier','!=',$currentSupplierE)
            ->where('comparisonType','!=',"Single Fuel + E and G separately")
            ->where('applicableForExistingCustomers','FALSE')
            ->orWhere('applicableForExistingCustomers','TRUE')
            ->where('productId',$getProducts->product_idE)
            ->where('comparisonType','!=',"Single Fuel + E and G separately")
            ->groupBy('discountId')
            ->get();
            }else{
            $getdiscountE=Discount::
            where('productId',$getProducts->product_idE)    
            //where('productId','LUM-RES-COMFY-1-E')
            ->whereDate('startdate','<=',$currentDate)->whereDate('enddate','>=',$currentDate)
            ->where('volume_lower','<=',$sumRegisters)
            ->where('volume_upper','>=',$sumRegisters)
            ->where('comparisonType','!=',"Single Fuel + E and G separately")
            //->where('applicableForExistingCustomers','FALSE')
            ->groupBy('discountId')
            ->get();

            }

            if($currentSupplierG){
            $getdiscountG=Discount::where('productId',$getProducts->product_idG)
            ->whereDate('startdate','<=',$currentDate)->whereDate('enddate','>=',$currentDate)                
            ->where('volume_lower','<=',$registerG)
            ->where('volume_upper','>=',$registerG)               
            ->where('supplier','!=',$currentSupplierG)
            ->where('applicableForExistingCustomers','FALSE')
            ->orWhere('applicableForExistingCustomers','TRUE')
            ->where('productId',$getProducts->product_idG)
            ->where('comparisonType','!=',"Single Fuel + E and G separately")
            ->groupBy('discountId')
            ->get();
            }else{

            $getdiscountG=Discount::where('applicableForExistingCustomers',false)
            ->where('productId',$getProducts->product_idG)
            // ->where('productId','LUM-RES-COMFY-1-G')
            ->whereDate('startdate','<=',$currentDate)->whereDate('enddate','>=',$currentDate)                
            ->where('volume_lower','<=',$registerG)
            ->where('volume_upper','>=',$registerG)  
            ->where('comparisonType','!=',"Single Fuel + E and G separately")
            //->where('applicableForExistingCustomers','FALSE')
            ->groupBy('discountId')
            ->get();


            }








            $arrayEG = $getdiscountE->merge($getdiscountG);
            $a=$arrayEG->groupBy('discountId');


            unset($discountgArray); $discountgArray=array();  

            $aa=""; 

            $getDisc=[];






            foreach($a as $discountS){

            $totalE=0;
            foreach($discountS as $discount){

            if($aa!=$discount->discountId){




            $aa=$discount->discountId;
            if($discount->fuelType=='electricity'){
            $calFixed=$discount->value;
            $CalcUsageEurocent=$discount->value;
            $totCunsum=$sumRegisters;
            $CalcUsagePct=(($getProducts->price_singleE*$registerNormal)+($getProducts->price_dayE*$registerDay)+($getProducts->price_nightE*$registerNight)+($getProducts->price_excl_nightE*$registerExclNight));
            }

            if($discount->fuelType=='gas'){
            $calFixed=$discount->value;
            $CalcUsageEurocent=$discount->value;
            $CalcUsagePct=$getProducts->price_gasG*$registerG;

            $totCunsum=$registerG;
            // $CalcUsagePct=(($getProducts->price_singleE*$registerNormal)+($getProducts->price_dayE*$registerDay)+($getProducts->price_nightE*$registerNight)+($getProducts->price_excl_nightE*$registerExclNight))*$discount->value;
            }
            if($discount->fuelType=='all'){
            $calFixed=$discount->value;
            $CalcUsageEurocent=$discount->value;
            $CalcUsagePct=(($getProducts->price_singleE*$registerNormal)+($getProducts->price_dayE*$registerDay)+($getProducts->price_nightE*$registerNight)+($getProducts->price_excl_nightE*$registerExclNight));
            }





            if($discount->unit=='euro'){

            if($discount->minimumSupplyCondition==0){
            $disc =$calFixed;
            }else{

            $disc=0; 
            }
            }
            if($discount->unit=='eurocent'){

            if($discount->minimumSupplyCondition==0){
            $disc =($discount->value*$totCunsum)/100;
            }else{

            $disc=0; 
            }
            }
            if($discount->unit=='pct'){
            if($discount->minimumSupplyCondition==0){
            $disc =(($discount->value)*$CalcUsagePct)/100;
            }else{

            $disc=0;

            }
            }


            $totalE=$totalE+$disc;
            array_push($getDisc,$disc);

            if($locale=='nl'){
            $D_name=iconv("cp1252", "utf-8//TRANSLIT", $discount->nameNl);
            //dd(iconv("cp1252", "utf-8//TRANSLIT", $discount->nameNl));
            // $D_name= utf8_encode($discount->nameNl);
            // $D_info=utf8_encode($discount->descriptionNl);
            $D_info=iconv("cp1252", "utf-8//TRANSLIT", $discount->descriptionNl);
            }else{
            $D_name=iconv("cp1252", "utf-8//TRANSLIT", $discount->nameFr);
            // $D_name=$discount->nameFR;
            // $D_info=utf8_encode($discount->descriptionFR);
            $D_info=iconv("cp1252", "utf-8//TRANSLIT", $discount->descriptionFr);
            }





            if($discount->discountType=='servicelevel' && $discount->minimumSupplyCondition==0){
            $servicelevel='domi';
            }

            if($discount->discountType=='promo'){
            $promo='promo';
            }

            if($discount->discountType=='loyalty' && $discount->minimumSupplyCondition==0){
            $loyalty='email';
            }


            if($discount->discountcodeE){
            $discountCoupenCodeE = $discount->discountcodeE;
            }
            if($discount->discountcodeG){
            $discountCoupenCodeG = $discount->discountcodeG;
            }
            if($discount->discountcodeP){
            $discountCoupenCodeP = $discount->discountcodeP;
            }


            $datagArray = [
            '_id'=>$discount->id,
            'id' => $discount->discountId,
            'name' =>  $D_name,
            'description' =>  $D_info,
            'type' => utf8_encode($discount->discountType),
            'included_in_calculation' => true,
            'amount'=>$disc,
            'validity_period'=>[
            'start' => $discount->startdate,
            'end' => $discount->enddate
            ],
            'parameters'=>[
            'volume_lower' => $discount->volume_lower,
            'volume_upper' => $discount->volume_upper,
            'fuel_type' => utf8_encode($discount->fuelType),
            'applicability' => utf8_encode($discount->applicability),
            'applicable_for_existing_customers' => strtolower($discount->applicableForExistingCustomers),
            'value_type' => utf8_encode($discount->valueType),
            'value' => $discount->value,
            'unit' => utf8_encode($discount->unit),
            'channel' => utf8_encode($discount->channel),
            'application_v_contract_duration' => utf8_encode($discount->applicationVContractDuration),
            'minimum_supply_condition' => $discount->minimumSupplyCondition,
            'duration' => $discount->duration,
            'service_level_payment' => utf8_encode(strtolower($discount->serviceLevelPayment)),
            'service_level_invoicing' => utf8_encode(strtolower($discount->serviceLevelInvoicing)),
            'service_level_contact' => utf8_encode(strtolower($discount->serviceLevelContact)),
            'discount_type' => utf8_encode(strtolower($discount->discountType))

            ]
            ];
            $discountgArray[]=$datagArray;

            $serviceLevelPayment=$discount->serviceLevelPayment;
            $serviceLevelInvoicing=$discount->serviceLevelInvoicing;
            $serviceLevelContact=$discount->serviceLevelContact;
            $discountType=$discount->discountType;
            $minimumSupplyCondition=$discount->minimumSupplyCondition;

            if($discount->discountcodeE){
            $discountcodeE=$discount->discountcodeE;
            }

            if($discount->discountcodeG){
            $discountcodeG=$discount->discountcodeG;
            }

            if($discount->discountcodeP){
            $discountcodeP=$discount->discountcodeP;
            }




            }else{

            $totalE=$totalE+0;
            }
            }


            }



            $totalDiscountAmount=array_sum($getDisc);  



            $products['price']['breakdown']['discounts']=$discountgArray;




            }



            $replace = ['(FIRSTNAME)', '(LASTNAME)', '(POSTCODE)', '(MONTHLYGAS)', '(MONTHLYELEK)', '(USAGE_G)', '(USAGE_E)', '(EMAIL)', '(ElecMeterType)', '(USAGE_E_NIGHT)','(USAGE_E_EXCL_NIGHT)','(DISCOUNTCODE_E)','(DISCOUNTCODE_G)','(DISCOUNTCODE_P)','(POSTALCODE)','((DISCOUNTCODE_G))'];
            $info = [
            'FIRSTNAME' => "",
            'LASTNAME' => "",
            'POSTCODE' => $postalCode,
            'MONTHLYGAS' => "",
            'MONTHLYELEK' => "",
            'USAGE_G' => $registerG,
            'USAGE_E' => $sumRegisters,
            'EMAIL' => "",
            'ElecMeterType' => $Metertype,
            'USAGE_E_NIGHT' => "",
            'USAGE_E_EXCL_NIGHT' => "",
            'DISCOUNTCODE_E' => $discountCoupenCodeE,
            'DISCOUNTCODE_G' => $discountCoupenCodeG,
            'DISCOUNTCODE_P'=> $discountCoupenCodeP,
            'POSTALCODE'=> $postalCode
            ];
            $products['supplier']['signup_url'] = str_replace($replace, $info, $pack_url);


            // price-discount-end
            if(count($products['price']['breakdown']['discounts'])>0){
            $promo_discount = [
            'promo' => $promo,
            'discount_serviceLevelInvoicing' =>$loyalty ,
            'discount_serviceLevelPayment' => $servicelevel,
            'discountcodeE'=>$discountcodeE,
            'discountcodeG'=>$discountcodeG,
            'discountcodeP'=>$discountcodeP
            ];
            $products['parameters']['values']['promo_discount']=$promo_discount;
            }else{
            $promo_discount = [
            'promo' => false,
            'discount_serviceLevelInvoicing' => false,
            'discount_serviceLevelPayment' => false,
            'discountcodeE'=>null,
            'discountcodeG'=>null,
            'discountcodeP'=>null
            ];
            $products['parameters']['values']['promo_discount']=$promo_discount;
            }




            // price-validity_period

            if($sumRegisters!=0 && $registerG==0){
            $products['price']['validity_period'] = [
            'start' =>  $getProducts->valid_from,
            'end' => $getProducts->valid_till
            ];
            $exclPrice=($getProducts->price_single*$registerNormal)+($getProducts->price_day*$registerDay)+($getProducts->price_night*$registerNight)+($getProducts->price_excl_night*$registerExclNight);
            // $inclPromo=$exclPromo-($totalDiscountE+$totalDiscountG);

            $distributionE=$distribution*100;
            $transportE=$transport*100;
            $fixedFee=$fixedFee;
            $certificateE=$certificate*100;
            $taxE=$tax*100;

            $excl_promo=$exclPrice+$distributionE+$transportE+$fixedFee+$certificateE+$taxE;
            $incl_promo=$excl_promo-($totaldE*100);

            $products['price']['totals']['month'] = [
            'incl_promo' => $incl_promo/12,
            'excl_promo' => $excl_promo/12
            ];
            $products['price']['totals']['year'] = [
            'incl_promo' => $incl_promo,
            'excl_promo' => $excl_promo
            ];
            }

            if($registerG!=0 && $sumRegisters==0){
            $products['price']['validity_period'] = [
            'start' =>  $getProducts->valid_from,
            'end' => $getProducts->valid_till
            ];
            $fixedFee=$getProducts->ff*100;
            $distributionG=$distribution*100;
            $transportG=$registerG*$result['gas']['Netcostes']->transport;
            $taxG=$tax*100;

            // $usg=$getProducts->price_gas*$registerG;

            // $inclPromo=$exclPromo-($totalDiscountE+$totalDiscountG);
            $excl_promo=$fixedFee+$usgG+$distributionG+$transportG+$taxG;
            $incl_promo=$excl_promo-($totaldG*100);
            $products['price']['totals']['month'] = [
            'incl_promo' => $incl_promo/12,
            'excl_promo' => $excl_promo/12
            ];
            $products['price']['totals']['year'] = [
            'incl_promo' => $incl_promo,
            'excl_promo' => $excl_promo
            ];
            }

            if($registerG!=0 && $sumRegisters!=0){
            $products['price']['validity_period'] = [
            'start' =>  $getProducts->valid_fromE,
            'end' => $getProducts->valid_tillE
            ];

            if($registerNormal!=0 && $registerDay==0 && $registerNight==0 && $registerExclNight==0){
            $fixedFeeE=$getProducts->ff_singleE;
            }
            if($registerNormal==0 && $registerDay!=0 && $registerNight!=0 && $registerExclNight==0){
            $fixedFeeE=$getProducts->ff_day_nightE;
            }
            if($registerNormal!=0 && $registerDay==0 && $registerNight==0 && $registerExclNight!=0){
            $fixedFeeE=$getProducts->ff_singleE+$getProducts->ff_excl_nightE;
            }
            if($registerNormal==0 && $registerDay!=0 && $registerNight!=0 && $registerExclNight!=0){
            $fixedFeeE=$getProducts->ff_day_nightE+$getProducts->ff_excl_nightE;
            }

            $fixedFeeG=$getProducts->ffG;


            $fixedFee=$fixedFee+$getProducts->ffG;
            $exclPromoG=$fixedFee+(($getProducts->price_gasE*$registerG)/1000);

            $exclPromoE=$fixedFee+(($getProducts->price_gas*$registerG)/1000)+($getProducts->price_singleE*$registerNormal)+($getProducts->price_dayE*$registerDay)+($getProducts->price_nightE*$registerNight)+($getProducts->price_excl_nightE*$registerExclNight)+$distribution+$transport+$tax;


            $inclPromo=0;
            $exclPromo=0;

            $excl_promo=$fixedFeeE*100+$certificateE+$single+$day+$night+$excl_night+$distributionE+$transportE+$taxE+($getProducts->ffG*100)+$usg+$distributionG+$transportG+$taxG;

            $incl_promo=$excl_promo-($totalDiscountAmount*100);

            $products['price']['totals']['month'] = [
            'incl_promo' => $incl_promo/12,
            'excl_promo' => $excl_promo/12,
            'promo'=>$totalDiscountAmount
            ];

            // $fixedFeeE=$fixedFee*100;
            // $certificateE=$certificate*100;
            // $single=$getProducts->price_singleE*$registerNormal;
            // $day=$getProducts->price_dayE*$registerDay;
            // $night=$getProducts->price_nightE*$registerNight;
            // $excl_night=$getProducts->price_excl_night*$registerExclNight;



            $products['price']['totals']['year'] = [
            'incl_promo' => $incl_promo,
            'excl_promo' => $excl_promo

            ];




            }



            // price-validity_period

            // braekdown    





            $products['price']['breakdown']['value_added_services'] = [];

            array_push($pro,$products);


            }

            //  $data=[
            //     'uuid'=>$products['parameters']['uuid'],
            //              'locale'=>$products['parameters']['values']['locale'],
            //              'firstname'=>null,
            //              'lastname'=>null,
            //              'residential_professional'=>$products['parameters']['values']['customer_group'],
            //              'postalcode'=>$products['parameters']['values']['postal_code'],
            //              'region'=>$products['parameters']['values']['region'],
            //              'familysize'=>$products['parameters']['values']['residents'],
            //              'comparison_type'=>$products['parameters']['values']['comparison_type'],
            //              'meter_type'=>$request->meterType,
            //              'pack_id'=>null,
            //              'eid'=>null,
            //              'gid'=>null,
            //              'total_cost'=>null,
            //              'single'=>$products['parameters']['values']['usage_single'],
            //              'day'=>$products['parameters']['values']['usage_day'],
            //              'night'=>$products['parameters']['values']['usage_night'],
            //              'excl_night'=>$products['parameters']['values']['usage_excl_night'],
            //              'current_electric_supplier'=>null,
            //              'gas_consumption'=>$products['parameters']['values']['usage_gas'],
            //              'current_gas_supplier'=>null,
            //              'email'=>$email,
            //              'data_from'=>$request->req_from
            //     ];


            $record = [
            'field' => 'value',
            // ...
            ];
            // ... Calculated additions to $record ...
            $records[] = $record;

            if ($products['parameters']['values']['locale'] == 'nl') {
            $contactAffiliate = 'tariefchecker';
            } else {
            $contactAffiliate = 'veriftarif';
            }












            return response()->json([
            'products' => $pro,200

            ]);


            //**json output -end */
            }


            }
