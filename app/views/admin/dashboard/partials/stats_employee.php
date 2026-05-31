<div class="row g-4 mb-4">
    <div class="col-md-6 col-xl-3">
        <div class="card stat-card">
            <div class="stat-icon p"><i class="fas fa-tasks"></i></div>
            <div><div class="stat-label">My Tasks</div><div class="stat-value"><?php echo number_format($stats['my_tasks'] ?? 0); ?></div></div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card stat-card">
            <div class="stat-icon s"><i class="fas fa-clock"></i></div>
            <div><div class="stat-label">Pending</div><div class="stat-value text-warning"><?php echo number_format($stats['pending_tasks'] ?? 0); ?></div></div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card stat-card">
            <div class="stat-icon w"><i class="fas fa-check-double"></i></div>
            <div><div class="stat-label">Completed</div><div class="stat-value text-success"><?php echo number_format($stats['completed_tasks'] ?? 0); ?></div></div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card stat-card">
            <div class="stat-icon i"><i class="fas fa-calendar-check"></i></div>
            <div><div class="stat-label">Attendance</div><div class="stat-value"><?php echo number_format($stats['attendance'] ?? 0); ?>%</div></div>
        </div>
    </div>
</div>
