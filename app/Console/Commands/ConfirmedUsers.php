<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Models\ConfirmedUser;

class ConfirmedUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'confirm:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
         try {
            $this->alert('Syncing Confirmed user data : ' . Carbon::now());
            $this->_confirm_sync();
            $this->comment('Completed at : ' . Carbon::now());
        } catch (Exception $ex) {
            return $ex->getMessage();
        }
    }


    public function _confirm_sync(){



        $results=ConfirmedUser::where('sync',0)->get();
               
 
            foreach($results as $result){


                $req=gettype($result['request']);
                $req=json_decode($result['request'] ,true);
               
                $this->addActivecampaign($req);
                $this->addDeal($req);

                ConfirmedUser::where('con_user_id',$result['con_user_id'])->update([

                    'sync'=>1
                ]);

            }

        //return $result;
        


    $this->info('All Confirmed data synced');


    }


     public function addActivecampaign($request)
    {
        $meter_type = "";
        $comporator_data = $request;
       
       
      

        $url = 'https://tariefchecker.api-us1.com';
        $params = array(
            'api_key'      => '3f69314bf2d12325004faa27a223f3096a8ab91f4a82aab05431f29c693d9ac63abf2684',
            'api_action'   => 'contact_sync',
            'api_output'   => 'serialize',
        );
        if ($comporator_data['meter_type'] == 'single') {
            $meter_type = 'Single Meter';
        }
        if ($comporator_data['meter_type'] == 'double') {
            $meter_type = 'Double Meter';
        }
        if ($comporator_data['meter_type'] == 'single_excl_night') {
            $meter_type = 'Single + Excl Night Meter';
        }
        if ($comporator_data['meter_type'] == 'double_excl_night') {
            $meter_type = 'Double + Excl Night Meter';
        }

        if(isset($request['from'])){

            $form = $request['from'];

        }else{

        if ($request['locale'] == 'fr') {

            $form = 'veriftarif';
        } else {
            $form = 'tariefchecker';
        }

        }

        if($comporator_data['comp_type']=='pack'){

            $compType="Pack";
        }elseif($comporator_data['comp_type']=='electricity'){
            $compType="SF E";

        }elseif($comporator_data['comp_type']=='gas'){
            $compType="SF G";


        }else{
            $compType="Separate Dual Fuel";

        }

        if($comporator_data['usage_excl_night']==null){
            $usage_exc_night=0;
        }else{
            $usage_exc_night=$comporator_data['usage_excl_night'];

        }

        if($comporator_data['price_type']=='Ind'){

            $price_type="Indexed";
        }else{

            $price_type="Fixed";
        }
        $price_type=$comporator_data['price_type'];

         if($comporator_data['decentralise_production']==null){

                $decentralise_production='false';
            }else{

                $decentralise_production='true';

            }

        
         if(isset($comporator_data['curr_supplierE'])){

            $curr_supplierE= $comporator_data['curr_supplierE'];
         }else{

            $curr_supplierE=null;
         }

         if(isset($comporator_data['curr_supplierG'])){

           $curr_supplierG= $comporator_data['curr_supplierG'];
         }else{

            $curr_supplierG=null;
         }
          if(isset($comporator_data['url'])){

           $comporator_data['url']= $comporator_data['url'];
         }else{

            $comporator_data['url']=null;
         }

        // here we define the data we are posting in order to perform an update
        $post = array(
            'email'              => $comporator_data['email'],
            "first_name"         => $comporator_data['firstname'],
            "last_name"          => $comporator_data['lastname'],
            "tags"               => $form . ", vgl-api, WFL-start",
            "p[1]" => 1,
            "field[%COMPARISON_TYPE%,0]"    => $comporator_data['type'],
            "phone"     =>  "",
            "field[%CONTACT_AFFILIATE%,0]" => $form,
            "field[%CONTACT_MEDIUM%,0]" => $form,
            "field[%CONTACT_LANGUAGE%,0]" => $comporator_data['locale'],
            "field[%CONTACT_TYPE%,0]"  =>  "customer",
            "field[%COMPARISON_UUID%,0]" => $comporator_data['uuid'],
            "field[%CONTACT_SOURCE%,0]" => "comporator App",
            "field[%CONTACT_CAMPAIGN%,0]" => "Campaign",
            "field[%CONSUMPTION_REGISTER_TYPE_E%,0]" => $meter_type,
            "field[%CONSUMPTION_E_MONO%,0]" => $comporator_data['usage_single'],
            "field[%CONSUMPTION_E_DAY%,0]" => $comporator_data['usage_day'],
            "field[%CONSUMPTION_E_NIGHT%,0]" => $comporator_data['usage_night'],
            "field[%CONSUMPTION_E_EXCL_NIGHT%,0]" => $usage_exc_night,
            "field[%CONSUMPTION_G%,0]" => $comporator_data['usage_gas'],
            "field[%COMPARISON_URL%,0]" => $comporator_data['url'],
            "field[%COMPARISON_TYPE%,0]" => $comporator_data['type'],
            "field[%COMPARISON_FUELS%,0]" => $compType,
            "field[%COMPARISON_CURRENT_SUPPLIER_E%,0]" => $curr_supplierE,
            "field[%COMPARISON_CURRENT_SUPPLIER_G%,0]" => $curr_supplierG,
            "field[3,0]" => $comporator_data['postcode'],
            "field[%ADDRESS_REGION%,0]" => $comporator_data['region'],
            "field[%ADDRESS_CITY%,0]"   =>  "",
            "field[%COMPARISON_ENERGY_COST_E%,0]" => $comporator_data['energycostE'],
            "field[%COMPARISON_OTHER_COSTS_E%,0]" => "0",
            "field[%COMPARISON_PROMO_AMOUNT_E%,0]" => $comporator_data['promoAmountE'],
            "field[%COMPARISON_ENERGY_COSTS_G%,0]" => $comporator_data['energycostG'],
            "field[%COMPARISON_OTHER_COSTS_G%,0]" => "0",
            "field[%COMPARISON_PROMO_AMOUNT_G%,0]" => $comporator_data['promoAmountG'],
            "field[%COMPARISON_SAVINGS%,0]" => $comporator_data['savings'],
            "field[%CONTRACT_SUPPLIER%,0]" => $comporator_data['supplier'],
            "field[%CONTRACT_SUPPLIER_ID%,0]" => $comporator_data['supplierID'],
            "field[%CONTRACT_TARIFF%,0]" => $comporator_data['tariff'],
            "field[%CONTRACT_TARIFF_ID%,0]" => $comporator_data['tariffID'],
            "field[%CONTRACT_SIGN_UP_URL%,0]" => $comporator_data['signupURL'],
            "field[%CONTRACT_URL_TARIFF_CARD_E%,0]" => $comporator_data['signupURLE'],
            "field[%CONTRACT_URL_TARIFF_CARD_G%,0]" => $comporator_data['signupURLG'],
            "field[%CONTRACT_SIGN_DATE%,0]" => $comporator_data['signdate'],
            "field[%CONTRACT_START_DATE%,0]" => $comporator_data['startdate'],
            "field[%CONTRACT_DURATION_DB%,0]" => $comporator_data['durationdb'],
            "field[%CONTRACT_DURATION%,0]" => $comporator_data['duration'],
            "field[%CONTRACT_END_DATE%,0]" => $comporator_data['enddate'],
            "field[%CONTRACT_ENERGY_COST_E%,0]" => $comporator_data['contract_energy_costE'],
            // "field[%CONTRACT_PROMO_AMOUNT_E%,0]" => '100',
            "field[%CONTRACT_ENERGY_COST_G%,0]" => $comporator_data['contract_energy_costG'],

            "field[%CONTRACT_PRICE_TYPE_E%,0]" => $price_type,
            "field[%CONTRACT_PRICE_TYPE_G%,0]" => $price_type,
            "field[%CONTRACT_OTHER_COSTS_G%,0]" => "0",
            "field[%CONTRACT_OTHER_COSTS_E%,0]" => "0",
           

            "field[%CUSTOMERTYPE%,0]" => $comporator_data['customer_group'],
            "field[%FAMILYSIZE%,0]" => $comporator_data['residents'],
            "field[%FIRSTRESIDENCE%,0]" => $comporator_data['first_residence'],
            "field[%DECENTRALISEDPRODUCTION%,0]" => $decentralise_production,
            "field[%CAPACITYDECENTALISE%,0]" => $comporator_data['capacity_decentalise'],
            "field[%INCLUDEG%,0]" => $comporator_data['includeG'],
            "field[%INCLUDEE%,0]" => $comporator_data['includeE'],
            "field[%PACKID%,0]" => $comporator_data['packid'],
            "field[%EID%,0]" => $comporator_data['eid'],
            "field[%GID%,0]" => $comporator_data['gid'],
            "field[%TOTALCOST%,0]" => $comporator_data['total']




        );

        // This section takes the input fields and converts them to the proper format
        $query = "";
        foreach ($params as $key => $value) $query .= urlencode($key) . '=' . urlencode($value) . '&';
        $query = rtrim($query, '& ');
        $data = "";
        foreach ($post as $key => $value) $data .= urlencode($key) . '=' . urlencode($value) . '&';
        $data = rtrim($data, '& ');
        $url = rtrim($url, '/ ');
        if (!function_exists('curl_init')) die('CURL not supported. (introduced in PHP 4.0.2)');
        if ($params['api_output'] == 'json' && !function_exists('json_decode')) {
            die('JSON not supported. (introduced in PHP 5.2.0)');
        }
        $api = $url . '/admin/api.php?' . $query;
        $request = curl_init($api);
        curl_setopt($request, CURLOPT_HEADER, 0);
        curl_setopt($request, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($request, CURLOPT_POSTFIELDS, $data);
        curl_setopt($request, CURLOPT_FOLLOWLOCATION, true);
        $response = (string) curl_exec($request);
        curl_close($request);
        if (!$response) {
            die('Nothing was returned. Do you have a connection to Email Marketing server?');
        }
        $result = unserialize($response);
        $params = array(
            'api_key'      => '3f69314bf2d12325004faa27a223f3096a8ab91f4a82aab05431f29c693d9ac63abf2684',
            'api_action'   => 'deal_add',
            'api_output'   => 'json',
        );
        $post = array(
            'title'    => "",
            'value'             => "",
            'currency'          => 'eur',
            'pipeline'          => '1',
            'stage'             => '1',
            'owner'             => '1',
            'contact'           => $comporator_data['email'],
            'contact_name'      => $comporator_data['firstname'] . '' . $comporator_data['lastname'],
            'contact_phone'     => "",
            'customer_account'  => 'tariefchecker',
            'customer_acct_id'  => '1'
        );
        $query = "";
        foreach ($params as $key => $value) $query .= urlencode($key) . '=' . urlencode($value) . '&';
        $query = rtrim($query, '& ');
        $data = "";
        foreach ($post as $key => $value) $data .= urlencode($key) . '=' . urlencode($value) . '&';
        $data = rtrim($data, '& ');
        $url = rtrim($url, '/ ');
        if (!function_exists('curl_init')) die('CURL not supported. (introduced in PHP 4.0.2)');
        if ($params['api_output'] == 'json' && !function_exists('json_decode')) {
            die('JSON not supported. (introduced in PHP 5.2.0)');
        }
        $api = $url . '/admin/api.php?' . $query;
        $request = curl_init($api);
        curl_setopt($request, CURLOPT_HEADER, 0);
        curl_setopt($request, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($request, CURLOPT_POSTFIELDS, $data);
        curl_setopt($request, CURLOPT_FOLLOWLOCATION, true);
        $response = (string) curl_exec($request);
        curl_close($request);
        if (!$response) {
            die('Nothing was returned. Do you have a connection to Email Marketing server?');
        }
        $result = json_decode($response, true);

        if($comporator_data['customer_group']=="residential"){

            $customer_group="RES";

        }else{
            $customer_group="PRO";

        }

        /*airtable*/
            if($comporator_data['first_residence']==1){
                $first_residence='true';

            }else{
                $first_residence='false';
                
            }

             if($comporator_data['includeG']==1){
                $includeG='true';

            }else{
                $includeG='false';
                
            }

            if($comporator_data['includeE']==1){
                $includeE='true';

            }else{
                $includeE='false';
                
            }
        

            if($comporator_data['first_residence']==1){
                $first_residence='true';

            }else{
                $first_residence='false';
                
            }

             if($comporator_data['includeG']==1){
                $includeG='true';

            }else{
                $includeG='false';
                
            }

            if($comporator_data['includeE']==1){
                $includeE='true';

            }else{
                $includeE='false';
                
            }

             if($comporator_data['usage_single']==0 || $comporator_data['usage_single']==null){


                $usage_single="";
                if($meter_type=='Single Meter'||$meter_type=='Single + Excl Night Meter'){
                    $usage_single=0;
                }
                

            }else{

                $usage_single=$comporator_data['usage_single'];
                
            }

            if($comporator_data['usage_day']==0 || $comporator_data['usage_day']==null){

                $usage_day="";
                if($meter_type=='Double Meter'||$meter_type=='Double + Excl Night Meter'){
                    $usage_day=0;
                }

            }else{

                $usage_day=$comporator_data['usage_day'];
                
            }

            if($comporator_data['usage_night']==0 || $comporator_data['usage_night']==null){

                $usage_night="";
                if($meter_type=='Double Meter'||$meter_type=='Double + Excl Night Meter'){
                    $usage_night=0;
                }

            }else{

                $usage_night=$comporator_data['usage_night'];
                
            }

            if($comporator_data['usage_excl_night']==0 || $comporator_data['usage_excl_night']==null){

                $usage_exc_night="";
                if($meter_type=='Single + Excl Night Meter'||$meter_type=='Double + Excl Night Meter'){
                    $usage_exc_night=0;
                }

            }else{

                $usage_exc_night=$comporator_data['usage_excl_night'];
                
            }

            if($comporator_data['usage_gas']==0 || $comporator_data['usage_gas']==null){

                $usage_gas="";
                if($comporator_data['includeG']==1){
                    $usage_gas=0;
                }


            }else{

                $usage_gas=$comporator_data['usage_gas'];
                if($comporator_data['usage_gas']==-1){
                    $usage_gas="";

                }
                
            }

            if($comporator_data['price_typeE']=="variable"){
            $price_typeE='Indexed';

            }else{
            $price_typeE='Fixed';

            }

            if($comporator_data['price_typeG']=="variable"){
            $price_typeG='Indexed';

            }else{
            $price_typeG='Fixed';

            }

            $queryairtable['records'][0]['fields']['Email']              = $comporator_data['email'];
            $queryairtable['records'][0]['fields']["First Name"]         = $comporator_data['firstname'];
            $queryairtable['records'][0]['fields']["Last Name"]          = $comporator_data['lastname'];
            $queryairtable['records'][0]['fields']['Comparison Type']= $comporator_data['type'];
            $queryairtable['records'][0]['fields']['Contact Affiliate']= $form;
            $queryairtable['records'][0]['fields']['Contact Language'] = $comporator_data['locale'];
            $queryairtable['records'][0]['fields']['UUID']= $comporator_data['uuid'];
            $queryairtable['records'][0]['fields']['Customer Segment']= $customer_group;

            $queryairtable['records'][0]['fields']['First residence']= $first_residence;

            $queryairtable['records'][0]['fields']['includeG']= $includeG;
            $queryairtable['records'][0]['fields']['includeE']= $includeE;

            $queryairtable['records'][0]['fields']['Pack id']= $comporator_data['packid'];
            $queryairtable['records'][0]['fields']['Total cost']= (float)$comporator_data['total'];


            $queryairtable['records'][0]['fields']['Consumption Single']= null;
            $queryairtable['records'][0]['fields']['Consumption Day'] = null;
            $queryairtable['records'][0]['fields']['Consumption Night']= null;
            $queryairtable['records'][0]['fields']['Consumption Exclusive night']= null;

            if($comporator_data['includeE']==1){

                if($meter_type=="Single Meter"){

                   $queryairtable['records'][0]['fields']['Consumption Single']= (float)$usage_single;

                }

                if($meter_type=="Double Meter"){

                    $queryairtable['records'][0]['fields']['Consumption Day'] = (float)$usage_day;
                    $queryairtable['records'][0]['fields']['Consumption Night']= (float)$usage_night;

                }

                if($meter_type=="Single + Excl Night Meter"){

                    $queryairtable['records'][0]['fields']['Consumption Single']= (float)$usage_single;
                    $queryairtable['records'][0]['fields']['Consumption Exclusive night']= (float)$usage_exc_night;

                }

                if($meter_type=="Double + Excl Night Meter"){

            
            $queryairtable['records'][0]['fields']['Consumption Day'] = (float)$usage_day;
            $queryairtable['records'][0]['fields']['Consumption Night']= (float)$usage_night;
            $queryairtable['records'][0]['fields']['Consumption Exclusive night']= (float)$usage_exc_night;

                }


            // $queryairtable['records'][0]['fields']['Consumption Single']= (float)$usage_single;
            // $queryairtable['records'][0]['fields']['Consumption Day'] = (float)$usage_day;
            // $queryairtable['records'][0]['fields']['Consumption Night']= (float)$usage_night;
            // $queryairtable['records'][0]['fields']['Consumption Exclusive night']= (float)$usage_exc_night;

            $queryairtable['records'][0]['fields']['Decentralise production']= $decentralise_production;
            $queryairtable['records'][0]['fields']['Capacity decentalise']= (float)$comporator_data['capacity_decentalise'];

            $queryairtable['records'][0]['fields']['Meter Type']= $meter_type;

            $queryairtable['records'][0]['fields']['Comparison Current Supplier E'] = $curr_supplierE;
            $queryairtable['records'][0]['fields']['Eid']= $comporator_data['eid'];

            $queryairtable['records'][0]['fields']['Comparison Energy Cost E']= (float)$comporator_data['energycostE'];
            $queryairtable['records'][0]['fields']['Comparison Other Cost E'] = null;
            $queryairtable['records'][0]['fields']['Comparison Promo amount E']= (float)$comporator_data['promoAmountE'];

            $queryairtable['records'][0]['fields']['Contract price type e'] = $price_typeE;
            $queryairtable['records'][0]['fields']['Contract energy cost E'] = (float)$comporator_data['energycostE'];

            $queryairtable['records'][0]['fields']['Contract othercosts e'] = null;
            $queryairtable['records'][0]['fields']['Contract promo amount e'] = (float)$comporator_data['promoAmountE'];

            $queryairtable['records'][0]['fields']['Contract othercosts g'] = null;
            $queryairtable['records'][0]['fields']['Contract promo amount g'] = (float)$comporator_data['promoAmountG'];

            if(isset($comporator_data['estimate_cunsomption'])){

                $estimate_cunsomption=$comporator_data['estimate_cunsomption'];
            }else{

                 $estimate_cunsomption=null;
            }
            $queryairtable['records'][0]['fields']['Estimate Consumption'] = $estimate_cunsomption;

            if(isset($comporator_data['residence'])){

                $residence=$comporator_data['residence'];
            }else{

                $residence=null;
            }
            $queryairtable['records'][0]['fields']['Aantal bewoners'] = $residence;
             if(isset($comporator_data['building_type'])){

                $building_type=$comporator_data['building_type'];
            }else{

                $building_type=null;
            }
            $queryairtable['records'][0]['fields']['Gebouwtype'] = $building_type;

            if(isset($comporator_data['isolation_level'])){

                $isolation_level=$comporator_data['isolation_level'];
            }else{

                $isolation_level=null;
            }

            $queryairtable['records'][0]['fields']['Isolatieniveau'] = $isolation_level;

             if(isset($comporator_data['heating_system'])){

                $heating_system=$comporator_data['heating_system'];
            }else{

                $heating_system=null;
            }


            $queryairtable['records'][0]['fields']['Verwarming'] =  $heating_system;

            if(isset($comporator_data['signupURLE'])){

                $signupURLE=$comporator_data['signupURLE'];
            }else{

                $signupURLE=null;
            }

              $queryairtable['records'][0]['fields']['Contract URL tariff card E']= $signupURLE;


            }else{

            $queryairtable['records'][0]['fields']['Consumption Single']= null;
            $queryairtable['records'][0]['fields']['Consumption Day'] = null;
            $queryairtable['records'][0]['fields']['Consumption Night']= null;
            $queryairtable['records'][0]['fields']['Consumption Exclusive night']=null;

            $queryairtable['records'][0]['fields']['Decentralise production']='false';
            $queryairtable['records'][0]['fields']['Capacity decentalise']= 0;
            $queryairtable['records'][0]['fields']['Meter Type']= null;
            $queryairtable['records'][0]['fields']['Comparison Current Supplier E'] = null;
            $queryairtable['records'][0]['fields']['Eid']= null;

            $queryairtable['records'][0]['fields']['Comparison Energy Cost E']= null;
            $queryairtable['records'][0]['fields']['Comparison Other Cost E'] = null;
            $queryairtable['records'][0]['fields']['Comparison Promo amount E']= null;

            $queryairtable['records'][0]['fields']['Contract price type e'] = null;
            $queryairtable['records'][0]['fields']['Contract energy cost E'] = null;

            $queryairtable['records'][0]['fields']['Contract othercosts e'] = null;
            $queryairtable['records'][0]['fields']['Contract promo amount e'] = null;

            $queryairtable['records'][0]['fields']['Contract othercosts g'] = null;
            $queryairtable['records'][0]['fields']['Contract promo amount g'] = null;

            $queryairtable['records'][0]['fields']['Estimate Consumption'] = "false";
            $queryairtable['records'][0]['fields']['Aantal bewoners'] = null;
            $queryairtable['records'][0]['fields']['Gebouwtype'] = null;
            $queryairtable['records'][0]['fields']['Isolatieniveau'] = null;
            $queryairtable['records'][0]['fields']['Verwarming'] = "";
            $queryairtable['records'][0]['fields']['Contract URL tariff card E']= null;

            }

            if($comporator_data['includeG']==1){

            $queryairtable['records'][0]['fields']['Consumption Gas']= (float)$usage_gas;
            $queryairtable['records'][0]['fields']['Comparison Current Supplier G']= $curr_supplierG;
            $queryairtable['records'][0]['fields']['Gid']= $comporator_data['gid'];

            $queryairtable['records'][0]['fields']['Comparison Energy Cost G']= (float)$comporator_data['energycostG'];
            $queryairtable['records'][0]['fields']['Comparison Other Cost G']= null;
            $queryairtable['records'][0]['fields']['Comparison Promo amount G']= (float)$comporator_data['promoAmountG'];
            $queryairtable['records'][0]['fields']['Contract price type g'] = $price_typeG;
            $queryairtable['records'][0]['fields']['Contract energy cost G'] = (float)$comporator_data['energycostG'];
            $queryairtable['records'][0]['fields']['Contract URL tariff card G'] = $comporator_data['signupURLG'];

            if(isset($comporator_data['supplier_G'])){
             $queryairtable['records'][0]['fields']['Contract Supplier G']= $comporator_data['supplier_G'];
            }
            
             if(isset($comporator_data['supplierID_G'])){
            $queryairtable['records'][0]['fields']['Contract Supplier Id G']= $comporator_data['supplierID_G'];
            }
            if(isset($comporator_data['tariff_G'])){
            $queryairtable['records'][0]['fields']['Contract Tariff G']= $comporator_data['tariff_G'];
            }
            if(isset($comporator_data['tariffID_G'])){
            $queryairtable['records'][0]['fields']['Contract Tariff Id G']= $comporator_data['tariffID_G'];
            }
            $queryairtable['records'][0]['fields']['Contract Sign-up URL G']= $comporator_data['signupURLG'];

            }else{

            $queryairtable['records'][0]['fields']['Consumption Gas']= null;
            $queryairtable['records'][0]['fields']['Comparison Current Supplier G']=null;
            $queryairtable['records'][0]['fields']['Gid']= "";

            $queryairtable['records'][0]['fields']['Comparison Energy Cost G']= null;
            $queryairtable['records'][0]['fields']['Comparison Other Cost G']= null;
            $queryairtable['records'][0]['fields']['Comparison Promo amount G']= null;
            $queryairtable['records'][0]['fields']['Contract price type g'] = null;
            $queryairtable['records'][0]['fields']['Contract energy cost G'] =null;
            $queryairtable['records'][0]['fields']['Contract URL tariff card G'] = null;

            }

            $queryairtable['records'][0]['fields']['Comparison URL']= $comporator_data['url'];
            $queryairtable['records'][0]['fields']['Region']= $comporator_data['region'];
            $queryairtable['records'][0]['fields']['Contract Supplier']= $comporator_data['supplier'];
            $queryairtable['records'][0]['fields']['Contract Supplier Id'] = (float)$comporator_data['supplierID'];
            $queryairtable['records'][0]['fields']['Contract Tariff']= $comporator_data['tariff'];
            $queryairtable['records'][0]['fields']['Contract Tariff Id']= $comporator_data['tariffID'];
            $queryairtable['records'][0]['fields']['Contract Sign-up URL']= $comporator_data['signupURL'];
            $queryairtable['records'][0]['fields']['Contract Duration db'] = (float)$comporator_data['durationdb'];
            $queryairtable['records'][0]['fields']['Contract Duration']= (float)$comporator_data['duration'];
            $queryairtable['records'][0]['fields']['Postal code'] = $comporator_data['postcode'];
            $queryairtable['records'][0]['fields']['Contract status supplier'] = $comporator_data['supplier'];
             try {
                $client = new \GuzzleHttp\Client();
               
                $request = $client->post('https://api.airtable.com/v0/applSCRl4UvL2haqK/user-log', [
                    'headers' => [
                        'Accept' => 'application/json',
                        'Content-type' => 'application/json',
                        'Authorization' => 'Bearer keySZo45QUBRPLwjL'
                    ],
                   'body' => json_encode($queryairtable)
                ]);
            } catch (Exception $ex) {
                $response = ['status' => false, 'message' => $ex->getMessage()];
            }
           $response = $request->getBody()->getContents();

        //  return $response;

        /*airtable*/

    }

    public function addDeal($comporator_data)
    {
        $url = 'https://tariefchecker.api-us1.com';
        $params = array(
            'api_key'      => '3f69314bf2d12325004faa27a223f3096a8ab91f4a82aab05431f29c693d9ac63abf2684',
            'api_action'   => 'deal_add',
            'api_output'   => 'json',
        );
        $post = array(
            'title'    => "",
            'value'             =>"",
            'currency'          => 'eur',
            'pipeline'          => '1',
            'stage'             => '1',
            'owner'             => '1',
            'contact'           => $comporator_data['email'],
            'contact_name'      => $comporator_data['firstname'] . '' . $comporator_data['lastname'],
            'contact_phone'     => "",
            'customer_account'  => 'tariefchecker',
            'customer_acct_id'  => '1'
        );
        $query = "";
        foreach ($params as $key => $value) $query .= urlencode($key) . '=' . urlencode($value) . '&';
        $query = rtrim($query, '& ');
        $data = "";
        foreach ($post as $key => $value) $data .= urlencode($key) . '=' . urlencode($value) . '&';
        $data = rtrim($data, '& ');
        $url = rtrim($url, '/ ');
        if (!function_exists('curl_init')) die('CURL not supported. (introduced in PHP 4.0.2)');
        if ($params['api_output'] == 'json' && !function_exists('json_decode')) {
            die('JSON not supported. (introduced in PHP 5.2.0)');
        }
        $api = $url . '/admin/api.php?' . $query;
        $request = curl_init($api);
        curl_setopt($request, CURLOPT_HEADER, 0);
        curl_setopt($request, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($request, CURLOPT_POSTFIELDS, $data);
        curl_setopt($request, CURLOPT_FOLLOWLOCATION, true);
        $response = (string) curl_exec($request);
        curl_close($request);
        if (!$response) {
            die('Nothing was returned. Do you have a connection to Email Marketing server?');
        }
        $result = json_decode($response, true);

    }
}
