<?php

namespace App\Http\Controllers\Api\Estimate;

use App\Models\Api\Calculation;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\EstimateConsumption;



use DB;
use Response;
use Validator;

class EstimateController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    
   
    public function index(Request $request)
    {
        
        
        
        $residence=$request->residence;
        $building_type=$request->building_type;
        $isolation_level=$request->isolation_level;
        $heating_system=$request->heating_system;
        
$res=EstimateConsumption::where('residents',$residence)
 ->where('building_type',$building_type)
 ->where('Isolation_level',$isolation_level)
 ->where('Heating_system',$heating_system)
->first();


$res['E_mono']= $res->E_mono;
$res['E_day']= $res->E_day;
$res['E_night']= $res->E_night;
$res['E_excl_night']= $res->E_excl_night;
$res['G']= $res->G;

return $res;


       


    }
    
    public function calculate(Request $request){
        
        $capacity_decen_pro=$request->capacity_decen_pro;
        $consuption1=$request->consuption1;
        $consuption1se=$request->consuption1se;
        $consuption_day_elec1=$request->consuption_day_elec1;
        $consuption_night_elec1=$request->consuption_night_elec1;
        $consuption_excl_night=$request->consuption_excl_night;
        $consumtion_gas1=$request->consumtion_gas1;
        $consuption_day_elec1de=$request->consuption_day_elec1de;
        $consuption_night_elec1de=$request->consuption_night_elec1de;
        $consuption_excl_nightde=$request->consuption_excl_nightde;
        
       
        
        if($capacity_decen_pro==1){
            
            
            $e_mono=800;
            $e_day=600;
            $e_night=200;
            $e_exnight=0;
            
            
        }
        if($capacity_decen_pro==2){
            
            $e_mono=1600;
            $e_day=1100;
            $e_night=500;
            $e_exnight=0;
            
            
        }
        if($capacity_decen_pro==3){
            
            $e_mono=2400;
            $e_day=1700;
            $e_night=700;
            $e_exnight=0;
            
        }
        if($capacity_decen_pro==4){
            
            $e_mono=3200;
            $e_day=2300;
            $e_night=900;
            $e_exnight=0;
            
        }
        if($capacity_decen_pro==5){
            
            $e_mono=4000;
            $e_day=2900;
            $e_night=1100;
            $e_exnight=0;
            
        }
        if($capacity_decen_pro==6){
            
            $e_mono=4800;
            $e_day=3400;
            $e_night=1400;
            $e_exnight=0;
            
        }
        if($capacity_decen_pro==7){
            
           $e_mono=5600;
            $e_day=4000;
            $e_night=1600;
            $e_exnight=0;
            
        }
        if($capacity_decen_pro==8){
            
            $e_mono=6400;
            $e_day=4600;
            $e_night=1800;
            $e_exnight=0;
            
        }
        if($capacity_decen_pro==9){
            
            $e_mono=7200;
            $e_day=5100;
            $e_night=2100;
            $e_exnight=0;
            
        }
        if($capacity_decen_pro==10){
            
            $e_mono=8000;
            $e_day=5700;
            $e_night=2300;
            $e_exnight=0;
            
        }


            
            $res['consuption1']= $consuption1-$e_mono;
            $res['consuption1se']= $consuption1se-$e_mono;
            $res['consuption_day_elec1']= $consuption_day_elec1-$e_day;
            $res['consuption_night_elec1']= $consuption_night_elec1-$e_night;
            $res['consuption_excl_night']= $consuption_excl_night-$e_exnight;
            $res['consumtion_gas1']= $consumtion_gas1;
            $res['consuption_day_elec1de']= $consuption_day_elec1de-$e_day;
            $res['consuption_night_elec1de']= $consuption_night_elec1de-$e_night;
            $res['consuption_excl_nightde']= $consuption_excl_nightde-$e_exnight;
           

        return $res;

        
        
    }
    
    
}
