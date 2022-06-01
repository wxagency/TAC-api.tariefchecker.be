<?php

namespace App\Http\Controllers\Api\Checkup;

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

class CheckupController extends Controller
{
/**
* Display a listing of the resource.
*
* @return \Illuminate\Http\Response
*/

        public function checkup(Request $request)
        {

        $query['locale']=$request->locale;
        $query['postalCode']=$request->postal_code;
        $query['customerGroup']=$request->customer_type;
        $query['first_residence']=1;
        $certificateE=null;
        $electricity=0; $gas=0;
        $query['registerNormal']=$single=0;
        $query['registerDay']=$day=0;
        $query['registerNight']=$night=0;
        $query['registerExclNight']=$excl_night=0;
        $query['registerG']=$gasC=0;
        $query['meterType']='single';
        if(isset($request->usage['electricity']['single']))
        {
        $query['registerNormal']=$single=$request->usage['electricity']['single'];
        $query['meterType']='single';
        $electricity=1;
        }
        if(isset($request->usage['electricity']['day']) && isset($request->usage['electricity']['night']))
        {
        $query['registerDay']=$day=$request->usage['electricity']['day'];
        $query['registerNight']=$night=$request->usage['electricity']['night'];
        $query['meterType']='double';
        $electricity=1;
        }
        if(isset($request->usage['electricity']['single']) && isset($request->usage['electricity']['excl_night']))
        {
        $query['registerNormal']=$single=$request->usage['electricity']['single'];
        $query['registerExclNight']=$excl_night=$request->usage['electricity']['excl_night'];
        $query['meterType']='single_excl_night';
        $electricity=1;
        }
        if(isset($request->usage['electricity']['day']) && isset($request->usage['electricity']['night']) && isset($request->usage['electricity']['excl_night']))
        {
        $query['registerDay']=$day=$request->usage['electricity']['day'];
        $query['registerNight']=$night=$request->usage['electricity']['night'];
        $query['registerExclNight']=$excl_night=$request->usage['electricity']['excl_night'];
        $query['meterType']='double_excl_night';
        $electricity=1;
        }
        if(isset($request->usage['gas']))
        {
        $query['registerG']=$gasC=$request->usage['gas'];
        $gas=1;
        }
        $sumE=$single+$day+$night+$excl_night;
        $sumG=$gasC;
        $elec_id="";
        $gas_id="";
        if(isset($request->current_contract['pack_id']))
        {
            if($request->customer_type=='residential'){
                $pack=StaticPackResidential::where('pack_id',$request->current_contract['pack_id'])->first();
            }else{
                $pack=StaticPackProfessional::where('pack_id',$request->current_contract['pack_id'])->first();
            }
            if($pack==null)
            {
                $response = 'Sorry!.Product id is not avilable.';
                return  response()->json(['response'=>'error','comments' => $response],422);
                exit();
            }
                $elec_id=$pack->pro_id_E;
                $gas_id=$pack->pro_id_G;
                $product_id=$request->current_contract['pack_id'];
            if($sumE!=0 && $sumG!=0)
            {
                $query['category']=$category='pack';
            }
            if($sumE!=0 && $sumG==0)
            {
                $query['category']=$category='electricity';
                $product_id=$elec_id;
            }
            if($sumE==0 && $sumG!=0)
            {
                $query['category']=$category='gas';
                $product_id=$gas_id;
            }
        }

        if(isset($request->current_contract['electricity_product_id']))
        {
            $elec_id=$request->current_contract['electricity_product_id'];
            $query['category']=$category='electricity';
            $product_id=$request->current_contract['electricity_product_id'];
                if($electricity==0){
                    $response = 'Inputs for electricity is not completed.';
                    return  response()->json(['response'=>'error','comments' => $response],422);
                    exit();
                }
        }

        if(isset($request->current_contract['gas_product_id']))
        {
            $gas_id=$request->current_contract['gas_product_id'];
            $query['category']=$category='gas';
            $product_id=$request->current_contract['gas_product_id'];
            if($gas==0)
            {
                $response = 'Inputs for gas is not completed.';
                return  response()->json(['response'=>'error','comments' => $response],422);
                exit();
            }
        }

        if(isset($request->current_contract['electricity_product_id']) && isset($request->current_contract['gas_product_id']))
        {
            if($request->customer_type=='residential')
            {
                $pack=StaticPackResidential::where('pro_id_E',$request->current_contract['electricity_product_id'])->where('pro_id_G',$request->current_contract['gas_product_id'])->get();
            }else
            {
                $pack=StaticPackProfessional::where('pro_id_E',$request->current_contract['electricity_product_id'])->where('pro_id_G',$request->current_contract['gas_product_id'])->get();
            }
                if($pack){
                    foreach($pack as $packs){
                        $product_id=$packs->pack_id;  
                    }
                    $query['category']=$category='pack';
                }
        }
        $query['checkup']='checkup';
        
        try 
        {
                $client = new \GuzzleHttp\Client(); 
                $request = $client->post('https://api.tariefchecker.be/api/checkup-calculation', [
                'headers' => [
                'Accept' => 'application/json',
                'Content-type' => 'application/x-www-form-urlencoded',
                'Authorization' => 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiMWFmMjVkYWNmZTNiM2I0MmZjOTJkMTU5MjIxY2RjNjNkY2MxMzEwZWU3NDJlM2YzNmRiOWZiMDZhZmMwNGMyNTgyNzEzNjRhYjU5Y2VkZGQiLCJpYXQiOjE2NDMyODY2MjcsIm5iZiI6MTY0MzI4NjYyNywiZXhwIjoyMjc0NDM4NjI3LCJzdWIiOiI1Iiwic2NvcGVzIjpbXX0.rmiDd2sM0kduf6CPed5rjbqPL4Fui-MDdOKViiPn49pcJEukW_kA2ByuJfHNUIe9rctIXsovX1T8kgeer6TxgQkGCvruO98zcGklVv470en8ul6NzOCMmaemX4cJj4XQYlcI1_-z6tnHqtbbc7_-TyvezidDGslhMAMmtREicgrubnp9VGyl6YtE_pXHedruJ7PxYsc2_Gqu-osdFOdEW6hxN3uPlpKbuHgrf9DvJr8B3PSDQLPl49Q9HzrL-vPgayZTpNFINpCw1QBKk_ooWo861UZQ_cE33TdNfXyoJ5WnXQ-AjvtInfw7C9skq57C9X4NmfsllWCacNn9IYNs4uocuFo259TbRXNuooHsWTDTty4kalcp3LD7G0exCTTDC3_QsEoZI6694ct8Fi0gOJ05thoS5grKIfKyFkRqu1eOS2wMdNs-6KZXwVQ6fv1sJE-VjdIKXoj-r6wo_FPSceB599yz22gwVQLnDQJAvu0OahSyU8DG3VMH__ItYBuTI0uOTJZerwaRwmnTkSWbWczA4c8AEb1H_W-G4Yblh4D9y_ZOW7FvvFj53dCX83mzUyBN3HahqzD8ZX0IvZXolZHLxluIOlFoR9HiLNzTZFrJSzWru39AjNmbaK8-AAydGlF606uGglo76ES7D7dOvDa5lgUjYzRvby8jDpRSkzY'],
                'query' => $query 
                ]);
                $response = $request->getBody()->getContents();       
                $json = json_decode($response, true);

        } catch (\Exception $e) 
        {
                $response = ['status' => false, 'message' => $e->getMessage()];
        }


        if(!isset($json)){
                $response = 'Something went wrong';
                return  response()->json(['response'=>'error','comments' => $response],422);
                exit();
        }
        $collection = collect($json['products']);
        $filter = $collection->filter(function($value, $key) use ($collection,$product_id) 
        {
                $satisfied = 0;
                $total_condition = 0;
                $total_condition++;
                if($value['product']['id']==$product_id){
                $satisfied ++;  
                }
                return $satisfied == $total_condition;
        });
        $getProducts=$products = $filter->first();
        if($getProducts==null){
                $response = 'Sorry!.Product id is not avilable.';
                return  response()->json(['response'=>'error','comments' => $response],422);
                exit();

        }
        if($category=='pack')
        {
            $fixed_fee= $getProducts['price']['breakdown']['electricity']['energy_cost']['fixed_fee'] + $getProducts['price']['breakdown']['gas']['energy_cost']['fixed_fee'];
            $fixed_fee_electricity = $getProducts['price']['breakdown']['electricity']['energy_cost']['fixed_fee'];
            $fixed_fee_gas = $getProducts['price']['breakdown']['gas']['energy_cost']['fixed_fee'];

            $energy_costs= ($getProducts['price']['breakdown']['electricity']['energy_cost']['fixed_fee']+$getProducts['price']['breakdown']['electricity']['energy_cost']['certificates']+$getProducts['price']['breakdown']['electricity']['energy_cost']['single']+$getProducts['price']['breakdown']['electricity']['energy_cost']['day']+$getProducts['price']['breakdown']['electricity']['energy_cost']['night']+$getProducts['price']['breakdown']['electricity']['energy_cost']['excl_night'])+($getProducts['price']['breakdown']['gas']['energy_cost']['fixed_fee']+$getProducts['price']['breakdown']['gas']['energy_cost']['usage']);
            $discounts= $getProducts['price']['totals']['year']['excl_promo']-$getProducts['price']['totals']['year']['incl_promo'];
        }

        if($category=='electricity'){

        $fixed_fee= $getProducts['price']['breakdown']['electricity']['energy_cost']['fixed_fee'];
        $energy_costs= ($getProducts['price']['breakdown']['electricity']['energy_cost']['fixed_fee']+$getProducts['price']['breakdown']['electricity']['energy_cost']['certificates']+$getProducts['price']['breakdown']['electricity']['energy_cost']['single']+$getProducts['price']['breakdown']['electricity']['energy_cost']['day']+$getProducts['price']['breakdown']['electricity']['energy_cost']['night']+$getProducts['price']['breakdown']['electricity']['energy_cost']['excl_night']);
        $discounts= $getProducts['price']['totals']['year']['excl_promo']-$getProducts['price']['totals']['year']['incl_promo'];

        }

        if($category=='gas'){

        $fixed_fee= $getProducts['price']['breakdown']['gas']['energy_cost']['fixed_fee'];
        $energy_costs= $getProducts['price']['breakdown']['gas']['energy_cost']['usage'];
        $discounts= $getProducts['price']['totals']['year']['excl_promo']-$getProducts['price']['totals']['year']['incl_promo'];

        }


        $checkup=[
        "scenario"=> "B1"
        ];
        $checkup['variables']=[

        "time_zone"=> "Europe/Brussels",
        "sign_date"=> date("Y-m-d"),
        "start_date"=> date("Y-m-d"),
        "current_date"=> date("Y-m-d"),
        "contract_duration"=> 0.12,
        "contract_renewal"=> false

        ];

        if($category=='pack')
        {
            $checkup['current_contract']=[
            "fixed_fee"=> floatval($fixed_fee),
            "fixed_fee_electricity"=> floatval($fixed_fee_electricity),
            "fixed_fee_gas"=> floatval($fixed_fee_gas),
            "energy_costs"=> floatval($energy_costs),
            "discounts"=> floatval($discounts)
            ];
        }else
        {
            $checkup['current_contract']=[
            "fixed_fee"=> floatval($fixed_fee),
            "energy_costs"=> floatval($energy_costs),
            "discounts"=> floatval($discounts)
            ];
        }



        $replace = ['(FIRSTNAME)','(LASTNAME)','(POSTCODE)','(MONTHLYGAS)','(MONTHLYELEK)','(USAGE_G)','(USAGE_E)','(EMAIL)','(ElecMeterType)','(USAGE_E_NIGHT)','(USAGE_E_EXCL_NIGHT)','(DISCOUNTCODE_E)','DISCOUNTCODE_G'];
        $info = [
        'FIRSTNAME' => "",
        'LASTNAME' => "",
        'POSTCODE' =>'', 
        'MONTHLYGAS'=> "", 
        'MONTHLYELEK'=> "",
        'USAGE_G' => "",
        'USAGE_E' => "",
        'EMAIL'=> "",
        'ElecMeterType'=> "",
        'USAGE_E_NIGHT'=> "",
        'USAGE_E_EXCL_NIGHT'=> "",
        'DISCOUNTCODE_E'=>"",
        'DISCOUNTCODE_G'=>""
        ];
        $subscribe_url=str_replace($replace, $info, $getProducts['product']['subscribe_url']);

        $id= $getProducts['product']['id'];
        $product_type= $getProducts['product']['type'];
        $supplier_name= $getProducts['supplier']['name'];
        $product_name= $getProducts['product']['name'];
        $subscribe_url= $subscribe_url;
        //$prices_url= $getProducts['product']['terms_url'];

        $checkup['current_contract']['product']=[

        "id"=> $id,
        "product_type"=> $product_type,
        "supplier_name"=> $supplier_name,
        "product_name"=> $product_name,
        "subscribe_url"=> $subscribe_url
        //"prices_url"=>$prices_url
        ];

        if($category=='pack'){

        $id= $getProducts['product']['underlying_products']['electricity']['id'];
        $product_name= $getProducts['product']['underlying_products']['electricity']['name'];
        $prices_url= $getProducts['product']['underlying_products']['electricity']['terms_url_dynamic'];


        $checkup['current_contract']['product']['electricity']=[

        "id"=> $id,
        "product_name"=> $product_name,
        "prices_url"=>$prices_url
        ];

        $id= $getProducts['product']['underlying_products']['gas']['id'];
        $product_name= $getProducts['product']['underlying_products']['gas']['name'];
        $prices_url= $getProducts['product']['underlying_products']['gas']['terms_url_dynamic'];

        $checkup['current_contract']['product']['gas']=[

        "id"=> $id,
        "product_name"=> $product_name,
        "prices_url"=>$prices_url
        ];

        }

        //  if($category=='electricity'){

        //     $id= $getProducts['product']['id'];
        //     $product_name= $getProducts['product']['name'];
        //     $prices_url= $getProducts['product']['terms_url'];

        //  }

        //  if($category=='gas'){

        //     $id= $getProducts['product']['id'];
        //     $product_name= $getProducts['product']['name'];
        //     $prices_url= $getProducts['product']['terms_url'];

        //  }


        if($category=='pack'){

        $checkup['current_contract']['energy_cost_breakdown']=[

        "gas"=> floatval($getProducts['price']['breakdown']['gas']['energy_cost']['usage'])

        ];

        }
        if($category=='electricity'){
        $checkup['current_contract']['energy_cost_breakdown']=[

        "gas"=> 0.0

        ];

        }
        if($category=='gas'){

        $checkup['current_contract']['energy_cost_breakdown']=[

        "gas"=> floatval($getProducts['price']['breakdown']['gas']['energy_cost']['usage'])
        ];


        }



        if($category=='pack'){

        $checkup['current_contract']['energy_cost_breakdown']['electricity']=[

        "certificate"=> floatval($getProducts['price']['breakdown']['electricity']['energy_cost']['certificates']),
        "single"=> floatval($getProducts['price']['breakdown']['electricity']['energy_cost']['single']),
        "day"=> floatval($getProducts['price']['breakdown']['electricity']['energy_cost']['day']),
        "night"=> floatval($getProducts['price']['breakdown']['electricity']['energy_cost']['night']),
        "excl_night"=> floatval($getProducts['price']['breakdown']['electricity']['energy_cost']['excl_night'])
        ];

        }
        if($category=='electricity'){

        $checkup['current_contract']['energy_cost_breakdown']['electricity']=[

        "certificate"=> floatval($getProducts['price']['breakdown']['electricity']['energy_cost']['certificates']),
        "single"=> floatval($getProducts['price']['breakdown']['electricity']['energy_cost']['single']),
        "day"=> floatval($getProducts['price']['breakdown']['electricity']['energy_cost']['day']),
        "night"=> floatval($getProducts['price']['breakdown']['electricity']['energy_cost']['night']),
        "excl_night"=> floatval($getProducts['price']['breakdown']['electricity']['energy_cost']['excl_night'])
        ];

        }

        if($category=='gas'){

        $checkup['current_contract']['energy_cost_breakdown']['electricity']=[

        "certificate"=> 0.0,
        "single"=> 0.0,
        "day"=> 0.0,
        "night"=> 0.0,
        "excl_night"=> 0.0
        ];
        }

                                $ele_disc="0";
                                $gas_disc="0";
                                foreach($getProducts['price']['breakdown']['discounts'] as $disc){
                                
                                if($disc['parameters']['fuel_type']=='electricity'){
                                
                                $ele_disc=$ele_disc+$disc['amount'];
                                }
                                 if($disc['parameters']['fuel_type']=='gas'){
                                
                                $gas_disc=$gas_disc+$disc['amount'];
                                }
                                 
                                }
                                
                                if(isset($ele_disc)){
                                    $ele_disc=$ele_disc;
                                }else{
                                    $ele_disc=0.0;
                                }
                                
                                if(isset($gas_disc)){
                                    $gas_disc=$gas_disc;
                                }else{
                                    $gas_disc=0.0;
                                }

        $checkup['current_contract']['discount_breakdown']=[

        "electricity"=> $ele_disc,
        "gas"=> $gas_disc

        ];

        // current-contract-end






        //  $filter = $collection->filter(function($value, $key) use ($collection) {
        // $satisfied = 0;
        // $total_condition = 0;
        // $total_condition++;
        // if($value['supplier']['name']==$supplier_name){
        //           $satisfied ++;  
        //       }
        //     return $satisfied == $total_condition;
        // });
        // $getProducts=$products = $filter->all();
        // $getProducts=collect($getProducts);

        $getProducts = $collection->sortBy(function ($item, $key) {
        return $item['price']['totals']['year']['incl_promo'];
        })->first();








        // proposed-start


        if($category=='pack'){

        $fixed_fee= $getProducts['price']['breakdown']['electricity']['energy_cost']['fixed_fee']+$getProducts['price']['breakdown']['gas']['energy_cost']['fixed_fee'];
        $energy_costs= ($getProducts['price']['breakdown']['electricity']['energy_cost']['fixed_fee']+$getProducts['price']['breakdown']['electricity']['energy_cost']['certificates']+$getProducts['price']['breakdown']['electricity']['energy_cost']['single']+$getProducts['price']['breakdown']['electricity']['energy_cost']['day']+$getProducts['price']['breakdown']['electricity']['energy_cost']['night']+$getProducts['price']['breakdown']['electricity']['energy_cost']['excl_night'])+($getProducts['price']['breakdown']['gas']['energy_cost']['fixed_fee']+$getProducts['price']['breakdown']['gas']['energy_cost']['usage']);
        $discounts= $getProducts['price']['totals']['year']['excl_promo']-$getProducts['price']['totals']['year']['incl_promo'];



        }

        if($category=='electricity'){

        $fixed_fee= $getProducts['price']['breakdown']['electricity']['energy_cost']['fixed_fee'];
        $energy_costs= ($getProducts['price']['breakdown']['electricity']['energy_cost']['fixed_fee']+$getProducts['price']['breakdown']['electricity']['energy_cost']['certificates']+$getProducts['price']['breakdown']['electricity']['energy_cost']['single']+$getProducts['price']['breakdown']['electricity']['energy_cost']['day']+$getProducts['price']['breakdown']['electricity']['energy_cost']['night']+$getProducts['price']['breakdown']['electricity']['energy_cost']['excl_night']);
        $discounts= $getProducts['price']['totals']['year']['excl_promo']-$getProducts['price']['totals']['year']['incl_promo'];

        }

        if($category=='gas'){

        $fixed_fee= $getProducts['price']['breakdown']['gas']['energy_cost']['fixed_fee'];
        $energy_costs= ($getProducts['price']['breakdown']['gas']['energy_cost']['fixed_fee']+$getProducts['price']['breakdown']['gas']['energy_cost']['usage']);
        $discounts= $getProducts['price']['totals']['year']['excl_promo']-$getProducts['price']['totals']['year']['incl_promo'];

        }


        $checkup['proposed_contract']=[

        "valid_until"=> $getProducts['price']['validity_period']['end'],
        "fixed_fee"=> floatval($fixed_fee),
        "energy_costs"=> floatval($energy_costs),
        "discounts"=> floatval($discounts)
        ];




        $id= $getProducts['product']['id'];
        $product_type= $getProducts['product']['type'];
        $supplier_name= $getProducts['supplier']['name'];
        $product_name= $getProducts['product']['name'];
        $subscribe_url= $getProducts['product']['subscribe_url'];
        //$prices_url= $getProducts['product']['terms_url'];


        $checkup['proposed_contract']['product']=[

        "id"=> $id,
        "product_type"=> $product_type,
        "supplier_name"=> $supplier_name,
        "product_name"=> $product_name,
        "subscribe_url"=> $subscribe_url
        //'prices_url'=>$prices_url
        ];




        if($category=='pack'){

        $id= $getProducts['product']['underlying_products']['electricity']['id'];
        $product_name= $getProducts['product']['underlying_products']['electricity']['name'];
        $prices_url= $getProducts['product']['underlying_products']['electricity']['terms_url_dynamic'];


        $checkup['proposed_contract']['product']['electricity']=[

        "id"=> $id,
        "product_name"=> $product_name,
        "prices_url"=>$prices_url
        ];

        $id= $getProducts['product']['underlying_products']['gas']['id'];
        $product_name= $getProducts['product']['underlying_products']['gas']['name'];
        $prices_url= $getProducts['product']['underlying_products']['gas']['terms_url_dynamic'];

        $checkup['proposed_contract']['product']['gas']=[

        "id"=> $id,
        "product_name"=> $product_name,
        "prices_url"=>$prices_url
        ];

        }


        // $checkup['proposed_contract']['product']['electricity']=[

        //     "id"=> "MEG-PAR-GROUPIND-1-E",
        //     "product_name"=> "Group Variabel",
        //     "prices_url"=> "http://www.tariefchecker.be/energie/tariefkaarten/2020/03-maart/Mega/particulieren/elektriciteit/mega-group-VLA-NL.pdf"
        // ];





        // $checkup['proposed_contract']['product']['gas']=[

        //   "id"=> "MEG-PAR-GROUPIND-1-G",
        //     "product_name"=> "Group Variabel",
        //     "prices_url"=> "http://www.tariefchecker.be/energie/tariefkaarten/2020/03-maart/Mega/particulieren/aardgas/mega-group-VLA-NL.pdf"
        // ];




        // $checkup['proposed_contract']['energy_cost_breakdown']=[
        //     "gas"=> 21450.0
        //     ];
        if($category=='pack'){

        $checkup['proposed_contract']['energy_cost_breakdown']=[

        "gas"=> floatval($getProducts['price']['breakdown']['gas']['energy_cost']['usage'])

        ];

        }
        if($category=='electricity'){
        $checkup['proposed_contract']['energy_cost_breakdown']=[

        "gas"=> 0.0

        ];

        }
        if($category=='gas'){

        $checkup['proposed_contract']['energy_cost_breakdown']=[

        "gas"=> floatval($getProducts['price']['breakdown']['gas']['energy_cost']['usage'])
        ];


        }




        if($category=='pack'){

        $checkup['proposed_contract']['energy_cost_breakdown']['electricity']=[

        "certificate"=> floatval($getProducts['price']['breakdown']['electricity']['energy_cost']['certificates']),
        "single"=> floatval($getProducts['price']['breakdown']['electricity']['energy_cost']['single']),
        "day"=> floatval($getProducts['price']['breakdown']['electricity']['energy_cost']['day']),
        "night"=> floatval($getProducts['price']['breakdown']['electricity']['energy_cost']['night']),
        "excl_night"=> floatval($getProducts['price']['breakdown']['electricity']['energy_cost']['excl_night'])
        ];

        }
        if($category=='electricity'){

        $checkup['proposed_contract']['energy_cost_breakdown']['electricity']=[

        "certificate"=> floatval($getProducts['price']['breakdown']['electricity']['energy_cost']['certificates']),
        "single"=> floatval($getProducts['price']['breakdown']['electricity']['energy_cost']['single']),
        "day"=> floatval($getProducts['price']['breakdown']['electricity']['energy_cost']['day']),
        "night"=> floatval($getProducts['price']['breakdown']['electricity']['energy_cost']['night']),
        "excl_night"=> floatval($getProducts['price']['breakdown']['electricity']['energy_cost']['excl_night'])
        ];

        }

        if($category=='gas'){

        $checkup['proposed_contract']['energy_cost_breakdown']['electricity']=[

        "certificate"=> 0.0,
        "single"=> 0.0,
        "day"=> 0.0,
        "night"=> 0.0,
        "excl_night"=> 0.0
        ];
        }   

        // $checkup['proposed_contract']['energy_cost_breakdown']['electricity']=[

        //     "certificate"=> 17730.0,
        //     "single"=> 26280.0,
        //     "day"=> 0.0,
        //     "night"=> 0.0,
        //     "excl_night"=> 0.0
        // ];


        $ele_disc="0";
                                $gas_disc="0";
                                foreach($getProducts['price']['breakdown']['discounts'] as $disc){
                                
                                if($disc['parameters']['fuel_type']=='electricity'){
                                
                                $ele_disc=$ele_disc+$disc['amount'];
                                }
                                 if($disc['parameters']['fuel_type']=='gas'){
                                
                                $gas_disc=$gas_disc+$disc['amount'];
                                }
                                 
                                }
                                
                                if(isset($ele_disc)){
                                    $ele_disc=$ele_disc;
                                }else{
                                    $ele_disc=0.0;
                                }
                                
                                if(isset($gas_disc)){
                                    $gas_disc=$gas_disc;
                                }else{
                                    $gas_disc=0.0;
                                }

        $checkup['proposed_contract']['discount_breakdown']=[

        "electricity"=> $ele_disc,
        "gas"=> $gas_disc

        ];



        // $checkup['proposed_contract']['discount_breakdown']=[
        //  "electricity"=> 15000.0,
        //  "gas"=> 14000.0
        // ];


        // proposed-end



        $checkup['impact']=[
        "market_evolution"=> -46790.0,
        "fixed_fee"=> 0.0,
        "discounts"=> 0.0,
        "total"=> -46790.0
        ];






        return  response()->json($checkup); 

        //return $checkup;

        }



        function generateUUID($length = 36) {


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

        public function calculate(Request $request)
        {






        $this->validate($request,[
        'locale'=>'required',
        'postalCode'=>'required',
        'customerGroup'=>'required',        


        ]);

        if(isset($request->registerNormal)){
        $registerNormal=$request->registerNormal;
        }else{
        $registerNormal=0;
        }

        if(isset($request->registerDay)){
        $registerDay=$request->registerDay;
        }else{
        $registerDay=0;
        }

        if(isset($request->registerNight)){
        $registerNight=$request->registerNight;
        }else{
        $registerNight=0;
        }

        if(isset($request->registerExclNight)){
        $registerExclNight=$request->registerExclNight;
        }else{
        $registerExclNight=0;
        }

        if(isset($request->registerG)){
        $registerG=$request->registerG;
        }else{
        $registerG=0;
        }

        $locale=$request->locale; //fr
        $postalCode=$request->postalCode;
        $customerGroup=$request->customerGroup; //professional 
        $Metertype=$request->meterType; 

        $includeG=false;
        $includeE=false;
        $first_residence=false;
        $decentralise_production=false;
        $capacity_decentalise=0;
        $currentSupplierE=null;
        $currentSupplierG=null;


        $CurrentSupplierE=$request->CurrentSupplierE;




        if(isset($request->uuid)){

        $pev_data=SearchDetail::where('uuid',$request->uuid)->latest('created_at')->first();

        if(isset($request->restore)){

        if($pev_data){
                                    if($pev_data->single!=0 || $pev_data->day!=0 || $pev_data->night!=0 || $pev_data->excl_night!=0){
                                        $registerNormal=$pev_data->single;
                                        $registerDay=$pev_data->day;
                                        $registerNight=$pev_data->night;
                                        $registerExclNight=$pev_data->excl_night;
                                            if($pev_data->meter_type){
                                                $Metertype=$pev_data->meter_type;
                                            }else{
                                                if($registerDay!=0 && $registerNight!=0 && $registerExclNight==0 ){
                                                    $Metertype='double';
                                                }elseif($registerNormal!=0 && $registerDay==0 && $registerNight==0 && $registerExclNight==0){
                                                    $Metertype='single';
                                                }elseif($registerNormal!=0 && $registerDay==0 && $registerNight==0 && $registerExclNight!=0){
                                                    $Metertype='single_excl_night';
                                                }elseif($registerNormal==0 && $registerDay!=0 && $registerNight!=0 && $registerExclNight!=0){
                                                    $Metertype='double_excl_night';
                                                }else{
                                                    $Metertype='single';
                                                }
                                            }
                                        $first_residence=$pev_data->first_residence;
                                        $decentralise_production=$pev_data->decentralise_production;
                                        $capacity_decentalise=$pev_data->capacity_decentalise;
                                                if($pev_data->current_electric_supplier){
                                                    $currentSupplierE=$pev_data->current_electric_supplier;
                                                }else{
                                                    $currentSupplierE=null;
                                                }
                                                if($pev_data->current_gas_supplier){
                                                    $currentSupplierG=$pev_data->current_gas_supplier;
                                                }else{
                                                    $currentSupplierG=null;
                                                }
                                        $IncludeG=$pev_data->includeG;
                                        $IncludeE=$pev_data->includeE;
                                        $email=$pev_data->email;
                                    }else{
                                        $registerNormal=3500;
                                        $registerDay=0;
                                        $registerNight=0;
                                        $registerExclNight=0;
                                        $Metertype='single'; 
                                        $IncludeG=1;
                                        $IncludeE=1;
                                    }
                                    if($pev_data->gas_consumption!=0){
                                    $registerG=$pev_data->gas_consumption;
                                    $IncludeG=$pev_data->includeG;
                                        $IncludeE=$pev_data->includeE;
                                    }else{
                                    $registerG=25000; 
                                    $IncludeG=1;
                                    $IncludeE=1;
                                    }
                            }else{
                                    $registerNormal=3500;
                                    $registerDay=0;
                                    $registerNight=0;
                                    $registerExclNight=0;
                                    $first_residence=1;
                                    $decentralise_production=0;
                                    $capacity_decentalise=0;
                                    $registerG=25000; 
                                    $Metertype='single';
                                    $IncludeG=1;
                                    $IncludeE=1;
                            }



        }

        if(isset($request->category)){

        // default-input-section-start 
            if($request->category=='pack'){
                    if(isset($request->registerNormal) || isset($request->registerDay) || isset($request->registerNight) || isset($request->registerExclNight) || isset($request->registerG)){
                            $registerNormal=$request->registerNormal;
                            $registerDay=$request->registerDay;
                            $registerNight=$request->registerNight;
                            $registerExclNight=$request->registerExclNight;
                            $registerG=$request->registerG;
                    
                    }else{
                            if($pev_data){
                                    if($pev_data->single!=0 || $pev_data->day!=0 || $pev_data->night!=0 || $pev_data->excl_night!=0){
                                        $registerNormal=$pev_data->single;
                                        $registerDay=$pev_data->day;
                                        $registerNight=$pev_data->night;
                                        $registerExclNight=$pev_data->excl_night;
                                            if($pev_data->meter_type){
                                                $Metertype=$pev_data->meter_type;
                                            }else{
                                                if($registerDay!=0 && $registerNight!=0 && $registerExclNight==0 ){
                                                    $Metertype='double';
                                                }elseif($registerNormal!=0 && $registerDay==0 && $registerNight==0 && $registerExclNight==0){
                                                    $Metertype='single';
                                                }elseif($registerNormal!=0 && $registerDay==0 && $registerNight==0 && $registerExclNight!=0){
                                                    $Metertype='single_excl_night';
                                                }elseif($registerNormal==0 && $registerDay!=0 && $registerNight!=0 && $registerExclNight!=0){
                                                    $Metertype='double_excl_night';
                                                }else{
                                                    $Metertype='single';
                                                }
                                            }
                                        $first_residence=$pev_data->first_residence;
                                        $decentralise_production=$pev_data->decentralise_production;
                                        $capacity_decentalise=$pev_data->capacity_decentalise;
                                                if($pev_data->current_electric_supplier){
                                                    $currentSupplierE=$pev_data->current_electric_supplier;
                                                }else{
                                                    $currentSupplierE=null;
                                                }
                                                if($pev_data->current_gas_supplier){
                                                    $currentSupplierG=$pev_data->current_gas_supplier;
                                                }else{
                                                    $currentSupplierG=null;
                                                }
                                        $IncludeG=1;
                                        $IncludeE=1;
                                    }else{
                                        $registerNormal=3500;
                                        $registerDay=0;
                                        $registerNight=0;
                                        $registerExclNight=0;
                                        $Metertype='single'; 
                                        $IncludeG=1;
                                        $IncludeE=1;
                                    }
                                    if($pev_data->gas_consumption!=0){
                                    $registerG=$pev_data->gas_consumption;
                                    $IncludeE=1;
                                    $IncludeG=1;
                                    }else{
                                    $registerG=25000; 
                                    $IncludeG=1;
                                    $IncludeE=1;
                                    }
                            }else{
                                    $registerNormal=3500;
                                    $registerDay=0;
                                    $registerNight=0;
                                    $registerExclNight=0;
                                    $first_residence=1;
                                    $decentralise_production=0;
                                    $capacity_decentalise=0;
                                    $registerG=25000; 
                                    $Metertype='single';
                                    $IncludeG=1;
                                    $IncludeE=1;
                            }
                    }
            }

            if($request->category=='electricity'){
                    if(isset($request->registerNormal) || isset($request->registerDay) || isset($request->registerNight) || isset($request->registerExclNight)){
                            $registerNormal=$request->registerNormal;
                            $registerDay=$request->registerDay;
                            $registerNight=$request->registerNight;
                            $registerExclNight=$request->registerExclNight;
                            $Metertype=$Metertype;
                            $IncludeG=0;
                            $IncludeE=1;
                    }else{
                            if($pev_data){
                                    if($pev_data->single!=0 || $pev_data->day!=0 || $pev_data->night!=0 || $pev_data->excl_night!=0){
                                            $registerNormal=$pev_data->single;
                                            $registerDay=$pev_data->day;
                                            $registerNight=$pev_data->night;
                                            $registerExclNight=$pev_data->excl_night;
                                            $first_residence=$pev_data->first_residence;
                                                if($pev_data->current_electric_supplier){
                                                    $currentSupplierE=$pev_data->current_electric_supplier;
                                                }else{
                                                    $currentSupplierE=null;
                                                }
                                                if($pev_data->current_gas_supplier){
                                                    $currentSupplierG=$pev_data->current_gas_supplier;
                                                }else{
                                                    $currentSupplierG=null;
                                                }
                                                if($pev_data->meter_type){
                                                    $Metertype=$pev_data->meter_type;
                                                }else{
                                                    if($registerDay!=0 && $registerNight!=0 && $registerExclNight==0 ){
                                                        $Metertype='double';
                                                    }elseif($registerNormal!=0 && $registerDay==0 && $registerNight==0 && $registerExclNight==0){
                                                        $Metertype='single';
                                                    }elseif($registerNormal!=0 && $registerDay==0 && $registerNight==0 && $registerExclNight!=0){
                                                        $Metertype='single_excl_night';
                                                    }elseif($registerNormal==0 && $registerDay!=0 && $registerNight!=0 && $registerExclNight!=0){
                                                        $Metertype='double_excl_night';
                                                    }else{
                                                        $Metertype='single';
                                                    }
                                                }
                                            $decentralise_production=$pev_data->decentralise_production;
                                            $capacity_decentalise=$pev_data->capacity_decentalise;
                                                        $IncludeE=1;
                                                        $IncludeG=0;
                                    }else{
                                            $registerNormal=3500;
                                            $registerDay=0;
                                            $registerNight=0;
                                            $registerExclNight=0;
                                            $Metertype='single';
                                            $IncludeE=1;
                                            $IncludeG=0;
                                            $decentralise_production=0;
                                            $capacity_decentalise=0;
                                    }
                                            $registerG=0;
                                }else{
                                            $registerNormal=3500;
                                            $registerDay=0;
                                            $registerNight=0;
                                            $registerExclNight=0;
                                            $Metertype='single';
                                            $IncludeE=1;
                                            $IncludeG=0;
                                            $registerG=0;
                                            $decentralise_production=0;
                                            $capacity_decentalise=0;
                                }
                    }
            }

            if($request->category=='gas'){
                    if($pev_data){
                            if($pev_data->gas_consumption!=0){
                                    $registerG=$pev_data->gas_consumption;
                                    $IncludeE=0;
                                    $decentralise_production=$pev_data->decentralise_production;
                                    $capacity_decentalise=$pev_data->capacity_decentalise;
                                        if($pev_data->current_electric_supplier){
                                            $currentSupplierE=$pev_data->current_electric_supplier;
                                        }else{
                                            $currentSupplierE=null;
                                        }
                                        if($pev_data->current_gas_supplier){
                                            $currentSupplierG=$pev_data->current_gas_supplier;
                                        }else{
                                            $currentSupplierG=null;
                                        }
                                    $IncludeG=1;
                            }else{
                                    $registerG=25000;
                                    $IncludeE=0;
                                    $decentralise_production=0;
                                    $capacity_decentalise=0;
                                    $IncludeG=1;
                            }
                    }
            }
        // default-input-section-end  

        }else{

        //change-your-data-input

        if(!isset($request->IncludeE) && !isset($request->IncludeG) ){
        $registerDay=0;
        $registerNight=0;
        $registerExclNight=0;
        $registerNormal=3500;
        $registerG=25000; 
        $Metertype='single';
        $decentralise_production=0;
        $capacity_decentalise=0;
        }else{
        if($request->IncludeE==1 ){
            if($request->meterType=='double'){
                $registerDay=$request->registerDay;
                $registerNight=$request->registerNight;
                $registerExclNight=0;
                $registerNormal=0;
            }elseif($request->meterType=='single'){
                $registerNormal=$request->registerNormal;
                $registerDay=0;
                $registerNight=0;
                $registerExclNight=0;
            }elseif($request->meterType=='single_excl_night'){
                $registerNormal=$request->registerNormal;
                $registerExclNight=$request->registerExclNight;
                $registerDay=0;
                $registerNight=0;
            }elseif($request->meterType=='double_excl_night'){
                $registerDay=$request->registerDay;
                $registerNight=$request->registerNight;
                $registerExclNight=$request->registerExclNight;
                $registerNormal=0;
            }else{
            
            }
            
            $IncludeE=1;
            if($request->currentSupplierE){
                $currentSupplierE=$request->CurrentSupplierE;
            }else{
                $currentSupplierE=null;
            }
            
             if($request->CurrentSupplierE){
                    $currentSupplierE=$request->CurrentSupplierE;
                }else{
                    $currentSupplierE=null;
             }
            
            
            
            
        }else{
            $registerNormal=0;
            $registerExclNight=0;
            $registerDay=0;
            $registerNight=0;
            $IncludeE=0;
        }
        if($request->IncludeG==1){
            $IncludeG= $request->IncludeG; 
                if($IncludeG==true){
                    $registerG=$request->registerG;
                }else{
                     $registerG=0; 
                }
                 $IncludeG=1;
                if($request->CurrentSupplierG){
                    $currentSupplierG=$request->CurrentSupplierG;
                }else{
                    $currentSupplierG=null;
                }
               
        }else{
                $IncludeG=0;
        }
        }
        }

        if(isset($request->email)){
        $email=$request->email;
        }else{
        if($pev_data){
        $email=$pev_data->email;
        }else{
        $email=null;
        }
        }

        $uuid=$request->uuid;
        if($pev_data){
        $includeG=$pev_data->includeG;
        $includeE=$pev_data->includeE;
        $first_residence=$pev_data->first_residence;
        $decentralise_production=$pev_data->decentralise_production;
        $capacity_decentalise=$pev_data->capacity_decentalise;
        }
        }else{



        // without-uuid



        if($request->category=='pack'){
        $registerNormal=3500;
        $registerDay=0;
        $registerNight=0;
        $registerExclNight=0;
        $registerG=25000;
        $Metertype='single';
        $first_residence=1;
        $IncludeG=1;
        $IncludeE=1;
        }
        if($request->category=='electricity'){
        $registerNormal=3500;
        $Metertype='single';
        $first_residence=1;
        $IncludeG=0;
        $IncludeE=1;
        }
        if($request->category=='gas'){
        $registerG=25000;
        $first_residence=1;
        $IncludeG=1;
        $IncludeE=0;
        }
        $email=$request->email;
        $uuid=$this->generateUUID();

        // without-uuid-end
        }

        if(isset($request->IncludeG)){

        $IncludeG= $request->IncludeG; 
        if($IncludeG==1){
        $registerG=$request->registerG;
        $IncludeG=1;
        }else{
        $registerG=0;
        $IncludeG=0;
        }
        }

        if(isset($request->checkup)){

                                    $registerNormal=$request->registerNormal;
                                    $registerDay=$request->registerDay;
                                    $registerNight=$request->registerNight;
                                    $registerExclNight=$request->registerExclNight;
                                    $first_residence=1;
                                    $decentralise_production=0;
                                    $capacity_decentalise=0;
                                    $registerG=$request->registerG; 
                                    $Metertype=$request->meterType;
                                    
                                    if($request->category=='pack'){
                                    $IncludeG=1;
                                    $IncludeE=1;
                                    }
                                    
                                    if($request->category=='electricity'){
                                    $IncludeG=0;
                                    $IncludeE=1;
                                    }
                                    if($request->category=='gas'){
                                    $IncludeG=1;
                                    $IncludeE=0;
                                    }



        }  



        $FirstResidence=$request->first_residence;  
        //$IncludeE=$request->IncludeE;  
        //$Metertype=$request->meterType;  

        $registerNormal=$registerNormal;
        $registerDay=$registerDay;
        $registerNight=$registerNight;
        $registerExclNight=$registerExclNight;
        $CurrentSupplierE=$request->currentSupplierE;
        $DecentralisedProduction=$request->decentralisedProduction;
        $CapacityDecenProduction=$request->capacityDecenProduction;
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




        if($request->category=='electricity'){

        $res['postalE']=PostalcodeElectricity::select('distribution_id','DNB','region')->where('netadmin_zip',$postalCode)->orderBy('DNB', 'asc')->first();


        $dnbE=$res['postalE']->DNB;
        $region=$res['postalE']->region;
        $distribution_id=$res['postalE']->distribution_id;


        if($customerGroup=='professional'){

        $customer='PRO';
        $currentDate=date("Y/m/d");
        $result['electricity']['Netcostes']=Netcostes::where('dgo',$dnbE)->where('segment',$customer)
        ->where('volume_lower','<=',$sumRegisters)->where('volume_upper','>=',$sumRegisters)
        ->first();
        $result['electricity']['tax']=TaxElectricity::where('dgo',$dnbE)->where('segment',$customer)->first();
        $result['products'] = DynamicElectricProfessional::whereHas('staticData', function($q) {
        $q->where('acticve', 'Y');
        })->where($region,'=','Y')->get();

        // $result['products']=DynamicElectricProfessional::where($region,'=','Y')          
        // ->get();
        $comparisonType='electricity';

        }else{

        $customer='RES';
        $currentDate=date("Y/m/d");
        $result['electricity']['Netcostes']=Netcostes::where('dgo',$dnbE)->where('segment',$customer)
        ->where('volume_lower','<=',$sumRegisters)->where('volume_upper','>=',$sumRegisters)
        ->first();

        $result['electricity']['tax']=TaxElectricity::where('dgo',$dnbE)->where('segment',$customer)->first();

        // $result['products']=DynamicElectricResidential::where($region,'=','Y')
        // ->get();
        $result['products'] = DynamicElectricResidential::whereHas('staticData', function($q) {
        $q->where('acticve', 'Y');
        })->where($region,'=','Y')->get();
        $comparisonType='electricity';

        }

        }

        if($request->category=='gas'){

        $res['postalG']=PostalcodeGas::select('distribution_id','DNB','region')->where('netadmin_zip',$postalCode)->orderBy('DNB', 'asc')->first();
        $dnbG=$res['postalG']->DNB;
        $region=$res['postalG']->region;
        $distribution_id=$res['postalG']->distribution_id;

        if($customerGroup=='professional'){

        $customer='PRO';
        $currentDate=date("Y/m/d");
        $result['gas']['Netcostes']=Netcostgs::where('dgo',$dnbG)->where('segment',$customer)
        ->where('volume_lower','<=',$registerG)->where('volume_upper','>=',$registerG)
        ->first();
        $result['gas']['tax']=TaxGas::where('dgo',$dnbG)->where('segment',$customer)->first();
        // $result['products']=DynamicGasProfessional::whereDate('valid_from','<=',$currentDate)->whereDate('valid_till','>=',$currentDate) 
        // ->where($region,'=','Y')           
        // ->get();
        $result['products'] = DynamicGasProfessional::whereHas('staticData', function($q) {
        $q->where('acticve', 'Y');
        })->where($region,'=','Y')->get();
        $comparisonType='gas';

        }else{

        $customer='RES';
        $currentDate=date("Y/m/d");
        $result['gas']['Netcostes']=Netcostgs::where('dgo',$dnbG)->where('segment',$customer)
        ->where('volume_lower','<=',$registerG)->where('volume_upper','>=',$registerG)
        ->first();

        $result['gas']['tax']=TaxGas::where('dgo',$dnbG)->where('segment',$customer)->first();
        // $result['products']=DynamicGasResidential::whereDate('valid_from','<=',$currentDate)->whereDate('valid_till','>=',$currentDate)
        // ->where($region,'=','Y') 

        // ->get();
        $result['products'] = DynamicGasResidential::whereHas('staticData', function($q) {
        $q->where('acticve', 'Y');
        })->where($region,'=','Y')->get();
        $comparisonType='gas';

        }

        }


        if($request->category=='pack'){

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

        if($request->category=='electricity'){  



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





        if($request->category=='gas'){ 

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



        if($request->category=='pack'){ 



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
        'pricing_type' => utf8_encode($getProducts->staticElectricDetails->fixed_indiable),
        'green_percentage' => $gp,
        'subscribe_url' => $pack_url,
        'terms_url' => $pack_term,
        'ff_pro_rata' => $FF,
        'inv_period' => $getProducts->staticElectricDetails->inv_period,
        'popularity_score' => $popularity
        ];


        }



        // product-underlying products-electricity
        if($request->category=='pack'){                  

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
        if($request->category=='pack'){

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

        if($request->category=='electricity'){


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
        if($request->meterType=='double_excl_night' || $request->meterType=='double_excl_night'){
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



        if($request->category=='pack'){



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
        'excl_night' =>str_replace(',', '', number_format($getProducts->price_excl_night*$registerExclNight, 3, '.', ','))                                    
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
        if($request->category=='gas'){
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

        if($request->category=='electricity'){

        if($currentSupplierE){

        $getdiscount=Discount::where('productId',$getProducts->product_id)
        ->whereDate('startdate','<=',$currentDate)->whereDate('enddate','>=',$currentDate)
        ->where('volume_lower','<=',$sumRegisters)
        ->where('volume_upper','>=',$sumRegisters)
        ->where('fuelType','electricity')
        ->where('supplier','!=',$currentSupplierE)
        //->where('applicableForExistingCustomers','TRUE')
        ->get();


        }else{

        $getdiscount=Discount::where('productId',$getProducts->product_id)
        ->whereDate('startdate','<=',$currentDate)->whereDate('enddate','>=',$currentDate)
        ->where('volume_lower','<=',$sumRegisters)
        ->where('volume_upper','>=',$sumRegisters)
        ->where('fuelType','electricity')
        ->where('applicableForExistingCustomers','FALSE')
        ->get();

        }





        unset($discountArray); $discountArray=array(); 
        $totaldE=0;                   
        foreach($getdiscount as $discount){

        if($discount->fuelType=='electricity'){
        $calFixed=$discount->value;
        $CalcUsageEurocent=($sumRegisters*$discount->value)/100;
        $CalcUsagePct=(($getProducts->price_single*$registerNormal)+($getProducts->price_day*$registerDay)+($getProducts->price_night*$registerNight)+($getProducts->price_excl_night*$registerExclNight))*$discount->value;
        }
        if($discount->fuelType=='gas'){

        $calFixed=$discount->value;
        $CalcUsageEurocent=($registerG*$discount->value)/100;
        $CalcUsagePct=($getProducts->price_gas*$registerG)*$discount->value;
        }

        if($discount->unit=='euro'){

        $disc =$calFixed*100;
        }
        if($discount->unit=='eurocent'){

        $disc =$CalcUsageEurocent*100;
        }
        if($discount->unit=='pct'){

        $disc =$CalcUsagePct;
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

        $dataArray = [
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
        'service_level_contact' => utf8_encode(strtolower($discount->serviceLevelContact))
        ]
        ];
        $discountArray[]=$dataArray;

        $serviceLevelPayment=$discount->serviceLevelPayment;
        $serviceLevelInvoicing=$discount->serviceLevelInvoicing;
        $serviceLevelContact=$discount->serviceLevelContact;
        $discountType=$discount->discountType;

        }

        $products['price']['breakdown']['discounts']=$discountArray;


        }



        if($request->category=='gas'){

        if($currentSupplierG){
        $getdiscount=Discount::where('productId',$getProducts->product_id)
        ->whereDate('startdate','<=',$currentDate)->whereDate('enddate','>=',$currentDate)
        ->where('volume_lower','<=',$registerG)
        ->where('volume_upper','>=',$registerG)
        ->where('fuelType','gas')
        ->where('supplier','!=',$currentSupplierG)
        // ->where('applicableForExistingCustomers','TRUE')
        ->get();
        }else{

        $getdiscount=Discount::where('productId',$getProducts->product_id)
        ->whereDate('startdate','<=',$currentDate)->whereDate('enddate','>=',$currentDate)
        ->where('volume_lower','<=',$registerG)
        ->where('volume_upper','>=',$registerG)
        ->where('fuelType','gas')
        ->where('applicableForExistingCustomers','FALSE')
        ->get(); 


        }



        unset($discountgArray); $discountgArray=array();    
        $totaldG=0;               
        foreach($getdiscount as $discount){


        $calFixed=$discount->value;
        $CalcUsageEurocent=($registerG*$discount->value)/100;
        $CalcUsagePct=($getProducts->price_gas*$registerG)*$discount->value;

        if($discount->unit=='euro'){

        $disc =$calFixed*100;
        }
        if($discount->unit=='eurocent'){

        $disc =$CalcUsageEurocent*100;
        }
        if($discount->unit=='pct'){

        $disc =$CalcUsagePct;
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
        $totaldG=$totaldG+$disc;
        $datagArray = [
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
        }

        $products['price']['breakdown']['discounts']=$discountgArray;


        }



        if($request->category=='pack'){



        if($currentSupplierE){
        $getdiscountE=Discount::where('applicableForExistingCustomers',false)
        ->where('productId',$getProducts->product_idE)                
        ->whereDate('startdate','<=',$currentDate)->whereDate('enddate','>=',$currentDate)
        ->where('volume_lower','<=',$sumRegisters)
        ->where('volume_upper','>=',$sumRegisters)                           
        ->where('supplier','!=',$currentSupplierE)
        ->where('applicableForExistingCustomers','FALSE')
        ->get();
        }else{
        $getdiscountE=Discount::where('applicableForExistingCustomers',false)
        ->where('productId',$getProducts->product_idE)                
        ->whereDate('startdate','<=',$currentDate)->whereDate('enddate','>=',$currentDate)
        ->where('volume_lower','<=',$sumRegisters)
        ->where('volume_upper','>=',$sumRegisters)                           
        ->where('applicableForExistingCustomers','FALSE')
        ->get();

        }

        if($currentSupplierG){
        $getdiscountG=Discount::where('applicableForExistingCustomers',false)
        ->where('productId',$getProducts->product_idG)
        ->whereDate('startdate','<=',$currentDate)->whereDate('enddate','>=',$currentDate)                
        ->where('volume_lower','<=',$registerG)
        ->where('volume_upper','>=',$registerG)               
        ->where('supplier','!=',$currentSupplierG)
        ->where('applicableForExistingCustomers','FALSE')
        ->get();
        }else{

        $getdiscountG=Discount::where('applicableForExistingCustomers',false)
        ->where('productId',$getProducts->product_idG)
        ->whereDate('startdate','<=',$currentDate)->whereDate('enddate','>=',$currentDate)                
        ->where('volume_lower','<=',$registerG)
        ->where('volume_upper','>=',$registerG)               
        ->where('applicableForExistingCustomers','FALSE')
        ->get();


        }








        unset($discountgArray); $discountgArray=array();  
        $totalE=0;
                
        foreach($getdiscountE as $discount){


        if($discount->fuelType=='electricity'){
        $calFixed=$discount->value;
        $CalcUsageEurocent=($sumRegisters*$discount->value)/100;
        $CalcUsagePct=(($getProducts->price_singleE*$registerNormal)+($getProducts->price_dayE*$registerDay)+($getProducts->price_nightE*$registerNight)+($getProducts->price_excl_nightE*$registerExclNight))*$discount->value;
        }
        if($discount->fuelType=='gas'){

        $calFixed=$discount->value;
        $CalcUsageEurocent=($registerG*$discount->value)/100;
        $CalcUsagePct=($getProducts->price_gasG*$registerG)*$discount->value;
        }

        if($discount->unit=='euro'){

        $disc =$calFixed*100;
        }
        if($discount->unit=='eurocent'){

        $disc =$CalcUsageEurocent*100;
        }
        if($discount->unit=='pct'){

        $disc =$CalcUsagePct;
        }
        $totalE=$totalE+$disc;

        if($locale=='nl'){
        $D_name=iconv("cp1252", "utf-8//TRANSLIT", $discount->nameNl);
        $D_info=iconv("cp1252", "utf-8//TRANSLIT", $discount->descriptionNl);
        }else{
        $D_name=iconv("cp1252", "utf-8//TRANSLIT", $discount->nameFr);
        $D_info=iconv("cp1252", "utf-8//TRANSLIT", $discount->descriptionFr);
        }

        $datagArray = [
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



        }






        $products['price']['breakdown']['discounts']=$discountgArray;








        $totalG=0;
        foreach($getdiscountG as $discount){


        if($discount->fuelType=='electricity'){
        $calFixed=$discount->value;
        $CalcUsageEurocent=($sumRegisters*$discount->value)/100;
        $CalcUsagePct=(($getProducts->price_singleE*$registerNormal)+($getProducts->price_dayE*$registerDay)+($getProducts->price_nightE*$registerNight)+($getProducts->price_excl_nightE*$registerExclNight))*$discount->value;
        }
        if($discount->fuelType=='gas'){

        $calFixed=$discount->value;
        $CalcUsageEurocent=($registerG*$discount->value)/100;
        $CalcUsagePct=($getProducts->price_gasG*$registerG)*$discount->value;
        }

        if($discount->unit=='euro'){

        $disc =$calFixed*100;
        }
        if($discount->unit=='eurocent'){

        $disc =$CalcUsageEurocent*100;
        }
        if($discount->unit=='pct'){

        $disc =$CalcUsagePct;
        }

        $totalG=$totalG+$disc;

        if($locale=='nl'){
        // $D_name=utf8_encode($discount->nameNl);
        $D_name=iconv("cp1252", "utf-8//TRANSLIT", $discount->nameNl);
        // $D_info=utf8_encode($discount->descriptionNl);
        $D_info=iconv("cp1252", "utf-8//TRANSLIT", $discount->descriptionNl);


        }else{

        // $D_name=utf8_encode($discount->nameFR);
        $D_name=iconv("cp1252", "utf-8//TRANSLIT", $discount->nameFr);
        // $D_info=utf8_encode($discount->descriptionFR);
        $D_name=iconv("cp1252", "utf-8//TRANSLIT", $discount->descriptionFr);

        }

        $datagArray = [
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

        }

        $products['price']['breakdown']['discounts']=$discountgArray;

        $totalDiscountAmount=$totalE+$totalG;

        }




        // price-discount-end
        if(count($products['price']['breakdown']['discounts'])>0){
        $promo_discount = [
        'promo' => $discountType,
        'discount_serviceLevelInvoicing' => $serviceLevelInvoicing,
        'discount_serviceLevelPayment' => $serviceLevelPayment,
        ];
        $products['parameters']['values']['promo_discount']=$promo_discount;
        }else{
        $promo_discount = [
        'promo' => false,
        'discount_serviceLevelInvoicing' => false,
        'discount_serviceLevelPayment' => false,
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
        $incl_promo=$excl_promo-$totaldE;

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
        $incl_promo=$excl_promo-$totaldG;
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

        $excl_promo=$fixedFeeE*100+$certificateE+$single+$distributionE+$transportE+$taxE+($getProducts->ffG*100)+$usg+$distributionG+$transportG+$taxG;

        $incl_promo=$excl_promo-$totalDiscountAmount;

        $products['price']['totals']['month'] = [
        'incl_promo' => $incl_promo/12,
        'excl_promo' => $excl_promo/12
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



        // if($request->IncludeE || $request->IncludeG ){
        SearchDetail::create(
        [ 
        'uuid'=>$products['parameters']['uuid'],
        'locale'=>$products['parameters']['values']['locale'],
        'firstname'=>null,
        'lastname'=>null,
        'residential_professional'=>$products['parameters']['values']['customer_group'],
        'postalcode'=>$products['parameters']['values']['postal_code'],
        'region'=>$products['parameters']['values']['region'],
        'familysize'=>$products['parameters']['values']['residents'],
        'comparison_type'=>$products['parameters']['values']['comparison_type'],
        'meter_type'=>$request->meterType,
        'pack_id'=>null,
        'eid'=>null,
        'gid'=>null,
        'total_cost'=>null,
        'single'=>$products['parameters']['values']['usage_single'],
        'day'=>$products['parameters']['values']['usage_day'],
        'night'=>$products['parameters']['values']['usage_night'],
        'excl_night'=>$products['parameters']['values']['usage_excl_night'],
        'current_electric_supplier'=>$currentSupplierE,
        'gas_consumption'=>$products['parameters']['values']['usage_gas'],
        'current_gas_supplier'=>$currentSupplierG,
        'email'=>$email,
        'first_residence'=>$request->first_residence,
        'decentralise_production'=>$request->dec_pro,
        'capacity_decentalise'=>$request->capacity_decen_pro,
        'includeG'=>$IncludeG,
        'includeE'=>$IncludeE,
        'data_from'=>$request->req_from
        ]);

        //   }








        return response()->json([
        'products' => $pro,200

        ]);


        //**json output -end */
        }




        }
