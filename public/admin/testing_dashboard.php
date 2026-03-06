
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Testing Dashboard - APS Dream Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <h1><i class="bi bi-clipboard-check me-2"></i>Testing Dashboard</h1>
                
                <!-- Test Results -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5>System Health Tests</h5>
                                <button class="btn btn-sm btn-primary" onclick="runTests()">Run Tests</button>
                            </div>
                            <div class="card-body">
                                <div id="testResults">
                                    <!-- Test results will be loaded here -->
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5>Backup Status</h5>
                                <button class="btn btn-sm btn-success" onclick="createBackup()">Create Backup</button>
                            </div>
                            <div class="card-body">
                                <div id="backupResults">
                                    <!-- Backup results will be loaded here -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Test History -->
                <div class="card">
                    <div class="card-header">
                        <h5>Test History</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="testHistoryChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Load test results
        function loadTestResults() {
            fetch("testing_api.php?action=test_results")
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayTestResults(data.results);
                    }
                })
                .catch(error => console.error("Error loading test results:", error));
        }
        
        // Display test results
        function displayTestResults(results) {
            const resultsDiv = document.getElementById("testResults");
            resultsDiv.innerHTML = "";
            
            Object.entries(results).forEach(([testName, result]) => {
                const alertClass = result.status === "pass" ? "alert-success" : "alert-danger";
                const icon = result.status === "pass" ? "bi-check-circle" : "bi-x-circle";
                
                const resultHtml = `
                    <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                        <i class="bi ${icon} me-2"></i>
                        <strong>${testName}:</strong> ${result.message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                `;
                
                resultsDiv.innerHTML += resultHtml;
            });
        }
        
        // Run tests
        function runTests() {
            fetch("testing_api.php?action=run_tests", {method: "POST"})
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayTestResults(data.results);
                        updateTestHistory();
                    }
                })
                .catch(error => console.error("Error running tests:", error));
        }
        
        // Create backup
        function createBackup() {
            fetch("testing_api.php?action=create_backup", {method: "POST"})
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        loadBackupResults();
                    }
                })
                .catch(error => console.error("Error creating backup:", error));
        }
        
        // Load backup results
        function loadBackupResults() {
            fetch("testing_api.php?action=backup_results")
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayBackupResults(data.results);
                    }
                })
                .catch(error => console.error("Error loading backup results:", error));
        }
        
        // Display backup results
        function displayBackupResults(results) {
            const resultsDiv = document.getElementById("backupResults");
            resultsDiv.innerHTML = "";
            
            results.forEach(result => {
                const resultHtml = `
                    <div class="alert alert-info alert-dismissible fade show" role="alert">
                        <i class="bi bi-cloud-download me-2"></i>
                        ${result}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                `;
                
                resultsDiv.innerHTML += resultHtml;
            });
        }
        
        // Update test history chart
        function updateTestHistory() {
            // This would load historical test data
            const ctx = document.getElementById("testHistoryChart").getContext("2d");
            new Chart(ctx, {
                type: "line",
                data: {
                    labels: ["1h ago", "30m ago", "15m ago", "Now"],
                    datasets: [{
                        label: "Tests Passed",
                        data: [4, 5, 5, 5],
                        borderColor: "rgb(75, 192, 192)",
                        tension: 0.1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 5
                        }
                    }
                }
            });
        }
        
        // Initial load
        loadTestResults();
        loadBackupResults();
        updateTestHistory();
    </script>
</body>
</html>