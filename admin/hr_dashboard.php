<?php
session_start();
include 'config.php';
require_once 'includes/universal_dashboard_template.php';

if (!isset($_SESSION['auser'])) {
    header('Location: login.php');
    exit();
}

// Employee stats
$total_employees = $conn->query("SELECT COUNT(*) AS c FROM employees WHERE status='active'")->fetch_assoc()['c'] ?? 0;
$total_leaves = $conn->query("SELECT COUNT(*) AS c FROM leaves WHERE status='approved'")->fetch_assoc()['c'] ?? 0;
$total_attendance = $conn->query("SELECT COUNT(*) AS c FROM attendance WHERE DATE(created_at) = CURDATE()")->fetch_assoc()['c'] ?? 0;
$pending_requests = $conn->query("SELECT COUNT(*) AS c FROM leaves WHERE status='pending'")->fetch_assoc()['c'] ?? 0;

// Recent leave requests
$recent_leaves = $conn->query("SELECT l.*, e.name as employee_name 
                             FROM leaves l 
                             LEFT JOIN employees e ON l.employee_id = e.id 
                             ORDER BY l.created_at DESC LIMIT 5");

// Recent employee activities
$recent_activities = [];
while($leave = $recent_leaves->fetch_assoc()) {
    $recent_activities[] = [
        'title' => 'Leave Request - ' . ucfirst($leave['status']),
        'description' => ($leave['employee_name'] ?? 'Employee') . ' requested ' . $leave['leave_type'] . ' leave',
        'time' => date('M j, Y', strtotime($leave['created_at'])),
        'icon' => $leave['status'] == 'approved' ? 'fas fa-check-circle text-success' : 
                 ($leave['status'] == 'pending' ? 'fas fa-clock text-warning' : 'fas fa-times-circle text-danger')
    ];
}

// Statistics for dashboard
$stats = [
    [
        'icon' => 'fas fa-users',
        'value' => $total_employees,
        'label' => 'Active Employees',
        'change' => '+2 this month',
        'change_type' => 'positive'
    ],
    [
        'icon' => 'fas fa-calendar-check',
        'value' => $total_leaves,
        'label' => 'Approved Leaves',
        'change' => '+8 this month',
        'change_type' => 'neutral'
    ],
    [
        'icon' => 'fas fa-clock',
        'value' => $total_attendance,
        'label' => 'Today\'s Attendance',
        'change' => '85% attendance rate',
        'change_type' => 'positive'
    ],
    [
        'icon' => 'fas fa-hourglass-half',
        'value' => $pending_requests,
        'label' => 'Pending Requests',
        'change' => '-2 from yesterday',
        'change_type' => 'positive'
    ]
];

// Quick actions for HR team
$quick_actions = [
    [
        'title' => 'Add Employee',
        'icon' => 'fas fa-user-plus',
        'url' => 'employees.php?action=add',
        'color' => 'primary'
    ],
    [
        'title' => 'Manage Leaves',
        'icon' => 'fas fa-calendar-alt',
        'url' => 'leaves.php',
        'color' => 'success'
    ],
    [
        'title' => 'Attendance Report',
        'icon' => 'fas fa-chart-bar',
        'url' => 'attendance.php',
        'color' => 'info'
    ],
    [
        'title' => 'Payroll',
        'icon' => 'fas fa-money-bill-wave',
        'url' => 'payroll.php',
        'color' => 'warning'
    ]
];

// Custom content for HR specific widgets
$custom_content = '
<div class="row mt-4">
    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="fas fa-chart-pie me-2"></i>Leave Types Distribution</h5>
            </div>
            <div class="card-body">
                <canvas id="leaveChart" height="300"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="fas fa-calendar-week me-2"></i>Weekly Attendance</h5>
            </div>
            <div class="card-body">
                <canvas id="attendanceChart" height="300"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="fas fa-tasks me-2"></i>HR Quick Tasks</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Review pending leave requests
                                <span class="badge bg-warning rounded-pill">' . $pending_requests . '</span>
                            </li>
                            <li class="list-group-item">Update employee records</li>
                            <li class="list-group-item">Process monthly payroll</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item">Conduct performance reviews</li>
                            <li class="list-group-item">Schedule team meetings</li>
                            <li class="list-group-item">Update HR policies</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.3.0/dist/chart.umd.min.js"></script>
<script>
// Leave Types Chart
const leaveData = {
    labels: ["Sick Leave", "Casual Leave", "Annual Leave", "Emergency Leave"],
    datasets: [{
        data: [25, 35, 30, 10],
        backgroundColor: [
            "var(--hr-primary)",
            "var(--hr-secondary)",
            "var(--success)",
            "var(--warning)"
        ],
        borderWidth: 2,
        borderColor: "rgba(255,255,255,0.8)"
    }]
};

new Chart(document.getElementById("leaveChart"), {
    type: "doughnut",
    data: leaveData,
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: "bottom"
            }
        }
    }
});

// Attendance Chart
const attendanceData = {
    labels: ["Mon", "Tue", "Wed", "Thu", "Fri", "Sat"],
    datasets: [{
        label: "Present",
        data: [45, 42, 48, 46, 44, 38],
        backgroundColor: "var(--success)",
        borderColor: "var(--success)",
        borderWidth: 2
    }, {
        label: "Absent", 
        data: [5, 8, 2, 4, 6, 12],
        backgroundColor: "var(--danger)",
        borderColor: "var(--danger)",
        borderWidth: 2
    }]
};

new Chart(document.getElementById("attendanceChart"), {
    type: "bar",
    data: attendanceData,
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});
</script>';

echo generateUniversalDashboard('hr', $stats, $quick_actions, $recent_activities, $custom_content);
