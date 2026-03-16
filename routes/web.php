<?php

use App\Http\Controllers\BonusController;
use App\Http\Controllers\InfractionController;
use App\Http\Controllers\InfractionReviewController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\KidAuthController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\RotationAssignmentsController;
use App\Http\Controllers\SubmissionController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('rewrite-home');
})->name('rewrite.home');

Route::get('/kid/login', [KidAuthController::class, 'showLogin'])->name('kid.login');
Route::post('/kid/login', [KidAuthController::class, 'login'])->name('kid.login.submit');
Route::post('/kid/logout', [KidAuthController::class, 'logout'])->name('kid.logout');
Route::get('/rotation/today', [RotationAssignmentsController::class, 'today'])->name('rotation.today');

Route::get('/submission', [SubmissionController::class, 'create'])->name('submission.create');
Route::post('/submission/base', [SubmissionController::class, 'storeBase'])->name('submission.storeBase');

Route::get('/bonus', [BonusController::class, 'index'])->name('bonus.index');
Route::post('/bonus/claim', [BonusController::class, 'claim'])->name('bonus.claim');
Route::post('/bonus/submit', [BonusController::class, 'submit'])->name('bonus.submit');

Route::get('/history', [HistoryController::class, 'index'])->name('history.index');

Route::get('/infractions', [InfractionController::class, 'index'])->name('infraction.index');
Route::post('/infractions/apply', [InfractionController::class, 'apply'])->name('infraction.apply');
Route::get('/infractions/review', [InfractionReviewController::class, 'index'])->name('infraction.review');
Route::post('/infractions/review', [InfractionReviewController::class, 'decide'])->name('infraction.review.decide');

Route::get('/review', [ReviewController::class, 'index'])->name('review.index');
Route::post('/review/decide', [ReviewController::class, 'decide'])->name('review.decide');
