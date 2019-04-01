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

Route::group(array('prefix' => '/'), function()
{

  Route::get('/', function () {
      return response()->json(['message' => 'API de Usuarios', 'status' => 'Conectado']);
  });

  // Endpoints de Usuarios
  Route::get('users/search', 'UsersController@search');
  Route::get('users/search_ids', 'UsersController@searchIds');
  Route::resource('users', 'UsersController');
});
