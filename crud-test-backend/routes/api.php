<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::group(['prefix' => 'user'], function () {
    Route::get('/', 'App\Http\Controllers\User\UserController@index')->name('api.user.index');
    Route::get('show/{id}', 'App\Http\Controllers\User\UserController@show')->name('api.user.show');
    Route::post('store', 'App\Http\Controllers\User\UserController@store')->name('api.user.store');
    Route::post('update/{id}', 'App\Http\Controllers\User\UserController@update')->name('api.user.update');
    Route::delete('destroy/{id}', 'App\Http\Controllers\User\UserController@destroy')->name('api.user.destroy');
});
