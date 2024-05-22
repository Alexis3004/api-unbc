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
Route::post('/login', 'App\Http\Controllers\LoginController@authenticate')->name('login');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', 'App\Http\Controllers\LoginController@logout')->name('logout');
    Route::post('/logoutAll', 'App\Http\Controllers\LoginController@logoutAll')->name('logoutAll');
});
