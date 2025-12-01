<!-- resources/views/admin/pages/setup/user/view.blade.php -->
<div class="container-fluid p-0">
    <div class="row">
        <div class="col-md-8">
            <dl class="row">
                <dt class="col-sm-4">Name:</dt>
                <dd class="col-sm-8">{{ $user->name }}</dd>

                <dt class="col-sm-4">Email:</dt>
                <dd class="col-sm-8">{{ $user->email }}</dd>

                <dt class="col-sm-4">User Type:</dt>
                <dd class="col-sm-8">
                    <span class="badge bg-gradient-info">
                        {{ ucfirst(strtolower(\App\Enums\UserType::from($user->usertype)->name)) }}
                    </span>
                </dd>

                <dt class="col-sm-4">Roles:</dt>
                <dd class="col-sm-8">
                    @if ($user->roles->count() > 0)
                        @foreach ($user->roles as $role)
                            <span class="badge bg-gradient-primary me-1">{{ ucfirst($role->name) }}</span>
                        @endforeach
                    @else
                        <span class="text-muted">No roles assigned</span>
                    @endif
                </dd>

                <dt class="col-sm-4">Status:</dt>
                <dd class="col-sm-8">
                    <span
                        class="badge bg-gradient-{{ $user->status == 1 ? 'success' : ($user->status == 2 ? 'warning' : 'danger') }}">
                        {{ ucfirst(strtolower(\App\Enums\Status::from($user->status)->name)) }}
                    </span>
                </dd>

                <dt class="col-sm-4">Created At:</dt>
                <dd class="col-sm-8">{{ $user->created_at->format('d M Y H:i') }}</dd>

                <dt class="col-sm-4">Last Updated:</dt>
                <dd class="col-sm-8">{{ $user->updated_at->format('d M Y H:i') }}</dd>
            </dl>
        </div>
    </div>
</div>
