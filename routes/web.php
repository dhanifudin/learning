<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Laravel\Fortify\Features;
use App\Http\Controllers\LearningStyleSurveyController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Student\ContentController as StudentContentController;
use App\Http\Controllers\Teacher\ContentController as TeacherContentController;
use App\Http\Controllers\Student\AnalyticsController as StudentAnalyticsController;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

// Unified Dashboard Route based on user role
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Content routes accessible from dashboard based on role
    Route::get('/dashboard/create', [DashboardController::class, 'create'])->name('dashboard.create');
    Route::post('/dashboard', [DashboardController::class, 'store'])->name('dashboard.store');
    Route::get('/dashboard/{id}', [DashboardController::class, 'show'])->name('dashboard.show');
    Route::get('/dashboard/{id}/edit', [DashboardController::class, 'edit'])->name('dashboard.edit');
    Route::put('/dashboard/{id}', [DashboardController::class, 'update'])->name('dashboard.update');
    Route::delete('/dashboard/{id}', [DashboardController::class, 'destroy'])->name('dashboard.destroy');
    
    // Student-specific routes
    Route::post('/dashboard/{id}/complete', [DashboardController::class, 'markComplete'])->name('dashboard.complete');
    Route::get('/dashboard/{id}/download', [DashboardController::class, 'download'])->name('dashboard.download');
    Route::post('/dashboard/{id}/track', [DashboardController::class, 'track'])->name('dashboard.track');
    Route::get('/dashboard/filter/recommendations', [DashboardController::class, 'recommendations'])->name('dashboard.recommendations');
    Route::get('/dashboard/filter/recent', [DashboardController::class, 'recent'])->name('dashboard.recent');
    Route::get('/dashboard/filter/search', [DashboardController::class, 'search'])->name('dashboard.search');
    Route::get('/dashboard/subject/{subject}', [DashboardController::class, 'bySubject'])->name('dashboard.by-subject');
    
    // Student Analytics Routes
    Route::prefix('student/analytics')->name('student.analytics.')->middleware('role:student')->group(function () {
        Route::get('/', [StudentAnalyticsController::class, 'index'])->name('index');
        Route::get('/performance', [StudentAnalyticsController::class, 'performance'])->name('performance');
        Route::get('/journey', [StudentAnalyticsController::class, 'learningJourney'])->name('journey');
        Route::get('/patterns', [StudentAnalyticsController::class, 'studyPatterns'])->name('patterns');
        Route::get('/report', [StudentAnalyticsController::class, 'generateReport'])->name('report');
        Route::get('/data', [StudentAnalyticsController::class, 'analyticsData'])->name('data');
        Route::get('/predictions', [StudentAnalyticsController::class, 'predictions'])->name('predictions');
        Route::post('/feedback/{feedbackId}/read', [StudentAnalyticsController::class, 'markFeedbackRead'])->name('feedback.read');
        Route::post('/recommendations/request', [StudentAnalyticsController::class, 'requestRecommendations'])->name('recommendations.request');
    });
    
    // Teacher-specific routes
    Route::post('/dashboard/{id}/duplicate', [DashboardController::class, 'duplicate'])->name('dashboard.duplicate');
    Route::post('/dashboard/{id}/toggle-status', [DashboardController::class, 'toggleStatus'])->name('dashboard.toggle-status');
    Route::get('/dashboard/{id}/analytics', [DashboardController::class, 'analytics'])->name('dashboard.analytics');
});

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

// Legacy route redirects for backward compatibility
Route::middleware(['auth', 'verified'])->group(function () {
    // Redirect old student content routes to dashboard
    Route::prefix('student/content')->group(function () {
        Route::get('/', function () {
            return redirect('/dashboard');
        });
        Route::get('/{any}', function () {
            return redirect('/dashboard');
        })->where('any', '.*');
    });
    
    // Redirect old teacher content routes to dashboard
    Route::prefix('teacher/content')->group(function () {
        Route::get('/', function () {
            return redirect('/dashboard');
        });
        Route::get('/{any}', function () {
            return redirect('/dashboard');
        })->where('any', '.*');
    });
    
    // Debug route for teacher data (temporary)
    Route::get('/debug/teacher', function () {
        $user = Auth::user();
        $teacher = $user->teacher ?? null;
        return response()->json([
            'user' => $user,
            'teacher' => $teacher,
            'user_role' => $user->role,
            'has_teacher_relationship' => $teacher ? 'yes' : 'no'
        ]);
    });
});

require __DIR__.'/settings.php';
