<?php

namespace App\Http\Controllers\Admin\Management;

use App\DataTables\Admin\Management\TeacherDataTable;
use App\Enums\Status;
use App\Enums\UserType;
use App\Helpers\ValidationRules;
use App\Http\Controllers\Admin\BaseManagementController;
use App\Models\User;
use App\Repositories\Interfaces\Admin\Management\SubjectRepositoryInterface;
use App\Repositories\Interfaces\Admin\Management\TeacherRepositoryInterface;
use App\Services\DatabaseTransactionService;
use App\Services\ImageUploadService;
use App\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Spatie\Permission\Models\Role;

class TeacherController extends BaseManagementController
{
    protected string $parentViewPath = 'admin.pages.management.teachers.';
    protected string $parentRoutePath = 'admin.management.teachers.';
    protected string $entityName = 'Teacher';
    protected string $entityType = 'teacher';

    protected SubjectRepositoryInterface $subjectRepository;

    public function __construct(
        TeacherRepositoryInterface $repository,
        SubjectRepositoryInterface $subjectRepository,
        UserService $userService,
        ImageUploadService $imageService,
        DatabaseTransactionService $transactionService
    ) {
        parent::__construct($repository, $userService, $imageService, $transactionService);
        $this->subjectRepository = $subjectRepository;
    }

    public function index(TeacherDataTable $datatable)
    {
        return $this->renderIndex($datatable, $this->parentViewPath);
    }

    protected function getFormData($id = null): array
    {
        $subjects = $this->subjectRepository->getAll();
        $roles = Role::where('name', 'teacher')->get();

        return compact('subjects', 'roles');
    }

    protected function getValidationRules(bool $isUpdate = false, $id = null): array
    {
        return ValidationRules::getTeacherRules($isUpdate, $id);
    }

    protected function performCreate(Request $request)
    {
        // Create user account
        $user = User::create([
            'name' => trim($request->first_name . ' ' . $request->last_name),
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'usertype' => UserType::TEACHER->value,
            'status' => Status::ACTIVE->value,
        ]);

        // Assign roles to user
        if ($request->has('roles')) {
            $user->assignRole($request->roles);
        }

        // Prepare teacher data
        $teacherData = $request->except([
            'password',
            'password_confirmation',
            'roles',
            'subjects',
            'profile_image'
        ]);

        $teacherData['user_id'] = $user->id;
        $teacherData['is_active'] = $request->input('is_active', true);
        $teacherData['teaching_level'] = $request->input('teaching_level');

        // Handle profile image upload
        if ($request->hasFile('profile_image')) {
            $imagePath = $this->imageService->uploadProfileImage(
                $request->file('profile_image'),
                'teacher',
                $user->id
            );
            $teacherData['photo_path'] = $imagePath;
        }

        // Generate teacher code if not provided
        if (empty($teacherData['teacher_code'])) {
            $teacherData['teacher_code'] = \App\Models\Teacher::generateTeacherCode();
        }

        $teacher = $this->repository->create($teacherData);

        // Assign subjects if provided
        if ($request->has('subjects') && !empty($request->subjects)) {
            $this->repository->assignSubjects($teacher->teacher_id, $request->subjects);
        }

        $this->notifyCreated($this->entityName, $teacher);
        return $teacher;
    }

    protected function performUpdate(Request $request, $id)
    {
        $teacher = $this->repository->getById($id);
        if (!$teacher) {
            throw new \Exception('Teacher not found.');
        }

        // Update user account
        $userData = [
            'name' => trim($request->first_name . ' ' . $request->last_name),
            'email' => $request->email,
        ];

        if ($request->filled('password')) {
            $userData['password'] = Hash::make($request->password);
        }

        $teacher->user->update($userData);

        // Update roles
        if ($request->has('roles')) {
            $teacher->user->syncRoles($request->roles);
        }

        // Prepare teacher data for update
        $teacherData = $request->except([
            'password',
            'password_confirmation',
            'roles',
            'subjects',
            'profile_image'
        ]);

        $teacherData['teaching_level'] = $request->input('teaching_level');

        // Handle profile image upload
        if ($request->hasFile('profile_image')) {
            $imagePath = $this->imageService->uploadProfileImage(
                $request->file('profile_image'),
                'teacher',
                $teacher->user_id,
                $teacher->photo_path
            );
            $teacherData['photo_path'] = $imagePath;
        }

        $this->repository->update($id, $teacherData);

        // Update subjects
        if ($request->has('subjects')) {
            $this->repository->assignSubjects($teacher->teacher_id, $request->subjects ?? []);
        }

        $this->notifyUpdated($this->entityName, $teacher);
        return $teacher;
    }

    public function show(string $id)
    {
        checkPermissionAndRedirect('admin.management.teachers.show');
        $teacher = $this->repository->getWithRelations($id);

        if (!$teacher) {
            flashResponse('Teacher not found.', 'danger');
            return Redirect::back();
        }

        return view($this->parentViewPath . 'view', compact('teacher'));
    }

    public function generateCode()
    {
        return response()->json([
            'code' => \App\Models\Teacher::generateTeacherCode(),
        ]);
    }

    /**
     * Get subjects based on teaching level
     */
    public function getSubjectsByTeachingLevel(Request $request)
    {
        $teachingLevel = $request->input('teaching_level');

        if (!$teachingLevel) {
            return response()->json([
                'success' => false,
                'message' => 'Teaching level is required',
                'subjects' => []
            ]);
        }

        try {
            $subjects = [];
            $message = '';

            // Map teaching level to grade level and stream
            switch ($teachingLevel) {
                case 'Primary':
                    $subjects = \App\Models\Subject::where('grade_level', '1-5')
                        ->where('status', 'active')
                        ->orderBy('category')
                        ->orderBy('subject_name')
                        ->get();
                    $message = 'Showing Primary Education subjects (Grades 1-5)';
                    break;

                case 'Secondary':
                    $subjects = \App\Models\Subject::where('grade_level', '6-11')
                        ->where('status', 'active')
                        ->orderBy('category')
                        ->orderBy('subject_name')
                        ->get();
                    $message = 'Showing Secondary Education subjects (Grades 6-11)';
                    break;

                case 'A/L-Arts':
                    $subjects = \App\Models\Subject::where('grade_level', '12-13')
                        ->where('stream', 'Arts')
                        ->where('status', 'active')
                        ->orderBy('subject_name')
                        ->get();
                    $message = 'Showing A/L Arts Stream subjects';
                    break;

                case 'A/L-Commerce':
                    $subjects = \App\Models\Subject::where('grade_level', '12-13')
                        ->where('stream', 'Commerce')
                        ->where('status', 'active')
                        ->orderBy('subject_name')
                        ->get();
                    $message = 'Showing A/L Commerce Stream subjects';
                    break;

                case 'A/L-Science':
                    $subjects = \App\Models\Subject::where('grade_level', '12-13')
                        ->where('stream', 'Science')
                        ->where('status', 'active')
                        ->orderBy('subject_name')
                        ->get();
                    $message = 'Showing A/L Science Stream subjects';
                    break;

                case 'A/L-Technology':
                    $subjects = \App\Models\Subject::where('grade_level', '12-13')
                        ->where('stream', 'Technology')
                        ->where('status', 'active')
                        ->orderBy('subject_name')
                        ->get();
                    $message = 'Showing A/L Technology Stream subjects';
                    break;

                default:
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid teaching level',
                        'subjects' => []
                    ]);
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'subjects' => $subjects->map(function ($subject) {
                    return [
                        'id' => $subject->id,
                        'subject_code' => $subject->subject_code,
                        'subject_name' => $subject->subject_name,
                        'category' => $subject->category,
                        'stream' => $subject->stream,
                        'grade_level' => $subject->grade_level,
                    ];
                })
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching subjects: ' . $e->getMessage(),
                'subjects' => []
            ]);
        }
    }

    protected function performDelete($id)
    {
        $teacher = $this->repository->getById($id);
        if (!$teacher) {
            throw new \Exception('Teacher not found.');
        }

        // Create notification before deletion
        $this->notifyDeleted($this->entityName, $teacher);

        // Delete associated user account
        if ($teacher->user) {
            $teacher->user->delete();
        }

        // Delete profile image if exists
        if ($teacher->photo_path) {
            $this->imageService->deleteProfileImage($teacher->photo_path);
        }

        return $this->repository->delete($id);
    }
}
