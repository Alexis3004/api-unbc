<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
Route::prefix('auth')->group(function () {
    Route::post('/login', 'App\Http\Controllers\LoginController@authenticate')->name('login');
    Route::middleware('auth:sanctum')->group(function () {
        Route::delete('/logout', 'App\Http\Controllers\LoginController@logout')->name('logout');
        Route::delete('/logout-all', 'App\Http\Controllers\LoginController@logoutAll')->name('logoutAll');
    });
});


Route::prefix('user')->group(function () {
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/get-user', 'App\Http\Controllers\UserController@getLoginUser')->name('user.get.user');
        Route::get('/', 'App\Http\Controllers\UserController@index')->name('user.index')->middleware('role:admin');
        Route::get('/{id}', 'App\Http\Controllers\UserController@show')->name('user.show')->middleware('role:admin');
        Route::put('/', 'App\Http\Controllers\UserController@update')->name('user.update');
        Route::patch('/{id}', 'App\Http\Controllers\UserController@updateRole')->name('user.updateRole')->middleware('role:admin');
        Route::delete('/{id}', 'App\Http\Controllers\UserController@destroy')->name('user.destroy');
        Route::post('/{id}/restore', 'App\Http\Controllers\UserController@restore')->name('user.restore')->middleware('role:admin');
    });
    Route::post('/', 'App\Http\Controllers\UserController@store')->name('user.store');
});
