<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\SupplierPopularity;
use DB;

class PopularityresetController extends Controller
{
    
     public function reset_popularity(){





        $start = Carbon::now()->startOfMonth();
        $end = Carbon::now();


         if($start==$end){

            SupplierPopularity::where('flag',1)->update([

                'popularity'=>0

            ]);

         }
    }
}
