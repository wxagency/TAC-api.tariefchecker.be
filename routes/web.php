<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
// wp-routes
Route::get('productset','Api\Wp\wpController@productset')->name('productset');
Route::get('supplier','Api\Wp\wpController@supplier')->name('supplier');
Route::get('supplier-details','Api\Wp\wpController@supplier_detail')->name('supplier-details');
    // wp-routes-end

Route::get('/clear-cache', function() 
{
    Artisan::call('cache:clear');
    Artisan::call('view:clear');
    return "Cache is cleared";
});

Route::get('/', function () 
{
    return view('welcome');
});

Route::get('/reset-popularity', 'PopularityresetController@reset_popularity')->name('reset_popularity');

Route::get('/confirm-sync', function() 
{
    Artisan::call('confirm:sync');
    echo "Success: Data synchronization completed";
});

Auth::routes([
  'register' => false, // Registration Routes...
  'reset' => false, // Password Reset Routes...
  'verify' => false, // Email Verification Routes...
]);

Route::get('/home', 'HomeController@index')->name('home');
Route::get('calculation','Api\Calculations\CalculationController@index')->name('calculation');
