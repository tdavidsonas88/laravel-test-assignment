<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TaskController;
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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});



Route::post('login', 'App\Http\Controllers\AuthController@login')->name('login');
Route::post('register', 'App\Http\Controllers\AuthController@register');

Route::group(['middleware' => 'auth.jwt'], function () {
    Route::post('logout', 'App\Http\Controllers\AuthController@logout');
    Route::resource('tasks', TaskController::class);
    Route::put('/tasks/close/{task}', 'App\Http\Controllers\TaskController@close');

    // routes related to messages
    Route::post('/tasks/{task}/messages', 'App\Http\Controllers\MessageController@create');
    Route::put('/messages/{message}', 'App\Http\Controllers\MessageController@update');
    Route::get('/messages/{message}', 'App\Http\Controllers\MessageController@show');
    Route::delete('/messages/{message}', 'App\Http\Controllers\MessageController@destroy');
});


