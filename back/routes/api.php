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

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::post('/user/signup', [
	'uses' => 'AuthController@signup'
]);

//socialite
Route::get('/login/{service}', [
	'uses' => 'AuthController@redirect'
]);
Route::get('/login/{service}/callback', [
	'uses' => 'AuthController@callback'
]);
////////////


Route::post('/user/signin', [
	'uses' => 'AuthController@signin'
]);

Route::post('/user/test', function(){
	return 'test';
});

Route::group(['middleware'=>['auth.jwt']], function(){ //token authentication
	Route::post('/test', function(){
		return response()->json(['test']);
	});

	Route::post('/user', [
		'uses' => 'UserController@index'
	]);
});