<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>APS Dream Home - Collaboration Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .collaboration-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            backdrop-filter: blur(10px);
            border: none;
            margin-bottom: 20px;
        }
        .user-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(45deg, #667eea, #764ba2);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            margin-right: 15px;
        }
        .task-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8em;
            font-weight: 500;
        }
        .status-online {
            background: #28a745;
            color: white;
        }
        .status-working {
            background: #ffc107;
            color: #212529;
        }
        .status-offline {
            background: #6c757d;
            color: white;
        }
        .work-area {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 10px;
        }
        .notification-item {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 8px;
            padding: 10px 15px;
            margin-bottom: 8px;
            border-left: 4px solid #667eea;
        }
        .real-time-indicator {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #28a745;
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 0.9em;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }
        .pulse {
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }
    </style>
</head>
<body>
    <div class="real-time-indicator pulse">
        <i class="fas fa-circle"></i> Real-Time Sync Active
    </div>

    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12 text-center mb-4">
                <h1 class="text-white mb-2">
                    <i class="fas fa-users-cog me-3"></i>
                    APS Dream Home - Collaboration Dashboard
                </h1>
                <p class="text-white-50 mb-0">Real-time collaborative development platform</p>
            </div>
        </div>

        <div class="row">
            <!-- Current Tasks -->
            <div class="col-lg-6">
                <div class="collaboration-card p-4">
                    <h4 class="mb-4">
                        <i class="fas fa-tasks text-primary me-2"></i>
                        Current Tasks
                    </h4>

                    <div id="currentTasks">
                        <!-- Tasks will be loaded here -->
                        <div class="text-center text-muted">
                            <i class="fas fa-spinner fa-spin fa-2x mb-3"></i>
                            <p>Loading current tasks...</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Work Division -->
            <div class="col-lg-6">
                <div class="collaboration-card p-4">
                    <h4 class="mb-4">
                        <i class="fas fa-sitemap text-success me-2"></i>
                        Work Division
                    </h4>

                    <div id="workDivision">
                        <!-- Work areas will be loaded here -->
                        <div class="text-center text-muted">
                            <i class="fas fa-spinner fa-spin fa-2x mb-3"></i>
                            <p>Loading work division...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Recent Notifications -->
            <div class="col-lg-8">
                <div class="collaboration-card p-4">
                    <h4 class="mb-4">
                        <i class="fas fa-bell text-warning me-2"></i>
                        Recent Notifications
                    </h4>

                    <div id="recentNotifications" style="max-height: 400px; overflow-y: auto;">
                        <!-- Notifications will be loaded here -->
                        <div class="text-center text-muted">
                            <i class="fas fa-spinner fa-spin fa-2x mb-3"></i>
                            <p>Loading notifications...</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="col-lg-4">
                <div class="collaboration-card p-4">
                    <h4 class="mb-4">
                        <i class="fas fa-bolt text-danger me-2"></i>
                        Quick Actions
                    </h4>

                    <div class="d-grid gap-2">
                        <button class="btn btn-primary" onclick="updateTask()">
                            <i class="fas fa-edit me-2"></i>
                            Update My Task
                        </button>

                        <button class="btn btn-success" onclick="runSync()">
                            <i class="fas fa-sync me-2"></i>
                            Run Git Sync
                        </button>

                        <button class="btn btn-info" onclick="viewLogs()">
                            <i class="fas fa-history me-2"></i>
                            View Activity Logs
                        </button>

                        <button class="btn btn-warning" onclick="assignTask()">
                            <i class="fas fa-user-plus me-2"></i>
                            Assign Task
                        </button>
                    </div>

                    <hr class="my-4">

                    <div class="text-center">
                        <small class="text-muted">
                            <i class="fas fa-clock me-1"></i>
                            Last updated: <span id="lastUpdate">--:--:--</span>
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Update Task Modal -->
    <div class="modal fade" id="taskModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Update Current Task</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Current Task</label>
                        <input type="text" class="form-control" id="taskInput" placeholder="What are you working on?">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" id="statusSelect">
                            <option value="active">Active</option>
                            <option value="break">On Break</option>
                            <option value="completed">Completed</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="saveTask()">Save Task</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Global variables
        let lastUpdate = Date.now();

        // Initialize dashboard
        document.addEventListener('DOMContentLoaded', function() {
            loadDashboardData();
            setInterval(loadDashboardData, 5000); // Refresh every 5 seconds
        });

        // Load dashboard data
        async function loadDashboardData() {
            try {
                const response = await fetch('collaboration_api.php?action=get_status');
                const data = await response.json();

                updateCurrentTasks(data.current_tasks);
                updateWorkDivision(data.work_division);
                updateNotifications(data.notifications);
                updateLastSync(data.last_sync);

                document.getElementById('lastUpdate').textContent = new Date().toLocaleTimeString();
            } catch (error) {
                console.error('Error loading dashboard data:', error);
            }
        }

        // Update current tasks display
        function updateCurrentTasks(tasks) {
            const container = document.getElementById('currentTasks');
            let html = '';

            for (const [userId, userData] of Object.entries(tasks)) {
                const statusClass = userData.status === 'active' ? 'status-online' :
                                  userData.status === 'working' ? 'status-working' : 'status-offline';

                html += `
                    <div class="d-flex align-items-center mb-3">
                        <div class="user-avatar">${userData.name.charAt(0).toUpperCase()}</div>
                        <div class="flex-grow-1">
                            <h6 class="mb-1">${userData.name}</h6>
                            <p class="mb-1 text-muted small">${userData.current_task}</p>
                            <span class="task-badge ${statusClass}">${userData.status}</span>
                        </div>
                        <small class="text-muted">${formatTimeAgo(userData.last_update)}</small>
                    </div>
                `;
            }

            container.innerHTML = html;
        }

        // Update work division display
        function updateWorkDivision(division) {
            const container = document.getElementById('workDivision');
            let html = '';

            for (const [area, assignedTo] of Object.entries(division)) {
                html += `
                    <div class="work-area">
                        <div class="d-flex justify-content-between align-items-center">
                            <span><strong>${area.charAt(0).toUpperCase() + area.slice(1)}</strong></span>
                            <span class="badge bg-light text-dark">${assignedTo}</span>
                        </div>
                    </div>
                `;
            }

            container.innerHTML = html;
        }

        // Update notifications display
        function updateNotifications(notifications) {
            const container = document.getElementById('recentNotifications');
            let html = '';

            if (notifications && notifications.length > 0) {
                notifications.slice(-10).reverse().forEach(notification => {
                    html += `
                        <div class="notification-item">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <strong>${notification.type}</strong>
                                    <p class="mb-0 small text-muted">${notification.message}</p>
                                </div>
                                <small class="text-muted">${formatTimeAgo(notification.timestamp)}</small>
                            </div>
                        </div>
                    `;
                });
            } else {
                html = '<p class="text-muted text-center">No recent notifications</p>';
            }

            container.innerHTML = html;
        }

        // Update last sync time
        function updateLastSync(syncTime) {
            // Update sync indicator if needed
        }

        // Format time ago
        function formatTimeAgo(timestamp) {
            const now = new Date();
            const time = new Date(timestamp);
            const diff = now - time;

            const minutes = Math.floor(diff / 60000);
            const hours = Math.floor(diff / 3600000);
            const days = Math.floor(diff / 86400000);

            if (minutes < 1) return 'Just now';
            if (minutes < 60) return `${minutes}m ago`;
            if (hours < 24) return `${hours}h ago`;
            return `${days}d ago`;
        }

        // Quick action functions
        function updateTask() {
            const modal = new bootstrap.Modal(document.getElementById('taskModal'));
            modal.show();
        }

        function runSync() {
            fetch('collaboration_api.php?action=run_sync')
                .then(response => response.json())
                .then(data => {
                    alert('Git sync completed: ' + data.message);
                    loadDashboardData();
                })
                .catch(error => {
                    alert('Sync failed: ' + error.message);
                });
        }

        function viewLogs() {
            window.open('collaboration_api.php?action=get_logs', '_blank');
        }

        function assignTask() {
            const taskType = prompt('Enter task type (e.g., frontend, backend):');
            const user = prompt('Assign to user:');

            if (taskType && user) {
                fetch('collaboration_api.php?action=assign_task', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ task_type: taskType, user: user })
                })
                .then(response => response.json())
                .then(data => {
                    alert('Task assigned: ' + data.message);
                    loadDashboardData();
                });
            }
        }

        function saveTask() {
            const task = document.getElementById('taskInput').value;
            const status = document.getElementById('statusSelect').value;

            fetch('collaboration_api.php?action=update_task', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ task: task, status: status })
            })
            .then(response => response.json())
            .then(data => {
                alert('Task updated: ' + data.message);
                bootstrap.Modal.getInstance(document.getElementById('taskModal')).hide();
                loadDashboardData();
            });
        }

        // Real-time updates using Server-Sent Events or WebSocket would go here
        // For now, we use polling every 5 seconds
    </script>
</body>
</html>
