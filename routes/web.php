<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;
use App\Http\Controllers\LearningStyleSurveyController;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Learning Style Survey Routes
Route::middleware(['auth', 'verified'])->group(function () {
    // Student Survey Routes
    Route::prefix('surveys')->name('survey.')->group(function () {
        Route::get('/', [LearningStyleSurveyController::class, 'index'])->name('index');
        Route::get('/{survey}', [LearningStyleSurveyController::class, 'show'])->name('show');
        Route::post('/{survey}/response', [LearningStyleSurveyController::class, 'storeResponse'])->name('response.store');
        Route::get('/results/{surveyResponse}', [LearningStyleSurveyController::class, 'showResults'])->name('results');
        Route::get('/evolution/data', [LearningStyleSurveyController::class, 'getStyleEvolution'])->name('evolution');
    });

    // Admin Survey Management Routes
    Route::prefix('admin/surveys')->name('admin.surveys.')->middleware('role:admin')->group(function () {
        Route::get('/', [LearningStyleSurveyController::class, 'adminIndex'])->name('index');
        Route::get('/create', [LearningStyleSurveyController::class, 'create'])->name('create');
        Route::post('/', [LearningStyleSurveyController::class, 'store'])->name('store');
        Route::post('/{survey}/toggle-status', [LearningStyleSurveyController::class, 'toggleStatus'])->name('toggle-status');
    });
});

require __DIR__.'/settings.php';
