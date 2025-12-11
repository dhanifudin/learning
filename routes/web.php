<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Laravel\Fortify\Features;
use App\Http\Controllers\LearningStyleSurveyController;
use App\Http\Controllers\Student\ContentController as StudentContentController;
use App\Http\Controllers\Teacher\ContentController as TeacherContentController;

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

// Student Content Routes
Route::middleware(['auth', 'verified', 'role:student'])->prefix('student')->name('student.')->group(function () {
    Route::prefix('content')->name('content.')->group(function () {
        Route::get('/test', [StudentContentController::class, 'test'])->name('test');
        Route::get('/simple', [StudentContentController::class, 'simple'])->name('simple');
        Route::get('/', [StudentContentController::class, 'index'])->name('index');
        Route::get('/recommendations', [StudentContentController::class, 'recommendations'])->name('recommendations');
        Route::get('/recent', [StudentContentController::class, 'recent'])->name('recent');
        Route::get('/search', [StudentContentController::class, 'search'])->name('search');
        Route::get('/subject/{subject}', [StudentContentController::class, 'bySubject'])->name('by-subject');
        Route::get('/{id}', [StudentContentController::class, 'show'])->name('show');
        Route::post('/{id}/complete', [StudentContentController::class, 'markComplete'])->name('complete');
        Route::get('/{id}/download', [StudentContentController::class, 'download'])->name('download');
        Route::post('/{id}/track', [StudentContentController::class, 'track'])->name('track');
    });
});

// Teacher Content Routes
Route::middleware(['auth', 'verified', 'role:teacher'])->prefix('teacher')->name('teacher.')->group(function () {
    // Debug route to check teacher data
    Route::get('/debug', function () {
        $user = Auth::user();
        $teacher = $user->teacher;
        return response()->json([
            'user' => $user,
            'teacher' => $teacher,
            'user_role' => $user->role,
            'has_teacher_relationship' => $teacher ? 'yes' : 'no'
        ]);
    });
    
    Route::prefix('content')->name('content.')->group(function () {
        Route::get('/', [TeacherContentController::class, 'index'])->name('index');
        Route::get('/create', [TeacherContentController::class, 'create'])->name('create');
        Route::post('/', [TeacherContentController::class, 'store'])->name('store');
        Route::get('/{id}', [TeacherContentController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [TeacherContentController::class, 'edit'])->name('edit');
        Route::put('/{id}', [TeacherContentController::class, 'update'])->name('update');
        Route::delete('/{id}', [TeacherContentController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/duplicate', [TeacherContentController::class, 'duplicate'])->name('duplicate');
        Route::post('/{id}/toggle-status', [TeacherContentController::class, 'toggleStatus'])->name('toggle-status');
        Route::get('/{id}/analytics', [TeacherContentController::class, 'analytics'])->name('analytics');
    });
});

require __DIR__.'/settings.php';
