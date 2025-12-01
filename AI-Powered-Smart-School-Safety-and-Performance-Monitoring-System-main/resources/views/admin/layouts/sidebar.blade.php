@php
use App\Enums\UserType;
$userType = auth()->user()->usertype ?? null;
// usertype is cast to UserType enum, so compare with enum directly
$isStudent = $userType === UserType::STUDENT;
$isTeacher = $userType === UserType::TEACHER;
$isParent = $userType === UserType::PARENT;
$isSecurity = $userType === UserType::SECURITY;
$isAdmin = in_array($userType, [UserType::ADMIN, UserType::USER]);

// Determine dashboard route based on user type
$dashboardRoute = match($userType) {
UserType::STUDENT => 'student.dashboard.index',
UserType::TEACHER => 'teacher.dashboard.index',
UserType::PARENT => 'parent.dashboard.index',
UserType::SECURITY => 'security.dashboard.index',
default => 'admin.dashboard.index',
};
@endphp
<aside class="sidenav navbar navbar-vertical navbar-expand-xs border-radius-lg fixed-start ms-2  bg-white my-2"
    id="sidenav-main">
    <div class="sidenav-header">
        <i class="fas fa-times p-3 cursor-pointer text-dark opacity-5 position-absolute end-0 top-0 d-none d-xl-none"
            aria-hidden="true" id="iconSidenav"></i>
        <a class="navbar-brand px-0 py-3 m-0 text-center" href="{{ route($dashboardRoute) }}">
            @php
            $globalSetting = app(\App\Models\Setting::class)->first();
            @endphp
            @if ($globalSetting && $globalSetting->logo)
            <img class="w-75 sidebar-logo" src="{{ asset('storage/' . $globalSetting->logo) }}"
                alt="{{ $globalSetting->school_name ?? ($globalSetting->title ?? 'School Logo') }}"
                style="max-height: 50px; object-fit: contain;">
            @else
            <img class="w-75 sidebar-logo" src="{{ asset('assets/img/logo_text.png') }}"
                alt="{{ $globalSetting->school_name ?? ($globalSetting->title ?? 'School') }}">
            @endif
        </a>
    </div>
    <hr class="horizontal dark mt-0 mb-2">
    <div class="collapse navbar-collapse w-100">
        <ul class="navbar-nav">
            @if($isStudent)
            {{-- Student Sidebar --}}
            <li class="nav-item">
                <a class="nav-link @if (Route::is('student.dashboard.*')) active bg-gradient-dark text-white @else text-dark @endif"
                    href="{{ route('student.dashboard.index') }}">
                    <i class="material-symbols-outlined opacity-5">home</i>
                    <span class="nav-link-text ms-1">{{ __('common.dashboard') }}</span>
                </a>
            </li>
            <li class="nav-item mt-3">
                <h6 class="ps-4 ms-2 text-uppercase text-xs text-dark font-weight-bolder opacity-5">My Academics</h6>
            </li>
            <li class="nav-item">
                <a class="nav-link @if (Route::is('student.homework.*')) active bg-gradient-dark text-white @else text-dark @endif"
                    href="{{ route('student.homework.index') }}">
                    <i class="material-symbols-outlined opacity-5">assignment</i>
                    <span class="nav-link-text ms-1">My Homework</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-dark" href="{{ route('admin.timetable-viewer.index') }}">
                    <i class="material-symbols-outlined opacity-5">schedule</i>
                    <span class="nav-link-text ms-1">My Timetable</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-dark" href="{{ route('admin.management.marks.index') }}">
                    <i class="material-symbols-outlined opacity-5">grade</i>
                    <span class="nav-link-text ms-1">My Marks</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-dark" href="{{ route('admin.management.attendance.dashboard') }}">
                    <i class="material-symbols-outlined opacity-5">fact_check</i>
                    <span class="nav-link-text ms-1">My Attendance</span>
                </a>
            </li>
            <li class="nav-item mt-3">
                <h6 class="ps-4 ms-2 text-uppercase text-xs text-dark font-weight-bolder opacity-5">Account</h6>
            </li>
            <li class="nav-item">
                <a class="nav-link text-dark" href="{{ route('admin.profile.index') }}">
                    <i class="material-symbols-outlined opacity-5">person</i>
                    <span class="nav-link-text ms-1">My Profile</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-dark" href="#" onclick="event.preventDefault(); document.getElementById('logout-form-sidebar').submit();">
                    <i class="material-symbols-outlined opacity-5">logout</i>
                    <span class="nav-link-text ms-1">Logout</span>
                </a>
                <form id="logout-form-sidebar" action="{{ route('logout') }}" method="POST" class="d-none">
                    @csrf
                </form>
            </li>
            @else
            {{-- Admin/Staff Sidebar --}}
            @foreach (config('sidebar') as $menu)
            @if (@isset($menu['name']))
            <li class="nav-item mt-3">
                <h6 class="ps-4 ms-2 text-uppercase text-xs text-dark font-weight-bolder opacity-5">
                    {{ translateSidebarText($menu['name']) }}
                </h6>
            </li>
            @endif

            @foreach ($menu['items'] as $sidebarItem)
            <li class="nav-item">
                <a class="nav-link @if (Route::is($sidebarItem['route'])) active bg-gradient-dark text-white @else nav-link text-dark @endif "
                    href="{{ route($sidebarItem['route']) }}">
                    <i class="material-symbols-outlined opacity-5">{{ $sidebarItem['icon'] }}</i>
                    <span class="nav-link-text ms-1">{{ translateSidebarText($sidebarItem['text']) }}</span>
                </a>
            </li>
            @endforeach
            @endforeach
            @endif
        </ul>
    </div>
    <div class="sidenav-footer position-absolute w-100 bottom-0 ">
        <div class="mx-3">
            <form method="POST" action="{{ route('logout') }}" class="w-100">
                @csrf
                <button class="btn btn-outline-primary w-100" type="submit">{{ __('common.logout') }}</button>
            </form>
        </div>
    </div>
</aside>