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

/*Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});*/

Route::post('login', 'UserController@login');
Route::post('signup', 'UserController@signup');

Route::group([
    'middleware' => 'auth:api'
], function() {
    Route::get('logout', 'UserController@logout');
    Route::get('products', 'ProductController@getAllProducts')->name('allProducts');
    Route::get('product/{id}', 'ProductController@getSingleProduct')->name('singleProduct');
    Route::post('product/create', 'ProductController@create')->name('createProduct');
    Route::get('product/edit/{id}', 'ProductController@getSingleProduct')->name('editProduct');
    Route::post('product/update/{id}', 'ProductController@update')->name('updateProduct');
    Route::delete('product/delete/{id}', 'ProductController@delete')->name('deleteProduct');
    Route::get('user', 'UserController@user');
    Route::post('place-order', 'UserController@placeOrder')->name('placeOrder');
});
