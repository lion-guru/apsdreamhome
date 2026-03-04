@extends('layouts.admin')

@section('title', 'Admin Dashboard - APS Dream Home')

@section('content')
<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3 col-lg-2 sidebar">
            <h4 class="text-white">
                <i class="fas fa-home me-2"></i>APS Dream Home
            </h4>
            <nav class="nav flex-column">
                <a href="{{ url('/admin') }}" class="nav-link active">
                    <i class="fas fa-tachometer-alt"></i>Dashboard
                </a>
                <a href="{{ url('/admin/properties') }}" class="nav-link">
                    <i class="fas fa-building"></i>Properties
                </a>
                <a href="{{ url('/admin/users') }}" class="nav-link">
                    <i class="fas fa-users"></i>Users
                </a>
                <a href="{{ url('/admin/leads') }}" class="nav-link">
                    <i class="fas fa-phone"></i>Leads
                </a>
                <a href="{{ url('/admin/reports') }}" class="nav-link">
                    <i class="fas fa-chart-bar"></i>Reports
                </a>
                <a href="{{ url('/admin/settings') }}" class="nav-link">
                    <i class="fas fa-cog"></i>Settings
                </a>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="col-md-9 col-lg-10 main-content">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">Dashboard Overview</h2>
                <div class="text-muted">
                    <i class="fas fa-clock me-2"></i>
                    Last updated: {{ now()->format('M j, Y - g:i A') }}
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="stat-card">
                        <div class="text-primary mb-3">
                            <i class="fas fa-building fa-3x"></i>
                        </div>
                        <h3 class="mb-2">{{ $total_properties ?? 150 }}</h3>
                        <p class="text-muted mb-0">Total Properties</p>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="stat-card">
                        <div class="text-success mb-3">
                            <i class="fas fa-users fa-3x"></i>
                        </div>
                        <h3 class="mb-2">{{ $total_users ?? 85 }}</h3>
                        <p class="text-muted mb-0">Total Users</p>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="stat-card">
                        <div class="text-warning mb-3">
                            <i class="fas fa-phone fa-3x"></i>
                        </div>
                        <h3 class="mb-2">{{ $total_leads ?? 42 }}</h3>
                        <p class="text-muted mb-0">New Leads</p>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="stat-card">
                        <div class="text-info mb-3">
                            <i class="fas fa-chart-line fa-3x"></i>
                        </div>
                        <h3 class="mb-2">{{ $monthly_views ?? 1250 }}</h3>
                        <p class="text-muted mb-0">Monthly Views</p>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="row">
                <div class="col-lg-8 mb-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">
                                <i class="fas fa-history me-2"></i>Recent Activity
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="activity-list">
                                @foreach($recent_activities ?? [] as $activity)
                                <div class="activity-item d-flex align-items-center mb-3">
                                    <div class="activity-icon me-3">
                                        <i class="fas {{ $activity['icon'] }} text-primary"></i>
                                    </div>
                                    <div class="activity-content flex-grow-1">
                                        <div class="fw-semibold">{{ $activity['title'] }}</div>
                                        <div class="text-muted small">{{ $activity['description'] }}</div>
                                        <div class="text-muted small">{{ $activity['time'] }}</div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 mb-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">
                                <i class="fas fa-chart-pie me-2"></i>Quick Stats
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="quick-stats">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span>Active Listings</span>
                                    <span class="badge bg-success">{{ $active_listings ?? 45 }}</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span>Pending Reviews</span>
                                    <span class="badge bg-warning">{{ $pending_reviews ?? 8 }}</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span>New Messages</span>
                                    <span class="badge bg-info">{{ $new_messages ?? 12 }}</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>System Health</span>
                                    <span class="badge bg-success">Good</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.sidebar {
    background: linear-gradient(135deg, #0f2b66 0%, #1b5fd0 50%, #0f2b66 100%);
    min-height: 100vh;
    padding: 20px;
}

.sidebar h4 {
    color: white;
    margin-bottom: 30px;
}

.nav-link {
    color: rgba(255,255,255,0.8);
    padding: 12px 15px;
    margin-bottom: 5px;
    border-radius: 8px;
    transition: all 0.3s ease;
    text-decoration: none;
}

.nav-link:hover, .nav-link.active {
    background: rgba(255,255,255,0.2);
    color: white;
}

.nav-link i {
    margin-right: 10px;
}

.main-content {
    padding: 30px;
    background: #f8f9fa;
    min-height: 100vh;
}

.stat-card {
    background: white;
    border-radius: 10px;
    padding: 25px;
    text-align: center;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    transition: transform 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 20px rgba(0,0,0,0.15);
}

.stat-card h3 {
    font-size: 2rem;
    font-weight: bold;
    margin-bottom: 0;
}

.activity-item {
    padding: 15px 0;
    border-bottom: 1px solid #eee;
}

.activity-item:last-child {
    border-bottom: none;
}

.activity-icon {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f8f9fa;
    border-radius: 50%;
}

.quick-stats .badge {
    font-size: 0.9rem;
    padding: 5px 10px;
}
</style>
@endpush
@endsection
