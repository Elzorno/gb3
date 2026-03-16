<?php

use App\Http\Controllers\KidAuthController;
use App\Http\Controllers\RotationAssignmentsController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('rewrite-home');
})->name('rewrite.home');

Route::get('/kid/login', [KidAuthController::class, 'showLogin'])->name('kid.login');
Route::post('/kid/login', [KidAuthController::class, 'login'])->name('kid.login.submit');
Route::post('/kid/logout', [KidAuthController::class, 'logout'])->name('kid.logout');
Route::get('/rotation/today', [RotationAssignmentsController::class, 'today'])->name('rotation.today');
