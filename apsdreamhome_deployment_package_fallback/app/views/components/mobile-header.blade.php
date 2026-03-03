{{-- APS Dream Home - Mobile Optimized Header Component
     Modern responsive header with mobile navigation for all user types
--}}

<header class="bg-white shadow-sm sticky top-0 z-50">
    <!-- Mobile Header -->
    <div class="mobile-header d-lg-none">
        <button class="mobile-menu-btn" onclick="toggleMobileMenu()">
            <i class="bi bi-list fs-4"></i>
        </button>

        <a href="{{ url('/') }}" class="flex items-center space-x-2 mx-auto">
            <img src="{{ asset('images/logo.png') }}" alt="APS Dream Home" class="h-8" onerror="this.style.display='none'">
            <span class="text-lg font-bold text-primary">APS Dream Home</span>
        </a>

        <div class="d-flex align-items-center gap-2">
            @auth
                <div class="dropdown">
                    <button class="btn btn-link p-0 text-decoration-none" type="button" data-bs-toggle="dropdown">
                        <img src="{{ Auth::user()->avatar ?? asset('images/user/default-avatar.jpg') }}"
                             alt="Profile" class="rounded-circle" style="width: 32px; height: 32px; object-fit: cover;">
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><h6 class="dropdown-header">{{ Auth::user()->name }}</h6></li>
                        <li><hr class="dropdown-divider"></li>
                        @if(Auth::user()->hasRole('associate'))
                            <li><a class="dropdown-item" href="{{ route('associate.dashboard') }}">
                                <i class="bi bi-house-door me-2"></i>Associate Dashboard
                            </a></li>
                        @elseif(Auth::user()->hasRole('agent'))
                            <li><a class="dropdown-item" href="{{ route('agent.dashboard') }}">
                                <i class="bi bi-person-badge me-2"></i>Agent Dashboard
                            </a></li>
                        @elseif(Auth::user()->hasRole('customer'))
                            <li><a class="dropdown-item" href="{{ route('customer.dashboard') }}">
                                <i class="bi bi-person-circle me-2"></i>Customer Dashboard
                            </a></li>
                        @elseif(Auth::user()->hasRole('employee'))
                            <li><a class="dropdown-item" href="{{ route('employee.dashboard') }}">
                                <i class="bi bi-building me-2"></i>Employee Dashboard
                            </a></li>
                        @endif
                        <li><a class="dropdown-item" href="{{ route('profile') }}">
                            <i class="bi bi-person me-2"></i>Profile
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}" class="d-inline">
                                @csrf
                                <button type="submit" class="dropdown-item">
                                    <i class="bi bi-box-arrow-right me-2"></i>Logout
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            @else
                <a href="{{ route('login') }}" class="btn btn-outline-primary btn-sm">Login</a>
            @endauth
        </div>
    </div>

    <!-- Desktop Header -->
    <div class="d-none d-lg-block">
        <div class="container-fluid px-4 py-3">
            <div class="row align-items-center">
                <!-- Logo -->
                <div class="col-lg-3">
                    <a href="{{ url('/') }}" class="d-flex align-items-center text-decoration-none">
                        <img src="{{ asset('images/logo.png') }}" alt="APS Dream Home" class="me-2" style="height: 40px;" onerror="this.style.display='none'">
                        <span class="h5 mb-0 text-primary fw-bold">APS Dream Home</span>
                    </a>
                </div>

                <!-- Navigation -->
                <div class="col-lg-6">
                    <nav class="navbar navbar-expand-lg navbar-light p-0">
                        <div class="navbar-nav mx-auto">
                            <a href="{{ url('/') }}" class="nav-link px-3 {{ request()->routeIs('home') ? 'active' : '' }}">Home</a>

                            @auth
                                @if(Auth::user()->hasRole('associate'))
                                    <a href="{{ route('associate.dashboard') }}" class="nav-link px-3 {{ request()->routeIs('associate.*') ? 'active' : '' }}">Dashboard</a>
                                    <a href="{{ route('associate.team') }}" class="nav-link px-3">Team</a>
                                    <a href="{{ route('associate.commissions') }}" class="nav-link px-3">Commissions</a>
                                @elseif(Auth::user()->hasRole('agent'))
                                    <a href="{{ route('agent.dashboard') }}" class="nav-link px-3 {{ request()->routeIs('agent.*') ? 'active' : '' }}">Dashboard</a>
                                    <a href="{{ route('properties.index') }}" class="nav-link px-3">Properties</a>
                                    <a href="{{ route('agent.leads') }}" class="nav-link px-3">Leads</a>
                                @elseif(Auth::user()->hasRole('customer'))
                                    <a href="{{ route('customer.dashboard') }}" class="nav-link px-3 {{ request()->routeIs('customer.*') ? 'active' : '' }}">Dashboard</a>
                                    <a href="{{ route('customer.properties') }}" class="nav-link px-3">Properties</a>
                                    <a href="{{ route('customer.inquiries') }}" class="nav-link px-3">My Inquiries</a>
                                @elseif(Auth::user()->hasRole('employee'))
                                    <a href="{{ route('employee.dashboard') }}" class="nav-link px-3 {{ request()->routeIs('employee.*') ? 'active' : '' }}">Dashboard</a>
                                    <a href="{{ route('employee.tasks') }}" class="nav-link px-3">Tasks</a>
                                    <a href="{{ route('employee.attendance') }}" class="nav-link px-3">Attendance</a>
                                @endif
                            @else
                                <a href="{{ route('properties.index') }}" class="nav-link px-3">Properties</a>
                                <a href="#about" class="nav-link px-3">About</a>
                                <a href="#contact" class="nav-link px-3">Contact</a>
                            @endauth
                        </div>
                    </nav>
                </div>

                <!-- User Menu -->
                <div class="col-lg-3 text-end">
                    @auth
                        <div class="dropdown">
                            <button class="btn btn-link text-decoration-none d-flex align-items-center ms-auto p-0" type="button" data-bs-toggle="dropdown">
                                <img src="{{ Auth::user()->avatar ?? asset('images/user/default-avatar.jpg') }}"
                                     alt="Profile" class="rounded-circle me-2" style="width: 36px; height: 36px; object-fit: cover;">
                                <div class="text-start d-none d-md-block">
                                    <div class="fw-bold small">{{ Auth::user()->name }}</div>
                                    <div class="text-muted small">
                                        @if(Auth::user()->hasRole('associate')) Associate
                                        @elseif(Auth::user()->hasRole('agent')) Agent
                                        @elseif(Auth::user()->hasRole('customer')) Customer
                                        @elseif(Auth::user()->hasRole('employee')) Employee
                                        @else Member @endif
                                    </div>
                                </div>
                                <i class="bi bi-chevron-down ms-2"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow">
                                @if(Auth::user()->hasRole('associate'))
                                    <li><a class="dropdown-item" href="{{ route('associate.dashboard') }}">
                                        <i class="bi bi-house-door me-2"></i>Dashboard
                                    </a></li>
                                    <li><a class="dropdown-item" href="{{ route('associate.team') }}">
                                        <i class="bi bi-people me-2"></i>Team
                                    </a></li>
                                    <li><a class="dropdown-item" href="{{ route('associate.commissions') }}">
                                        <i class="bi bi-cash me-2"></i>Commissions
                                    </a></li>
                                @elseif(Auth::user()->hasRole('agent'))
                                    <li><a class="dropdown-item" href="{{ route('agent.dashboard') }}">
                                        <i class="bi bi-person-badge me-2"></i>Dashboard
                                    </a></li>
                                    <li><a class="dropdown-item" href="{{ route('properties.index') }}">
                                        <i class="bi bi-house me-2"></i>Properties
                                    </a></li>
                                    <li><a class="dropdown-item" href="{{ route('agent.leads') }}">
                                        <i class="bi bi-person-lines-fill me-2"></i>Leads
                                    </a></li>
                                @elseif(Auth::user()->hasRole('customer'))
                                    <li><a class="dropdown-item" href="{{ route('customer.dashboard') }}">
                                        <i class="bi bi-person-circle me-2"></i>Dashboard
                                    </a></li>
                                    <li><a class="dropdown-item" href="{{ route('customer.properties') }}">
                                        <i class="bi bi-house me-2"></i>Properties
                                    </a></li>
                                    <li><a class="dropdown-item" href="{{ route('customer.inquiries') }}">
                                        <i class="bi bi-envelope me-2"></i>My Inquiries
                                    </a></li>
                                @elseif(Auth::user()->hasRole('employee'))
                                    <li><a class="dropdown-item" href="{{ route('employee.dashboard') }}">
                                        <i class="bi bi-building me-2"></i>Dashboard
                                    </a></li>
                                    <li><a class="dropdown-item" href="{{ route('employee.tasks') }}">
                                        <i class="bi bi-list-check me-2"></i>Tasks
                                    </a></li>
                                    <li><a class="dropdown-item" href="{{ route('employee.attendance') }}">
                                        <i class="bi bi-calendar-check me-2"></i>Attendance
                                    </a></li>
                                @endif
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="{{ route('profile') }}">
                                    <i class="bi bi-person me-2"></i>Profile Settings
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="dropdown-item text-danger">
                                            <i class="bi bi-box-arrow-right me-2"></i>Logout
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    @else
                        <div class="d-flex gap-2 justify-content-end">
                            <a href="{{ route('login') }}" class="btn btn-outline-primary">Login</a>
                            <a href="{{ route('register') }}" class="btn btn-primary">Register</a>
                        </div>
                    @endauth
                </div>
            </div>
        </div>
    </div>

    <!-- Mobile Sidebar Navigation -->
    <div class="sidebar d-lg-none" id="mobileSidebar">
        <div class="p-3">
            <!-- User Info -->
            @auth
                <div class="text-center mb-4 pb-3 border-bottom">
                    <img src="{{ Auth::user()->avatar ?? asset('images/user/default-avatar.jpg') }}"
                         alt="Profile" class="rounded-circle mb-2" style="width: 60px; height: 60px; object-fit: cover;">
                    <div class="fw-bold">{{ Auth::user()->name }}</div>
                    <small class="text-muted">
                        @if(Auth::user()->hasRole('associate')) Associate
                        @elseif(Auth::user()->hasRole('agent')) Agent
                        @elseif(Auth::user()->hasRole('customer')) Customer
                        @elseif(Auth::user()->hasRole('employee')) Employee
                        @else Member @endif
                    </small>
                </div>
            @endauth

            <!-- Navigation Menu -->
            <nav class="navbar navbar-light p-0">
                <ul class="navbar-nav w-100">
                    <li class="nav-item">
                        <a href="{{ url('/') }}" class="nav-link">
                            <i class="bi bi-house-door me-2"></i>Home
                        </a>
                    </li>

                    @auth
                        @if(Auth::user()->hasRole('associate'))
                            <li class="nav-item">
                                <a href="{{ route('associate.dashboard') }}" class="nav-link">
                                    <i class="bi bi-house-door me-2"></i>Associate Dashboard
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('associate.team') }}" class="nav-link">
                                    <i class="bi bi-people me-2"></i>My Team
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('associate.commissions') }}" class="nav-link">
                                    <i class="bi bi-cash me-2"></i>Commissions
                                </a>
                            </li>
                        @elseif(Auth::user()->hasRole('agent'))
                            <li class="nav-item">
                                <a href="{{ route('agent.dashboard') }}" class="nav-link">
                                    <i class="bi bi-person-badge me-2"></i>Agent Dashboard
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('properties.index') }}" class="nav-link">
                                    <i class="bi bi-house me-2"></i>Properties
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('agent.leads') }}" class="nav-link">
                                    <i class="bi bi-person-lines-fill me-2"></i>My Leads
                                </a>
                            </li>
                        @elseif(Auth::user()->hasRole('customer'))
                            <li class="nav-item">
                                <a href="{{ route('customer.dashboard') }}" class="nav-link">
                                    <i class="bi bi-person-circle me-2"></i>Customer Dashboard
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('customer.properties') }}" class="nav-link">
                                    <i class="bi bi-house me-2"></i>Browse Properties
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('customer.inquiries') }}" class="nav-link">
                                    <i class="bi bi-envelope me-2"></i>My Inquiries
                                </a>
                            </li>
                        @elseif(Auth::user()->hasRole('employee'))
                            <li class="nav-item">
                                <a href="{{ route('employee.dashboard') }}" class="nav-link">
                                    <i class="bi bi-building me-2"></i>Employee Dashboard
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('employee.tasks') }}" class="nav-link">
                                    <i class="bi bi-list-check me-2"></i>My Tasks
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('employee.attendance') }}" class="nav-link">
                                    <i class="bi bi-calendar-check me-2"></i>Attendance
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('employee.payroll') }}" class="nav-link">
                                    <i class="bi bi-cash me-2"></i>Payroll
                                </a>
                            </li>
                        @endif

                        <li class="nav-item">
                            <a href="{{ route('profile') }}" class="nav-link">
                                <i class="bi bi-person me-2"></i>Profile Settings
                            </a>
                        </li>
                        <li class="nav-item">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="nav-link w-100 text-start border-0 bg-transparent">
                                    <i class="bi bi-box-arrow-right me-2"></i>Logout
                                </button>
                            </form>
                        </li>
                    @else
                        <li class="nav-item">
                            <a href="{{ route('properties.index') }}" class="nav-link">
                                <i class="bi bi-house me-2"></i>Properties
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="#about" class="nav-link">
                                <i class="bi bi-info-circle me-2"></i>About
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="#contact" class="nav-link">
                                <i class="bi bi-envelope me-2"></i>Contact
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('login') }}" class="nav-link">
                                <i class="bi bi-box-arrow-in-right me-2"></i>Login
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('register') }}" class="nav-link">
                                <i class="bi bi-person-plus me-2"></i>Register
                            </a>
                        </li>
                    @endauth
                </ul>
            </nav>
        </div>
    </div>

    <!-- Mobile Overlay -->
    <div class="d-lg-none" id="mobileOverlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1040;" onclick="closeMobileMenu()"></div>
</header>

<script>
// Mobile menu functionality
function toggleMobileMenu() {
    const sidebar = document.getElementById('mobileSidebar');
    const overlay = document.getElementById('mobileOverlay');
    const body = document.body;

    if (sidebar.classList.contains('show')) {
        closeMobileMenu();
    } else {
        sidebar.classList.add('show');
        overlay.style.display = 'block';
        body.style.overflow = 'hidden';
    }
}

function closeMobileMenu() {
    const sidebar = document.getElementById('mobileSidebar');
    const overlay = document.getElementById('mobileOverlay');
    const body = document.body;

    sidebar.classList.remove('show');
    overlay.style.display = 'none';
    body.style.overflow = 'auto';
}

// Close mobile menu when clicking on nav links
document.addEventListener('DOMContentLoaded', function() {
    const navLinks = document.querySelectorAll('#mobileSidebar .nav-link');
    navLinks.forEach(link => {
        link.addEventListener('click', function() {
            // Only close menu if it's not a dropdown toggle
            if (!this.hasAttribute('data-bs-toggle')) {
                closeMobileMenu();
            }
        });
    });
});
</script>
