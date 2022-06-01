<?php

namespace App\Http\Controllers\Api\ActiveCampaign;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SearchDetails\SearchDetail;

class activeCampaignController extends Controller
{

	public function index($request, $subscribe_url)
	{



		$comporator_data = $request;


		


		// By default, this sample code is designed to get the result from your ActiveCampaign installation and print out the result
		$url = 'https://tariefchecker.api-us1.com';


		$params = array(
			'api_key'      => '3f69314bf2d12325004faa27a223f3096a8ab91f4a82aab05431f29c693d9ac63abf2684',
			'api_action'   => 'contact_sync',
			'api_output'   => 'serialize',
		);

		if ($comporator_data->meter_type == 'single') {

			$meter_type = 'Single Meter';
		}
		if ($comporator_data->meter_type == 'double') {

			$meter_type = 'Double Meter';
		}
		if ($comporator_data->meter_type == 'single_excl_night') {

			$meter_type = 'Single + Excl Night Meter';
		}
		if ($comporator_data->meter_type == 'double_excl_night') {

			$meter_type = 'Double + Excl Night Meter';
		}

		// if ($request->locale == 'fr') {

		// 	$form = 'veriftarif';
		// } else {
		// 	$form = 'tariefchecker';
		// }


		if(isset($request->from)){

			$form = $request->from;

		}else{

		if ($request->locale == 'fr') {

			$form = 'veriftarif';
		} else {
			$form = 'tariefchecker';
		}

	    }
		

		// here we define the data we are posting in order to perform an update
		$post = array(
			'email'              => $comporator_data->email,
			"first_name"         => $comporator_data->firstname,
			"last_name"          => $comporator_data->lastname,
			"tags"               => $form . ", vgl-api, WFL-start",
			"p[3,5,7]" => 3, 5, 7,
			"field[%COMPARISON_TYPE%,0]"    => $comporator_data->type,
			"phone"     =>  $comporator_data->phone,
			"field[%CONTACT_LANGUAGE%,0]" => $comporator_data->locale,
			"field[%CONTACT_TYPE%,0]"  =>  "customers",
			"field[%COMPARISON_UUID%,0]" => $comporator_data->uuid,
			"field[%CONTACT_SOURCE%,0]" => "comporator App",
			"field[%CONTACT_CAMPAIGN%,0]" => "Campaign",
			"field[%CONSUMPTION_REGISTER_TYPE_E%,0]" => $meter_type,
			"field[%CONSUMPTION_E_MONO%,0]" => $comporator_data->usage_single,
			"field[%CONSUMPTION_E_DAY%,0]" => $comporator_data->usage_day,
			"field[%CONSUMPTION_E_NIGHT%,0]" => $comporator_data->usage_night,
			"field[%CONSUMPTION_E_EXCL_NIGHT%,0]" => $comporator_data->usage_exc_night,
			"field[%CONSUMPTION_G%,0]" => $comporator_data->usage_gas,
			"field[%COMPARISON_URL%,0]" => $request->url,
			"field[%COMPARISON_TYPE%,0]" => "",
			"field[%COMPARISON_FUELS%,0]" => "",
			"field[%COMPARISON_CURRENT_SUPPLIER_E%,0]" => $request->CurrentSupplierE,
			"field[%COMPARISON_CURRENT_SUPPLIER_G%,0]" => $request->CurrentSupplierG,
			"field[3,0]" => $comporator_data->postcode,
			"field[%ADDRESS_REGION%,0]" => $comporator_data->region,
			"field[%ADDRESS_CITY%,0]"   =>  ""
		);

		// This section takes the input fields and converts them to the proper format
		$query = "";
		foreach ($params as $key => $value) $query .= urlencode($key) . '=' . urlencode($value) . '&';
		$query = rtrim($query, '& ');

		// This section takes the input data and converts it to the proper format
		$data = "";
		foreach ($post as $key => $value) $data .= urlencode($key) . '=' . urlencode($value) . '&';
		$data = rtrim($data, '& ');

		// clean up the url
		$url = rtrim($url, '/ ');

		// submit your request, and show (print out) the response.
		if (!function_exists('curl_init')) die('CURL not supported. (introduced in PHP 4.0.2)');

		// If JSON is used, check if json_decode is present (PHP 5.2.0+)
		if ($params['api_output'] == 'json' && !function_exists('json_decode')) {
			die('JSON not supported. (introduced in PHP 5.2.0)');
		}

		// define a final API request - GET
		$api = $url . '/admin/api.php?' . $query;

		$request = curl_init($api); // initiate curl object
		curl_setopt($request, CURLOPT_HEADER, 0); // set to 0 to eliminate header info from response
		curl_setopt($request, CURLOPT_RETURNTRANSFER, 1); // Returns response data instead of TRUE(1)
		curl_setopt($request, CURLOPT_POSTFIELDS, $data); // use HTTP POST to send form data
		curl_setopt($request, CURLOPT_FOLLOWLOCATION, true);
		$response = (string) curl_exec($request); // execute curl post and store results in $response
		curl_close($request); // close curl object
		if (!$response) {
			die('Nothing was returned. Do you have a connection to Email Marketing server?');
		}
		$result = unserialize($response);






		$params = array(

			'api_key'      => '3f69314bf2d12325004faa27a223f3096a8ab91f4a82aab05431f29c693d9ac63abf2684',
			'api_action'   => 'deal_add',
			'api_output'   => 'json',
		);
		// here we define the data we are posting in order to perform an update
		$post = array(
			'title'    => $comporator_data->act_title,
			'value'             => $comporator_data->active_value,
			'currency'          => 'eur',
			'pipeline'          => '1',
			'stage'             => '1',
			'owner'             => '1',
			'contact'           => $comporator_data->email,
			'contact_name'      => $comporator_data->firstname . '' . $comporator_data->lastname,
			'contact_phone'     => $comporator_data->phone,
			'customer_account'  => $form,
			'customer_acct_id'  => '1'
		);

		// This section takes the input fields and converts them to the proper format
		$query = "";
		foreach ($params as $key => $value) $query .= urlencode($key) . '=' . urlencode($value) . '&';
		$query = rtrim($query, '& ');
		$data = "";
		foreach ($post as $key => $value) $data .= urlencode($key) . '=' . urlencode($value) . '&';
		$data = rtrim($data, '& ');

		// clean up the url
		$url = rtrim($url, '/ ');
		if (!function_exists('curl_init')) die('CURL not supported. (introduced in PHP 4.0.2)');
		// If JSON is used, check if json_decode is present (PHP 5.2.0+)
		if ($params['api_output'] == 'json' && !function_exists('json_decode')) {
			die('JSON not supported. (introduced in PHP 5.2.0)');
		}
		// define a final API request - GET
		$api = $url . '/admin/api.php?' . $query;
		$request = curl_init($api); // initiate curl object
		curl_setopt($request, CURLOPT_HEADER, 0); // set to 0 to eliminate header info from response
		curl_setopt($request, CURLOPT_RETURNTRANSFER, 1); // Returns response data instead of TRUE(1)
		curl_setopt($request, CURLOPT_POSTFIELDS, $data); // use HTTP POST to send form data

		curl_setopt($request, CURLOPT_FOLLOWLOCATION, true);

		$response = (string) curl_exec($request); // execute curl post and store results in $response

		curl_close($request); // close curl object

		if (!$response) {
			die('Nothing was returned. Do you have a connection to Email Marketing server?');
		}
		$result = json_decode($response, true);

		if($request->usage_single==0 || $request->usage_single==null){

            	$usage_single="";

            }else{

            	$usage_single=$request->usage_single;
            }

            if($request->usage_day==0 || $request->usage_day==null){

            	$usage_day="";

            }else{

            	$usage_day=$request->usage_day;
            }

            if($request->usage_night==0 || $request->usage_night==null){

            	$usage_night="";

            }else{

            	$usage_night=$request->usage_night;
            }

            if($request->usage_excl_night==0 || $request->usage_excl_night==null){

            	$usage_exc_night="";

            }else{

            	$usage_exc_night=$request->usage_excl_night;
            }

            if($request->usage_gas==0 || $request->usage_gas==null){

            	$usage_gas="";

            }else{

            	$usage_gas=$request->usage_gas;
            }


        /*airtable*/


            $queryairtable['records'][0]['fields']['Email']= $request->email;
            $queryairtable['records'][0]['fields']['Comparison Type']= $request->comparison_type;
            $queryairtable['records'][0]['fields']['Contact Affiliate']= $form;
            $queryairtable['records'][0]['fields']['Contact Language'] = $request->locale;
            $queryairtable['records'][0]['fields']['Contact Type'] =  "hot prospect";
            $queryairtable['records'][0]['fields']['UUID']= $request->uuid;
            $queryairtable['records'][0]['fields']['Contact source'] = $request->req_from;
            $queryairtable['records'][0]['fields']['Contact campaign']= "Campaign";
            $queryairtable['records'][0]['fields']['Meter Type']= $meter_type;


            $queryairtable['records'][0]['fields']['Consumption Single']= (float)$usage_single;
            $queryairtable['records'][0]['fields']['Consumption Day'] = (float)$usage_day;
            $queryairtable['records'][0]['fields']['Consumption Night']= (float)$usage_night;
            $queryairtable['records'][0]['fields']['Consumption Exclusive night']= (float)$usage_excl_night;
            $queryairtable['records'][0]['fields']['Consumption Gas']= (float)$usage_gas;
            $queryairtable['records'][0]['fields']['Comparison URL']= $request->url;
            $queryairtable['records'][0]['fields']['Comaprison Fuel']= $request->comparison_type;
            $queryairtable['records'][0]['fields']['Current Supplier E'] = $request->CurrentSupplierE;
            $queryairtable['records'][0]['fields']['Current Supplier G']= $request->CurrentSupplierG;
            $queryairtable['records'][0]['fields']['Region']= $request->region;
            $queryairtable['records'][0]['fields']['Postal code'] = $comporator_data->postcode;
             $queryairtable['records'][0]['fields']['First residence']= $comporator_data->first_residence;
           
            $queryairtable['records'][0]['fields']['Decentralise production']= $decentralise_production;
            $queryairtable['records'][0]['fields']['Capacity decentalise']= $comporator_data->capacity_decentalise;
            $queryairtable['records'][0]['fields']['includeG']= $comporator_data->includeG;
            $queryairtable['records'][0]['fields']['includeE']= $comporator_data->includeE;

             try {
                $client = new \GuzzleHttp\Client();
               
                $request = $client->post('https://api.airtable.com/v0/applSCRl4UvL2haqK/user-log', [
                    'headers' => [
                        'Accept' => 'application/json',
                        'Content-type' => 'application/x-www-form-urlencoded',
                        'Authorization' => 'Bearer keySZo45QUBRPLwjL'
                    ],
                    'form_params' => $queryairtable
                ]);
            } catch (Exception $ex) {
                return $ex->getCode();
            }
           $response = $request->getBody()->getContents();



        /*airtable*/
	}



	public function addDeal($comporator_data)
	{




		$url = 'https://tariefchecker.api-us1.com';

		// if ($comporator_data->locale == 'fr') {

		// 	$form = 'veriftarif';
		// } else {
		// 	$form = 'tariefchecker';
		// }

		if(isset($comporator_data->from)){

			$form = $request->from;

		}else{

		if ($comporator_data->locale == 'fr') {

			$form = 'veriftarif';
		} else {
			$form = 'tariefchecker';
		}

	    }


		$params = array(

			'api_key'      => '3f69314bf2d12325004faa27a223f3096a8ab91f4a82aab05431f29c693d9ac63abf2684',
			'api_action'   => 'deal_add',
			'api_output'   => 'json',
		);
		// here we define the data we are posting in order to perform an update
		$post = array(
			'title'    => $comporator_data->act_title,
			'value'             => $comporator_data->active_value,
			'currency'          => 'eur',
			'pipeline'          => '1',
			'stage'             => '1',
			'owner'             => '1',
			'contact'           => $comporator_data->email,
			'contact_name'      => $comporator_data->firstname . '' . $comporator_data->lastname,
			'contact_phone'     => $comporator_data->phone,
			'customer_account'  => $form,
			'customer_acct_id'  => '1'
		);

		// This section takes the input fields and converts them to the proper format
		$query = "";
		foreach ($params as $key => $value) $query .= urlencode($key) . '=' . urlencode($value) . '&';
		$query = rtrim($query, '& ');
		$data = "";
		foreach ($post as $key => $value) $data .= urlencode($key) . '=' . urlencode($value) . '&';
		$data = rtrim($data, '& ');

		// clean up the url
		$url = rtrim($url, '/ ');
		if (!function_exists('curl_init')) die('CURL not supported. (introduced in PHP 4.0.2)');
		// If JSON is used, check if json_decode is present (PHP 5.2.0+)
		if ($params['api_output'] == 'json' && !function_exists('json_decode')) {
			die('JSON not supported. (introduced in PHP 5.2.0)');
		}
		// define a final API request - GET
		$api = $url . '/admin/api.php?' . $query;
		$request = curl_init($api); // initiate curl object
		curl_setopt($request, CURLOPT_HEADER, 0); // set to 0 to eliminate header info from response
		curl_setopt($request, CURLOPT_RETURNTRANSFER, 1); // Returns response data instead of TRUE(1)
		curl_setopt($request, CURLOPT_POSTFIELDS, $data); // use HTTP POST to send form data

		curl_setopt($request, CURLOPT_FOLLOWLOCATION, true);

		$response = (string) curl_exec($request); // execute curl post and store results in $response

		curl_close($request); // close curl object

		if (!$response) {
			die('Nothing was returned. Do you have a connection to Email Marketing server?');
		}
		$result = json_decode($response, true);
	}

	public function change_data_sync(Request $request)
	{



		$url = 'https://tariefchecker.api-us1.com';
		$params = array(
			'api_key'      => '3f69314bf2d12325004faa27a223f3096a8ab91f4a82aab05431f29c693d9ac63abf2684',
			'api_action'   => 'contact_sync',
			'api_output'   => 'serialize',
		);

		if ($request->meter_type == 'single') {

			$meter_type = 'Single Meter';
		}
		if ($request->meter_type == 'double') {

			$meter_type = 'Double Meter';
		}
		if ($request->meter_type == 'single_excl_night') {

			$meter_type = 'Single + Excl Night Meter';
		}
		if ($request->meter_type == 'double_excl_night') {

			$meter_type = 'Double + Excl Night Meter';
		}


		if(isset($request->from)){

			$form = $request->from;

		}else{

		if ($request->locale == 'fr') {

			$form = 'veriftarif';
		} else {
			$form = 'tariefchecker';
		}

	    }

		//$request->CurrentSupplierE
		// here we define the data we are posting in order to perform an update
		$post = array(
			'email'              => $request->email,
			"field[%COMPARISON_UUID%,0]" => $request->uuid,
			"tags"               => $form . ", vgl-api, WFL-start",
			"p[3]" => 3,
			"field[%CONSUMPTION_REGISTER_TYPE_E%,0]" => $meter_type,
			"field[%CONSUMPTION_E_MONO%,0]" => $request->usage_single,
			"field[%CONSUMPTION_E_DAY%,0]" => $request->usage_day,
			"field[%CONSUMPTION_E_NIGHT%,0]" => $request->usage_night,
			"field[%CONSUMPTION_E_EXCL_NIGHT%,0]" => $request->usage_exc_night,
			"field[%CONSUMPTION_G%,0]" => $request->usage_gas,
			"field[%COMPARISON_URL%,0]" => $request->url,
			"field[%CONTACT_LANGUAGE%,0]" => $request->locale,
			"field[%COMPARISON_TYPE%,0]" => "",
			"field[%COMPARISON_FUELS%,0]" => "",
			"field[%CONTACT_TYPE%,0]"  =>  "hot prospect",
			"field[%COMPARISON_CURRENT_SUPPLIER_E%,0]" => $request->CurrentSupplierE,
			"field[%COMPARISON_CURRENT_SUPPLIER_G%,0]" => $request->CurrentSupplierG,
			"field[3,0]" => $request->postalcode,
			"field[%ADDRESS_REGION%,0]" => $request->region,
			"field[%ADDRESS_CITY%,0]"   =>  ""
		);



		 /*airtable*/

		 if($request->email){

		 	if($form=="tariefchecker"){

		 		$locale="nl";
		 	}else{

		 		$locale="fr";
		 	}
			$locale=$request->locale;
            
           
            
            

            if($request->customer_group=='residential'){

            	$customer_group="RES";

            }else{

            	$customer_group="PRO";
            }

            $queryairtable['records'][0]['fields']['Customer Segment']= $customer_group;
            



            if($request->usage_single==0 || $request->usage_single==null){


            	$usage_single="";
            	if($meter_type=='Single Meter'||$meter_type=='Single + Excl Night Meter'){
            		$usage_single=0;
            	}
            	

            }else{

            	$usage_single=$request->usage_single;
            	
            }

            if($request->usage_day==0 || $request->usage_day==null){

            	$usage_day="";
            	if($meter_type=='Double Meter'||$meter_type=='Double + Excl Night Meter'){
            		$usage_day=0;
            	}

            }else{

            	$usage_day=$request->usage_day;
            	
            }

            if($request->usage_night==0 || $request->usage_night==null){

            	$usage_night="";
            	if($meter_type=='Double Meter'||$meter_type=='Double + Excl Night Meter'){
            		$usage_night=0;
            	}

            }else{

            	$usage_night=$request->usage_night;
            	
            }

            if($request->usage_excl_night==0 || $request->usage_excl_night==null){

            	$usage_exc_night="";
            	if($meter_type=='Single + Excl Night Meter'|| $meter_type=='Double + Excl Night Meter'){
            		$usage_exc_night=0;
            	}

            }else{

            	$usage_exc_night=$request->usage_excl_night;
            	
            }

            if($request->usage_gas==0 || $request->usage_gas==null){

            	$usage_gas="";
            	if($request->includeG==1){
            		$usage_gas=0;
            	}


            }else{

            	$usage_gas=$request->usage_gas;
            	if($request->usage_gas==-1){

            		$usage_gas="";

            	}
            	
            }

            if($request->decentralise_production==null || $request->decentralise_production==0){

            	$decentralise_production='false';
            }else{

            	$decentralise_production='true';

            }

            if($request->first_residence==null || $request->first_residence==0){

            	$first_residence='false';
            }else{

            	$first_residence='true';

            }

            if($request->includeG==null || $request->includeG==0){

            	$includeG='false';
            }else{

            	$includeG='true';

            }

            if($request->includeE==null || $request->includeE==0){

            	$includeE='false';
            }else{

            	$includeE='true';

            }

            if($request->capacity_decentalise>0){

            	$decentralise_production="true";
            }else{

            	$decentralise_production="false";
            }

           

            $queryairtable['records'][0]['fields']['Email']= $request->email;
            $queryairtable['records'][0]['fields']['Comparison Type']= $request->comparison_type;
            $queryairtable['records'][0]['fields']['Contact Affiliate']= $form;
            $queryairtable['records'][0]['fields']['Contact Language'] = $locale;
            $queryairtable['records'][0]['fields']['UUID']= $request->uuid;
            $queryairtable['records'][0]['fields']['Comparison URL']= $request->url;
            $queryairtable['records'][0]['fields']['Region']= $request->region;
            $queryairtable['records'][0]['fields']['Postal code'] = $request->postalcode;

// elctricity true

        if($request->includeE==1){
        	$queryairtable['records'][0]['fields']['Consumption Single']= null;
            $queryairtable['records'][0]['fields']['Consumption Day'] = null;
            $queryairtable['records'][0]['fields']['Consumption Night']= null;
            $queryairtable['records'][0]['fields']['Consumption Exclusive night']= null;

            

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
            

            $queryairtable['records'][0]['fields']['Estimate Consumption'] = $request->estimate_cunsomption;
            $queryairtable['records'][0]['fields']['Aantal bewoners'] = $request->residence;
            $queryairtable['records'][0]['fields']['Gebouwtype'] = $request->building_type;
            $queryairtable['records'][0]['fields']['Isolatieniveau'] = $request->isolation_level;
            $queryairtable['records'][0]['fields']['Verwarming'] = $request->heating_system;

            $queryairtable['records'][0]['fields']['First residence']= $first_residence;
            $queryairtable['records'][0]['fields']['Decentralise production']= $decentralise_production;
            $queryairtable['records'][0]['fields']['Capacity decentalise']= (float)$request->capacity_decentalise;
            $queryairtable['records'][0]['fields']['Meter Type']= $meter_type;

            $queryairtable['records'][0]['fields']['Comparison Current Supplier E'] = $request->CurrentSupplierE;
        }else{

        	$queryairtable['records'][0]['fields']['Consumption Single']= null;
            $queryairtable['records'][0]['fields']['Consumption Day'] = null;
            $queryairtable['records'][0]['fields']['Consumption Night']= null;
            $queryairtable['records'][0]['fields']['Consumption Exclusive night']= null;
            


        	$queryairtable['records'][0]['fields']['Estimate Consumption'] = 'false';
            $queryairtable['records'][0]['fields']['Aantal bewoners'] = "";
            $queryairtable['records'][0]['fields']['Gebouwtype'] = "";
            $queryairtable['records'][0]['fields']['Isolatieniveau'] = "";
            $queryairtable['records'][0]['fields']['Verwarming'] = "";

            $queryairtable['records'][0]['fields']['First residence']= $first_residence;
            $queryairtable['records'][0]['fields']['Decentralise production']= "false";
            $queryairtable['records'][0]['fields']['Capacity decentalise']=0;
            $queryairtable['records'][0]['fields']['Meter Type']= "";

            $queryairtable['records'][0]['fields']['Comparison Current Supplier E'] = "";

        }

// gas true

        if($request->includeG==1){

        	$queryairtable['records'][0]['fields']['Consumption Gas']= (float)$usage_gas;
        	$queryairtable['records'][0]['fields']['Comparison Current Supplier G']= $request->CurrentSupplierG;
        }else{
        	$queryairtable['records'][0]['fields']['Consumption Gas']= null;
        	$queryairtable['records'][0]['fields']['Comparison Current Supplier G']= "";

        }	
            
        $queryairtable['records'][0]['fields']['includeG']= $includeG;
        $queryairtable['records'][0]['fields']['includeE']= $includeE;


             try {
                $client = new \GuzzleHttp\Client();
               
                $requests = $client->post('https://api.airtable.com/v0/applSCRl4UvL2haqK/user-log', [
                    'headers' => [
                        'Accept' => 'application/json',
                        'Content-type' => 'application/json',
                        'Authorization' => 'Bearer keySZo45QUBRPLwjL'
                    ],
                    'json' => $queryairtable
                ]);

                $response = $requests->getBody()->getContents();
            } catch (Exception $ex) {
                return $ex->getCode();
            }
           

       


        /*airtable*/


		 /*insert to user search history */
		SearchDetail::create(
			[ 
			'uuid'=>$request->uuid,
			'locale'=>$request->locale,
			'postalcode'=>$request->postalcode,
			'region'=>$request->region,
			'email' => $request->email,
			'comparison_type'=>$request->comparison_type,
			'meter_type'=>$meter_type,
			'comparison_url' => $request->url,
			'single' => $request->usage_single,
            'day' => $request->usage_day,
            'night' => $request->usage_night,
			'excl_night' => $request->usage_excl_night,
			'gas_consumption' => $request->usage_gas,
			'contact_type'=> 'hot prospect',
			'data_from'=>$request->req_from,
			'contact_affiliate' => $form,
			'current_electric_supplier'=>$request->CurrentSupplierE,
			'current_gas_supplier'=>$request->CurrentSupplierG
	     ]);

		 /*insert to user search history */

		// This section takes the input fields and converts them to the proper format
		$query = "";
		foreach ($params as $key => $value) $query .= urlencode($key) . '=' . urlencode($value) . '&';
		$query = rtrim($query, '& ');

		// This section takes the input data and converts it to the proper format
		$data = "";
		foreach ($post as $key => $value) $data .= urlencode($key) . '=' . urlencode($value) . '&';
		$data = rtrim($data, '& ');

		// clean up the url
		$url = rtrim($url, '/ ');

		// submit your request, and show (print out) the response.
		if (!function_exists('curl_init')) die('CURL not supported. (introduced in PHP 4.0.2)');

		// If JSON is used, check if json_decode is present (PHP 5.2.0+)
		if ($params['api_output'] == 'json' && !function_exists('json_decode')) {
			die('JSON not supported. (introduced in PHP 5.2.0)');
		}

		// define a final API request - GET
		$api = $url . '/admin/api.php?' . $query;

		$request = curl_init($api); // initiate curl object
		curl_setopt($request, CURLOPT_HEADER, 0); // set to 0 to eliminate header info from response
		curl_setopt($request, CURLOPT_RETURNTRANSFER, 1); // Returns response data instead of TRUE(1)
		curl_setopt($request, CURLOPT_POSTFIELDS, $data); // use HTTP POST to send form data
		curl_setopt($request, CURLOPT_FOLLOWLOCATION, true);
		$response = (string) curl_exec($request); // execute curl post and store results in $response
		curl_close($request); // close curl object
		if (!$response) {
			die('Nothing was returned. Do you have a connection to Email Marketing server?');
		}
		$result = unserialize($response);



}




       

	}
}
