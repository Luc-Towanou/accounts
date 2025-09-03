<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
Route::get('dashboard/user/', function () {
    return view('dash.dashboard');
});
Route::get('dashboard/user_2/', function () {
    return view('dash.dashboard_co');
});

Route::get('/dashboard/user_tr', fn() => view('dash.dashboard_tr1'));
Route::get('/operation/b', fn() => view('dash.operation_b'));
Route::get('/operation/d', fn() => view('dash.operation_deep'));
Route::get('/logo_eventrush', fn() => view('logo-eventrush'));

