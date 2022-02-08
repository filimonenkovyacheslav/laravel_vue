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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('properties/{params?}', 'Properties\PropertyController@getAllPropertiesJson');
Route::get('property/{slug}', 'Properties\PropertyController@getPropertyBySlugJson');
Route::get('agencies/{params?}', 'Agencies\AgencyController@getAllUsersJson');
Route::get('agency/{slug}', 'Profile\UserController@getUserBySlugJson');
Route::get('agents/{params?}', 'Agents\AgentController@getAllUsersJson');
Route::get('agent/{slug}', 'Profile\UserController@getUserBySlugJson');
Route::get('all/{slug}', 'Profile\UserController@getUserBySlugJson');
