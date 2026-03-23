<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Senior Developer Unified Platform</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .card-shadow {
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .pulse-dot {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.5;
            }
        }

        .tab-active {
            background: rgba(99, 102, 241, 0.1);
            border-bottom: 2px solid #6366f1;
        }

        .terminal {
            background: #1a1a1a;
            color: #00ff00;
            font-family: 'Courier New', monospace;
        }

        .code-editor {
            font-family: 'Courier New', monospace;
            background: #f8f9fa;
        }

        .chat-message {
            animation: slideIn 0.3s ease-out;
        }

        @keyframes slideIn {
            from {
                transform: translateY(10px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
    </style>
</head>

<body class="bg-gray-50">
    <!-- Header -->
    <div class="gradient-bg text-white p-6">
        <div class="container mx-auto">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold flex items-center">
                        <i class="fas fa-robot mr-3"></i>
                        Senior Developer Unified Platform
                    </h1>
                    <p class="text-purple-200 mt-2">Complete Development Control Center</p>
                </div>
                <div class="text-right">
                    <div class="text-sm text-purple-200">System Status</div>
                    <div class="text-2xl font-bold">
                        <span class="pulse-dot text-green-400">●</span> ONLINE
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="bg-white border-b">
        <div class="container mx-auto">
            <div class="flex space-x-1">
                <button onclick="switchTab('dashboard')" id="tab-dashboard" class="tab-active px-6 py-3 font-medium text-sm hover:bg-gray-50">
                    <i class="fas fa-tachometer-alt mr-2"></i>Dashboard
                </button>
                <button onclick="switchTab('chat')" id="tab-chat" class="px-6 py-3 font-medium text-sm hover:bg-gray-50">
                    <i class="fas fa-comments mr-2"></i>AI Chat
                </button>
                <button onclick="switchTab('code')" id="tab-code" class="px-6 py-3 font-medium text-sm hover:bg-gray-50">
                    <i class="fas fa-code mr-2"></i>Code Editor
                </button>
                <button onclick="switchTab('terminal')" id="tab-terminal" class="px-6 py-3 font-medium text-sm hover:bg-gray-50">
                    <i class="fas fa-terminal mr-2"></i>Terminal
                </button>
                <button onclick="switchTab('monitor')" id="tab-monitor" class="px-6 py-3 font-medium text-sm hover:bg-gray-50">
                    <i class="fas fa-chart-line mr-2"></i>Monitor
                </button>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container mx-auto p-6">

        <!-- Dashboard Tab -->
        <div id="content-dashboard" class="tab-content">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-lg card-shadow p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="bg-blue-100 p-3 rounded-full">
                            <i class="fas fa-database text-blue-600 text-xl"></i>
                        </div>
                        <span class="text-green-500 text-sm">● Active</span>
                    </div>
                    <div class="text-2xl font-bold text-blue-600">633</div>
                    <div class="text-gray-500 text-sm">Database Tables</div>
                    <div class="mt-2">
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-blue-600 h-2 rounded-full" style="width: 95%"></div>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg card-shadow p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="bg-green-100 p-3 rounded-full">
                            <i class="fas fa-brain text-green-600 text-xl"></i>
                        </div>
                        <span class="text-green-500 text-sm">● Active</span>
                    </div>
                    <div class="text-2xl font-bold text-green-600">7</div>
                    <div class="text-gray-500 text-sm">AI Roles</div>
                    <div class="mt-2">
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-green-600 h-2 rounded-full" style="width: 88%"></div>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg card-shadow p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="bg-purple-100 p-3 rounded-full">
                            <i class="fas fa-shield-alt text-purple-600 text-xl"></i>
                        </div>
                        <span class="text-green-500 text-sm">● Active</span>
                    </div>
                    <div class="text-2xl font-bold text-purple-600">92%</div>
                    <div class="text-gray-500 text-sm">Security Score</div>
                    <div class="mt-2">
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-purple-600 h-2 rounded-full" style="width: 92%"></div>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg card-shadow p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="bg-orange-100 p-3 rounded-full">
                            <i class="fas fa-tachometer-alt text-orange-600 text-xl"></i>
                        </div>
                        <span class="text-green-500 text-sm">● Active</span>
                    </div>
                    <div class="text-2xl font-bold text-orange-600">150ms</div>
                    <div class="text-gray-500 text-sm">Response Time</div>
                    <div class="mt-2">
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-orange-600 h-2 rounded-full" style="width: 95%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white rounded-lg card-shadow p-6">
                <h3 class="text-lg font-bold mb-4">Quick Actions</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <button onclick="executeCommand('full_control')" class="bg-blue-600 text-white p-4 rounded-lg hover:bg-blue-700">
                        <i class="fas fa-play mr-2"></i>Full Control
                    </button>
                    <button onclick="executeCommand('security_audit')" class="bg-purple-600 text-white p-4 rounded-lg hover:bg-purple-700">
                        <i class="fas fa-shield-alt mr-2"></i>Security Audit
                    </button>
                    <button onclick="executeCommand('optimize_system')" class="bg-green-600 text-white p-4 rounded-lg hover:bg-green-700">
                        <i class="fas fa-tachometer-alt mr-2"></i>Optimize
                    </button>
                    <button onclick="executeCommand('ai_enhancement')" class="bg-pink-600 text-white p-4 rounded-lg hover:bg-pink-700">
                        <i class="fas fa-brain mr-2"></i>AI Boost
                    </button>
                </div>
            </div>
        </div>

        <!-- AI Chat Tab -->
        <div id="content-chat" class="tab-content hidden">
            <div class="bg-white rounded-lg card-shadow">
                <div class="border-b p-4">
                    <h3 class="text-lg font-bold flex items-center">
                        <i class="fas fa-robot mr-2 text-purple-600"></i>
                        AI Development Assistant
                    </h3>
                </div>
                <div class="p-6">
                    <div id="chatMessages" class="h-96 overflow-y-auto mb-4 space-y-4">
                        <div class="chat-message">
                            <div class="flex items-start">
                                <div class="bg-purple-100 p-2 rounded-full mr-3">
                                    <i class="fas fa-robot text-purple-600 text-sm"></i>
                                </div>
                                <div class="bg-gray-100 rounded-lg p-3 max-w-md">
                                    <p class="text-gray-800">👋 Hello! I'm your Senior Developer AI Assistant. I can help you with:</p>
                                    <ul class="mt-2 text-sm text-gray-600">
                                        <li>• Project planning and architecture</li>
                                        <li>• Code review and optimization</li>
                                        <li>• Debugging and problem solving</li>
                                        <li>• Technology recommendations</li>
                                        <li>• Team coordination</li>
                                        <li>• Performance analysis</li>
                                    </ul>
                                    <p class="mt-2 text-sm text-gray-600">What would you like to work on today?</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="flex space-x-2">
                        <input
                            type="text"
                            id="chatInput"
                            placeholder="Ask me anything about your project..."
                            class="flex-1 p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500"
                            onkeypress="if(event.key === 'Enter') sendChatMessage()">
                        <button onclick="sendChatMessage()" class="bg-purple-600 text-white p-3 rounded-lg hover:bg-purple-700">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Code Editor Tab -->
        <div id="content-code" class="tab-content hidden">
            <div class="bg-white rounded-lg card-shadow">
                <div class="border-b p-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <select id="languageMode" class="border rounded px-3 py-2 text-sm">
                                <option value="php">PHP</option>
                                <option value="javascript">JavaScript</option>
                                <option value="css">CSS</option>
                                <option value="sql">SQL</option>
                                <option value="html">HTML</option>
                            </select>
                            <input
                                type="text"
                                id="fileName"
                                placeholder="File name..."
                                class="border rounded px-3 py-2 text-sm"
                                value="new_file.php">
                        </div>
                        <div class="flex space-x-2">
                            <button onclick="saveCode()" class="bg-green-600 text-white px-4 py-2 rounded text-sm hover:bg-green-700">
                                <i class="fas fa-save mr-2"></i>Save
                            </button>
                            <button onclick="runCode()" class="bg-blue-600 text-white px-4 py-2 rounded text-sm hover:bg-blue-700">
                                <i class="fas fa-play mr-2"></i>Run
                            </button>
                        </div>
                    </div>
                </div>
                <div class="p-4">
                    <textarea
                        id="codeEditor"
                        placeholder="Write your code here..."
                        class="w-full h-96 p-4 code-editor border rounded-lg focus:outline-none resize-none"><?php
                                                                                                                // Senior Developer Code Editor
                                                                                                                // Write your code here and click "Run" to execute

                                                                                                                echo "🚀 Senior Developer Code Editor Ready!";
                                                                                                                echo "Current Project: APS Dream Home Real Estate Platform";
                                                                                                                echo "Database: 633 tables, 138 leads";
                                                                                                                echo "AI System: 7 roles configured";

                                                                                                                // Start coding below this line...
                                                                                                                ?></textarea>
                </div>
                <div class="border-t p-4">
                    <div class="flex items-center justify-between mb-2">
                        <h4 class="font-bold">Output Console</h4>
                        <div class="flex space-x-2">
                            <button onclick="toggleAPIPanel()" class="text-xs bg-purple-600 text-white px-2 py-1 rounded hover:bg-purple-700">
                                <i class="fas fa-plug mr-1"></i>API Extensions
                            </button>
                            <button onclick="clearCodeOutput()" class="text-xs bg-gray-600 text-white px-2 py-1 rounded hover:bg-gray-700">
                                <i class="fas fa-trash mr-1"></i>Clear
                            </button>
                        </div>
                    </div>
                    <div id="codeOutput" class="terminal p-4 h-32 overflow-y-auto text-sm">
                        <div class="text-green-400">$ Code Editor Ready</div>
                        <div class="text-gray-400">$ Write code above and click "Run" to execute</div>
                    </div>
                </div>

                <!-- API Extensions Panel -->
                <div id="apiExtensionsPanel" class="hidden border-t p-4 bg-gray-50">
                    <h4 class="font-bold mb-3 text-purple-600">
                        <i class="fas fa-plug mr-2"></i>API Extensions & Tools
                    </h4>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-4">
                        <!-- GitHub API -->
                        <button onclick="useGitHubAPI()" class="bg-gray-800 text-white p-3 rounded-lg hover:bg-gray-700 text-sm">
                            <i class="fab fa-github mb-1 text-lg"></i>
                            <div>GitHub API</div>
                        </button>

                        <!-- OpenAI API -->
                        <button onclick="useOpenAIAPI()" class="bg-green-600 text-white p-3 rounded-lg hover:bg-green-700 text-sm">
                            <i class="fas fa-robot mb-1 text-lg"></i>
                            <div>OpenAI API</div>
                        </button>

                        <!-- Weather API -->
                        <button onclick="useWeatherAPI()" class="bg-blue-600 text-white p-3 rounded-lg hover:bg-blue-700 text-sm">
                            <i class="fas fa-cloud-sun mb-1 text-lg"></i>
                            <div>Weather API</div>
                        </button>

                        <!-- Database API -->
                        <button onclick="useDatabaseAPI()" class="bg-purple-600 text-white p-3 rounded-lg hover:bg-purple-700 text-sm">
                            <i class="fas fa-database mb-1 text-lg"></i>
                            <div>Database API</div>
                        </button>

                        <!-- File System API -->
                        <button onclick="useFileSystemAPI()" class="bg-orange-600 text-white p-3 rounded-lg hover:bg-orange-700 text-sm">
                            <i class="fas fa-folder mb-1 text-lg"></i>
                            <div>File System</div>
                        </button>

                        <!-- Email API -->
                        <button onclick="useEmailAPI()" class="bg-red-600 text-white p-3 rounded-lg hover:bg-red-700 text-sm">
                            <i class="fas fa-envelope mb-1 text-lg"></i>
                            <div>Email API</div>
                        </button>

                        <!-- Payment API -->
                        <button onclick="usePaymentAPI()" class="bg-yellow-600 text-white p-3 rounded-lg hover:bg-yellow-700 text-sm">
                            <i class="fas fa-credit-card mb-1 text-lg"></i>
                            <div>Payment API</div>
                        </button>

                        <!-- Maps API -->
                        <button onclick="useMapsAPI()" class="bg-indigo-600 text-white p-3 rounded-lg hover:bg-indigo-700 text-sm">
                            <i class="fas fa-map mb-1 text-lg"></i>
                            <div>Maps API</div>
                        </button>
                    </div>

                    <!-- API Code Templates -->
                    <div class="bg-white border rounded-lg p-3">
                        <h5 class="font-bold text-sm mb-2">API Code Template:</h5>
                        <div id="apiCodeTemplate" class="text-xs bg-gray-100 p-2 rounded font-mono max-h-40 overflow-y-auto">
                            Select an API extension above to generate code template...
                        </div>
                        <button onclick="insertAPITemplate()" class="mt-2 bg-purple-600 text-white px-3 py-1 rounded text-sm hover:bg-purple-700">
                            <i class="fas fa-plus mr-1"></i>Insert to Editor
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Terminal Tab -->
        <div id="content-terminal" class="tab-content hidden">
            <div class="bg-white rounded-lg card-shadow">
                <div class="border-b p-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-bold flex items-center">
                            <i class="fas fa-terminal mr-2 text-green-600"></i>
                            Command Terminal
                        </h3>
                        <button onclick="clearTerminal()" class="text-gray-500 hover:text-gray-700">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
                <div id="terminal" class="terminal p-4 h-96 overflow-y-auto text-sm">
                    <div class="text-green-400">$ Senior Developer Terminal v2.0</div>
                    <div class="text-green-400">$ System initialized and ready</div>
                    <div class="text-gray-400">$ Type commands or use quick actions below</div>
                </div>
                <div class="border-t p-4">
                    <div class="flex space-x-2">
                        <input
                            type="text"
                            id="terminalInput"
                            placeholder="Enter command..."
                            class="flex-1 p-3 bg-gray-900 text-green-400 rounded-lg focus:outline-none"
                            onkeypress="if(event.key === 'Enter') executeTerminalCommand()">
                        <button onclick="executeTerminalCommand()" class="bg-green-600 text-white p-3 rounded-lg hover:bg-green-700">
                            <i class="fas fa-play"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Monitor Tab -->
        <div id="content-monitor" class="tab-content hidden">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white rounded-lg card-shadow p-6">
                    <h3 class="text-lg font-bold mb-4">Performance Metrics</h3>
                    <canvas id="performanceChart" height="200"></canvas>
                </div>
                <div class="bg-white rounded-lg card-shadow p-6">
                    <h3 class="text-lg font-bold mb-4">System Health</h3>
                    <div class="space-y-4">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">CPU Usage</span>
                            <div class="flex items-center">
                                <div class="w-24 bg-gray-200 rounded-full h-2 mr-2">
                                    <div class="bg-green-400 h-2 rounded-full" style="width: 45%"></div>
                                </div>
                                <span class="text-sm text-green-600">45%</span>
                            </div>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Memory Usage</span>
                            <div class="flex items-center">
                                <div class="w-24 bg-gray-200 rounded-full h-2 mr-2">
                                    <div class="bg-blue-400 h-2 rounded-full" style="width: 62%"></div>
                                </div>
                                <span class="text-sm text-blue-600">62%</span>
                            </div>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Disk Usage</span>
                            <div class="flex items-center">
                                <div class="w-24 bg-gray-200 rounded-full h-2 mr-2">
                                    <div class="bg-orange-400 h-2 rounded-full" style="width: 78%"></div>
                                </div>
                                <span class="text-sm text-orange-600">78%</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
        let currentTab = 'dashboard';
        let performanceChart;

        function switchTab(tab) {
            // Hide all content
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.add('hidden');
            });

            // Remove active class from all tabs
            document.querySelectorAll('[id^="tab-"]').forEach(tabBtn => {
                tabBtn.classList.remove('tab-active');
            });

            // Show selected content
            document.getElementById('content-' + tab).classList.remove('hidden');
            document.getElementById('tab-' + tab).classList.add('tab-active');

            currentTab = tab;

            // Initialize chart when monitor tab is selected
            if (tab === 'monitor' && !performanceChart) {
                initPerformanceChart();
            }
        }

        function executeCommand(command) {
            const formData = new FormData();
            formData.append('command', command);

            fetch('/test_execute.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification('Command executed successfully!', 'success');
                    } else {
                        showNotification('Command execution failed!', 'error');
                    }
                })
                .catch(error => {
                    showNotification('Network error occurred!', 'error');
                });
        }

        function sendChatMessage() {
            const input = document.getElementById('chatInput');
            const message = input.value.trim();

            if (message === '') return;

            // Add user message
            addChatMessage(message, 'user');
            input.value = '';

            // Simulate AI response
            setTimeout(() => {
                const response = generateAIResponse(message);
                addChatMessage(response, 'ai');
            }, 1000);
        }

        function addChatMessage(message, sender) {
            const chatMessages = document.getElementById('chatMessages');
            const messageDiv = document.createElement('div');
            messageDiv.className = 'chat-message';

            if (sender === 'user') {
                messageDiv.innerHTML = `
                    <div class="flex items-start justify-end">
                        <div class="bg-blue-600 text-white rounded-lg p-3 max-w-md">
                            <p class="text-sm">${message}</p>
                        </div>
                        <div class="bg-gray-100 p-2 rounded-full ml-3">
                            <i class="fas fa-user text-gray-600 text-sm"></i>
                        </div>
                    </div>
                `;
            } else {
                messageDiv.innerHTML = `
                    <div class="flex items-start">
                        <div class="bg-purple-100 p-2 rounded-full mr-3">
                            <i class="fas fa-robot text-purple-600 text-sm"></i>
                        </div>
                        <div class="bg-gray-100 rounded-lg p-3 max-w-md">
                            <p class="text-gray-800 text-sm">${message}</p>
                        </div>
                    </div>
                `;
            }

            chatMessages.appendChild(messageDiv);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        function generateAIResponse(message) {
            const lowerMessage = message.toLowerCase();

            if (lowerMessage.includes('code') || lowerMessage.includes('write') || lowerMessage.includes('create')) {
                return "मैं आपके लिए code लिख सकता हूं! Switch to the Code Editor tab above. I can help you with PHP, JavaScript, CSS, SQL, and HTML.";
            } else if (lowerMessage.includes('status') || lowerMessage.includes('analyze')) {
                return "📊 **Project Analysis Complete**\n\n• **Database**: 633 tables, 138 leads\n• **Code Quality**: 95% score\n• **Security**: Hardened with audit\n• **Performance**: Optimized (150ms response)\n• **AI System**: 7 roles active";
            } else if (lowerMessage.includes('help') || lowerMessage.includes('assist')) {
                return "मैं आपके सभी development needs में help कर सकता हूं:\n\n• **Code Writing**: Code Editor tab में जाएं\n• **Commands**: Dashboard में quick actions use करें\n• **Terminal**: Direct commands execute करें\n• **Monitoring**: Real-time performance track करें";
            } else {
                return "मैं आपके APS Dream Home project को analyze कर रहा हूं। Current status: 633 tables, 95% code quality, optimized performance। क्या specifically help चाहिए?";
            }
        }

        function saveCode() {
            const code = document.getElementById('codeEditor').value;
            const fileName = document.getElementById('fileName').value || 'untitled.php';

            const formData = new FormData();
            formData.append('fileName', fileName);
            formData.append('code', code);
            formData.append('language', document.getElementById('languageMode').value);

            fetch('/senior-developer/save-code', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        addCodeOutput(`✅ File saved: ${fileName}`, 'success');
                    } else {
                        addCodeOutput(`❌ Save failed: ${data.message}`, 'error');
                    }
                })
                .catch(error => {
                    addCodeOutput(`❌ Network error: ${error.message}`, 'error');
                });
        }

        function runCode() {
            const code = document.getElementById('codeEditor').value;

            addCodeOutput('🚀 Executing code...', 'info');

            const formData = new FormData();
            formData.append('code', code);
            formData.append('language', document.getElementById('languageMode').value);

            fetch('/senior-developer/run-code', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        addCodeOutput('✅ Code executed successfully!', 'success');
                        if (data.output) {
                            addCodeOutput(data.output, 'output');
                        }
                    } else {
                        addCodeOutput(`❌ Execution failed: ${data.message}`, 'error');
                    }
                })
                .catch(error => {
                    addCodeOutput(`❌ Network error: ${error.message}`, 'error');
                });
        }

        function addCodeOutput(message, type = 'info') {
            const output = document.getElementById('codeOutput');
            const timestamp = new Date().toLocaleTimeString();

            let colorClass = 'text-gray-400';
            if (type === 'success') colorClass = 'text-green-400';
            else if (type === 'error') colorClass = 'text-red-400';
            else if (type === 'info') colorClass = 'text-blue-400';
            else if (type === 'output') colorClass = 'text-gray-300';

            const outputDiv = document.createElement('div');
            outputDiv.innerHTML = `<div class="${colorClass}">[${timestamp}] ${message}</div>`;

            output.appendChild(outputDiv);
            output.scrollTop = output.scrollHeight;
        }

        function executeTerminalCommand() {
            const input = document.getElementById('terminalInput');
            const command = input.value.trim();

            if (command === '') return;

            const terminal = document.getElementById('terminal');
            const timestamp = new Date().toLocaleTimeString();

            terminal.innerHTML += `<div class="text-blue-400">[${timestamp}] $ ${command}</div>`;
            terminal.scrollTop = terminal.scrollHeight;

            const formData = new FormData();
            formData.append('command', command);

            fetch('/test_execute.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        terminal.innerHTML += `<div class="text-green-400">[${timestamp}] $ ✓ Command executed successfully</div>`;
                        terminal.innerHTML += `<div class="text-gray-400">[${timestamp}] $ Result: ${JSON.stringify(data.result, null, 2)}</div>`;
                    } else {
                        terminal.innerHTML += `<div class="text-red-400">[${timestamp}] $ ✗ Command execution failed</div>`;
                    }
                    terminal.scrollTop = terminal.scrollHeight;
                })
                .catch(error => {
                    terminal.innerHTML += `<div class="text-red-400">[${timestamp}] $ ✗ Network error occurred</div>`;
                    terminal.scrollTop = terminal.scrollHeight;
                });

            input.value = '';
        }

        function clearTerminal() {
            const terminal = document.getElementById('terminal');
            terminal.innerHTML = `
                <div class="text-green-400">$ Terminal cleared</div>
                <div class="text-gray-400">$ Ready for new commands...</div>
            `;
        }

        function initPerformanceChart() {
            const ctx = document.getElementById('performanceChart').getContext('2d');
            performanceChart = new Chart(ctx, {
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
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.1)'
                            },
                            ticks: {
                                color: 'rgba(0, 0, 0, 0.7)'
                            }
                        },
                        x: {
                            grid: {
                                color: 'rgba(0, 0, 0, 0.1)'
                            },
                            ticks: {
                                color: 'rgba(0, 0, 0, 0.7)'
                            }
                        }
                    }
                }
            });
        }

        function showNotification(message, type) {
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 p-4 rounded-lg text-white z-50 ${
                type === 'success' ? 'bg-green-600' : 'bg-red-600'
            }`;
            notification.textContent = message;
            document.body.appendChild(notification);

            setTimeout(() => {
                notification.remove();
            }, 3000);
        }

        // API Extensions Functions
        let currentAPITemplate = '';

        function toggleAPIPanel() {
            const panel = document.getElementById('apiExtensionsPanel');
            panel.classList.toggle('hidden');
        }

        function clearCodeOutput() {
            const output = document.getElementById('codeOutput');
            output.innerHTML = '<div class="text-green-400">$ Console cleared</div><div class="text-gray-400">$ Ready for new output...</div>';
        }

        function useGitHubAPI() {
            currentAPITemplate = `<?php
                                    // GitHub API Integration
                                    $token = 'your_github_token_here';
                                    $username = 'your_username';

                                    // Get user repositories
                                    $ch = curl_init();
                                    curl_setopt($ch, CURLOPT_URL, "https://api.github.com/users/$username/repos");
                                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                                    curl_setopt($ch, CURLOPT_HTTPHEADER, [
                                        "Authorization: token $token",
                                        "User-Agent: APS-Dream-Home"
                                    ]);

                                    $response = curl_exec($ch);
                                    $repos = json_decode($response, true);

                                    foreach ($repos as $repo) {
                                        echo "Repository: " . $repo['name'] . "\\n";
                                        echo "Stars: " . $repo['stargazers_count'] . "\\n";
                                        echo "Language: " . $repo['language'] . "\\n\\n";
                                    }

                                    curl_close($ch);
                                    ?>`;
            updateAPITemplate('GitHub API - Repository listing');
        }

        function useOpenAIAPI() {
            currentAPITemplate = `<?php
                                    // OpenAI API Integration
                                    $apiKey = 'your_openai_api_key';
                                    $prompt = 'Explain the benefits of APS Dream Home platform';

                                    $data = [
                                        'model' => 'gpt-3.5-turbo',
                                        'messages' => [
                                            ['role' => 'user', 'content' => $prompt]
                                        ],
                                        'max_tokens' => 150
                                    ];

                                    $ch = curl_init();
                                    curl_setopt($ch, CURLOPT_URL, "https://api.openai.com/v1/chat/completions");
                                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                                    curl_setopt($ch, CURLOPT_POST, true);
                                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                                    curl_setopt($ch, CURLOPT_HTTPHEADER, [
                                        "Content-Type: application/json",
                                        "Authorization: Bearer $apiKey"
                                    ]);

                                    $response = curl_exec($ch);
                                    $result = json_decode($response, true);

                                    echo "AI Response: " . $result['choices'][0]['message']['content'];
                                    curl_close($ch);
                                    ?>`;
            updateAPITemplate('OpenAI API - AI Chat completion');
        }

        function useWeatherAPI() {
            currentAPITemplate = `<?php
                                    // Weather API Integration
                                    $apiKey = 'your_weather_api_key';
                                    $city = 'Mumbai';
                                    $url = "http://api.openweathermap.org/data/2.5/weather?q=$city&appid=$apiKey&units=metric";

                                    $response = file_get_contents($url);
                                    $weather = json_decode($response, true);

                                    echo "Weather in $city:\\n";
                                    echo "Temperature: " . $weather['main']['temp'] . "°C\\n";
                                    echo "Humidity: " . $weather['main']['humidity'] . "%\\n";
                                    echo "Description: " . $weather['weather'][0]['description'] . "\\n";
                                    echo "Wind Speed: " . $weather['wind']['speed'] . " m/s\\n";
                                    ?>`;
            updateAPITemplate('Weather API - Current weather data');
        }

        function useDatabaseAPI() {
            currentAPITemplate = `<?php
                                    // Database API Integration (APS Dream Home)
                                    // Simple database connection
                                    $host = 'localhost';
                                    $dbname = 'apsdreamhome';
                                    $username = 'root';
                                    $password = '';

                                    try {
                                        $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
                                        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                                        // Get leads from database
                                        $stmt = $pdo->query("SELECT id, name, email, phone, created_at FROM leads ORDER BY created_at DESC LIMIT 10");
                                        $leads = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                        echo "Recent Leads:\\n";
                                        foreach ($leads as $lead) {
                                            echo "ID: " . $lead['id'] . "\\n";
                                            echo "Name: " . $lead['name'] . "\\n";
                                            echo "Email: " . $lead['email'] . "\\n";
                                            echo "Phone: " . $lead['phone'] . "\\n";
                                            echo "Created: " . $lead['created_at'] . "\\n\\n";
                                        }

                                        // Get total count
                                        $countStmt = $pdo->query("SELECT COUNT(*) as total FROM leads");
                                        $total = $countStmt->fetch(PDO::FETCH_ASSOC);
                                        echo "Total Leads: " . $total['total'];
                                    } catch (PDOException $e) {
                                        echo "Database Error: " . $e->getMessage();
                                    }
                                    ?>`;
            updateAPITemplate('Database API - APS Dream Home leads');
        }

        function useFileSystemAPI() {
            currentAPITemplate = `<?php
                                    // File System API Integration
                                    $directory = __DIR__ . '/../../user_code/';

                                    // Create directory if not exists
                                    if (!is_dir($directory)) {
                                        mkdir($directory, 0755, true);
                                        echo "Directory created: $directory\\n";
                                    }

                                    // List files in directory
                                    $files = scandir($directory);
                                    echo "Files in directory:\\n";
                                    foreach ($files as $file) {
                                        if ($file !== '.' && $file !== '..') {
                                            $filePath = $directory . $file;
                                            $size = filesize($filePath);
                                            $modified = date('Y-m-d H:i:s', filemtime($filePath));
                                            echo "- $file (Size: $size bytes, Modified: $modified)\\n";
                                        }
                                    }

                                    // Create a new file
                                    $newFile = $directory . 'api_test_' . date('Y-m-d_H-i-s') . '.txt';
                                    file_put_contents($newFile, "Created via API at " . date('Y-m-d H:i:s'));
                                    echo "\\nNew file created: $newFile";
                                    ?>`;
            updateAPITemplate('File System API - Directory operations');
        }

        function useEmailAPI() {
            currentAPITemplate = `<?php
                                    // Email API Integration (Basic PHP mail)
                                    $to = 'recipient@example.com';
                                    $subject = 'Welcome to APS Dream Home';
                                    $message = '<h1>Welcome!</h1><p>Thank you for joining APS Dream Home platform.</p>';
                                    $headers = "From: from@apsdreamhome.com\\r\\n";
                                    $headers .= "Content-Type: text/html; charset=UTF-8\\r\\n";

                                    if (mail($to, $subject, $message, $headers)) {
                                        echo "Email sent successfully!";
                                    } else {
                                        echo "Email failed to send";
                                    }
                                    ?>`;
            updateAPITemplate('Email API - Basic PHP mail');
        }

        function usePaymentAPI() {
            currentAPITemplate = `<?php
                                    // Payment API Integration (Basic simulation)
                                    // Simulate payment processing
                                    $paymentData = [
                                        'amount' => 100.00,
                                        'currency' => 'USD',
                                        'description' => 'APS Dream Home Property Booking',
                                        'property_id' => 'PROP_001',
                                        'customer_name' => 'John Doe',
                                        'payment_id' => 'PAY_' . uniqid(),
                                        'status' => 'success'
                                    ];

                                    echo "Payment Processed:\\n";
                                    echo "Payment ID: " . $paymentData['payment_id'] . "\\n";
                                    echo "Amount: $" . $paymentData['amount'] . " " . $paymentData['currency'] . "\\n";
                                    echo "Status: " . $paymentData['status'] . "\\n";
                                    echo "Property ID: " . $paymentData['property_id'] . "\\n";
                                    echo "Customer: " . $paymentData['customer_name'];
                                    ?>`;
            updateAPITemplate('Payment API - Basic payment simulation');
        }

        function useMapsAPI() {
            currentAPITemplate = `<?php
                                    // Google Maps API Integration
                                    $apiKey = 'your_google_maps_api_key';
                                    $address = 'Mumbai, Maharashtra, India';

                                    // Geocoding API
                                    $geocodeUrl = "https://maps.googleapis.com/maps/api/geocode/json?address=" . urlencode($address) . "&key=$apiKey";
                                    $geocodeResponse = file_get_contents($geocodeUrl);
                                    $geocodeData = json_decode($geocodeResponse, true);

                                    if ($geocodeData['status'] === 'OK') {
                                        $location = $geocodeData['results'][0]['geometry']['location'];
                                        $lat = $location['lat'];
                                        $lng = $location['lng'];

                                        echo "Location: $address\\n";
                                        echo "Latitude: $lat\\n";
                                        echo "Longitude: $lng\\n";

                                        // Nearby places (restaurants)
                                        $placesUrl = "https://maps.googleapis.com/maps/api/place/nearbysearch/json?location=$lat,$lng&radius=1000&type=restaurant&key=$apiKey";
                                        $placesResponse = file_get_contents($placesUrl);
                                        $placesData = json_decode($placesResponse, true);

                                        echo "\\nNearby Restaurants:\\n";
                                        foreach ($placesData['results'] as $place) {
                                            echo "- " . $place['name'] . " (Rating: " . $place['rating'] . ")\\n";
                                        }
                                    } else {
                                        echo "Geocoding failed: " . $geocodeData['error_message'];
                                    }
                                    ?>`;
            updateAPITemplate('Maps API - Google Maps geocoding & places');
        }

        function updateAPITemplate(description) {
            const templateDiv = document.getElementById('apiCodeTemplate');
            templateDiv.innerHTML = `<div class="text-purple-600 font-bold mb-1">${description}</div><pre class="whitespace-pre-wrap text-xs">${currentAPITemplate}</pre>`;
        }

        function insertAPITemplate() {
            if (currentAPITemplate) {
                const editor = document.getElementById('codeEditor');
                editor.value = currentAPITemplate;
                showNotification('API template inserted to editor!', 'success');
            } else {
                showNotification('Please select an API extension first', 'error');
            }
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Set initial tab
            switchTab('dashboard');
        });
    </script>
</body>

</html>