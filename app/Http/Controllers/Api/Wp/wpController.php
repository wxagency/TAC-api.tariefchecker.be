<?php
namespace App\Http\Controllers\Api\Wp;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

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
use Response;
use Lang;

class wpController extends Controller
{
    public function supplier_details(Request $request){
        
        

        $pack=StaticPackResidential::where('check_elec',$request->supplier)->pluck('pack_id'); 

        $electricity=StaticElecticResidential::where('supplier',$request->supplier)->pluck('product_id');

        $product['electricity'] = $electricity;
        $product['pack'] = $pack;
        return $product;
        exit();

        $packs= array();
        $electricity= array();

        foreach ($pack as $key => $value) {
                $packs[]=utf8_encode($value);
            } 
        foreach ($electricity as $key => $value) {
                $electricity[]=utf8_encode($value);
            }   


        $pdoduct['electricity'] = $electricity;
        $pdoduct['pack'] = $packs;    
        return $pdoduct;
    }
    
    public function productset(Request $request){
        
        
      //  $locale="nl";


        if(isset($request->api_locale)){

        $query['locale']=$locale=$request->api_locale;

        }else{
          
          $query['locale']='nl';  
            
        }

        if(isset($request->analytic_id)){

        $analytic_id=$request->analytic_id;

        }else{
          
          $analytic_id=null;  
            
        }
        if(isset($request->api_postal_code)){

        $query['postalCode']=$request->api_postal_code;

        }else{
            
         $query['postalCode']=2000;

        }


        if(isset($request->api_comparison_type)){

        $query['customerGroup']=$request->api_comparison_type;
        

        }else{
            
           $query['customerGroup']='residential';
           

        }

        if(isset($request->api_category_type)){

        $query['category']=$request->api_category_type;

        if($request->api_category_type=='electricity'){

        $query['IncludeG']=0;
        $query['IncludeE']=1; 

        }else{

        $query['IncludeG']=1;
        $query['IncludeE']=1;
            
        }
        

        }else{
            
        $query['category']='pack';
        

        }


        if(isset($request->api_locale)){
        $query['registerNormal']=$request->api_usage_single;
        }else{
            
            $query['registerNormal']=3500;
        }
        if(isset($request->api_locale)){
        $query['registerG']=$request->api_usage_gas;
        }else{
            
         $query['registerG']=25000;   
        }
        
        $query['first_residence']=1;
        $query['meterType']='single';
       // $query['category']='pack';
       
    

        
          try {

        $client = new \GuzzleHttp\Client(); 
                  $request = $client->post('http://api.tariefchecker.be/api/calculation', [
                      'headers' => [
                          'Accept' => 'application/json',
                          'Content-type' => 'application/x-www-form-urlencoded',
                          'Authorization' => 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiMWFmMjVkYWNmZTNiM2I0MmZjOTJkMTU5MjIxY2RjNjNkY2MxMzEwZWU3NDJlM2YzNmRiOWZiMDZhZmMwNGMyNTgyNzEzNjRhYjU5Y2VkZGQiLCJpYXQiOjE2NDMyODY2MjcsIm5iZiI6MTY0MzI4NjYyNywiZXhwIjoyMjc0NDM4NjI3LCJzdWIiOiI1Iiwic2NvcGVzIjpbXX0.rmiDd2sM0kduf6CPed5rjbqPL4Fui-MDdOKViiPn49pcJEukW_kA2ByuJfHNUIe9rctIXsovX1T8kgeer6TxgQkGCvruO98zcGklVv470en8ul6NzOCMmaemX4cJj4XQYlcI1_-z6tnHqtbbc7_-TyvezidDGslhMAMmtREicgrubnp9VGyl6YtE_pXHedruJ7PxYsc2_Gqu-osdFOdEW6hxN3uPlpKbuHgrf9DvJr8B3PSDQLPl49Q9HzrL-vPgayZTpNFINpCw1QBKk_ooWo861UZQ_cE33TdNfXyoJ5WnXQ-AjvtInfw7C9skq57C9X4NmfsllWCacNn9IYNs4uocuFo259TbRXNuooHsWTDTty4kalcp3LD7G0exCTTDC3_QsEoZI6694ct8Fi0gOJ05thoS5grKIfKyFkRqu1eOS2wMdNs-6KZXwVQ6fv1sJE-VjdIKXoj-r6wo_FPSceB599yz22gwVQLnDQJAvu0OahSyU8DG3VMH__ItYBuTI0uOTJZerwaRwmnTkSWbWczA4c8AEb1H_W-G4Yblh4D9y_ZOW7FvvFj53dCX83mzUyBN3HahqzD8ZX0IvZXolZHLxluIOlFoR9HiLNzTZFrJSzWru39AjNmbaK8-AAydGlF606uGglo76ES7D7dOvDa5lgUjYzRvby8jDpRSkzY'],
                      'query' => $query 
                  ]);

       
        $response = $request->getBody()->getContents();       
        $json = json_decode($response, true);

          } catch (\Exception $e) {

            $response = ['status' => false, 'message' => $e->getMessage()];

            }


 
              $collection = collect($json['products']);


              
                $sorted = $collection->sortBy(function ($item, $key) {
                return $item['price']['totals']['year']['incl_promo'];
                })->first();


               
                $cheapest_price=$sorted['price']['totals']['year']['excl_promo'];
            
                $filter = $collection->filter(function($value, $key) {
                    $satisfied = 0;
                    $total_condition = 0;
                     $total_condition++;

                    // if ($value['product']['contract_duration']>=3 && $value['product']['underlying_products']['electricity']['pricing_type']=="fixed" && $value['product']['underlying_products']['gas']['pricing_type']=="fixed") {

                     if ( $value['product']['contract_duration']>=3 ) {

                                    
                        $satisfied ++;

                    }
                    
                    
                    return $satisfied == $total_condition;
                });
        
                $getProducts= $filter->all();
              
              
                $getProducts=collect($getProducts);
                $sorted = $getProducts->sortBy(function ($item, $key) {
                return $item['price']['totals']['year']['incl_promo'];
                })->first();
                
            
              
                    $price_incl_promo_month_raw=number_format($sorted['price']['totals']['year']['incl_promo']/100,2,',', '.');
                    $price_excl_promo_month=number_format($sorted['price']['totals']['month']['excl_promo']/100,2,',', '.');
                    $price_incl_promo_year=number_format($sorted['price']['totals']['year']['incl_promo']/100,2,',', '.');
                    
                    $price_excl_promo_year=number_format($sorted['price']['totals']['year']['excl_promo']/100,2,',', '.');
                    
                    $cheapest_price3=number_format(($sorted['price']['totals']['year']['excl_promo']-$cheapest_price)/100,2,',', '.');
                   
                    $price_promo_montha=$sorted['price']['totals']['month']['excl_promo']-$sorted['price']['totals']['year']['incl_promo'];
                    $price_promo_month=number_format($price_promo_montha,2,',', '.');
                    
                    $price_promo_yeara=$sorted['price']['totals']['year']['incl_promo']-$sorted['price']['totals']['year']['excl_promo'];
                    $price_promo_year=number_format($price_promo_yeara/100,2,',', '.');
                    
                    if($sorted['product']['pricing_type']=='Fix'){
                        if($locale=='nl'){
                        $pricing_type='vast tarief';
                        }else{
                          $pricing_type='tarif fix';  
                            
                        }
                    }else{
                        if($locale=='nl'){
                        $pricing_type='variabele prijs';
                        }else{
                          $pricing_type='tarif variable ';  
                            
                        }
                    }
                    
                    $time=strtotime($sorted['price']['validity_period']['end']);
                    //$month=strtolower(date("F",$time));
                    $month=date('m');
                    Lang::setLocale($locale);
                   
                     if($month==1){
            $x2=trans("home.1");
            }
            if($month==2){
            $x2=ucfirst(trans("home.2"));
            }
            if($month==3){
            $x2=ucfirst(trans("home.3"));
            }
            if($month==4){
            $x2=ucfirst(trans("home.4"));
            }
            if($month==5){
            $x2=ucfirst(trans("home.5"));
            }
            if($month==6){
            $x2=ucfirst(trans("home.6"));
            }
            if($month==7){
            $x2=ucfirst(trans("home.7"));
            }
            if($month==8){
            $x2=ucfirst(trans("home.8"));
            }
            if($month==9){
            $x2=ucfirst(trans("home.9"));
            }
            if($month==10){
            $x2=ucfirst(trans("home.10"));
            }
            if($month==11){
            $x2=ucfirst(trans("home.11"));
            }
            if($month==12){
            $x2=ucfirst(trans("home.12"));
            }
            
            if($sorted['product']['service_level_payment']=='free' && $sorted['product']['service_level_invoicing']=='free' && $sorted['product']['service_level_contact']=='free'){
                
                
                $service_text="Volwaardige dienstverlening";
                
            }else{
                
                $service_text="Verplichte facturatie per email en uitsluitend elektronische communicatie";
                
            }
            
            $replace = ['analytics_id'];
            $info = [

            'analytics_id'=>$analytic_id

            ];

            $sub_url= str_replace($replace, $info, $sorted['product']['subscribe_url']);
                    
                   $products['3jrvast'] =[
                        'supplier_name'=> $sorted['supplier']['name'],
                        'supplier_logo'=>$sorted['supplier']['logo'],
                        'supplier_url'=>$sorted['supplier']['url'],
                        'supplier_origin'=>$sorted['supplier']['origin'],
                        'supplier_customer_rating'=>$sorted['supplier']['customer_rating'],
                        'supplier_greenpeace_rating'=>$sorted['supplier']['greenpeace_rating'],
                        'product_name'=>$sorted['product']['name'],
                        'product_type'=>$sorted['product']['type'],
                        'product_contract_duration'=>3,
                        'product_service_level_payment'=>$sorted['product']['service_level_payment'],
                        'product_service_level_invoicing'=>$sorted['product']['service_level_invoicing'],
                        'product_service_level_contact'=>$sorted['product']['service_level_contact'],
                        'product_pricing_type'=>$pricing_type,
                        'product_subscribe_url'=>$sub_url,
                        'product_prices_url'=>$sorted['product']['terms_url'],
                        'product_green_percentage'=>$sorted['product']['green_percentage'],
                        'product_origin'=>$sorted['product']['origin'],
                        'product_terms_url'=>$sorted['product']['terms_url'],
                        'product_tariff_description'=>$sorted['product']['tariff_description'],
                        'name'=>$sorted['product']['name'],
                        'price_incl_promo_month'=>'€'.$price_incl_promo_month_raw,
                        'price_incl_promo_month_raw'=>$sorted['price']['totals']['month']['incl_promo'],
                        'price_excl_promo_month'=>'€'.$price_excl_promo_month,
                        'price_excl_promo_month_raw'=>$sorted['price']['totals']['month']['excl_promo'],
                        'price_incl_promo_year'=>'€'.str_replace('.', '.', $price_incl_promo_year),
                        'price_incl_promo_year_raw'=>$sorted['price']['totals']['year']['incl_promo'],
                        'price_excl_promo_year'=>'€'.str_replace('.', '.', $price_excl_promo_year),
                        'price_excl_promo_year_raw'=>$sorted['price']['totals']['year']['excl_promo'],
                        'price_promo_month'=>'€'.$price_promo_month,
                        'price_promo_month_raw'=>$price_promo_montha,
                        'price_promo_year'=>'€'.str_replace('.', '.', $price_promo_year),
                        'price_promo_year_raw'=>$price_promo_yeara,
                        'price_period_start'=>strtotime($sorted['price']['validity_period']['start']),
                        'price_period_end'=>strtotime($sorted['price']['validity_period']['end']),
                        'price_end_month'=>$x2,
                        'name'=>$sorted['supplier']['name']." ".$sorted['product']['name'],
                        'cheapest_price'=>'€'.$cheapest_price3,
                        'service_text'=>$service_text,
                        ];

                  


                   
                        
                        
                        
               if($json['products'][0]['parameters']['values']['comparison_type']=='electricity')
                     {         
               
             $filter = $collection->filter(function($value, $key) {
                    $satisfied = 0;
                    $total_condition = 0;
                     $total_condition++;

                    


                        if ($value['product']['contract_duration']==1 && $value['product']["pricing_type"]=="fixed") {
                       
                                        
                            $satisfied ++;
                       
                  


                     }
                    
                    
                   
                    
                    
                    
                    return $satisfied == $total_condition;
                });


               }else
                     {

$filter = $collection->filter(function($value, $key ) {
                    $satisfied = 0;
                    $total_condition = 0;
                     $total_condition++;




                        if ($value['product']['contract_duration']==1 && $value['product']['underlying_products']["electricity"]["pricing_type"]=="fixed" && $value['product']['underlying_products']["gas"]["pricing_type"]=="fixed") {
                       
                                        
                            $satisfied ++;
                       
                    }

                     return $satisfied == $total_condition;
                });

}
        
                $getProducts= $filter->all();
                
            
                
            
                $getProducts=collect($getProducts);
                $sorted = $getProducts->sortBy(function ($item, $key) {
                return $item['price']['totals']['year']['incl_promo'];
                })->first();
                
            
              
                    $price_incl_promo_month_raw=number_format($sorted['price']['totals']['year']['incl_promo']/100,2,',', '.');
                    $price_excl_promo_month=number_format($sorted['price']['totals']['month']['excl_promo']/100,2,',', '.');
                    $price_incl_promo_year=number_format($sorted['price']['totals']['year']['incl_promo']/100,2,',', '.');
                    
                    $price_excl_promo_year=number_format($sorted['price']['totals']['year']['excl_promo']/100,2,',', '.');
                    $price_promo_montha=$sorted['price']['totals']['month']['excl_promo']-$sorted['price']['totals']['year']['incl_promo'];
                    $price_promo_month=number_format($price_promo_montha,2,',', '.');
                    
                    $cheapest_price1=number_format(($sorted['price']['totals']['year']['excl_promo']-$cheapest_price)/100,2,',', '.');
                    
                    $price_promo_yeara=$sorted['price']['totals']['year']['incl_promo']-$sorted['price']['totals']['year']['excl_promo'];
                    $price_promo_year=number_format($price_promo_yeara/100,2,',', '.');
                    
                      if($sorted['product']['pricing_type']=='Fix'){
                        if($locale=='nl'){
                        $pricing_type='vast tarief';
                        }else{
                          $pricing_type='tarif fix';  
                            
                        }
                    }else{
                        if($locale=='nl'){
                        $pricing_type='variabele prijs';
                        }else{
                          $pricing_type='tarif variable ';  
                            
                        }
                    }
                    
                    $time=strtotime($sorted['price']['validity_period']['end']);
                    $month=strtolower(date("F",$time));
                    Lang::setLocale($locale);
                   
                     if($month=='january'){
            $x2=trans("home.1");
            }
            if($month=='february'){
            $x2=trans("home.2");
            }
            if($month=='march'){
            $x2=trans("home.3");
            }
            if($month=='april'){
            $x2=trans("home.4");
            }
            if($month=='may'){
            $x2=trans("home.5");
            }
            if($month=='june'){
            $x2=trans("home.6");
            }
            if($month=='july'){
            $x2=trans("home.7");
            }
            if($month=='august'){
            $x2=trans("home.8");
            }
            if($month=='september'){
            $x2=trans("home.9");
            }
            if($month=='october'){
            $x2=trans("home.10");
            }
            if($month=='november'){
            $x2=trans("home.11");
            }
            if($month=='december'){
            $x2=trans("home.12");
            }
            
             if($sorted['product']['service_level_payment']=='free' && $sorted['product']['service_level_invoicing']=='free' && $sorted['product']['service_level_contact']=='free'){
                
                
                $service_text="Volwaardige dienstverlening";
                
            }else{
                
                $service_text="Verplichte facturatie per email en uitsluitend elektronische communicatie";
                
            }

            $replace = ['analytics_id'];
            $info = [

            'analytics_id'=>$analytic_id

            ];

            $sub_url= str_replace($replace, $info, $sorted['product']['subscribe_url']);
                        
                  $products['1jrvast'] =[
                        'supplier_name'=> $sorted['supplier']['name'],
                        'supplier_logo'=>$sorted['supplier']['logo'],
                        'supplier_url'=>$sorted['supplier']['url'],
                        'supplier_origin'=>$sorted['supplier']['origin'],
                        'supplier_customer_rating'=>$sorted['supplier']['customer_rating'],
                        'supplier_greenpeace_rating'=>$sorted['supplier']['greenpeace_rating'],
                        'product_name'=>$sorted['product']['name'],
                        'product_type'=>$sorted['product']['type'],
                        'product_contract_duration'=>$sorted['product']['contract_duration'],
                        'product_service_level_payment'=>$sorted['product']['service_level_payment'],
                        'product_service_level_invoicing'=>$sorted['product']['service_level_invoicing'],
                        'product_service_level_contact'=>$sorted['product']['service_level_contact'],
                        'product_pricing_type'=>$pricing_type,
                        'product_subscribe_url'=>$sub_url,
                        'product_prices_url'=>$sorted['product']['terms_url'],
                        'product_green_percentage'=>$sorted['product']['green_percentage'],
                        'product_origin'=>$sorted['product']['origin'],
                        'product_terms_url'=>$sorted['product']['terms_url'],
                        'product_tariff_description'=>$sorted['product']['tariff_description'],
                        'name'=>$sorted['product']['name'],
                        'price_incl_promo_month'=>$sorted['price']['totals']['month']['incl_promo'],
                        'price_incl_promo_month_raw'=>'€'.$price_incl_promo_month_raw,
                        'price_excl_promo_month'=>'€'.$price_excl_promo_month,
                        'price_excl_promo_month_raw'=>$sorted['price']['totals']['month']['excl_promo'],
                        'price_incl_promo_year'=>'€'.str_replace('.', '.', $price_incl_promo_year),
                        'price_incl_promo_year_raw'=>$sorted['price']['totals']['year']['incl_promo'],
                        'price_excl_promo_year'=>$price_excl_promo_year,
                        'price_excl_promo_year_raw'=>$sorted['price']['totals']['year']['excl_promo'],
                        'price_promo_month'=>'€'.$price_promo_month,
                        'price_promo_month_raw'=>$price_promo_montha,
                        'price_promo_year'=>'€'.str_replace('.', ',', $price_promo_year),
                        'price_promo_year_raw'=>$price_promo_yeara,
                        'price_period_start'=>strtotime($sorted['price']['validity_period']['start']),
                        'price_period_end'=>strtotime($sorted['price']['validity_period']['end']),
                        'name'=>$sorted['supplier']['name']." ".$sorted['product']['name'],
                        'cheapest_price'=>'€'.$cheapest_price1,
                        'service_text'=>$service_text,
                        ];
                        
                        
               
               
            
                              $filter = $collection->filter(function($value, $key) {
                    $satisfied = 0;
                    $total_condition = 0;
                     $total_condition++;
                    if ($value['product']['green_percentage']==100 ) {
                       
                                        
                            $satisfied ++;
                       
                    }
                    
                   
                    
                    
                    
                    return $satisfied == $total_condition;
                });
        
                $getProducts= $filter->all();
                
              
                $getProducts=collect($getProducts);
                $sorted = $getProducts->sortBy(function ($item, $key) {
                return $item['price']['totals']['year']['incl_promo'];
                })->first();
                
           
              
                    $price_incl_promo_month_raw=number_format($sorted['price']['totals']['year']['incl_promo']/100,2,',', '.');
                    $price_excl_promo_month=number_format($sorted['price']['totals']['month']['excl_promo']/100,2,',', '.');
                    $price_incl_promo_year=number_format($sorted['price']['totals']['year']['incl_promo']/100,2,',', '.');
                    
                    $price_excl_promo_year=number_format($sorted['price']['totals']['year']['excl_promo']/100,2,',', '.');
                    $price_promo_montha=$sorted['price']['totals']['month']['excl_promo']-$sorted['price']['totals']['year']['incl_promo'];
                    $price_promo_month=number_format($price_promo_montha,2,',', '.');
                    
                    $price_promo_yeara=$sorted['price']['totals']['year']['incl_promo']-$sorted['price']['totals']['year']['excl_promo'];
                    $price_promo_year=number_format($price_promo_yeara/100,2,',', '.');
                    
                     $cheapest_pricegreen=number_format(($sorted['price']['totals']['year']['excl_promo']-$cheapest_price)/100,2,',', '.');
                   
                    
                     if($sorted['product']['pricing_type']=='Fix'){
                        if($locale=='nl'){
                        $pricing_type='vast tarief';
                        }else{
                          $pricing_type='tarif fix';  
                            
                        }
                    }else{
                        if($locale=='nl'){
                        $pricing_type='variabele prijs';
                        }else{
                          $pricing_type='tarif variable ';  
                            
                        }
                    }
                    
                    $time=strtotime($sorted['price']['validity_period']['end']);
                    $month=strtolower(date("F",$time));
                    Lang::setLocale($locale);
                   
                     if($month=='january'){
            $x2=trans("home.1");
            }
            if($month=='february'){
            $x2=trans("home.2");
            }
            if($month=='march'){
            $x2=trans("home.3");
            }
            if($month=='april'){
            $x2=trans("home.4");
            }
            if($month=='may'){
            $x2=trans("home.5");
            }
            if($month=='june'){
            $x2=trans("home.6");
            }
            if($month=='july'){
            $x2=trans("home.7");
            }
            if($month=='august'){
            $x2=trans("home.8");
            }
            if($month=='september'){
            $x2=trans("home.9");
            }
            if($month=='october'){
            $x2=trans("home.10");
            }
            if($month=='november'){
            $x2=trans("home.11");
            }
            if($month=='december'){
            $x2=trans("home.12");
            }   
                        
                        
                        if($sorted['product']['service_level_payment']=='free' && $sorted['product']['service_level_invoicing']=='free' && $sorted['product']['service_level_contact']=='free'){
                
                
                $service_text="Volwaardige dienstverlening";
                
            }else{
                
                $service_text="Verplichte facturatie per email en uitsluitend elektronische communicatie";
                
            }


            $replace = ['analytics_id'];
            $info = [

            'analytics_id'=>$analytic_id

            ];

            $sub_url= str_replace($replace, $info, $sorted['product']['subscribe_url']);
                        //$sorted['supplier']['url'] , $sorted['product']['subscribe_url']
            $products['Groene'] =[
                        'supplier_name'=> $sorted['supplier']['name'],
                        'supplier_logo'=>$sorted['supplier']['logo'],
                        'supplier_url'=>$sorted['supplier']['url'],
                        'supplier_origin'=>$sorted['supplier']['origin'],
                        'supplier_customer_rating'=>$sorted['supplier']['customer_rating'],
                        'supplier_greenpeace_rating'=>$sorted['supplier']['greenpeace_rating'],
                        'product_name'=>$sorted['product']['name'],
                        'product_type'=>$sorted['product']['type'],
                        'product_contract_duration'=>$sorted['product']['contract_duration'],
                        'product_service_level_payment'=>$sorted['product']['service_level_payment'],
                        'product_service_level_invoicing'=>$sorted['product']['service_level_invoicing'],
                        'product_service_level_contact'=>$sorted['product']['service_level_contact'],
                        'product_pricing_type'=>$pricing_type,
                        'product_subscribe_url'=>$sub_url,
                        'product_prices_url'=>$sorted['product']['terms_url'],
                        'product_green_percentage'=>$sorted['product']['green_percentage'],
                        'product_origin'=>$sorted['product']['origin'],
                        'product_terms_url'=>$sorted['product']['terms_url'],
                        'product_tariff_description'=>$sorted['product']['tariff_description'],
                        'name'=>$sorted['product']['name'],
                        'price_incl_promo_month'=>$sorted['price']['totals']['month']['incl_promo'],
                        'price_incl_promo_month_raw'=>'€'.$price_incl_promo_month_raw,
                        'price_excl_promo_month'=>'€'.$price_excl_promo_month,
                        'price_excl_promo_month_raw'=>$sorted['price']['totals']['month']['excl_promo'],
                        'price_incl_promo_year'=>'€'.str_replace('.', '.', $price_incl_promo_year),
                        'price_incl_promo_year_raw'=>$sorted['price']['totals']['year']['incl_promo'],
                        'price_excl_promo_year'=>$price_excl_promo_year,
                        'price_excl_promo_year_raw'=>$sorted['price']['totals']['year']['excl_promo'],
                        'price_promo_month'=>'€'.$price_promo_month,
                        'price_promo_month_raw'=>$price_promo_montha,
                        'price_promo_year'=>'€'.str_replace('.', ',', $price_promo_year),
                        'price_promo_year_raw'=>$price_promo_yeara,
                        'price_period_start'=>strtotime($sorted['price']['validity_period']['start']),
                        'price_period_end'=>strtotime($sorted['price']['validity_period']['end']),
                        'name'=>$sorted['supplier']['name']." ".$sorted['product']['name'],
                        'cheapest_price'=>'€ 0,00',
                        'service_text'=>$service_text,
                        ];
                        
                        
                        
                        
                
              
                $getProducts=collect($getProducts);
                $sorted = $collection->sortBy(function ($item, $key) {
                return $item['price']['totals']['year']['incl_promo'];
                })->first();
                
            
              
                    $price_incl_promo_month_raw=number_format($sorted['price']['totals']['year']['incl_promo']/100,2,',', '.');
                    $price_excl_promo_month=number_format($sorted['price']['totals']['month']['excl_promo']/100,2,',', '.');
                    $price_incl_promo_year=number_format($sorted['price']['totals']['year']['incl_promo']/100,2,',', '.');
                    
                    $price_excl_promo_year=number_format($sorted['price']['totals']['year']['excl_promo']/100,2,',', '.');
                    $price_promo_montha=$sorted['price']['totals']['month']['incl_promo']-$sorted['price']['totals']['month']['excl_promo'];
                    $price_promo_month=number_format($price_promo_montha,2,',', '.');
                    
                    $price_promo_yeara=$sorted['price']['totals']['year']['excl_promo']-$sorted['price']['totals']['year']['incl_promo'];
                    $price_promo_year=number_format($price_promo_yeara/100,2,',', '.');
                    
                    if($sorted['product']['pricing_type']=='Fix'){
                        if($locale=='nl'){
                        $pricing_type='vast tarief';
                        }else{
                          $pricing_type='tarif fix';  
                            
                        }
                    }else{
                        if($locale=='nl'){
                        $pricing_type='variabele prijs';
                        }else{
                          $pricing_type='tarif variable ';  
                            
                        }
                    }
                    
                    $time=strtotime($sorted['price']['validity_period']['end']);
                    $month=strtolower(date("F",$time));
                    
                    Lang::setLocale($locale);
                   
                     if($month=='january'){
            $x2=trans("home.1");
            }
            if($month=='february'){
            $x2=trans("home.2");
            }
            if($month=='march'){
            $x2=trans("home.3");
            }
            if($month=='April'){
            $x2=trans("home.4");
            }
            if($month=='may'){
            $x2=trans("home.5");
            }
            if($month=='june'){
            $x2=trans("home.6");
            }
            if($month=='july'){
            $x2=trans("home.7");
            }
            if($month=='august'){
            $x2=trans("home.8");
            }
            if($month=='september'){
            $x2=trans("home.9");
            }
            if($month=='october'){
            $x2=trans("home.10");
            }
            if($month=='november'){
            $x2=trans("home.11");
            }
            if($month=='december'){
            $x2=trans("home.12");
            }
            
            
             if($sorted['product']['service_level_payment']=='free' && $sorted['product']['service_level_invoicing']=='free' && $sorted['product']['service_level_contact']=='free'){
                
                
                $service_text="Volwaardige dienstverlening";
                
            }else{
                
                $service_text="Verplichte facturatie per email en uitsluitend elektronische communicatie";
                
            }
                        

                $sub_url= str_replace($replace, $info, $sorted['product']['subscribe_url']);
                $products['Goedkoopste'] =[
                        'supplier_name'=> $sorted['supplier']['name'],
                        'supplier_logo'=>$sorted['supplier']['logo'],
                        'supplier_url'=>$sorted['supplier']['url'],
                        'supplier_origin'=>$sorted['supplier']['origin'],
                        'supplier_customer_rating'=>$sorted['supplier']['customer_rating'],
                        'supplier_greenpeace_rating'=>$sorted['supplier']['greenpeace_rating'],
                        'product_name'=>$sorted['product']['name'],
                        'product_type'=>$sorted['product']['type'],
                        'product_contract_duration'=>$sorted['product']['contract_duration'],
                        'product_service_level_payment'=>$sorted['product']['service_level_payment'],
                        'product_service_level_invoicing'=>$sorted['product']['service_level_invoicing'],
                        'product_service_level_contact'=>$sorted['product']['service_level_contact'],
                        'product_pricing_type'=>$pricing_type,
                        'product_subscribe_url'=>$sub_url,
                        'product_prices_url'=>$sorted['product']['terms_url'],
                        'product_green_percentage'=>$sorted['product']['green_percentage'],
                        'product_origin'=>$sorted['product']['origin'],
                        'product_terms_url'=>$sorted['product']['terms_url'],
                        'product_tariff_description'=>$sorted['product']['tariff_description'],
                        'name'=>$sorted['product']['name'],
                        'price_incl_promo_month'=>$sorted['price']['totals']['month']['incl_promo'],
                        'price_incl_promo_month_raw'=>'€'.$price_incl_promo_month_raw,
                        'price_excl_promo_month'=>'€'.$price_excl_promo_month,
                        'price_excl_promo_month_raw'=>$sorted['price']['totals']['month']['excl_promo'],
                        'price_incl_promo_year'=>'€'.number_format($sorted['price']['totals']['year']['incl_promo']/100,2,',', '.'),
                        'price_incl_promo_year_raw'=>$sorted['price']['totals']['year']['incl_promo'],
                        'price_excl_promo_year'=>$price_excl_promo_year,
                        'price_excl_promo_year_raw'=>$sorted['price']['totals']['year']['excl_promo'],
                        'price_promo_month'=>'€'.$price_promo_month,
                        'price_promo_month_raw'=>$price_promo_montha,
                        'price_promo_year'=>'€'.str_replace('.', ',', $price_promo_year),
                        'price_promo_year_raw'=>$price_promo_yeara,
                        'price_period_start'=>strtotime($sorted['price']['validity_period']['start']),
                        'price_period_end'=>strtotime($sorted['price']['validity_period']['end']),
                        'price_end_month'=>$x2,
                        'name'=>$sorted['supplier']['name']." ".$sorted['product']['name'],
                        'service_text'=>$service_text
                        ];
                        

                $sub_url= str_replace($replace, $info, $sorted['product']['subscribe_url']);
                $products['Poweo'] =[
                        'supplier_name'=> $sorted['supplier']['name'],
                        'supplier_logo'=>$sorted['supplier']['logo'],
                        'supplier_url'=>$sorted['supplier']['url'],
                        'supplier_origin'=>$sorted['supplier']['origin'],
                        'supplier_customer_rating'=>$sorted['supplier']['customer_rating'],
                        'supplier_greenpeace_rating'=>$sorted['supplier']['greenpeace_rating'],
                        'product_name'=>$sorted['product']['name'],
                        'product_type'=>$sorted['product']['type'],
                        'product_contract_duration'=>$sorted['product']['contract_duration'],
                        'product_service_level_payment'=>$sorted['product']['service_level_payment'],
                        'product_service_level_invoicing'=>$sorted['product']['service_level_invoicing'],
                        'product_service_level_contact'=>$sorted['product']['service_level_contact'],
                        'product_pricing_type'=>$pricing_type,
                        'product_subscribe_url'=>$sub_url,
                        'product_prices_url'=>$sorted['product']['terms_url'],
                        'product_green_percentage'=>$sorted['product']['green_percentage'],
                        'product_origin'=>$sorted['product']['origin'],
                        'product_terms_url'=>$sorted['product']['terms_url'],
                        'product_tariff_description'=>$sorted['product']['tariff_description'],
                        'name'=>$sorted['product']['name'],
                        'price_incl_promo_month'=>$sorted['price']['totals']['month']['incl_promo'],
                        'price_incl_promo_month_raw'=>'€'.$price_incl_promo_month_raw,
                        'price_excl_promo_month'=>'€'.$price_excl_promo_month,
                        'price_excl_promo_month_raw'=>$sorted['price']['totals']['month']['excl_promo'],
                        'price_incl_promo_year'=>'€'.str_replace('.', '.', $price_incl_promo_year),
                        'price_incl_promo_year_raw'=>$sorted['price']['totals']['year']['incl_promo'],
                        'price_excl_promo_year'=>$price_excl_promo_year,
                        'price_excl_promo_year_raw'=>$sorted['price']['totals']['year']['excl_promo'],
                        'price_promo_month'=>'€'.$price_promo_month,
                        'price_promo_month_raw'=>$price_promo_montha,
                        'price_promo_year'=>'€'.str_replace('.', ',', $price_promo_year),
                        'price_promo_year_raw'=>$price_promo_yeara,
                        'price_period_start'=>strtotime($sorted['price']['validity_period']['start']),
                        'price_period_end'=>strtotime($sorted['price']['validity_period']['end']),
                        'name'=>$sorted['supplier']['name']." ".$sorted['product']['name'],
                        'service_text'=>$service_text
                        ];
                    
                    
           
            
             
              return Response::json($products);
              //return json($products);
           
           
    }
    
    public function supplier(Request $request)
    {
        
        if($request->type=='elec'){
        $supplier = DynamicElectricResidential::whereHas('staticData', function($q) {
                    $q->where('acticve', 'Y');
                })
                 ->where('supplier',$request->supplier)
                ->get();
                
                 $html="";
         
         foreach($supplier as $suppliers){
             
             
             if($suppliers->staticData->service_level_payment=="Free" && $suppliers->staticData->service_level_invoicing=="Free" && $suppliers->staticData->service_level_contact=="Free"){
                 
                 $SAE="free";
             }else{
                 
                 $SAE="active";
             }
             
            
             
             
             if($SAE=="free"){
                 
                 $SAtext="Volwaardige dienstverlening";
             }else{
                 
                 $SAtext="Verplichte facturatie per email en uitsluitend elektronische communicatie";
                 
             }
            
             
             if($suppliers->duration==0){
             $duration="Onbepaald";     
             }else{
                 
              $duration=$suppliers->duration;   
             }
             
             if($suppliers->fixed_indexed=='Ind'){
             $fixed_indiableE="Variabel";   
             }else{
                 
              $fixed_indiableE='Vast';   
             }
             
             if($suppliers->staticData->green_percentage=='100%'){
             $green_percentage="Ja";    
             }else{
                 
              $green_percentage='Nee';   
             }

             
             
             
         $html.='<tr>
      <td><span data-ps-field="supplier_name" data-ps-product="Cheapest">'.$suppliers->product.'</span></td>

      <td data-ps-field="Duurtijd" data-ps-product="Cheapest">'.$duration.' '.$year.'</td>
      <td data-ps-field="Vast of variabel" data-ps-product="Cheapest">'.$fixed_indiableE.'</td>
      <td data-ps-field="Groene energie" data-ps-product="Cheapest">'.$green_percentage.'</td>
      <td data-ps-field="Dienstverlening" data-ps-product="Cheapest">'.$SAtext.'</td>
      <td data-ps-field="Klant worden" data-ps-product="Cheapest"><a href="'.$suppliers->prices_url_nl.'" target="_blank">Klik hier</a></td>
      </tr>';
         }
      
      
      echo $html;
                
               
                
        }else{
                
                
        
        $supplier=StaticPackResidential::select(
                'static_pack_residentials.*',
                'dynamic_electric_residentials.product_id as product_idE','dynamic_electric_residentials.date as dateE','dynamic_electric_residentials.valid_from as valid_fromE','dynamic_electric_residentials.valid_till as valid_tillE','dynamic_electric_residentials.supplier as supplierE','dynamic_electric_residentials.product as productE','dynamic_electric_residentials.fuel as fuelE','dynamic_electric_residentials.duration as durationE','dynamic_electric_residentials.fixed_indexed as fixed_indiableE','dynamic_electric_residentials.fixed_indexed as fixed_indiableE','dynamic_electric_residentials.customer_segment as segmentE','dynamic_electric_residentials.VL as VLE','dynamic_electric_residentials.WA as WAE','dynamic_electric_residentials.BR as BRE','dynamic_electric_residentials.volume_lower as volume_lowerE','dynamic_electric_residentials.volume_upper as volume_upperE','dynamic_electric_residentials.price_single as price_singleE','dynamic_electric_residentials.price_day as price_dayE','dynamic_electric_residentials.price_night as price_nightE','dynamic_electric_residentials.price_excl_night as price_excl_nightE','dynamic_electric_residentials.ff_single as ff_singleE','dynamic_electric_residentials.ff_day_night as ff_day_nightE','dynamic_electric_residentials.ff_excl_night as ff_excl_nightE','dynamic_electric_residentials.gsc_vl as gsc_vlE','dynamic_electric_residentials.wkc_vl as wkc_vlE','dynamic_electric_residentials.gsc_wa as gsc_waE','dynamic_electric_residentials.gsc_br as gsc_brE','dynamic_electric_residentials.prices_url_nl as prices_url_nlE','dynamic_electric_residentials.prices_url_fr as prices_url_frE','dynamic_electric_residentials.index_name as indexE','dynamic_electric_residentials.index_value as waardeE','dynamic_electric_residentials.coeff_single as coeff_singleE','dynamic_electric_residentials.term_single as term_singleE','dynamic_electric_residentials.coeff_day as coeff_dayE','dynamic_electric_residentials.term_day as term_dayE','dynamic_electric_residentials.coeff_night as coeff_nightE','dynamic_electric_residentials.term_night as term_nightE','dynamic_electric_residentials.coeff_excl as coeff_exclE','dynamic_electric_residentials.term_excl as term_exclE',
                'dynamic_gas_residentials.product_id as product_idG','dynamic_gas_residentials.date as dateG','dynamic_gas_residentials.valid_from as valid_fromG','dynamic_gas_residentials.valid_till as valid_tillG','dynamic_gas_residentials.supplier as supplierG','dynamic_gas_residentials.product as productG','dynamic_gas_residentials.fuel as fuelG','dynamic_gas_residentials.duration as durationG','dynamic_gas_residentials.fixed_indexed as fixed_indiableG','dynamic_gas_residentials.fixed_indexed as fixed_indiableG','dynamic_gas_residentials.segment as segmentG','dynamic_gas_residentials.VL as VLG','dynamic_gas_residentials.WA as WAG','dynamic_gas_residentials.BR as BRG','dynamic_gas_residentials.volume_lower as volume_lowerG','dynamic_gas_residentials.volume_upper as volume_upperG',
                'dynamic_gas_residentials.ff as ffG',
                'dynamic_gas_residentials.price_gas as price_gasG','dynamic_gas_residentials.prices_url_nl as prices_url_nlG','dynamic_gas_residentials.prices_url_fr as prices_url_frG','dynamic_gas_residentials.index_name as indexG','dynamic_gas_residentials.index_value as waardeG','dynamic_gas_residentials.coeff as coeffG','dynamic_gas_residentials.term as term')            
            ->Join('dynamic_electric_residentials','dynamic_electric_residentials.product_id','=','static_pack_residentials.pro_id_E')
            ->Join('dynamic_gas_residentials','dynamic_gas_residentials.product_id','=','static_pack_residentials.pro_id_G')   
            //->where('dynamic_gas_residentials.'.$region.'','=','Y') 
            //->whereDate('dynamic_gas_residentials.valid_from','<=',$currentDate)->whereDate('dynamic_gas_residentials.valid_till','>=',$currentDate)
            //->whereDate('dynamic_electric_residentials.valid_from','<=',$currentDate)->whereDate('dynamic_electric_residentials.valid_till','>=',$currentDate)
            ->where('static_pack_residentials.active','Y')
            ->where('dynamic_gas_residentials.supplier',$request->supplier)
            ->where('dynamic_electric_residentials.supplier',$request->supplier)
            ->get();
        //$supplier=StaticPackResidential::where('supplier',$request->supplier)->get();
        
         //return Response::json($supplier);
         
         $html="";
         
         foreach($supplier as $suppliers){
             $product_idE=StaticElecticResidential::where('product_id',$suppliers->product_idE)->first();
             $product_idG=StaticGasResidential::where('product_id',$suppliers->product_idG)->first();
             
             if($product_idE->service_level_payment=="Free" && $product_idE->service_level_invoicing=="Free" && $product_idE->service_level_contact=="Free"){
                 
                 $SAE="free";
             }else{
                 
                 $SAE="active";
             }
             
             if($product_idG->service_level_payment=="Free" && $product_idG->service_level_invoicing=="Free" && $product_idG->service_level_contact=="Free"){
                 
                 $SAG="free";
             }else{
                 
                 $SAG="active";
             }
             
             
             if($SAE=="free"){
                 if($request->locale=='nl'){
                 $SAtext="Volwaardige dienstverlening";
                }else{
                  $SAtext="Service complet";  
                }
             }else{
                 if($request->locale=='nl'){
                 $SAtext="Verplichte facturatie per email en uitsluitend elektronische communicatie";
                }else{

                    $SAtext="Verplichte facturatie per email en uitsluitend elektronische communicatie";
                }
                 
             }
             
             if($suppliers->durationE==0){
             $duration="Onbepaald";     
             }else{
                 
              $duration=$suppliers->durationE;   
             }
             
             if($suppliers->fixed_indiableE=='Ind'){

                    if($request->locale=='nl'){
                    $fixed_indiableE="Variabel"; 
                    }else{
                    $fixed_indiableE="Variable"; 

                    }

             }else{
                 
              if($request->locale=='nl'){
              $fixed_indiableE='Vast'; 
              }else{
                $fixed_indiableE='Fixe';

              } 

             }
             
             if($product_idE->green_percentage=='100%'){
                        if($request->locale=='nl'){
                        $green_percentage="Ja";
                        }else{
                        $green_percentage="Oui";
                        } 

             }else{
                
                        if($request->locale=='nl'){
                        $green_percentage='Nee'; 
                        }else
                        {
                        $green_percentage='Non';

                        }

             }
             
             
             $locale=$request->locale;

             if($locale=='fr'){

                        if($duration==1){

                            $year="an";

                        }elseif($duration=='Onbepaald'){

                            $year="";
                            $duration="Durée indéterminée";
                        }

             }else{


                $year="jaar";

                if($duration==1){

                            $year="jaar";

                        }elseif($duration=='Onbepaald'){

                            $year="";
                            $duration="Onbepaald jaar";
                        }

             }

             if($locale=='fr'){

                $click="Cliquez ici";

             }else{

                $click="Klik hier";

             }

             
         $html.='<tr>
      <td><span data-ps-field="supplier_name" data-ps-product="Cheapest">'.$suppliers->productE.'</span> + <span data-ps-field="product_name" data-ps-product="Cheapest">Gas '.$suppliers->productG.'</span></td>
      <td data-ps-field="Duurtijd" data-ps-product="Cheapest">'.$duration.' '.$year.'</td>
      <td data-ps-field="Vast of variabel" data-ps-product="Cheapest">'.$fixed_indiableE.'</td>
      <td data-ps-field="Groene energie" data-ps-product="Cheapest">'.$green_percentage.'</td>
      <td data-ps-field="Dienstverlening" data-ps-product="Cheapest">'.$SAtext.'</td>
      <td data-ps-field="Klant worden" data-ps-product="Cheapest"><a href="'.$suppliers->URL_NL.'" target="_blank">'.$click.'</a></td>
      </tr>';
         }
      
      
      echo $html;
      
        }
    }
    
        public function supplier_detail(Request $request)
    {
        
          $locale= $request->locale;
            
         if($request->type=='elec'){
             
        $supplier = DynamicElectricResidential::whereHas('staticData', function($q) {
                    $q->where('acticve', 'Y');
                })
                 ->where('supplier',$request->supplier)
                ->get();
                
                
                 // pro list
        
            
        $query['locale']=$locale;
       
        if(isset($request->api_postal_code)){
        $query['postalCode']=$request->api_postal_code;
        }else{
            
         $query['postalCode']=2000;
        }
        if(isset($request->api_locale)){
        $query['customerGroup']=$request->api_comparison_type;
        }else{
            
           $query['customerGroup']='residential';
        }
        if(isset($request->api_locale)){
        $query['registerNormal']=$request->api_usage_single;
        }else{
            
            $query['registerNormal']=3500;
        }
        
        
        $query['first_residence']=1;
        $query['meterType']='single';
        $query['category']='electricity';
       
       

        
          try {

        $client = new \GuzzleHttp\Client(); 
                  $request = $client->post('http://api.tariefchecker.be/api/calculation', [
                      'headers' => [
                          'Accept' => 'application/json',
                          'Content-type' => 'application/x-www-form-urlencoded',
                          'Authorization' => 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiMWFmMjVkYWNmZTNiM2I0MmZjOTJkMTU5MjIxY2RjNjNkY2MxMzEwZWU3NDJlM2YzNmRiOWZiMDZhZmMwNGMyNTgyNzEzNjRhYjU5Y2VkZGQiLCJpYXQiOjE2NDMyODY2MjcsIm5iZiI6MTY0MzI4NjYyNywiZXhwIjoyMjc0NDM4NjI3LCJzdWIiOiI1Iiwic2NvcGVzIjpbXX0.rmiDd2sM0kduf6CPed5rjbqPL4Fui-MDdOKViiPn49pcJEukW_kA2ByuJfHNUIe9rctIXsovX1T8kgeer6TxgQkGCvruO98zcGklVv470en8ul6NzOCMmaemX4cJj4XQYlcI1_-z6tnHqtbbc7_-TyvezidDGslhMAMmtREicgrubnp9VGyl6YtE_pXHedruJ7PxYsc2_Gqu-osdFOdEW6hxN3uPlpKbuHgrf9DvJr8B3PSDQLPl49Q9HzrL-vPgayZTpNFINpCw1QBKk_ooWo861UZQ_cE33TdNfXyoJ5WnXQ-AjvtInfw7C9skq57C9X4NmfsllWCacNn9IYNs4uocuFo259TbRXNuooHsWTDTty4kalcp3LD7G0exCTTDC3_QsEoZI6694ct8Fi0gOJ05thoS5grKIfKyFkRqu1eOS2wMdNs-6KZXwVQ6fv1sJE-VjdIKXoj-r6wo_FPSceB599yz22gwVQLnDQJAvu0OahSyU8DG3VMH__ItYBuTI0uOTJZerwaRwmnTkSWbWczA4c8AEb1H_W-G4Yblh4D9y_ZOW7FvvFj53dCX83mzUyBN3HahqzD8ZX0IvZXolZHLxluIOlFoR9HiLNzTZFrJSzWru39AjNmbaK8-AAydGlF606uGglo76ES7D7dOvDa5lgUjYzRvby8jDpRSkzY'],
                      'query' => $query 
                  ]);

       
        $response = $request->getBody()->getContents();       
        $json = json_decode($response, true);

          } catch (\Exception $e) {

            $response = ['status' => false, 'message' => $e->getMessage()];

            }
            
              $collection = collect($json['products']);
              
        
        // end-pro list
         
         
         
         $html="";
         
         foreach($supplier as $suppliers){
             $product_idE=StaticElecticResidential::where('product_id',$suppliers->product_idE)->first();
            
             
             if($suppliers->staticData->service_level_payment=="Free" && $suppliers->staticData->service_level_invoicing=="Free" && $suppliers->staticData->service_level_contact=="Free"){
                 
                 $SAE="free";
             }else{
                 
                 $SAE="active";
             }
             
             
             
             
             if($SAE=="free"){
                 if($locale=='nl'){
                    $SAtext="Volwaardige dienstverlening";
                }else{
                    $SAtext="Service complet";
                }
                 
             }else{
                 
                 $SAtext="Verplichte facturatie per email en uitsluitend elektronische communicatie";
                 
             }
             
             
             if($suppliers->duration==0){

                        if($locale=='nl'){
                            $duration="Onbepaald jaar";     
                        }else{
                            $duration="Durée indéterminée"; 
                        }


             }else{
                 
                        if($locale=='nl'){

                            $duration=$suppliers->duration." "."jaar";

                        } else{

                            if($suppliers->duration>1){

                               $duration=$suppliers->duration." "."ans"; 

                            }else{

                                $duration=$suppliers->duration." "."an";
                            }
                            
                        }     
             }
             
             if($suppliers->fixed_indexed=='Ind'){
             $fixed_indiableE="Variabel";   
             }else{
                 if($locale=='nl'){
              $fixed_indiableE='Vast'; 
              }else{
                $fixed_indiableE='Prix fixe';
              }  
             }
             
             if($suppliers->staticData->green_percentage=='100%'){
                if($locale=='nl'){
                    $green_percentage="100% groene energie"; 
                }else{
                    $green_percentage="Energie 100% verte"; 
                }
               
             }else{
                 if($locale=='nl'){
              $green_percentage='0% groene energie'; 
              }else{
                $green_percentage='0% Energie verte';
              }  
             }






             
            
             $proID=$suppliers->product_id;
             $filter = $collection->filter(function($value, $key) use($proID) {
                    $satisfied = 0;
                    $total_condition = 0;
                     $total_condition++;
                    if ($value['product']['id']==$proID) {
                       
                                        
                            $satisfied ++;
                       
                    }
                    
                    
                    return $satisfied == $total_condition;
                });
        
                $getProducts= $filter->first();
                $disc=($getProducts['price']['totals']['year']['excl_promo']-$getProducts['price']['totals']['year']['incl_promo'])/100;
                 if($getProducts['product']['service_level_payment']=="free" && $getProducts['product']['service_level_invoicing']=="free" && $getProducts['product']['service_level_contact']=="free" )
                {
                    if($locale=='nl'){
                    $text="Volwaardige dienstverlening";
                    $SA='true';
                    }else{
                    $text="Service complet";
                    $SA='true';  
                    }
                    
                }else{
                    
                   
                if($locale=='nl'){
                     $text="Verplichte facturatie per email en uitsluitend elektronische communicatie";
                     $SA='false';
                 }else{
                    $text="Tarif fixe durant l'année pour l'électricité verte en Belgique.";
                    $SA='false';

                 }
                }
                
             
            if($locale=='nl'){ 
         $html.='<div class="col-md-12 col-lg-12 details"><div class="col-md-6 col-lg-5 detail-info">
         <h3>'.$suppliers->product.'</h3>
         <ul><li>Duurtijd: '.$duration.'</li><li>'.$fixed_indiableE.' </li><li>'.$green_percentage.'</li><li>'.$text.'</li>';
         if($disc>0){
         $html.='<li><strong>korting van&nbsp;€'.number_format(($getProducts['price']['totals']['year']['excl_promo']-$getProducts['price']['totals']['year']['incl_promo'])/100,2,',', '.').'/jaar</strong>. Enkel geldig voor nieuwe klanten die zich <strong>via Tariefchecker</strong> registreren voor 30/'.date('m').'/'.date('Y').'</li>';
         }
         $html.='</ul>
         </div>
         <div class="col-md-6 col-lg-6 col-lg-offset-1">
         <h5>Voor wie is dit tarief?</h5>';
         if($SA=='false'){
         $html.='<p>Verlaagd tarief voor groene stroom aan een '.$fixed_indiableE.' tarief gedurende '.$duration.' jaar met de verplichting facturen per email te ontvangen en enkel online contact op te nemen met '.$suppliers->supplierE.'.</p>';
         
         }else{
             
            $html.='<p>'.$fixed_indiableE.' tarief gedurende '.$duration.'  jaar voor groene stroom van Belgische bodem.</p>';  
         }
         
         $html.='
         
        
         <p><a href="'.$suppliers->URL_NL.'" target="_blank" class="red">Word klant</a> of <a href="http://www.tariefchecker.be">bereken jouw persoonlijk tarief</a></p>
         </div></div>';

     }else{

        $html.='<div class="col-md-12 col-lg-12 details"><div class="col-md-6 col-lg-5 detail-info">
         <h3>'.$suppliers->product.'</h3>
         <ul><li>Durée: '.$duration.'</li><li>'.$fixed_indiableE.' </li><li>'.$green_percentage.'</li><li>'.$text.'</li>';
         if($disc>0){
         $html.='<li><strong>korting van&nbsp;€'.number_format(($getProducts['price']['totals']['year']['excl_promo']-$getProducts['price']['totals']['year']['incl_promo'])/100,2,',', '.').'/jaar</strong>. Enkel geldig voor nieuwe klanten die zich <strong>via Tariefchecker</strong> registreren voor 30/'.date('m').'/'.date('Y').'</li>';
         }
         $html.='</ul>
         </div>
         <div class="col-md-6 col-lg-6 col-lg-offset-1">
         <h5>Meilleur tarif pour :</h5>';
         if($SA=='false'){
         $html.='<p>Verlaagd tarief voor groene stroom aan een '.$fixed_indiableE.' tarief gedurende '.$duration.' jaar met de verplichting facturen per email te ontvangen en enkel online contact op te nemen met '.$suppliers->supplierE.'.</p>';
         
         }else{
             
            $html.='<p>Tarif fixe durant l\'année pour l\'électricité verte en Belgique.</p>';  
         }
         
         $html.='
         
        
         <p><a href="'.$suppliers->URL_NL.'" target="_blank" class="red">Devenez client</a> ou <a href="http://www.tariefchecker.be">calculez votre tarif personnalisé</a></p>
         </div></div>';




         }



     }
      
      
      echo $html;
                
                
                
                
         }else{


  
        
        $supplier=StaticPackResidential::select(
                'static_pack_residentials.*',
                'dynamic_electric_residentials.product_id as product_idE','dynamic_electric_residentials.date as dateE','dynamic_electric_residentials.valid_from as valid_fromE','dynamic_electric_residentials.valid_till as valid_tillE','dynamic_electric_residentials.supplier as supplierE','dynamic_electric_residentials.product as productE','dynamic_electric_residentials.fuel as fuelE','dynamic_electric_residentials.duration as durationE','dynamic_electric_residentials.fixed_indexed as fixed_indiableE','dynamic_electric_residentials.fixed_indexed as fixed_indiableE','dynamic_electric_residentials.customer_segment as segmentE','dynamic_electric_residentials.VL as VLE','dynamic_electric_residentials.WA as WAE','dynamic_electric_residentials.BR as BRE','dynamic_electric_residentials.volume_lower as volume_lowerE','dynamic_electric_residentials.volume_upper as volume_upperE','dynamic_electric_residentials.price_single as price_singleE','dynamic_electric_residentials.price_day as price_dayE','dynamic_electric_residentials.price_night as price_nightE','dynamic_electric_residentials.price_excl_night as price_excl_nightE','dynamic_electric_residentials.ff_single as ff_singleE','dynamic_electric_residentials.ff_day_night as ff_day_nightE','dynamic_electric_residentials.ff_excl_night as ff_excl_nightE','dynamic_electric_residentials.gsc_vl as gsc_vlE','dynamic_electric_residentials.wkc_vl as wkc_vlE','dynamic_electric_residentials.gsc_wa as gsc_waE','dynamic_electric_residentials.gsc_br as gsc_brE','dynamic_electric_residentials.prices_url_nl as prices_url_nlE','dynamic_electric_residentials.prices_url_fr as prices_url_frE','dynamic_electric_residentials.index_name as indexE','dynamic_electric_residentials.index_value as waardeE','dynamic_electric_residentials.coeff_single as coeff_singleE','dynamic_electric_residentials.term_single as term_singleE','dynamic_electric_residentials.coeff_day as coeff_dayE','dynamic_electric_residentials.term_day as term_dayE','dynamic_electric_residentials.coeff_night as coeff_nightE','dynamic_electric_residentials.term_night as term_nightE','dynamic_electric_residentials.coeff_excl as coeff_exclE','dynamic_electric_residentials.term_excl as term_exclE',
                'dynamic_gas_residentials.product_id as product_idG','dynamic_gas_residentials.date as dateG','dynamic_gas_residentials.valid_from as valid_fromG','dynamic_gas_residentials.valid_till as valid_tillG','dynamic_gas_residentials.supplier as supplierG','dynamic_gas_residentials.product as productG','dynamic_gas_residentials.fuel as fuelG','dynamic_gas_residentials.duration as durationG','dynamic_gas_residentials.fixed_indexed as fixed_indiableG','dynamic_gas_residentials.fixed_indexed as fixed_indiableG','dynamic_gas_residentials.segment as segmentG','dynamic_gas_residentials.VL as VLG','dynamic_gas_residentials.WA as WAG','dynamic_gas_residentials.BR as BRG','dynamic_gas_residentials.volume_lower as volume_lowerG','dynamic_gas_residentials.volume_upper as volume_upperG',
                'dynamic_gas_residentials.ff as ffG',
                'dynamic_gas_residentials.price_gas as price_gasG','dynamic_gas_residentials.prices_url_nl as prices_url_nlG','dynamic_gas_residentials.prices_url_fr as prices_url_frG','dynamic_gas_residentials.index_name as indexG','dynamic_gas_residentials.index_value as waardeG','dynamic_gas_residentials.coeff as coeffG','dynamic_gas_residentials.term as term')            
            ->Join('dynamic_electric_residentials','dynamic_electric_residentials.product_id','=','static_pack_residentials.pro_id_E')
            ->Join('dynamic_gas_residentials','dynamic_gas_residentials.product_id','=','static_pack_residentials.pro_id_G')   
            //->where('dynamic_gas_residentials.'.$region.'','=','Y') 
            //->whereDate('dynamic_gas_residentials.valid_from','<=',$currentDate)->whereDate('dynamic_gas_residentials.valid_till','>=',$currentDate)
            //->whereDate('dynamic_electric_residentials.valid_from','<=',$currentDate)->whereDate('dynamic_electric_residentials.valid_till','>=',$currentDate)
            ->where('static_pack_residentials.active','Y')
            ->where('dynamic_gas_residentials.supplier',$request->supplier)
            ->where('dynamic_electric_residentials.supplier',$request->supplier)
            ->get();
        //$supplier=StaticPackResidential::where('supplier',$request->supplier)->get();
        
         //return Response::json($supplier);
         
         
               // pro list
        
           
        if(isset($request->api_postal_code)){
        $query['postalCode']=$request->api_postal_code;
        }else{
            
         $query['postalCode']=2000;
        }
        if(isset($request->api_locale)){
        $query['customerGroup']=$request->api_comparison_type;
        }else{
            
           $query['customerGroup']='residential';
        }
        if(isset($request->api_locale)){
        $query['registerNormal']=$request->api_usage_single;
        }else{
            
            $query['registerNormal']=3500;
        }
        if(isset($request->api_locale)){
        $query['registerG']=$request->api_usage_gas;
        }else{
            
         $query['registerG']=25000;   
        }
        
        $query['first_residence']=1;
        $query['meterType']='single';
        $query['category']='pack';
        $query['locale']=$locale;
       
       
        
        
          try {

        $client = new \GuzzleHttp\Client(); 
                  $request = $client->post('http://api.tariefchecker.be/api/calculation', [
                      'headers' => [
                          'Accept' => 'application/json',
                          'Content-type' => 'application/x-www-form-urlencoded',
                          'Authorization' => 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiMWFmMjVkYWNmZTNiM2I0MmZjOTJkMTU5MjIxY2RjNjNkY2MxMzEwZWU3NDJlM2YzNmRiOWZiMDZhZmMwNGMyNTgyNzEzNjRhYjU5Y2VkZGQiLCJpYXQiOjE2NDMyODY2MjcsIm5iZiI6MTY0MzI4NjYyNywiZXhwIjoyMjc0NDM4NjI3LCJzdWIiOiI1Iiwic2NvcGVzIjpbXX0.rmiDd2sM0kduf6CPed5rjbqPL4Fui-MDdOKViiPn49pcJEukW_kA2ByuJfHNUIe9rctIXsovX1T8kgeer6TxgQkGCvruO98zcGklVv470en8ul6NzOCMmaemX4cJj4XQYlcI1_-z6tnHqtbbc7_-TyvezidDGslhMAMmtREicgrubnp9VGyl6YtE_pXHedruJ7PxYsc2_Gqu-osdFOdEW6hxN3uPlpKbuHgrf9DvJr8B3PSDQLPl49Q9HzrL-vPgayZTpNFINpCw1QBKk_ooWo861UZQ_cE33TdNfXyoJ5WnXQ-AjvtInfw7C9skq57C9X4NmfsllWCacNn9IYNs4uocuFo259TbRXNuooHsWTDTty4kalcp3LD7G0exCTTDC3_QsEoZI6694ct8Fi0gOJ05thoS5grKIfKyFkRqu1eOS2wMdNs-6KZXwVQ6fv1sJE-VjdIKXoj-r6wo_FPSceB599yz22gwVQLnDQJAvu0OahSyU8DG3VMH__ItYBuTI0uOTJZerwaRwmnTkSWbWczA4c8AEb1H_W-G4Yblh4D9y_ZOW7FvvFj53dCX83mzUyBN3HahqzD8ZX0IvZXolZHLxluIOlFoR9HiLNzTZFrJSzWru39AjNmbaK8-AAydGlF606uGglo76ES7D7dOvDa5lgUjYzRvby8jDpRSkzY'],
                      'query' => $query 
                  ]);

       
        $response = $request->getBody()->getContents();       
        $json = json_decode($response, true);

          } catch (\Exception $e) {

            $response = ['status' => false, 'message' => $e->getMessage()];

            }
            
              $collection = collect($json['products']);
        
        // end-pro list
         
         
         
         $html="";
         
         foreach($supplier as $suppliers){
             $product_idE=StaticElecticResidential::where('product_id',$suppliers->product_idE)->first();
             $product_idG=StaticGasResidential::where('product_id',$suppliers->product_idG)->first();
             
              if($product_idE->service_level_payment=="Free" && $product_idE->service_level_invoicing=="Free" && $product_idE->service_level_contact=="Free"){
                 
                 $SAE="free";
             }else{
                 
                 $SAE="active";
             }
             
             
             
             
             if($SAE=="free"){
                 if($locale=='nl'){
                 $SAtext="Volwaardige dienstverlening";
                    }else
                    {
                 $SAtext="Service complet";

                    }
             }else{
                 
                 $SAtext="Verplichte facturatie per email en uitsluitend elektronische communicatie";
                 
             }
             
             if($suppliers->durationE==0){
             $duration="Onbepaald";     
             }else{

              if($locale=='nl'){
                 
              $duration=$suppliers->durationE." "."jaar";  
              }else{

                $duration=$suppliers->durationE." "."Année"; 
              } 
             }
             
             if($suppliers->fixed_indiableE=='Ind'){
                        if($locale=='nl'){
                            $fixed_indiableE="Variabel prijs"; 
                        }else{
                            $fixed_indiableE="Variable fixe"; 
                        }  
             }else{
                 if($locale=='nl'){
              $fixed_indiableE='Vast prijs';  
              }else{
                $fixed_indiableE='Prix fixe';
              } 
             }
             
             if($product_idE->green_percentage=='100%'){
                if($locale=='nl'){
             $green_percentage="100% groene energie";   
                }else{
                $green_percentage="Energie 100% verte"; 
                }
             }else{
                 if($locale=='nl'){
              $green_percentage='0% groene energie';   
                 }else{
              $green_percentage='0% Energie verte'; 
                 }
             }
             
            
             $proID=$suppliers->pack_id;
             $filter = $collection->filter(function($value, $key) use($proID) {
                    $satisfied = 0;
                    $total_condition = 0;
                     $total_condition++;
                    if ($value['product']['id']==$proID) {
                       
                                        
                            $satisfied ++;
                       
                    }
                    
                    
                    return $satisfied == $total_condition;
                });
        
                $getProducts= $filter->first();
                $disc=($getProducts['price']['totals']['year']['excl_promo']-$getProducts['price']['totals']['year']['incl_promo'])/100;
                
                if($getProducts['product']['service_level_payment']=="free" && $getProducts['product']['service_level_invoicing']=="free" && $getProducts['product']['service_level_contact']=="free" )
                {
                    if($locale=='nl'){
                        $text="Volwaardige dienstverlening"; 
                    }else{
                         $text="Service complet";
                    }
                    
                     $SA='true';
                   
                    
                }else{
                    
                    $text="Verplichte facturatie per email en uitsluitend elektronische communicatie";
                    $SA="false";
                    
                }

    if($locale=='nl'){  

         $html.='<div class="col-md-12 col-lg-12 details"><div class="col-md-6 col-lg-5 detail-info">
         <h3>'.$suppliers->productE.' + Gas '.$suppliers->productG.'</h3>
         <ul><li>Duurtijd: '.$duration.'</li><li>'.$fixed_indiableE.'</li><li>'.$green_percentage.'</li>
         <li>'.$text.'</li>';

     }else{

        $html.='<div class="col-md-12 col-lg-12 details"><div class="col-md-6 col-lg-5 detail-info">
         <h3>'.$suppliers->productE.' + Gas '.$suppliers->productG.'</h3>
         <ul><li>Durée: '.$duration.'</li><li>'.$fixed_indiableE.' </li><li>'.$green_percentage.'</li>
         <li>'.$text.'</li>';

     }

if($locale=='nl'){
         if($disc>0){
         $html.='<li><strong>korting van&nbsp;€'.number_format(($getProducts['price']['totals']['year']['excl_promo']-$getProducts['price']['totals']['year']['incl_promo'])/100,2,',', '.').'/jaar</strong>. Enkel geldig voor nieuwe klanten die zich <strong>via Tariefchecker</strong> registreren voor 30/'.date('m').'/'.date('Y').'</li>';
         }
         $html.='</ul>
         </div>
         <div class="col-md-6 col-lg-6 col-lg-offset-1">
         <h5>Voor wie is dit tarief?</h5>';
         if($SA=='false'){
         $html.='<p>Verlaagd tarief voor groene stroom + aardgas aan een '.$fixed_indiableE.' tarief gedurende '.$duration.' jaar met de verplichting facturen per email te ontvangen en enkel online contact op te nemen met '.$suppliers->supplierE.'.</p>';
         
         }else{
             
            $html.='<p>'.$fixed_indiableE.' tarief gedurende '.$duration.' jaar voor groene stroom van Belgische bodem + aardgas.</p>';  
         }
         $html.='
         <p><a href="'.$suppliers->URL_NL.'" target="_blank" class="red">Word klant</a> of <a href="http://www.tariefchecker.be">bereken jouw persoonlijk tarief</a></p>
         </div></div>';
         
      }else{


            if($disc>0){
         $html.='<li><strong>korting van&nbsp;€'.number_format(($getProducts['price']['totals']['year']['excl_promo']-$getProducts['price']['totals']['year']['incl_promo'])/100,2,',', '.').'/jaar</strong>. Enkel geldig voor nieuwe klanten die zich <strong>via Tariefchecker</strong> registreren voor 30/'.date('m').'/'.date('Y').'</li>';
         }
         $html.='</ul>
         </div>
         <div class="col-md-6 col-lg-6 col-lg-offset-1">
         <h5>Meilleur tarif pour :</h5>';
         if($SA=='false'){
         $html.='<p>Verlaagd tarief voor groene stroom + aardgas aan een '.$fixed_indiableE.' tarief gedurende '.$duration.' jaar met de verplichting facturen per email te ontvangen en enkel online contact op te nemen met '.$suppliers->supplierE.'.</p>';
         
         }else{
             
            $html.='<p>'.$fixed_indiableE.' tarief gedurende '.$duration.' jaar voor groene stroom van Belgische bodem + aardgas.</p>';  
         }
         $html.='
         <p><a href="'.$suppliers->URL_NL.'" target="_blank" class="red">Devenez client</a> ou <a href="http://www.tariefchecker.be">calculez votre tarif personnalisé</a></p>
         </div></div>';
         }


      }
      
      echo $html;
      
         }
    }
    
    
}
