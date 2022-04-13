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

Route::get('/', function () {
    return view('welcome');
});
Route::get('/schedule/info', function () {
    return view('schedule');
});
Route::get('/schedule/menu', 'ScheduleController@menu');
Route::get('/schedule/push','ScheduleController@push');
