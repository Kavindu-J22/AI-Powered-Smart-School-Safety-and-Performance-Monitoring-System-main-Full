<?php

namespace App\Http\Controllers\Admin\Management;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class LessonController extends Controller
{
    protected string $viewDirectory = 'admin.pages.management.lessons.';

    public function index(): View
    {
        $lessons = Lesson::with(['subject', 'teacher'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        
        return view($this->viewDirectory . 'index', compact('lessons'));
    }

    public function create(): View
    {
        $subjects = Subject::orderBy('subject_name')->get();
        $teachers = Teacher::orderBy('first_name')->get();
        
        return view($this->viewDirectory . 'create', compact('subjects', 'teachers'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'teacher_id' => 'required|exists:teachers,teacher_id',
            'grade_level' => 'required|integer|min:1|max:13',
            'unit' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'topics' => 'nullable|string',
            'learning_outcomes' => 'nullable|string',
            'difficulty' => 'required|in:beginner,intermediate,advanced',
            'duration_minutes' => 'nullable|integer|min:1',
        ]);

        // Convert comma-separated topics to array
        $validated['topics'] = $validated['topics'] 
            ? array_map('trim', explode(',', $validated['topics'])) 
            : [];
        
        $validated['learning_outcomes'] = $validated['learning_outcomes']
            ? array_map('trim', explode(',', $validated['learning_outcomes']))
            : [];

        $validated['status'] = 'published';

        $lesson = Lesson::create($validated);

        return redirect()
            ->route('admin.management.lessons.index')
            ->with('success', 'Lesson created successfully');
    }

    public function show(Lesson $lesson): View
    {
        $lesson->load(['subject', 'teacher', 'homework']);
        
        return view($this->viewDirectory . 'show', compact('lesson'));
    }

    public function edit(Lesson $lesson): View
    {
        $subjects = Subject::orderBy('subject_name')->get();
        $teachers = Teacher::orderBy('first_name')->get();
        
        return view($this->viewDirectory . 'edit', compact('lesson', 'subjects', 'teachers'));
    }

    public function update(Request $request, Lesson $lesson): RedirectResponse
    {
        $validated = $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'grade_level' => 'required|integer|min:1|max:13',
            'unit' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'topics' => 'nullable|string',
            'learning_outcomes' => 'nullable|string',
            'difficulty' => 'required|in:beginner,intermediate,advanced',
            'duration_minutes' => 'nullable|integer|min:1',
            'status' => 'required|in:draft,published,archived',
        ]);

        $validated['topics'] = $validated['topics'] 
            ? array_map('trim', explode(',', $validated['topics'])) 
            : [];
        
        $validated['learning_outcomes'] = $validated['learning_outcomes']
            ? array_map('trim', explode(',', $validated['learning_outcomes']))
            : [];

        $lesson->update($validated);

        return redirect()
            ->route('admin.management.lessons.index')
            ->with('success', 'Lesson updated successfully');
    }

    public function destroy(Lesson $lesson): RedirectResponse
    {
        $lesson->delete();

        return redirect()
            ->route('admin.management.lessons.index')
            ->with('success', 'Lesson deleted successfully');
    }

    public function getBySubject(int $subjectId): JsonResponse
    {
        $lessons = Lesson::where('subject_id', $subjectId)
            ->where('status', 'published')
            ->orderBy('title')
            ->get(['lesson_id', 'title', 'unit', 'grade_level', 'topics']);

        return response()->json([
            'success' => true,
            'lessons' => $lessons,
        ]);
    }
}

