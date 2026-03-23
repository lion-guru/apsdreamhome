<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Senior Developer Full Dashboard - APS Dream Home</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .card-shadow {
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        .pulse-dot {
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }
        .terminal {
            background: #1a1a1a;
            color: #00ff00;
            font-family: 'Courier New', monospace;
        }
        .command-btn {
            transition: all 0.3s ease;
        }
        .command-btn:hover {
            transform: translateY(-2px);
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <div class="gradient-bg text-white p-6">
        <div class="container mx-auto">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-4xl font-bold flex items-center">
                        <i class="fas fa-robot mr-3"></i>
                        Senior Developer Control Center
                    </h1>
                    <p class="text-purple-200 mt-2">Complete Autonomous Project Management System</p>
                </div>
                <div class="text-right">
                    <div class="text-sm text-purple-200">System Status</div>
                    <div class="text-3xl font-bold">
                        <span class="pulse-dot text-green-400">●</span> FULL CONTROL
                    </div>
                    <div class="text-sm text-purple-200 mt-1">
                        Last Updated: <?php echo $status['timestamp']; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container mx-auto p-6">
        <!-- System Overview -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg p-6 card-shadow border-l-4 border-blue-500">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-gray-500 text-sm flex items-center">
                            <i class="fas fa-database mr-2"></i> Database
                        </div>
                        <div class="text-2xl font-bold text-blue-600">633 Tables</div>
                        <div class="text-sm text-gray-600">138 Leads</div>
                    </div>
                    <div class="bg-blue-100 p-3 rounded-full">
                        <i class="fas fa-chart-line text-blue-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg p-6 card-shadow border-l-4 border-green-500">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-gray-500 text-sm flex items-center">
                            <i class="fas fa-brain mr-2"></i> AI System
                        </div>
                        <div class="text-2xl font-bold text-green-600">7 Roles</div>
                        <div class="text-sm text-gray-600">Rate Limited</div>
                    </div>
                    <div class="bg-green-100 p-3 rounded-full">
                        <i class="fas fa-microchip text-green-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg p-6 card-shadow border-l-4 border-purple-500">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-gray-500 text-sm flex items-center">
                            <i class="fas fa-shield-alt mr-2"></i> Security
                        </div>
                        <div class="text-2xl font-bold text-purple-600">HARDENED</div>
                        <div class="text-sm text-gray-600">Protected</div>
                    </div>
                    <div class="bg-purple-100 p-3 rounded-full">
                        <i class="fas fa-lock text-purple-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg p-6 card-shadow border-l-4 border-orange-500">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-gray-500 text-sm flex items-center">
                            <i class="fas fa-tachometer-alt mr-2"></i> Performance
                        </div>
                        <div class="text-2xl font-bold text-orange-600"><?php echo $status['performance_metrics']['code_quality_score']; ?>%</div>
                        <div class="text-sm text-gray-600">Quality Score</div>
                    </div>
                    <div class="bg-orange-100 p-3 rounded-full">
                        <i class="fas fa-rocket text-orange-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Command Panel -->
        <div class="bg-white rounded-lg p-6 card-shadow mb-8">
            <h2 class="text-2xl font-bold mb-6 flex items-center">
                <i class="fas fa-terminal mr-3 text-gray-600"></i>
                Command Execution Panel
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
                <?php foreach ($commands as $cmd => $desc): ?>
                    <button onclick="executeCommand('<?php echo $cmd; ?>')" 
                            class="command-btn bg-gradient-to-r from-blue-500 to-blue-600 text-white p-4 rounded-lg hover:from-blue-600 hover:to-blue-700 flex items-center">
                        <i class="fas fa-play-circle mr-3"></i>
                        <div class="text-left">
                            <div class="font-medium"><?php echo ucfirst(str_replace('_', ' ', $cmd)); ?></div>
                            <div class="text-xs opacity-80"><?php echo $desc; ?></div>
                        </div>
                    </button>
                <?php endforeach; ?>
            </div>

            <!-- Terminal Output -->
            <div class="terminal rounded-lg p-4 h-64 overflow-y-auto" id="terminal">
                <div class="text-green-400">$ Senior Developer Ready...</div>
                <div class="text-green-400">$ Awaiting command...</div>
            </div>
        </div>

        <!-- Real-time Monitoring -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- System Metrics -->
            <div class="bg-white rounded-lg p-6 card-shadow">
                <h2 class="text-xl font-bold mb-4 flex items-center">
                    <i class="fas fa-chart-bar mr-2 text-blue-500"></i>
                    System Metrics
                </h2>
                <div class="space-y-4">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Code Quality</span>
                        <div class="flex items-center">
                            <div class="w-32 bg-gray-200 rounded-full h-2 mr-2">
                                <div class="bg-green-500 h-2 rounded-full" style="width: <?php echo $status['performance_metrics']['code_quality_score']; ?>%"></div>
                            </div>
                            <span class="text-sm font-medium"><?php echo $status['performance_metrics']['code_quality_score']; ?>%</span>
                        </div>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Performance</span>
                        <div class="flex items-center">
                            <div class="w-32 bg-gray-200 rounded-full h-2 mr-2">
                                <div class="bg-blue-500 h-2 rounded-full" style="width: <?php echo $status['performance_metrics']['performance_score']; ?>%"></div>
                            </div>
                            <span class="text-sm font-medium"><?php echo $status['performance_metrics']['performance_score']; ?>%</span>
                        </div>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Security</span>
                        <div class="flex items-center">
                            <div class="w-32 bg-gray-200 rounded-full h-2 mr-2">
                                <div class="bg-purple-500 h-2 rounded-full" style="width: <?php echo $status['performance_metrics']['security_score']; ?>%"></div>
                            </div>
                            <span class="text-sm font-medium"><?php echo $status['performance_metrics']['security_score']; ?>%</span>
                        </div>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Test Coverage</span>
                        <div class="flex items-center">
                            <div class="w-32 bg-gray-200 rounded-full h-2 mr-2">
                                <div class="bg-orange-500 h-2 rounded-full" style="width: <?php echo $status['performance_metrics']['test_coverage']; ?>%"></div>
                            </div>
                            <span class="text-sm font-medium"><?php echo $status['performance_metrics']['test_coverage']; ?>%</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Logs -->
            <div class="bg-white rounded-lg p-6 card-shadow">
                <h2 class="text-xl font-bold mb-4 flex items-center">
                    <i class="fas fa-history mr-2 text-green-500"></i>
                    Recent Activity Logs
                </h2>
                <div class="space-y-2 max-h-64 overflow-y-auto">
                    <?php if (!empty($recent_logs)): ?>
                        <?php foreach ($recent_logs as $log): ?>
                            <div class="text-sm p-2 bg-gray-50 rounded font-mono text-gray-700">
                                <?php echo htmlspecialchars($log); ?>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-gray-500 text-center py-4">No recent logs available</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="bg-white rounded-lg p-6 card-shadow">
            <h2 class="text-xl font-bold mb-4 flex items-center">
                <i class="fas fa-info-circle mr-2 text-purple-500"></i>
                System Information
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div>
                    <div class="text-gray-500 text-sm">Uptime</div>
                    <div class="text-xl font-bold text-green-600"><?php echo $status['performance_metrics']['uptime_percentage']; ?>%</div>
                </div>
                <div>
                    <div class="text-gray-500 text-sm">Response Time</div>
                    <div class="text-xl font-bold text-blue-600"><?php echo $status['performance_metrics']['response_time_ms']; ?>ms</div>
                </div>
                <div>
                    <div class="text-gray-500 text-sm">Error Rate</div>
                    <div class="text-xl font-bold text-red-600"><?php echo $status['performance_metrics']['error_rate']; ?>%</div>
                </div>
                <div>
                    <div class="text-gray-500 text-sm">Team Status</div>
                    <div class="text-xl font-bold text-purple-600"><?php echo $status['team_status']; ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
        function executeCommand(command) {
            const terminal = document.getElementById('terminal');
            
            // Add command to terminal
            terminal.innerHTML += `<div class="text-yellow-400">$ Executing: ${command}</div>`;
            
            fetch('/senior-developer/execute', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ command: command })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    terminal.innerHTML += `<div class="text-green-400">✓ Command executed successfully</div>`;
                    terminal.innerHTML += `<div class="text-blue-400">Result: ${JSON.stringify(data.result, null, 2)}</div>`;
                    setTimeout(() => location.reload(), 2000);
                } else {
                    terminal.innerHTML += `<div class="text-red-400">✗ Command execution failed</div>`;
                }
                terminal.scrollTop = terminal.scrollHeight;
            })
            .catch(error => {
                console.error('Error:', error);
                terminal.innerHTML += `<div class="text-red-400">✗ Network error occurred</div>`;
                terminal.scrollTop = terminal.scrollHeight;
            });
        }

        // Auto-refresh monitoring data
        setInterval(() => {
            fetch('/senior-developer/monitor')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        console.log('Monitoring data updated:', data.monitoring);
                    }
                });
        }, 10000);

        // Auto-refresh logs
        setInterval(() => {
            fetch('/senior-developer/logs')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        console.log('Logs updated:', data.logs);
                    }
                });
        }, 15000);
    </script>
</body>
</html>
