<nav class="navbar navbar-main navbar-expand-lg px-0 mx-3 shadow-none border-radius-xl" id="navbarBlur" data-scroll="true">
    <div class="container-fluid py-1 px-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">
                @php
                    $breadcrumbs = getBreadcrumbs();
                @endphp

                @if (is_array($breadcrumbs))
                    @foreach ($breadcrumbs as $key => $breadcrumb)
                        @if ($breadcrumb != 'index')
                            @php
                                $breadcrumb = ucfirst($breadcrumb);
                            @endphp
                            @if (count($breadcrumbs) - ($breadcrumb != 'index' ? 2 : 1) == $key)
                                <li class="breadcrumb-item text-sm text-dark active" aria-current="page">
                                    {{ $breadcrumb }}
                                </li>
                            @else
                                <li class="breadcrumb-item text-sm"><a class="opacity-5 text-dark"
                                        href="javascript:;">{{ $breadcrumb }}</a></li>
                            @endif
                        @endif
                    @endforeach
                @endif
            </ol>
        </nav>
        <div class="collapse navbar-collapse mt-sm-0 mt-2 me-md-0 me-sm-4" id="navbar">
            <div class="ms-md-auto pe-md-3 d-flex align-items-center d-none d-md-block">
                <div class="input-group input-group-outline">
                    <label class="form-label">Search here...</label>
                    <input type="text" class="form-control">
                </div>
            </div>
            <ul class="navbar-nav d-flex align-items-center justify-content-end">
                <li class="nav-item d-xl-none ps-3 d-flex align-items-center">
                    <a href="javascript:;" class="nav-link text-body p-0" id="iconNavbarSidenav">
                        <div class="sidenav-toggler-inner">
                            <i class="sidenav-toggler-line"></i>
                            <i class="sidenav-toggler-line"></i>
                            <i class="sidenav-toggler-line"></i>
                        </div>
                    </a>
                </li>
                <li class="nav-item px-3 d-flex align-items-center d-none d-md-block">
                    <a href="{{ route('admin.setup.settings.index') }}" class="nav-link text-body p-0">
                        <i class="d-flex material-symbols-rounded fixed-plugin-button-nav">settings</i>
                    </a>
                </li>
                <li class="nav-item dropdown pe-3 d-flex align-items-center d-none d-md-block">
                    <a href="javascript:;" class="nav-link text-body p-0 position-relative" id="dropdownMenuButton"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="d-flex material-symbols-rounded">notifications</i>
                        <span class="badge bg-danger position-absolute top-0 start-100 translate-middle rounded-pill"
                            id="notification-badge" style="display: none;">
                            <span id="notification-count">0</span>
                        </span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end px-2 py-3 me-sm-n4" aria-labelledby="dropdownMenuButton"
                        style="max-width: 350px; max-height: 400px; overflow-y: auto;">
                        <li class="text-center mb-3">
                            <h6 class="mb-0">Notifications</h6>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="mark-all-read">
                                Mark all as read
                            </button>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <div id="notifications-container">
                            <li class="text-center text-muted py-3">
                                <i class="material-symbols-rounded">notifications</i>
                                <p class="mb-0">Loading notifications...</p>
                            </li>
                        </div>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li class="text-center">
                            <a href="#" class="btn btn-sm btn-link" id="view-all-notifications">View All
                                Notifications</a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item dropdown d-flex align-items-center d-none d-md-block">
                    <a href="javascript:;" class="d-flex gap-3 nav-link text-body font-weight-bold px-0 position-relative"
                        id="profileDropdownButton" data-bs-toggle="dropdown" aria-expanded="false">
                        @if (Auth::user()->profile_image)
                            <img src="{{ Storage::url(Auth::user()->profile_image) }}" alt="Profile"
                                class="rounded-circle me-2" style="width: 32px; height: 32px; object-fit: cover;">
                        @else
                            <i class="material-symbols-rounded">account_circle</i>
                        @endif
                        <span class="d-none d-lg-inline">{{ Auth::user()->name }}</span>
                        <i class="d-flex align-items-center material-symbols-rounded ms-1" style="font-size: 16px;">keyboard_arrow_down</i>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end px-2 py-3 me-sm-n4"
                        aria-labelledby="profileDropdownButton" style="min-width: 220px;">
                        <li class="mb-2">
                            <div class="d-flex align-items-center p-2">
                                @if (Auth::user()->profile_image)
                                    <img src="{{ Storage::url(Auth::user()->profile_image) }}" alt="Profile"
                                        class="rounded-circle me-3"
                                        style="width: 40px; height: 40px; object-fit: cover;">
                                @else
                                    <div class="bg-gradient-primary rounded-circle me-3 d-flex align-items-center justify-content-center"
                                        style="width: 40px; height: 40px;">
                                        <i class="material-symbols-rounded text-white">account_circle</i>
                                    </div>
                                @endif
                                <div>
                                    <h6 class="mb-0 text-sm">{{ Auth::user()->name }}</h6>
                                    <p class="mb-0 text-xs text-secondary">
                                        @if (Auth::user()->getRoleNames()->isNotEmpty())
                                            {{ Auth::user()->getRoleNames()->first() }}
                                        @else
                                            User
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li>
                            <a class="d-flex dropdown-item border-radius-md" href="{{ route('admin.profile.index') }}">
                                <i class="material-symbols-rounded me-2">person</i>
                                <span class="text-sm">My Profile</span>
                            </a>
                        </li>
                        <li>
                            <a class="d-flex dropdown-item border-radius-md" href="{{ route('admin.profile.edit') }}">
                                <i class="material-symbols-rounded me-2">edit</i>
                                <span class="text-sm">Edit Profile</span>
                            </a>
                        </li>
                        <li>
                            <a class="d-flex dropdown-item border-radius-md" href="{{ route('admin.setup.settings.index') }}">
                                <i class="material-symbols-rounded me-2">settings</i>
                                <span class="text-sm">Settings</span>
                            </a>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li>
                            <a class="d-flex dropdown-item border-radius-md text-danger" href="{{ route('logout') }}"
                                onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                <i class="material-symbols-rounded me-2">logout</i>
                                <span class="text-sm">Sign Out</span>
                            </a>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                @csrf
                            </form>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
