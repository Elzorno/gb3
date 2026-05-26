<?php

use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminProofController;
use App\Http\Controllers\BonusController;
use App\Http\Controllers\DefinitionController;
use App\Http\Controllers\FamilyController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\InfractionController;
use App\Http\Controllers\InfractionReviewController;
use App\Http\Controllers\KidAuthController;
use App\Http\Controllers\PayoutController;
use App\Http\Controllers\PrivilegeController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\RotationAssignmentsController;
use App\Http\Controllers\RotationController;
use App\Http\Controllers\RulesController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\SubmissionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Grounding Buddy Routes
|--------------------------------------------------------------------------
|
| Routes are organized into three groups:
| 1. Public routes (login pages)
| 2. Admin routes (parent dashboard, settings)
| 3. App routes (kid interface)
|
*/

// =============================================================================
// Public Routes
// =============================================================================

Route::get('/', function () {
    // Redirect to appropriate login based on context
    return redirect()->route('app.login');
})->name('home');

Route::view('/design-lab/layouts', 'design.layouts')->name('design.layouts');

// =============================================================================
// Admin Authentication (no middleware - these are the login forms)
// =============================================================================

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AdminAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'login'])->name('login.submit');
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');
    Route::get('/setup', [AdminAuthController::class, 'showSetup'])->name('setup');
    Route::post('/setup', [AdminAuthController::class, 'setup'])->name('setup.submit');
});

// =============================================================================
// Admin Protected Routes
// =============================================================================

Route::prefix('admin')->name('admin.')->middleware('admin.auth')->group(function () {
    // Dashboard
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
    
    // Family management
    Route::get('/family', [FamilyController::class, 'index'])->name('family');
    Route::get('/family/create', [FamilyController::class, 'create'])->name('family.create');
    Route::post('/family', [FamilyController::class, 'store'])->name('family.store');
    Route::get('/family/{kid}/edit', [FamilyController::class, 'edit'])->name('family.edit');
    Route::put('/family/{kid}', [FamilyController::class, 'update'])->name('family.update');
    Route::post('/family/{kid}/reset-pin', [FamilyController::class, 'resetPin'])->name('family.reset-pin');
    Route::post('/family/reorder', [FamilyController::class, 'reorder'])->name('family.reorder');
    Route::delete('/family/{kid}', [FamilyController::class, 'destroy'])->name('family.destroy');
    
    // Rotation / Chore management
    Route::get('/rotation', [RotationController::class, 'index'])->name('rotation');
    Route::get('/rotation/slot/create', [RotationController::class, 'createSlot'])->name('rotation.slot.create');
    Route::post('/rotation/slot', [RotationController::class, 'storeSlot'])->name('rotation.slot.store');
    Route::get('/rotation/slot/{slot}/edit', [RotationController::class, 'editSlot'])->name('rotation.slot.edit');
    Route::put('/rotation/slot/{slot}', [RotationController::class, 'updateSlot'])->name('rotation.slot.update');
    Route::post('/rotation/slot/{slot}/toggle', [RotationController::class, 'toggleSlot'])->name('rotation.slot.toggle');
    Route::post('/rotation/slots/reorder', [RotationController::class, 'reorderSlots'])->name('rotation.slots.reorder');
    Route::delete('/rotation/slot/{slot}', [RotationController::class, 'destroySlot'])->name('rotation.slot.destroy');
    Route::post('/rotation/rule', [RotationController::class, 'updateRule'])->name('rotation.rule.update');
    
    // Privileges / Grounding management
    Route::get('/privileges', [PrivilegeController::class, 'index'])->name('privileges');
    Route::get('/privileges/{kid}', [PrivilegeController::class, 'show'])->name('privileges.show');
    Route::post('/privileges/{kid}/toggle', [PrivilegeController::class, 'toggleLock'])->name('privileges.toggle');
    Route::post('/privileges/{kid}/bank', [PrivilegeController::class, 'updateBank'])->name('privileges.bank');
    Route::post('/privileges/{kid}/ground', [PrivilegeController::class, 'setGrounding'])->name('privileges.ground');
    Route::post('/privileges/{kid}/lift', [PrivilegeController::class, 'liftGrounding'])->name('privileges.lift');
    
    // Reviews hub
    Route::get('/reviews', [ReviewController::class, 'index'])->name('reviews');
    Route::post('/reviews/decide', [ReviewController::class, 'decide'])->name('reviews.decide');
    Route::post('/reviews/undo', [ReviewController::class, 'undo'])->name('reviews.undo');
    Route::get('/submissions/{submission}/proof', [AdminProofController::class, 'show'])->name('submissions.proof');
    
    // Infraction management
    Route::get('/infractions', [InfractionController::class, 'index'])->name('infractions');
    Route::post('/infractions/apply', [InfractionController::class, 'apply'])->name('infractions.apply');
    Route::get('/infractions/review', [InfractionReviewController::class, 'index'])->name('infractions.review');
    Route::post('/infractions/review', [InfractionReviewController::class, 'decide'])->name('infractions.review.decide');
    
    // Definitions (bonuses, infractions)
    Route::get('/definitions', [DefinitionController::class, 'index'])->name('definitions');
    
    // Bonus definitions
    Route::get('/definitions/bonus/create', [DefinitionController::class, 'createBonus'])->name('definitions.bonus.create');
    Route::post('/definitions/bonus', [DefinitionController::class, 'storeBonus'])->name('definitions.bonus.store');
    Route::get('/definitions/bonus/{bonus}/edit', [DefinitionController::class, 'editBonus'])->name('definitions.bonus.edit');
    Route::put('/definitions/bonus/{bonus}', [DefinitionController::class, 'updateBonus'])->name('definitions.bonus.update');
    Route::post('/definitions/bonus/{bonus}/toggle', [DefinitionController::class, 'toggleBonus'])->name('definitions.bonus.toggle');
    Route::post('/definitions/bonuses/reorder', [DefinitionController::class, 'reorderBonuses'])->name('definitions.bonuses.reorder');
    Route::delete('/definitions/bonus/{bonus}', [DefinitionController::class, 'destroyBonus'])->name('definitions.bonus.destroy');
    
    // Infraction definitions
    Route::get('/definitions/infraction/create', [DefinitionController::class, 'createInfraction'])->name('definitions.infraction.create');
    Route::post('/definitions/infraction', [DefinitionController::class, 'storeInfraction'])->name('definitions.infraction.store');
    Route::get('/definitions/infraction/{infraction}/edit', [DefinitionController::class, 'editInfraction'])->name('definitions.infraction.edit');
    Route::put('/definitions/infraction/{infraction}', [DefinitionController::class, 'updateInfraction'])->name('definitions.infraction.update');
    Route::post('/definitions/infraction/{infraction}/toggle', [DefinitionController::class, 'toggleInfraction'])->name('definitions.infraction.toggle');
    Route::post('/definitions/infractions/reorder', [DefinitionController::class, 'reorderInfractions'])->name('definitions.infractions.reorder');
    Route::delete('/definitions/infraction/{infraction}', [DefinitionController::class, 'destroyInfraction'])->name('definitions.infraction.destroy');
    
    // Payout requests management
    Route::post('/payouts/decide', [PayoutController::class, 'adminDecide'])->name('payouts.decide');
    
    // Settings
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
    Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');
});

// =============================================================================
// Kid/App Authentication
// =============================================================================

Route::prefix('app')->name('app.')->group(function () {
    Route::get('/login', [KidAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [KidAuthController::class, 'login'])->name('login.submit');
    Route::post('/logout', [KidAuthController::class, 'logout'])->name('logout');
});

// =============================================================================
// Kid/App Protected Routes
// =============================================================================

Route::prefix('app')->name('app.')->middleware('kid.auth')->group(function () {
    // Today's view (main dashboard for kids)
    Route::get('/today', [RotationAssignmentsController::class, 'today'])->name('today');
    
    // Weekly schedule grid
    Route::get('/rules', [RulesController::class, 'index'])->name('rules');
    
    // Bonus opportunities
    Route::get('/bonuses', [BonusController::class, 'index'])->name('bonuses');
    Route::post('/bonuses/claim', [BonusController::class, 'claim'])->name('bonuses.claim');
    Route::post('/bonuses/submit', [BonusController::class, 'submit'])->name('bonuses.submit');
    Route::post('/bonuses/payout', [PayoutController::class, 'request'])->name('bonuses.payout');
    
    // History / ledger
    Route::get('/history', [HistoryController::class, 'index'])->name('history');
    
    // Proof submission
    Route::get('/submit', [SubmissionController::class, 'create'])->name('submit');
    Route::post('/submit', [SubmissionController::class, 'storeBase'])->name('submit.store');
});

// =============================================================================
// Legacy Compatibility Redirects
// (Redirect old URLs to new routes - can be removed later)
// =============================================================================

Route::get('/kid/login', fn () => redirect()->route('app.login'));
Route::get('/rotation/today', fn () => redirect()->route('app.today'));
Route::get('/bonus', fn () => redirect()->route('app.bonuses'));
Route::get('/history', fn () => redirect()->route('app.history'));
Route::get('/submission', fn () => redirect()->route('app.submit'));
Route::get('/review', fn () => redirect()->route('admin.reviews'));
Route::get('/infractions', fn () => redirect()->route('admin.infractions'));
