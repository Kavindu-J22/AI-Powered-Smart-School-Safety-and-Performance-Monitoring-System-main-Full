<?php

namespace App\Http\Controllers\Admin\Management;

use App\DataTables\Admin\Management\StudentDataTable;
use App\Enums\Grade;
use App\Enums\Status;
use App\Enums\UserType;
use App\Helpers\ValidationRules;
use App\Http\Controllers\Admin\BaseManagementController;
use App\Models\User;
use App\Repositories\Interfaces\Admin\Management\ParentRepositoryInterface;
use App\Repositories\Interfaces\Admin\Management\SchoolClassRepositoryInterface;
use App\Repositories\Interfaces\Admin\Management\StudentRepositoryInterface;
use App\Repositories\Interfaces\Admin\Management\SubjectRepositoryInterface;
use App\Services\DatabaseTransactionService;
use App\Services\ImageUploadService;
use App\Services\ParentCreationService;
use App\Services\UserService;
use App\Services\ArduinoNFCService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Spatie\Permission\Models\Role;

class StudentController extends BaseManagementController
{
    protected string $parentViewPath = 'admin.pages.management.students.';
    protected string $parentRoutePath = 'admin.management.students.';
    protected string $entityName = 'Student';
    protected string $entityType = 'student';

    protected SchoolClassRepositoryInterface $classRepository;

    protected SubjectRepositoryInterface $subjectRepository;

    protected ParentRepositoryInterface $parentRepository;
    protected ParentCreationService $parentCreationService;
    protected ArduinoNFCService $arduinoNFCService;

    public function __construct(
        StudentRepositoryInterface $repository,
        SchoolClassRepositoryInterface $classRepository,
        SubjectRepositoryInterface $subjectRepository,
        ParentRepositoryInterface $parentRepository,
        UserService $userService,
        ImageUploadService $imageService,
        DatabaseTransactionService $transactionService,
        ParentCreationService $parentCreationService,
        ArduinoNFCService $arduinoNFCService
    ) {
        parent::__construct($repository, $userService, $imageService, $transactionService);
        $this->classRepository = $classRepository;
        $this->subjectRepository = $subjectRepository;
        $this->parentRepository = $parentRepository;
        $this->parentCreationService = $parentCreationService;
        $this->arduinoNFCService = $arduinoNFCService;
    }

    public function index(StudentDataTable $datatable)
    {
        return $this->renderIndex($datatable, $this->parentViewPath);
    }

    protected function getFormData($id = null): array
    {
        $classes = $this->classRepository->getAll();
        $subjects = $this->subjectRepository->getAll();
        $parents = $this->parentRepository->getActive();
        $roles = Role::where('name', 'student')->get();
        $grades = Grade::getOptions(); // Add grades from enum

        return compact('classes', 'subjects', 'parents', 'roles', 'grades');
    }

    protected function getValidationRules(bool $isUpdate = false, $id = null): array
    {
        $rules = ValidationRules::getStudentRules($isUpdate, $id);

        // Add parent validation rules for creation/update
        $parentRules = ValidationRules::getParentArrayRules();

        return array_merge($rules, $parentRules);
    }

    protected function performCreate(Request $request)
    {
        // Create user account
        $user = User::create([
            'name' => trim($request->first_name . ' ' . $request->last_name),
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'usertype' => UserType::STUDENT->value,
            'status' => Status::ACTIVE->value,
        ]);

        // Assign roles to user
        if ($request->has('roles')) {
            $user->assignRole($request->roles);
        }

        // Prepare student data
        $studentData = $request->except([
            'password',
            'password_confirmation',
            'roles',
            'parents',
            'subjects',
            'profile_image',
            'parent_first_name',
            'parent_last_name',
            'parent_middle_name',
            'parent_gender',
            'parent_date_of_birth',
            'parent_relationship_type',
            'parent_mobile_phone',
            'parent_email',
            'parent_occupation',
            'parent_workplace',
            'parent_work_phone',
            'parent_is_emergency_contact',
            'parent_address_line1',
        ]);

        $studentData['user_id'] = $user->id;
        $studentData['is_active'] = $request->input('is_active', true);

        // Handle profile image upload
        if ($request->hasFile('profile_image')) {
            $imagePath = $this->imageService->uploadProfileImage(
                $request->file('profile_image'),
                'student',
                $user->id
            );
            $studentData['photo_path'] = $imagePath;
        }

        // Generate student code if not provided
        if (empty($studentData['student_code'])) {
            $studentData['student_code'] = \App\Models\Student::generateStudentCode();
        }

        $student = $this->repository->create($studentData);

        // Create and assign parents
        $parentIds = $this->parentCreationService->createParentsFromArray($request->all());
        if (!empty($parentIds)) {
            $student->parents()->sync($parentIds);
        }

        // Assign existing parents if provided
        if ($request->has('parents') && !empty($request->parents)) {
            $existingParentIds = array_merge($parentIds, $request->parents);
            $student->parents()->sync(array_unique($existingParentIds));
        }

        // Assign subjects if provided
        if ($request->has('subject_ids')) {
            $subjectIds = json_decode($request->input('subject_ids'), true);
            if (is_array($subjectIds) && !empty($subjectIds)) {
                $this->repository->assignSubjects($student->student_id, $subjectIds, $request->grade_level);
            }
        }

        $this->notifyCreated($this->entityName, $student);
        return $student;
    }

    protected function performUpdate(Request $request, $id)
    {
        $student = $this->repository->getById($id);
        if (!$student) {
            throw new \Exception('Student not found.');
        }

        // Update user account
        $user = $student->user;
        $user->update([
            'name' => trim($request->first_name . ' ' . $request->last_name),
            'email' => $request->email,
        ]);

        // Update password if provided
        if ($request->filled('password')) {
            $user->update(['password' => Hash::make($request->password)]);
        }

        // Update roles
        if ($request->has('roles')) {
            $user->syncRoles($request->roles);
        }

        // Prepare student data for update
        $studentData = $request->except([
            'password',
            'password_confirmation',
            'roles',
            'parents',
            'subjects',
            'profile_image',
            'parent_first_name',
            'parent_last_name',
            'parent_middle_name',
            'parent_gender',
            'parent_date_of_birth',
            'parent_relationship_type',
            'parent_mobile_phone',
            'parent_email',
            'parent_occupation',
            'parent_workplace',
            'parent_work_phone',
            'parent_is_emergency_contact',
            'parent_address_line1',
        ]);

        // Handle profile image upload
        if ($request->hasFile('profile_image')) {
            // Delete old image if exists
            if ($student->photo_path) {
                $this->imageService->deleteProfileImage($student->photo_path);
            }

            $imagePath = $this->imageService->uploadProfileImage(
                $request->file('profile_image'),
                'student',
                $user->id,
                $student->photo_path
            );
            $studentData['photo_path'] = $imagePath;
        }

        $this->repository->update($id, $studentData);

        // Handle parent creation and relationships
        $existingParentIds = $request->input('parents', []);
        $newParentIds = $this->parentCreationService->createParentsFromArray($request->all());
        $allParentIds = array_unique(array_merge($existingParentIds, $newParentIds));

        $student->parents()->sync($allParentIds);

        // Update subjects
        if ($request->has('subject_ids')) {
            $subjectIds = json_decode($request->input('subject_ids'), true);
            if (is_array($subjectIds)) {
                $this->repository->assignSubjects($student->student_id, $subjectIds, $request->grade_level);
            }
        }

        $this->notifyUpdated($this->entityName, $student);
        return $student;
    }

    public function show(string $id)
    {
        checkPermissionAndRedirect('admin.management.students.show');
        $student = $this->repository->getWithRelations($id);

        if (! $student) {
            flashResponse('Student not found.', 'danger');

            return Redirect::back();
        }

        return view($this->parentViewPath . 'view', compact('student'));
    }

    public function generateCode()
    {
        return response()->json([
            'code' => \App\Models\Student::generateStudentCode(),
        ]);
    }

    /**
     * Get subjects for a specific grade level
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSubjectsByGrade(Request $request)
    {
        $gradeLevel = (int) $request->input('grade_level');

        if (!$gradeLevel) {
            return response()->json([
                'success' => false,
                'message' => 'Grade level is required',
                'data' => null
            ]);
        }

        try {
            $grade = Grade::from($gradeLevel);
            $subjectData = \App\Models\Subject::getSubjectsWithRules($gradeLevel);

            return response()->json([
                'success' => true,
                'data' => $subjectData
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching subjects: ' . $e->getMessage(),
                'data' => null
            ]);
        }
    }

    /**
     * Get classes for a specific grade level
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getClassesByGrade(Request $request)
    {
        $gradeLevel = (int) $request->input('grade_level');

        if (!$gradeLevel) {
            return response()->json([
                'success' => false,
                'message' => 'Grade level is required',
                'classes' => []
            ]);
        }

        try {
            $classes = $this->classRepository->getByGrade($gradeLevel);

            return response()->json([
                'success' => true,
                'classes' => $classes->map(function ($class) {
                    return [
                        'id' => $class->id,
                        'class_name' => $class->class_name,
                        'grade_level' => $class->grade_level,
                        'section' => $class->section,
                        'full_name' => $class->class_name . ' (Grade ' . $class->grade_level . ')',
                    ];
                })
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching classes: ' . $e->getMessage(),
                'classes' => []
            ]);
        }
    }

    protected function performDelete($id)
    {
        $student = $this->repository->getById($id);
        if (!$student) {
            throw new \Exception('Student not found.');
        }

        // Create notification before deletion
        $this->notifyDeleted($this->entityName, $student);

        // Delete associated user account
        if ($student->user) {
            $student->user->delete();
        }

        // Delete profile image if exists
        if ($student->photo_path) {
            $this->imageService->deleteProfileImage($student->photo_path);
        }

        return $this->repository->delete($id);
    }

    /**
     * Write student data to NFC tag via Arduino
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function writeToNFC(Request $request)
    {
        try {
            // Validate request data
            $request->validate([
                'student_code' => 'required|string|max:50',
                'first_name' => 'required|string|max:50',
                'last_name' => 'required|string|max:50',
                'grade_level' => 'nullable|string',
                'class_id' => 'nullable|string',
                'enrollment_date' => 'nullable|date',
            ]);

            // Prepare student data
            $studentData = [
                'student_code' => $request->student_code,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'grade_level' => $request->grade_level ?? '',
                'class_id' => $request->class_id ?? '',
                'enrollment_date' => $request->enrollment_date ?? '',
            ];

            // Write to NFC via Arduino
            $result = $this->arduinoNFCService->writeStudentDataToNFC($studentData);

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test Arduino connection
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function testArduino()
    {
        try {
            $result = $this->arduinoNFCService->testConnection();
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Connection test failed: ' . $e->getMessage()
            ], 500);
        }
    }
}
