<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NotifyController;

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

Route::get('/form', function () {
    //event(new \App\Events\NotifyProcessed('Test message from the server'));
    return view('form');
});

// send-notify is working fine
Route::get('/send-notify', [NotifyController::class, 'sendNotify']);







// test sendNotify is working fine
Route::get('/test', function () {
    event(new \App\Events\NotifyProcessed('Test message from the server'));
    return 'form';
});