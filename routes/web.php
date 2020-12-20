<?php

use Illuminate\Support\Facades\Route;

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

Route::get('/', [\App\Http\Controllers\BBCodeController::class,'index'])->name('welcome');
Route::get('/{parser}',[\App\Http\Controllers\BBCodeController::class,'input'])->name('input');
Route::post('/{parser}',[\App\Http\Controllers\BBCodeController::class,'parser'])->name('parser');
