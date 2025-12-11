<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Redirect based on user role to the appropriate dashboard content
        switch ($user->role) {
            case 'student':
                return $this->studentDashboard($request);
            case 'teacher':
                return $this->teacherDashboard($request);
            case 'admin':
                return $this->adminDashboard($request);
            default:
                abort(403, 'Unauthorized role');
        }
    }
    
    private function studentDashboard(Request $request)
    {
        // Get the student content controller to handle the logic
        $studentController = app(\App\Http\Controllers\Student\ContentController::class);
        return $studentController->index($request);
    }
    
    private function teacherDashboard(Request $request)
    {
        // Get the teacher content controller to handle the logic
        $teacherController = app(\App\Http\Controllers\Teacher\ContentController::class);
        return $teacherController->index($request);
    }
    
    private function adminDashboard(Request $request)
    {
        // For now, render a simple admin dashboard
        // This can be expanded with admin-specific content later
        return Inertia::render('Admin/Dashboard', [
            'user' => Auth::user(),
        ]);
    }
    
    // Handle content-related routes that can be used by both students and teachers
    public function show(Request $request, $id)
    {
        $user = Auth::user();
        
        if ($user->role === 'student') {
            $controller = app(\App\Http\Controllers\Student\ContentController::class);
            return $controller->show($request, $id);
        } elseif ($user->role === 'teacher') {
            $controller = app(\App\Http\Controllers\Teacher\ContentController::class);
            return $controller->show($request, $id);
        }
        
        abort(403, 'Unauthorized');
    }
    
    // Handle create content (teacher only)
    public function create(Request $request)
    {
        $user = Auth::user();
        
        if ($user->role !== 'teacher') {
            abort(403, 'Only teachers can create content');
        }
        
        $controller = app(\App\Http\Controllers\Teacher\ContentController::class);
        return $controller->create($request);
    }
    
    // Handle store content (teacher only)
    public function store(Request $request)
    {
        $user = Auth::user();
        
        if ($user->role !== 'teacher') {
            abort(403, 'Only teachers can create content');
        }
        
        $controller = app(\App\Http\Controllers\Teacher\ContentController::class);
        return $controller->store($request);
    }
    
    // Handle edit content (teacher only)
    public function edit(Request $request, $id)
    {
        $user = Auth::user();
        
        if ($user->role !== 'teacher') {
            abort(403, 'Only teachers can edit content');
        }
        
        $controller = app(\App\Http\Controllers\Teacher\ContentController::class);
        return $controller->edit($request, $id);
    }
    
    // Handle update content (teacher only)
    public function update(Request $request, $id)
    {
        $user = Auth::user();
        
        if ($user->role !== 'teacher') {
            abort(403, 'Only teachers can update content');
        }
        
        $controller = app(\App\Http\Controllers\Teacher\ContentController::class);
        return $controller->update($request, $id);
    }
    
    // Handle delete content (teacher only)
    public function destroy(Request $request, $id)
    {
        $user = Auth::user();
        
        if ($user->role !== 'teacher') {
            abort(403, 'Only teachers can delete content');
        }
        
        $controller = app(\App\Http\Controllers\Teacher\ContentController::class);
        return $controller->destroy($request, $id);
    }
    
    // Handle student-specific actions
    public function markComplete(Request $request, $id)
    {
        $user = Auth::user();
        
        if ($user->role !== 'student') {
            abort(403, 'Only students can mark content as complete');
        }
        
        $controller = app(\App\Http\Controllers\Student\ContentController::class);
        return $controller->markComplete($request, $id);
    }
    
    public function download(Request $request, $id)
    {
        $user = Auth::user();
        
        if ($user->role !== 'student') {
            abort(403, 'Only students can download content');
        }
        
        $controller = app(\App\Http\Controllers\Student\ContentController::class);
        return $controller->download($request, $id);
    }
    
    public function track(Request $request, $id)
    {
        $user = Auth::user();
        
        if ($user->role !== 'student') {
            abort(403, 'Only students can track content');
        }
        
        $controller = app(\App\Http\Controllers\Student\ContentController::class);
        return $controller->track($request, $id);
    }
    
    // Handle teacher-specific actions
    public function duplicate(Request $request, $id)
    {
        $user = Auth::user();
        
        if ($user->role !== 'teacher') {
            abort(403, 'Only teachers can duplicate content');
        }
        
        $controller = app(\App\Http\Controllers\Teacher\ContentController::class);
        return $controller->duplicate($request, $id);
    }
    
    public function toggleStatus(Request $request, $id)
    {
        $user = Auth::user();
        
        if ($user->role !== 'teacher') {
            abort(403, 'Only teachers can toggle content status');
        }
        
        $controller = app(\App\Http\Controllers\Teacher\ContentController::class);
        return $controller->toggleStatus($request, $id);
    }
    
    public function analytics(Request $request, $id)
    {
        $user = Auth::user();
        
        if ($user->role !== 'teacher') {
            abort(403, 'Only teachers can view content analytics');
        }
        
        $controller = app(\App\Http\Controllers\Teacher\ContentController::class);
        return $controller->analytics($request, $id);
    }
    
    // Handle student-specific content routes
    public function recommendations(Request $request)
    {
        $user = Auth::user();
        
        if ($user->role !== 'student') {
            abort(403, 'Only students can view recommendations');
        }
        
        $controller = app(\App\Http\Controllers\Student\ContentController::class);
        return $controller->recommendations($request);
    }
    
    public function recent(Request $request)
    {
        $user = Auth::user();
        
        if ($user->role !== 'student') {
            abort(403, 'Only students can view recent content');
        }
        
        $controller = app(\App\Http\Controllers\Student\ContentController::class);
        return $controller->recent($request);
    }
    
    public function search(Request $request)
    {
        $user = Auth::user();
        
        $controller = $user->role === 'student' 
            ? app(\App\Http\Controllers\Student\ContentController::class)
            : app(\App\Http\Controllers\Teacher\ContentController::class);
            
        return $controller->search($request);
    }
    
    public function bySubject(Request $request, $subject)
    {
        $user = Auth::user();
        
        if ($user->role !== 'student') {
            abort(403, 'Only students can filter content by subject');
        }
        
        $controller = app(\App\Http\Controllers\Student\ContentController::class);
        return $controller->bySubject($request, $subject);
    }
}