<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Senior Developer AI Assistant</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
        .chat-message {
            animation: slideIn 0.3s ease-out;
        }
        @keyframes slideIn {
            from { transform: translateY(10px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        .typing-indicator {
            display: inline-flex;
            align-items: center;
        }
        .typing-indicator span {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background-color: #667eea;
            margin: 0 2px;
            animation: typing 1.4s infinite;
        }
        .typing-indicator span:nth-child(2) {
            animation-delay: 0.2s;
        }
        .typing-indicator span:nth-child(3) {
            animation-delay: 0.4s;
        }
        @keyframes typing {
            0%, 60%, 100% { transform: translateY(0); }
            30% { transform: translateY(-10px); }
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
                        Senior Developer AI Assistant
                    </h1>
                    <p class="text-purple-200 mt-2">Your Intelligent Development Partner</p>
                </div>
                <div class="text-right">
                    <div class="text-sm text-purple-200">AI Status</div>
                    <div class="text-2xl font-bold">
                        <span class="pulse-dot text-green-400">●</span> ONLINE
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container mx-auto p-6">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Chat Area -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg card-shadow h-[600px] flex flex-col">
                    <!-- Chat Header -->
                    <div class="border-b p-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="bg-blue-100 p-3 rounded-full mr-3">
                                    <i class="fas fa-code text-blue-600"></i>
                                </div>
                                <div>
                                    <h3 class="font-bold">Senior Developer</h3>
                                    <p class="text-sm text-gray-500">AI Development Assistant</p>
                                </div>
                            </div>
                            <div class="flex space-x-2">
                                <button onclick="clearChat()" class="text-gray-500 hover:text-gray-700">
                                    <i class="fas fa-trash"></i>
                                </button>
                                <button onclick="toggleVoiceChat()" class="text-gray-500 hover:text-gray-700">
                                    <i class="fas fa-microphone"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Chat Messages -->
                    <div id="chatMessages" class="flex-1 overflow-y-auto p-4 space-y-4">
                        <!-- Welcome Message -->
                        <div class="chat-message">
                            <div class="flex items-start">
                                <div class="bg-blue-100 p-2 rounded-full mr-3">
                                    <i class="fas fa-robot text-blue-600 text-sm"></i>
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
                    
                    <!-- Chat Input -->
                    <div class="border-t p-4">
                        <div class="flex space-x-2">
                            <input 
                                type="text" 
                                id="messageInput" 
                                placeholder="Ask me anything about your project..."
                                class="flex-1 p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                onkeypress="if(event.key === 'Enter') sendMessage()"
                            >
                            <button 
                                onclick="sendMessage()"
                                class="bg-blue-600 text-white p-3 rounded-lg hover:bg-blue-700 transition"
                            >
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </div>
                        
                        <!-- Quick Actions -->
                        <div class="mt-3 flex flex-wrap gap-2">
                            <button onclick="sendQuickMessage('Analyze current project status')" class="text-xs bg-gray-100 px-3 py-1 rounded-full hover:bg-gray-200">
                                📊 Analyze Project
                            </button>
                            <button onclick="sendQuickMessage('Suggest next development steps')" class="text-xs bg-gray-100 px-3 py-1 rounded-full hover:bg-gray-200">
                                🎯 Next Steps
                            </button>
                            <button onclick="sendQuickMessage('Review code quality')" class="text-xs bg-gray-100 px-3 py-1 rounded-full hover:bg-gray-200">
                                🔍 Code Review
                            </button>
                            <button onclick="sendQuickMessage('Optimize performance')" class="text-xs bg-gray-100 px-3 py-1 rounded-full hover:bg-gray-200">
                                ⚡ Optimize
                            </button>
                            <button onclick="sendQuickMessage('Plan new features')" class="text-xs bg-gray-100 px-3 py-1 rounded-full hover:bg-gray-200">
                                💡 New Features
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Side Panel -->
            <div class="space-y-6">
                <!-- Project Status -->
                <div class="bg-white rounded-lg card-shadow p-6">
                    <h3 class="font-bold mb-4 flex items-center">
                        <i class="fas fa-chart-line mr-2 text-green-500"></i>
                        Project Status
                    </h3>
                    <div id="projectStatus" class="space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Database</span>
                            <span class="text-sm font-medium text-green-600">633 tables</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Code Quality</span>
                            <span class="text-sm font-medium text-blue-600">95%</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Security</span>
                            <span class="text-sm font-medium text-purple-600">Hardened</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Performance</span>
                            <span class="text-sm font-medium text-orange-600">Optimized</span>
                        </div>
                    </div>
                </div>
                
                <!-- Current Tasks -->
                <div class="bg-white rounded-lg card-shadow p-6">
                    <h3 class="font-bold mb-4 flex items-center">
                        <i class="fas fa-tasks mr-2 text-blue-500"></i>
                        Current Tasks
                    </h3>
                    <div id="currentTasks" class="space-y-2">
                        <div class="flex items-center p-2 bg-blue-50 rounded">
                            <i class="fas fa-circle text-blue-500 text-xs mr-2"></i>
                            <span class="text-sm">Monitoring system performance</span>
                        </div>
                        <div class="flex items-center p-2 bg-green-50 rounded">
                            <i class="fas fa-check-circle text-green-500 text-xs mr-2"></i>
                            <span class="text-sm">Database optimization completed</span>
                        </div>
                        <div class="flex items-center p-2 bg-purple-50 rounded">
                            <i class="fas fa-clock text-purple-500 text-xs mr-2"></i>
                            <span class="text-sm">Security audit in progress</span>
                        </div>
                    </div>
                </div>
                
                <!-- AI Capabilities -->
                <div class="bg-white rounded-lg card-shadow p-6">
                    <h3 class="font-bold mb-4 flex items-center">
                        <i class="fas fa-brain mr-2 text-purple-500"></i>
                        AI Capabilities
                    </h3>
                    <div class="space-y-2">
                        <div class="flex items-center">
                            <i class="fas fa-check text-green-500 text-xs mr-2"></i>
                            <span class="text-sm">Code Analysis</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-check text-green-500 text-xs mr-2"></i>
                            <span class="text-sm">Problem Solving</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-check text-green-500 text-xs mr-2"></i>
                            <span class="text-sm">Architecture Planning</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-check text-green-500 text-xs mr-2"></i>
                            <span class="text-sm">Performance Optimization</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-check text-green-500 text-xs mr-2"></i>
                            <span class="text-sm">Security Analysis</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
        let chatHistory = [];
        let isTyping = false;

        function sendMessage() {
            const input = document.getElementById('messageInput');
            const message = input.value.trim();
            
            if (message === '') return;
            
            addMessage(message, 'user');
            input.value = '';
            
            // Show typing indicator
            showTypingIndicator();
            
            // Simulate AI response
            setTimeout(() => {
                hideTypingIndicator();
                generateAIResponse(message);
            }, 1000 + Math.random() * 2000);
        }

        function sendQuickMessage(message) {
            document.getElementById('messageInput').value = message;
            sendMessage();
        }

        function addMessage(message, sender) {
            const messagesContainer = document.getElementById('chatMessages');
            const messageDiv = document.createElement('div');
            messageDiv.className = 'chat-message';
            
            if (sender === 'user') {
                messageDiv.innerHTML = `
                    <div class="flex items-start justify-end">
                        <div class="bg-blue-600 text-white rounded-lg p-3 max-w-md">
                            <p>${message}</p>
                        </div>
                        <div class="bg-gray-100 p-2 rounded-full ml-3">
                            <i class="fas fa-user text-gray-600 text-sm"></i>
                        </div>
                    </div>
                `;
            } else {
                messageDiv.innerHTML = `
                    <div class="flex items-start">
                        <div class="bg-blue-100 p-2 rounded-full mr-3">
                            <i class="fas fa-robot text-blue-600 text-sm"></i>
                        </div>
                        <div class="bg-gray-100 rounded-lg p-3 max-w-md">
                            <p class="text-gray-800">${message}</p>
                        </div>
                    </div>
                `;
            }
            
            messagesContainer.appendChild(messageDiv);
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
            
            chatHistory.push({ message, sender, timestamp: new Date() });
        }

        function showTypingIndicator() {
            const messagesContainer = document.getElementById('chatMessages');
            const typingDiv = document.createElement('div');
            typingDiv.id = 'typingIndicator';
            typingDiv.className = 'chat-message';
            typingDiv.innerHTML = `
                <div class="flex items-start">
                    <div class="bg-blue-100 p-2 rounded-full mr-3">
                        <i class="fas fa-robot text-blue-600 text-sm"></i>
                    </div>
                    <div class="bg-gray-100 rounded-lg p-3">
                        <div class="typing-indicator">
                            <span></span>
                            <span></span>
                            <span></span>
                        </div>
                    </div>
                </div>
            `;
            
            messagesContainer.appendChild(typingDiv);
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
            isTyping = true;
        }

        function hideTypingIndicator() {
            const typingIndicator = document.getElementById('typingIndicator');
            if (typingIndicator) {
                typingIndicator.remove();
            }
            isTyping = false;
        }

        function generateAIResponse(userMessage) {
            const responses = {
                'analyze': `📊 **Project Analysis Complete**

Current Status:
• **Database**: 633 tables optimized, 138 leads
• **Code Quality**: 95% score, 0 syntax errors
• **Security**: Hardened with audit reports
• **Performance**: Optimized, 99.9% uptime
• **AI System**: 7 roles configured

Recommendations:
1. Implement real-time monitoring dashboard
2. Add automated testing pipeline
3. Enhance API documentation
4. Consider microservices architecture

Would you like me to elaborate on any of these areas?`,

                'suggest': `🎯 **Next Development Steps**

Based on your current project state:

**Priority 1 - High Impact:**
• Real-time notification system
• Advanced search functionality
• Mobile app development
• Payment gateway integration

**Priority 2 - Enhancement:**
• AI-powered recommendations
• Advanced analytics dashboard
• Multi-language support
• API rate limiting

**Priority 3 - Future:**
• Machine learning integration
• Blockchain features
• IoT device support
• Advanced security features

Which area would you like to focus on first?`,

                'review': `🔍 **Code Quality Review**

**Strengths:**
✅ Clean MVC architecture
✅ Proper error handling
✅ Security measures implemented
✅ Database optimization complete

**Areas for Improvement:**
⚠️ Add unit tests (coverage: 0%)
⚠️ Implement API documentation
⚠️ Add caching layer
⚠️ Consider dependency injection

**Action Items:**
1. Set up PHPUnit testing framework
2. Generate API docs with Swagger
3. Implement Redis caching
4. Refactor for better testability

Shall I help implement any of these improvements?`,

                'optimize': `⚡ **Performance Optimization Plan**

**Current Performance:**
• Response Time: 150ms
• Memory Usage: 64MB
• Database Queries: Optimized
• Cache Hit Rate: 85%

**Optimization Strategies:**
1. **Database**: Add indexes, implement query caching
2. **Application**: Implement Redis, optimize algorithms
3. **Frontend**: Lazy loading, CDN integration
4. **Server**: Load balancing, CDN setup

**Expected Results:**
• 50% faster response times
• 30% reduced memory usage
• 99.99% uptime
• Better user experience

Ready to implement these optimizations?`,

                'plan': `💡 **New Feature Planning**

**Suggested Features:**

**1. AI Chat Assistant** (Priority: High)
• Natural language processing
• Context-aware responses
• Multi-language support
• Voice interaction

**2. Real-time Collaboration** (Priority: High)
• Live editing
• Team chat
• Activity feeds
• Notification system

**3. Advanced Analytics** (Priority: Medium)
• User behavior tracking
• Performance metrics
• Business intelligence
• Custom reports

**4. Mobile Application** (Priority: Medium)
• Native iOS/Android apps
• Offline functionality
• Push notifications
• Biometric auth

Which feature interests you most? I can create a detailed implementation plan.`,

                'default': `🤖 **AI Assistant Response**

I understand you're working on: "${userMessage}"

Let me help you with this. Based on your APS Dream Home project, I can:

**Technical Assistance:**
• Code review and optimization
• Architecture planning
• Database design
• API development
• Security implementation

**Project Management:**
• Task breakdown and planning
• Technology recommendations
• Performance analysis
• Bug identification and fixes
• Feature prioritization

**Strategic Planning:**
• Roadmap development
• Resource allocation
• Risk assessment
• Quality assurance
• Deployment strategies

Could you provide more details about what specific aspect you'd like to focus on? I'm here to help you build an amazing real estate platform!`
            };

            let response = responses.default;
            
            // Check for keywords
            const lowerMessage = userMessage.toLowerCase();
            if (lowerMessage.includes('analyze') || lowerMessage.includes('status')) {
                response = responses.analyze;
            } else if (lowerMessage.includes('suggest') || lowerMessage.includes('next') || lowerMessage.includes('plan')) {
                response = responses.suggest;
            } else if (lowerMessage.includes('review') || lowerMessage.includes('code') || lowerMessage.includes('quality')) {
                response = responses.review;
            } else if (lowerMessage.includes('optimize') || lowerMessage.includes('performance') || lowerMessage.includes('speed')) {
                response = responses.optimize;
            } else if (lowerMessage.includes('feature') || lowerMessage.includes('new') || lowerMessage.includes('add')) {
                response = responses.plan;
            }

            addMessage(response, 'ai');
            
            // Update project status dynamically
            updateProjectStatus();
        }

        function updateProjectStatus() {
            // Simulate real-time status updates
            const statusElement = document.getElementById('projectStatus');
            const tasksElement = document.getElementById('currentTasks');
            
            // Add some dynamic updates
            setTimeout(() => {
                const tasks = tasksElement.innerHTML;
                if (!tasks.includes('AI chat active')) {
                    tasksElement.innerHTML += `
                        <div class="flex items-center p-2 bg-green-50 rounded">
                            <i class="fas fa-circle text-green-500 text-xs mr-2"></i>
                            <span class="text-sm">AI chat active</span>
                        </div>
                    `;
                }
            }, 2000);
        }

        function clearChat() {
            const messagesContainer = document.getElementById('chatMessages');
            messagesContainer.innerHTML = `
                <div class="chat-message">
                    <div class="flex items-start">
                        <div class="bg-blue-100 p-2 rounded-full mr-3">
                            <i class="fas fa-robot text-blue-600 text-sm"></i>
                        </div>
                        <div class="bg-gray-100 rounded-lg p-3 max-w-md">
                            <p class="text-gray-800">Chat cleared. How can I help you today?</p>
                        </div>
                    </div>
                </div>
            `;
            chatHistory = [];
        }

        function toggleVoiceChat() {
            alert('Voice chat feature coming soon! 🎤');
        }

        // Auto-update project status every 30 seconds
        setInterval(() => {
            fetch('/senior-developer/status')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        console.log('Status updated:', data.status);
                    }
                })
                .catch(error => console.log('Status update failed:', error));
        }, 30000);
    </script>
</body>
</html>
