<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Senior Developer Control Center</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .card-shadow {
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        .pulse-dot {
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        .terminal {
            background: #1a1a1a;
            color: #00ff00;
            font-family: 'Courier New', monospace;
        }
        .typing-cursor {
            animation: blink 1s infinite;
        }
        @keyframes blink {
            0%, 50% { opacity: 1; }
            51%, 100% { opacity: 0; }
        }
        .metric-card {
            transition: all 0.3s ease;
        }
        .metric-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.15);
        }
        .progress-ring {
            transform: rotate(-90deg);
        }
        .progress-ring-circle {
            transition: stroke-dashoffset 0.5s ease;
        }
        .command-btn {
            transition: all 0.3s ease;
        }
        .command-btn:hover {
            transform: scale(1.05);
        }
        .status-indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 8px;
        }
        .status-online { background: #10b981; }
        .status-warning { background: #f59e0b; }
        .status-offline { background: #ef4444; }
    </style>
</head>
<body class="bg-gray-900 text-white">
    <!-- Header -->
    <div class="gradient-bg p-6">
        <div class="container mx-auto">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-4xl font-bold flex items-center">
                        <i class="fas fa-robot mr-3"></i>
                        Senior Developer Control Center
                    </h1>
                    <p class="text-purple-200 mt-2">Advanced Project Management & AI Assistant</p>
                </div>
                <div class="text-right">
                    <div class="text-sm text-purple-200">System Status</div>
                    <div class="text-2xl font-bold">
                        <span class="pulse-dot text-green-400">●</span> ONLINE
                    </div>
                    <div class="text-sm text-purple-200 mt-1">
                        <span id="currentTime"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container mx-auto p-6">
        <!-- Top Metrics Row -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="metric-card bg-gray-800 rounded-lg p-6 border border-gray-700">
                <div class="flex items-center justify-between mb-4">
                    <div class="bg-blue-500 bg-opacity-20 p-3 rounded-full">
                        <i class="fas fa-database text-blue-400 text-xl"></i>
                    </div>
                    <div class="text-right">
                        <span class="status-indicator status-online"></span>
                        <span class="text-xs text-gray-400">Active</span>
                    </div>
                </div>
                <div class="text-3xl font-bold text-blue-400">633</div>
                <div class="text-gray-400 text-sm">Database Tables</div>
                <div class="mt-2">
                    <div class="w-full bg-gray-700 rounded-full h-2">
                        <div class="bg-blue-400 h-2 rounded-full" style="width: 95%"></div>
                    </div>
                    <div class="text-xs text-gray-500 mt-1">95% Optimized</div>
                </div>
            </div>

            <div class="metric-card bg-gray-800 rounded-lg p-6 border border-gray-700">
                <div class="flex items-center justify-between mb-4">
                    <div class="bg-green-500 bg-opacity-20 p-3 rounded-full">
                        <i class="fas fa-brain text-green-400 text-xl"></i>
                    </div>
                    <div class="text-right">
                        <span class="status-indicator status-online"></span>
                        <span class="text-xs text-gray-400">Active</span>
                    </div>
                </div>
                <div class="text-3xl font-bold text-green-400">7</div>
                <div class="text-gray-400 text-sm">AI Roles</div>
                <div class="mt-2">
                    <div class="w-full bg-gray-700 rounded-full h-2">
                        <div class="bg-green-400 h-2 rounded-full" style="width: 88%"></div>
                    </div>
                    <div class="text-xs text-gray-500 mt-1">88% Efficient</div>
                </div>
            </div>

            <div class="metric-card bg-gray-800 rounded-lg p-6 border border-gray-700">
                <div class="flex items-center justify-between mb-4">
                    <div class="bg-purple-500 bg-opacity-20 p-3 rounded-full">
                        <i class="fas fa-shield-alt text-purple-400 text-xl"></i>
                    </div>
                    <div class="text-right">
                        <span class="status-indicator status-online"></span>
                        <span class="text-xs text-gray-400">Active</span>
                    </div>
                </div>
                <div class="text-3xl font-bold text-purple-400">92%</div>
                <div class="text-gray-400 text-sm">Security Score</div>
                <div class="mt-2">
                    <div class="w-full bg-gray-700 rounded-full h-2">
                        <div class="bg-purple-400 h-2 rounded-full" style="width: 92%"></div>
                    </div>
                    <div class="text-xs text-gray-500 mt-1">Hardened</div>
                </div>
            </div>

            <div class="metric-card bg-gray-800 rounded-lg p-6 border border-gray-700">
                <div class="flex items-center justify-between mb-4">
                    <div class="bg-orange-500 bg-opacity-20 p-3 rounded-full">
                        <i class="fas fa-tachometer-alt text-orange-400 text-xl"></i>
                    </div>
                    <div class="text-right">
                        <span class="status-indicator status-online"></span>
                        <span class="text-xs text-gray-400">Active</span>
                    </div>
                </div>
                <div class="text-3xl font-bold text-orange-400">150ms</div>
                <div class="text-gray-400 text-sm">Response Time</div>
                <div class="mt-2">
                    <div class="w-full bg-gray-700 rounded-full h-2">
                        <div class="bg-orange-400 h-2 rounded-full" style="width: 95%"></div>
                    </div>
                    <div class="text-xs text-gray-500 mt-1">99.9% Uptime</div>
                </div>
            </div>
        </div>

        <!-- Main Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Left Column - Command Center -->
            <div class="lg:col-span-2 space-y-6">
                
                <!-- Terminal -->
                <div class="bg-gray-800 rounded-lg border border-gray-700">
                    <div class="border-b border-gray-700 p-4">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-bold flex items-center">
                                <i class="fas fa-terminal mr-2 text-green-400"></i>
                                Command Terminal
                            </h3>
                            <div class="flex space-x-2">
                                <button onclick="clearTerminal()" class="text-gray-400 hover:text-white">
                                    <i class="fas fa-trash"></i>
                                </button>
                                <button onclick="toggleFullscreen()" class="text-gray-400 hover:text-white">
                                    <i class="fas fa-expand"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div id="terminal" class="terminal p-4 h-64 overflow-y-auto text-sm">
                        <div class="text-green-400">$ Senior Developer AI Assistant v2.0</div>
                        <div class="text-green-400">$ System initialized and ready</div>
                        <div class="text-green-400">$ Type commands or use quick actions below</div>
                    </div>
                </div>

                <!-- Command Palette -->
                <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
                    <h3 class="text-lg font-bold mb-4 flex items-center">
                        <i class="fas fa-command mr-2 text-blue-400"></i>
                        Command Palette
                    </h3>
                    
                    <!-- Quick Commands -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
                        <button onclick="executeCommand('full_control')" class="command-btn bg-gradient-to-r from-blue-500 to-blue-600 text-white p-3 rounded-lg text-sm">
                            <i class="fas fa-play-circle mr-2"></i>
                            Full Control
                        </button>
                        <button onclick="executeCommand('security_audit')" class="command-btn bg-gradient-to-r from-purple-500 to-purple-600 text-white p-3 rounded-lg text-sm">
                            <i class="fas fa-shield-alt mr-2"></i>
                            Security Audit
                        </button>
                        <button onclick="executeCommand('optimize_system')" class="command-btn bg-gradient-to-r from-green-500 to-green-600 text-white p-3 rounded-lg text-sm">
                            <i class="fas fa-tachometer-alt mr-2"></i>
                            Optimize
                        </button>
                        <button onclick="executeCommand('deploy_update')" class="command-btn bg-gradient-to-r from-orange-500 to-orange-600 text-white p-3 rounded-lg text-sm">
                            <i class="fas fa-rocket mr-2"></i>
                            Deploy
                        </button>
                        <button onclick="executeCommand('emergency_fix')" class="command-btn bg-gradient-to-r from-red-500 to-red-600 text-white p-3 rounded-lg text-sm">
                            <i class="fas fa-wrench mr-2"></i>
                            Emergency Fix
                        </button>
                        <button onclick="executeCommand('team_coordination')" class="command-btn bg-gradient-to-r from-indigo-500 to-indigo-600 text-white p-3 rounded-lg text-sm">
                            <i class="fas fa-users mr-2"></i>
                            Team Sync
                        </button>
                        <button onclick="executeCommand('ai_enhancement')" class="command-btn bg-gradient-to-r from-pink-500 to-pink-600 text-white p-3 rounded-lg text-sm">
                            <i class="fas fa-brain mr-2"></i>
                            AI Boost
                        </button>
                        <button onclick="executeCommand('system_status')" class="command-btn bg-gradient-to-r from-gray-500 to-gray-600 text-white p-3 rounded-lg text-sm">
                            <i class="fas fa-info-circle mr-2"></i>
                            Status
                        </button>
                    </div>

                    <!-- Custom Command Input -->
                    <div class="flex space-x-2">
                        <input 
                            type="text" 
                            id="customCommand" 
                            placeholder="Enter custom command..."
                            class="flex-1 bg-gray-700 border border-gray-600 rounded-lg p-3 text-white placeholder-gray-400 focus:outline-none focus:border-blue-500"
                            onkeypress="if(event.key === 'Enter') executeCustomCommand()"
                        >
                        <button 
                            onclick="executeCustomCommand()"
                            class="bg-blue-600 text-white p-3 rounded-lg hover:bg-blue-700 transition"
                        >
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </div>
                </div>

                <!-- Performance Charts -->
                <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
                    <h3 class="text-lg font-bold mb-4 flex items-center">
                        <i class="fas fa-chart-line mr-2 text-green-400"></i>
                        Performance Analytics
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h4 class="text-sm font-medium text-gray-400 mb-2">Response Time</h4>
                            <canvas id="responseTimeChart" height="150"></canvas>
                        </div>
                        <div>
                            <h4 class="text-sm font-medium text-gray-400 mb-2">Memory Usage</h4>
                            <canvas id="memoryChart" height="150"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column - Status & Monitoring -->
            <div class="space-y-6">
                
                <!-- AI Assistant Chat -->
                <div class="bg-gray-800 rounded-lg border border-gray-700">
                    <div class="border-b border-gray-700 p-4">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-bold flex items-center">
                                <i class="fas fa-robot mr-2 text-purple-400"></i>
                                AI Assistant
                            </h3>
                            <button onclick="openChatWindow()" class="text-blue-400 hover:text-blue-300">
                                <i class="fas fa-expand-alt"></i>
                            </button>
                        </div>
                    </div>
                    <div class="p-4">
                        <div class="bg-gray-700 rounded-lg p-3 mb-3">
                            <div class="flex items-start">
                                <div class="bg-purple-500 bg-opacity-20 p-2 rounded-full mr-2">
                                    <i class="fas fa-robot text-purple-400 text-xs"></i>
                                </div>
                                <div class="text-sm">
                                    <p class="text-gray-300">Hello! I'm your AI development assistant. I can help you with:</p>
                                    <ul class="mt-1 text-xs text-gray-400">
                                        <li>• Code analysis & optimization</li>
                                        <li>• Architecture planning</li>
                                        <li>• Debugging assistance</li>
                                        <li>• Performance recommendations</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="flex space-x-2">
                            <input 
                                type="text" 
                                id="aiChatInput" 
                                placeholder="Ask me anything..."
                                class="flex-1 bg-gray-700 border border-gray-600 rounded p-2 text-sm text-white placeholder-gray-400 focus:outline-none focus:border-purple-500"
                                onkeypress="if(event.key === 'Enter') sendAIChat()"
                            >
                            <button onclick="sendAIChat()" class="bg-purple-600 text-white p-2 rounded hover:bg-purple-700">
                                <i class="fas fa-paper-plane text-xs"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- System Health -->
                <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
                    <h3 class="text-lg font-bold mb-4 flex items-center">
                        <i class="fas fa-heartbeat mr-2 text-red-400"></i>
                        System Health
                    </h3>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-gray-400">CPU Usage</span>
                            <div class="flex items-center">
                                <div class="w-24 bg-gray-700 rounded-full h-2 mr-2">
                                    <div class="bg-green-400 h-2 rounded-full" style="width: 45%"></div>
                                </div>
                                <span class="text-sm text-green-400">45%</span>
                            </div>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-400">Memory Usage</span>
                            <div class="flex items-center">
                                <div class="w-24 bg-gray-700 rounded-full h-2 mr-2">
                                    <div class="bg-blue-400 h-2 rounded-full" style="width: 62%"></div>
                                </div>
                                <span class="text-sm text-blue-400">62%</span>
                            </div>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-400">Disk Usage</span>
                            <div class="flex items-center">
                                <div class="w-24 bg-gray-700 rounded-full h-2 mr-2">
                                    <div class="bg-orange-400 h-2 rounded-full" style="width: 78%"></div>
                                </div>
                                <span class="text-sm text-orange-400">78%</span>
                            </div>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-400">Network I/O</span>
                            <div class="flex items-center">
                                <div class="w-24 bg-gray-700 rounded-full h-2 mr-2">
                                    <div class="bg-purple-400 h-2 rounded-full" style="width: 23%"></div>
                                </div>
                                <span class="text-sm text-purple-400">23%</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Active Processes -->
                <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
                    <h3 class="text-lg font-bold mb-4 flex items-center">
                        <i class="fas fa-cogs mr-2 text-orange-400"></i>
                        Active Processes
                    </h3>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between p-2 bg-gray-700 rounded">
                            <div class="flex items-center">
                                <span class="status-indicator status-online"></span>
                                <span class="text-sm">Database Optimizer</span>
                            </div>
                            <span class="text-xs text-gray-400">Running</span>
                        </div>
                        <div class="flex items-center justify-between p-2 bg-gray-700 rounded">
                            <div class="flex items-center">
                                <span class="status-indicator status-online"></span>
                                <span class="text-sm">Security Monitor</span>
                            </div>
                            <span class="text-xs text-gray-400">Active</span>
                        </div>
                        <div class="flex items-center justify-between p-2 bg-gray-700 rounded">
                            <div class="flex items-center">
                                <span class="status-indicator status-warning"></span>
                                <span class="text-sm">Cache Manager</span>
                            </div>
                            <span class="text-xs text-gray-400">Warning</span>
                        </div>
                        <div class="flex items-center justify-between p-2 bg-gray-700 rounded">
                            <div class="flex items-center">
                                <span class="status-indicator status-online"></span>
                                <span class="text-sm">AI Assistant</span>
                            </div>
                            <span class="text-xs text-gray-400">Ready</span>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
                    <h3 class="text-lg font-bold mb-4 flex items-center">
                        <i class="fas fa-history mr-2 text-blue-400"></i>
                        Recent Activity
                    </h3>
                    <div id="activityLog" class="space-y-2 max-h-48 overflow-y-auto">
                        <div class="text-xs p-2 bg-gray-700 rounded font-mono text-gray-300">
                            <span class="text-green-400">[07:42:04]</span> Performance optimization completed
                        </div>
                        <div class="text-xs p-2 bg-gray-700 rounded font-mono text-gray-300">
                            <span class="text-blue-400">[07:38:21]</span> Database tables optimized
                        </div>
                        <div class="text-xs p-2 bg-gray-700 rounded font-mono text-gray-300">
                            <span class="text-purple-400">[07:35:12]</span> Security audit performed
                        </div>
                        <div class="text-xs p-2 bg-gray-700 rounded font-mono text-gray-300">
                            <span class="text-orange-400">[07:32:45]</span> System cache cleared
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
        // Initialize charts
        let responseTimeChart, memoryChart;

        function initCharts() {
            // Response Time Chart
            const responseCtx = document.getElementById('responseTimeChart').getContext('2d');
            responseTimeChart = new Chart(responseCtx, {
                type: 'line',
                data: {
                    labels: ['1m', '2m', '3m', '4m', '5m'],
                    datasets: [{
                        label: 'Response Time (ms)',
                        data: [150, 145, 152, 148, 150],
                        borderColor: 'rgb(34, 197, 94)',
                        backgroundColor: 'rgba(34, 197, 94, 0.1)',
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { color: 'rgba(255, 255, 255, 0.1)' },
                            ticks: { color: 'rgba(255, 255, 255, 0.7)' }
                        },
                        x: {
                            grid: { color: 'rgba(255, 255, 255, 0.1)' },
                            ticks: { color: 'rgba(255, 255, 255, 0.7)' }
                        }
                    }
                }
            });

            // Memory Chart
            const memoryCtx = document.getElementById('memoryChart').getContext('2d');
            memoryChart = new Chart(memoryCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Used', 'Free'],
                    datasets: [{
                        data: [62, 38],
                        backgroundColor: [
                            'rgba(59, 130, 246, 0.8)',
                            'rgba(75, 85, 99, 0.3)'
                        ],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: { color: 'rgba(255, 255, 255, 0.7)' }
                        }
                    }
                }
            });
        }

        function executeCommand(command) {
            const terminal = document.getElementById('terminal');
            const timestamp = new Date().toLocaleTimeString();
            
            // Add command to terminal
            terminal.innerHTML += `<div class="text-blue-400">[$timestamp] $ Executing: ${command}</div>`;
            terminal.scrollTop = terminal.scrollHeight;
            
            // Show processing
            terminal.innerHTML += `<div class="text-yellow-400">[$timestamp] $ Processing...</div>`;
            terminal.scrollTop = terminal.scrollHeight;
            
            // Create form data for POST request
            const formData = new FormData();
            formData.append('command', command);
            
            fetch('/senior-developer/execute', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                terminal.innerHTML = terminal.innerHTML.replace('<div class="text-yellow-400">[$timestamp] $ Processing...</div>', '');
                
                if (data.success) {
                    terminal.innerHTML += `<div class="text-green-400">[$timestamp] $ ✓ Command executed successfully</div>`;
                    terminal.innerHTML += `<div class="text-gray-400">[$timestamp] $ Result: ${JSON.stringify(data.result, null, 2)}</div>`;
                    
                    // Update activity log
                    addActivityLog(command, 'success');
                    
                    // Update charts
                    updateCharts();
                } else {
                    terminal.innerHTML += `<div class="text-red-400">[$timestamp] $ ✗ Command execution failed</div>`;
                    addActivityLog(command, 'failed');
                }
                
                terminal.scrollTop = terminal.scrollHeight;
            })
            .catch(error => {
                terminal.innerHTML = terminal.innerHTML.replace('<div class="text-yellow-400">[$timestamp] $ Processing...</div>', '');
                terminal.innerHTML += `<div class="text-red-400">[$timestamp] $ ✗ Network error occurred</div>`;
                terminal.scrollTop = terminal.scrollHeight;
            });
        }

        function executeCustomCommand() {
            const input = document.getElementById('customCommand');
            const command = input.value.trim();
            
            if (command === '') return;
            
            executeCommand(command);
            input.value = '';
        }

        function sendAIChat() {
            const input = document.getElementById('aiChatInput');
            const message = input.value.trim();
            
            if (message === '') return;
            
            // Add user message to activity log
            addActivityLog(`AI Chat: ${message}`, 'info');
            
            // Simulate AI response
            setTimeout(() => {
                const responses = [
                    "I understand your request. Let me analyze the current system state...",
                    "Based on your project metrics, I recommend optimizing the database queries first.",
                    "The code quality looks good! Consider adding more unit tests for better coverage.",
                    "Security is well implemented. Just ensure regular security audits.",
                    "Performance is optimal. Consider implementing caching for better response times."
                ];
                
                const randomResponse = responses[Math.floor(Math.random() * responses.length)];
                addActivityLog(`AI Response: ${randomResponse}`, 'success');
            }, 1000);
            
            input.value = '';
        }

        function addActivityLog(message, type) {
            const log = document.getElementById('activityLog');
            const timestamp = new Date().toLocaleTimeString();
            
            let colorClass = 'text-gray-400';
            if (type === 'success') colorClass = 'text-green-400';
            else if (type === 'failed') colorClass = 'text-red-400';
            else if (type === 'info') colorClass = 'text-blue-400';
            
            const logEntry = document.createElement('div');
            logEntry.className = 'text-xs p-2 bg-gray-700 rounded font-mono text-gray-300';
            logEntry.innerHTML = `<span class="${colorClass}">[${timestamp}]</span> ${message}`;
            
            log.insertBefore(logEntry, log.firstChild);
            
            // Keep only last 10 entries
            while (log.children.length > 10) {
                log.removeChild(log.lastChild);
            }
        }

        function clearTerminal() {
            const terminal = document.getElementById('terminal');
            terminal.innerHTML = `
                <div class="text-green-400">$ Terminal cleared</div>
                <div class="text-green-400">$ Ready for new commands</div>
            `;
        }

        function toggleFullscreen() {
            // Implement fullscreen functionality
            alert('Fullscreen mode coming soon!');
        }

        function openChatWindow() {
            window.open('/senior-developer/chat', '_blank', 'width=800,height=600');
        }

        function updateCharts() {
            // Simulate real-time data updates
            if (responseTimeChart) {
                const newData = 140 + Math.floor(Math.random() * 20);
                responseTimeChart.data.datasets[0].data.shift();
                responseTimeChart.data.datasets[0].data.push(newData);
                responseTimeChart.update();
            }
            
            if (memoryChart) {
                const newUsage = 55 + Math.floor(Math.random() * 15);
                memoryChart.data.datasets[0].data = [newUsage, 100 - newUsage];
                memoryChart.update();
            }
        }

        function updateCurrentTime() {
            const timeElement = document.getElementById('currentTime');
            if (timeElement) {
                timeElement.textContent = new Date().toLocaleString();
            }
        }

        // Initialize everything when page loads
        document.addEventListener('DOMContentLoaded', function() {
            initCharts();
            updateCurrentTime();
            setInterval(updateCurrentTime, 1000);
            setInterval(updateCharts, 5000); // Update charts every 5 seconds
            
            // Add welcome message to terminal
            const terminal = document.getElementById('terminal');
            terminal.innerHTML += `<div class="text-green-400">$ Welcome to Senior Developer Control Center v2.0</div>`;
            terminal.innerHTML += `<div class="text-green-400">$ All systems operational and ready</div>`;
        });
    </script>
</body>
</html>
