
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Monitoring - APS Dream Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <h1><i class="bi bi-speedometer2 me-2"></i>System Monitoring</h1>
                
                <!-- System Status Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <h5 class="card-title">Database</h5>
                                <h2 id="dbStatus">Online</h2>
                                <small>Connection Status</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body">
                                <h5 class="card-title">Memory</h5>
                                <h2 id="memoryUsage">0%</h2>
                                <small>Memory Usage</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-white">
                            <div class="card-body">
                                <h5 class="card-title">CPU</h5>
                                <h2 id="cpuUsage">0%</h2>
                                <small>CPU Usage</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <h5 class="card-title">Storage</h5>
                                <h2 id="storageUsage">0%</h2>
                                <small>Disk Usage</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Performance Charts -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5>Database Performance</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="dbChart"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5>System Resources</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="resourceChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Activities -->
                <div class="card">
                    <div class="card-header">
                        <h5>Recent System Activities</h5>
                    </div>
                    <div class="card-body">
                        <div id="recentActivities">
                            <!-- Activities will be loaded here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Initialize charts
        const dbChart = new Chart(document.getElementById("dbChart"), {
            type: "line",
            data: {
                labels: ["1m ago", "30s ago", "Now"],
                datasets: [{
                    label: "Query Time (ms)",
                    data: [12, 8, 5],
                    borderColor: "rgb(75, 192, 192)",
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
        
        const resourceChart = new Chart(document.getElementById("resourceChart"), {
            type: "doughnut",
            data: {
                labels: ["CPU", "Memory", "Storage"],
                datasets: [{
                    data: [25, 45, 30],
                    backgroundColor: [
                        "rgb(255, 99, 132)",
                        "rgb(54, 162, 235)",
                        "rgb(255, 205, 86)"
                    ]
                }]
            },
            options: {
                responsive: true
            }
        });
        
        // Load system stats
        function loadSystemStats() {
            fetch("monitoring_api.php?action=system_stats")
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById("memoryUsage").textContent = data.memory + "%";
                        document.getElementById("cpuUsage").textContent = data.cpu + "%";
                        document.getElementById("storageUsage").textContent = data.storage + "%";
                    }
                })
                .catch(error => console.error("Error loading system stats:", error));
        }
        
        // Load recent activities
        function loadRecentActivities() {
            fetch("monitoring_api.php?action=recent_activities")
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const activitiesDiv = document.getElementById("recentActivities");
                        activitiesDiv.innerHTML = data.activities.map(activity => 
                            `<div class="alert alert-${activity.type} alert-dismissible fade show" role="alert">
                                <strong>${activity.time}</strong> - ${activity.message}
                            </div>`
                        ).join("");
                    }
                })
                .catch(error => console.error("Error loading activities:", error));
        }
        
        // Auto-refresh every 30 seconds
        setInterval(() => {
            loadSystemStats();
            loadRecentActivities();
        }, 30000);
        
        // Initial load
        loadSystemStats();
        loadRecentActivities();
    </script>
</body>
</html>