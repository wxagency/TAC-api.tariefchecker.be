<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::get('/test', 'Api\Calculations\CalculationController@test')->name('test');

Route::group(['middleware' => ['json.response']], function () 
{
        Route::middleware('auth:api')->get('/user', function (Request $request) 
        {
        return $request->user();
        });
        
        // public routes
        Route::post('/login', 'Api\AuthController@login')->name('login.api');
        Route::post('/register', 'Api\AuthController@register')->name('register.api');
        Route::post('calculation','Api\Calculations\CalculationController@index')->name('calculation')->middleware('auth:api');
        Route::post('checkup-calculation','Api\Checkup\CheckupController@calculate')->name('checkup-calculation')->middleware('auth:api');
        Route::post('estimate-consumtion','Api\Estimate\EstimateController@index')->name('estimate-consumtion')->middleware('auth:api');
        Route::post('estimate-consumtion-cal','Api\Estimate\EstimateController@calculate')->name('calculate')->middleware('auth:api');
        Route::get('june-calculation','Api\Calculations\june\CalculationController@juneCalculation')->name('calculation');
        Route::post('conversion','Api\Conversion\ConversionController@index')->name('conversion')->middleware('auth:api');    
        Route::post('change-data-sync','Api\ActiveCampaign\activeCampaignController@change_data_sync')->name('change-data-sync')->middleware('auth:api');
        Route::post('user-search-details','Api\UserSearchDetails\UserSearchDetailController@index')->name('user-search-details')->middleware('auth:api');
        Route::get('checkup','Api\Checkup\CheckupController@checkup')->name('checkup');
        // private routes
        Route::middleware('auth:api')->group(function () {
        Route::get('/logout', 'Api\AuthController@logout')->name('logout');
        Route::get('calculation','Api\Calculations\CalculationController@index')->name('calculation');
    });

});

