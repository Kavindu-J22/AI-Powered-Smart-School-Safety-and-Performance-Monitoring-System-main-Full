<?php

namespace App\Http\Controllers\Admin\Setup;

use App\DataTables\Admin\Setup\UserDataTable;
use App\Enums\Status;
use App\Enums\UserType;
use App\Http\Controllers\Controller;
use App\Repositories\Interfaces\Admin\Setup\UserRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    protected UserRepositoryInterface $repository;

    protected $parentViewPath = 'admin.pages.setup.user.';

    protected $parentRoutePath = 'admin.setup.user.';

    public function __construct(UserRepositoryInterface $repository)
    {
        $this->middleware('auth');
        $this->repository = $repository;
    }

    public function index(UserDataTable $datatable)
    {
        checkPermissionAndRedirect('admin.setup.users.index');
        Session::put('title', 'User Management');

        return $datatable->render($this->parentViewPath.'index');
    }

    public function form($id = null)
    {
        checkPermissionAndRedirect('admin.setup.users.'.($id ? 'edit' : 'form'));
        Session::put('title', ($id ? 'Update' : 'Create').' User');

        $usertypes = UserType::options();
        $statuses = Status::options();
        $roles = Role::all()->pluck('name', 'name')->toArray();

        if ($id) {
            $user = $this->repository->getOne($id);
            if (! $user) {
                flashResponse('User not found.', 'danger');

                return Redirect::route($this->parentRoutePath.'index');
            }
            $userRoles = $user->roles->pluck('name')->toArray();

            return view($this->parentViewPath.'form', compact('user', 'id', 'usertypes', 'statuses', 'roles', 'userRoles'));
        }

        $user = null;
        $userRoles = [];

        return view($this->parentViewPath.'form', compact('id', 'usertypes', 'statuses', 'roles', 'userRoles'));
    }

    public function enroll(Request $request)
    {
        $id = $request->input('id');
        checkPermissionAndRedirect('admin.setup.users.'.($id ? 'edit' : 'form'));

        if ($request->has('id') && $request->filled('id')) {
            return $this->update($request);
        }

        $rules = [
            'name' => [
                'required',
                'min:3',
                'max:255',
                'unique:users,name',
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email',
            ],
            'usertype' => 'required|in:'.implode(',', UserType::values()),
            'status' => 'sometimes|in:'.implode(',', Status::values()),
            'password' => 'required|min:8|confirmed',
            'roles' => 'sometimes|array',
            'roles.*' => 'exists:roles,name',
        ];

        $request->validate($rules);

        try {
            DB::beginTransaction();

            $data = $request->only(['name', 'email', 'usertype', 'status']);
            $data['password'] = bcrypt($request->password);
            $data['status'] = $request->status ?? Status::ACTIVE->value;

            $user = $this->repository->create($data);

            // Assign roles if provided
            if ($user && $request->has('roles')) {
                $user->syncRoles($request->roles);
            }

            DB::commit();
            flashResponse('User created successfully.', 'success');
        } catch (\Exception $e) {
            DB::rollBack();
            flashResponse('Failed to create User. Please try again.', 'danger');
        }

        return redirect()->route($this->parentRoutePath.'index');
    }

    public function show(string $id)
    {
        checkPermissionAndRedirect('admin.setup.users.show');
        $user = $this->repository->getOne($id);
        if (! $user) {
            flashResponse('User not found.', 'danger');

            return Redirect::back();
        }

        return view($this->parentViewPath.'view', compact('user'));
    }

    public function update(Request $request)
    {
        $rules = [
            'name' => [
                'required',
                'min:3',
                'max:255',
                Rule::unique('users')->ignore($request->id),
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users')->ignore($request->id),
            ],
            'usertype' => 'required|in:'.implode(',', UserType::values()),
            'status' => 'sometimes|in:'.implode(',', Status::values()),
            'password' => 'nullable|min:8|confirmed',
            'roles' => 'sometimes|array',
            'roles.*' => 'exists:roles,name',
        ];

        $request->validate($rules);

        try {
            DB::beginTransaction();

            $data = $request->only(['name', 'email', 'usertype', 'status']);
            $data['status'] = $request->status ?? Status::ACTIVE->value;

            if ($request->filled('password')) {
                $data['password'] = bcrypt($request->password);
            }

            $user = $this->repository->update($request->id, $data);

            // Sync roles if provided
            if ($user) {
                if ($request->has('roles')) {
                    $user->syncRoles($request->roles);
                } else {
                    $user->syncRoles([]);
                }
            }

            DB::commit();
            flashResponse('User updated successfully.', 'warning');
        } catch (\Exception $e) {
            DB::rollBack();
            flashResponse('Failed to update User. Please try again.', 'danger');
        }

        return redirect()->route($this->parentRoutePath.'index');
    }

    public function delete($id)
    {
        checkPermissionAndRedirect('admin.setup.users.delete');

        $user = $this->repository->getOne($id);
        if (! $user) {
            flashResponse('User not found.', 'danger');

            return Redirect::back();
        }

        // Prevent deletion of admin user
        if ($user->hasRole('admin')) {
            flashResponse('Cannot delete admin user.', 'danger');

            return Redirect::back();
        }

        try {
            DB::beginTransaction();

            $deleted = $this->repository->delete($id);

            DB::commit();

            if ($deleted) {
                flashResponse('User deleted successfully.', 'danger');
            } else {
                flashResponse('Failed to delete user.', 'danger');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            flashResponse('Failed to delete User. Please try again.', 'danger');
        }

        return Redirect::back();
    }
}
