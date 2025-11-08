<?php
/**
 * APS Dream Home - AI Agent Dashboard
 * Comprehensive interface for AI agent interaction, training, and monitoring
 */

require_once 'includes/config.php';

// Check if user is authenticated (implement proper auth check)
$user_authenticated = true; // For demo purposes
$user_name = 'Admin User';
$user_role = 'admin';

if (!$user_authenticated) {
    header('Location: login.php');
    exit;
}

$page_title = 'AI Agent Dashboard - APS Dream Home';
include 'includes/enhanced_universal_template.php';
?>

<div class="container-fluid mt-4">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-header bg-gradient-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-robot me-2"></i>
                        AI Agent Control Panel
                    </h5>
                </div>
                <div class="card-body">
                    <div class="ai-agent-status mb-4">
                        <div class="d-flex align-items-center mb-2">
                            <div class="ai-avatar me-3">
                                <i class="fas fa-robot fa-2x text-primary"></i>
                            </div>
                            <div>
                                <h6 class="mb-0">APS Assistant</h6>
                                <small class="text-muted">AI Agent v2.1</small>
                            </div>
                        </div>
                        <div class="mood-indicator">
                            <span class="badge bg-success">🧠 Learning Active</span>
                            <span class="badge bg-info ms-1">💬 Interactive</span>
                        </div>
                    </div>

                    <nav class="nav nav-pills flex-column">
                        <a class="nav-link active" href="#chat" data-bs-toggle="tab">
                            <i class="fas fa-comments me-2"></i>Chat Interface
                        </a>
                        <a class="nav-link" href="#training" data-bs-toggle="tab">
                            <i class="fas fa-graduation-cap me-2"></i>Training Mode
                        </a>
                        <a class="nav-link" href="#analytics" data-bs-toggle="tab">
                            <i class="fas fa-chart-bar me-2"></i>Learning Analytics
                        </a>
                        <a class="nav-link" href="#personality" data-bs-toggle="tab">
                            <i class="fas fa-user-cog me-2"></i>Personality Settings
                        </a>
                        <a class="nav-link" href="#knowledge" data-bs-toggle="tab">
                            <i class="fas fa-database me-2"></i>Knowledge Base
                        </a>
                        <a class="nav-link" href="#workflows" data-bs-toggle="tab">
                            <i class="fas fa-cogs me-2"></i>Workflow Patterns
                        </a>
                    </nav>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card mt-3 shadow-sm">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="fas fa-bolt me-2"></i>Quick Actions
                    </h6>
                </div>
                <div class="card-body">
                    <button class="btn btn-outline-primary btn-sm w-100 mb-2" onclick="generateReport()">
                        <i class="fas fa-file-alt me-2"></i>Generate AI Report
                    </button>
                    <button class="btn btn-outline-success btn-sm w-100 mb-2" onclick="runDiagnostics()">
                        <i class="fas fa-stethoscope me-2"></i>Run Diagnostics
                    </button>
                    <button class="btn btn-outline-warning btn-sm w-100 mb-2" onclick="backupKnowledge()">
                        <i class="fas fa-save me-2"></i>Backup Knowledge
                    </button>
                    <button class="btn btn-outline-info btn-sm w-100" onclick="showRecommendations()">
                        <i class="fas fa-lightbulb me-2"></i>View Recommendations
                    </button>
                </div>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="col-md-9">
            <div class="tab-content">
                <!-- Chat Interface Tab -->
                <div class="tab-pane fade show active" id="chat">
                    <div class="card shadow-sm">
                        <div class="card-header bg-gradient-primary text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-comments me-2"></i>
                                Interactive AI Chat
                            </h5>
                        </div>
                        <div class="card-body">
                            <!-- Chat Messages Area -->
                            <div id="chatMessages" class="chat-messages mb-3" style="height: 400px; overflow-y: auto; border: 1px solid #dee2e6; border-radius: 5px; padding: 15px;">
                                <div class="message ai-message">
                                    <div class="message-avatar">
                                        <i class="fas fa-robot text-primary"></i>
                                    </div>
                                    <div class="message-content">
                                        <div class="message-bubble">
                                            <strong>APS Assistant:</strong> Hello! I'm your AI assistant for APS Dream Home. I'm here to help you with development, deployment, analysis, and any questions you might have. I've been learning about your workflows and I'm excited to assist you! 🚀
                                        </div>
                                        <div class="message-time">Just now</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Chat Input Area -->
                            <div class="chat-input-area">
                                <div class="input-group">
                                    <input type="text" id="userMessage" class="form-control" placeholder="Ask me anything about your project, development, or APS Dream Home...">
                                    <button class="btn btn-primary" onclick="sendMessage()">
                                        <i class="fas fa-paper-plane me-1"></i>Send
                                    </button>
                                </div>
                                <div class="mt-2">
                                    <small class="text-muted">
                                        💡 Tip: I learn from every interaction! The more you chat with me, the better I understand your needs.
                                    </small>
                                </div>
                            </div>

                            <!-- Quick Question Buttons -->
                            <div class="quick-questions mt-3">
                                <h6>Quick Questions:</h6>
                                <div class="btn-group flex-wrap" role="group">
                                    <button class="btn btn-outline-secondary btn-sm me-1 mb-1" onclick="askQuickQuestion('How do I deploy this system?')">
                                        🚀 Deployment
                                    </button>
                                    <button class="btn btn-outline-secondary btn-sm me-1 mb-1" onclick="askQuickQuestion('Show me recent issues')">
                                        🐛 Issues
                                    </button>
                                    <button class="btn btn-outline-secondary btn-sm me-1 mb-1" onclick="askQuickQuestion('What are my next priorities?')">
                                        📋 Priorities
                                    </button>
                                    <button class="btn btn-outline-secondary btn-sm me-1 mb-1" onclick="askQuickQuestion('Analyze my code quality')">
                                        🔍 Code Analysis
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Training Mode Tab -->
                <div class="tab-pane fade" id="training">
                    <div class="card shadow-sm">
                        <div class="card-header bg-gradient-success text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-graduation-cap me-2"></i>
                                AI Training Mode
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <h6>Training Interface</h6>
                                    <p>Help me learn by providing feedback and corrections to my responses.</p>

                                    <div class="training-session mb-4">
                                        <div class="alert alert-info">
                                            <strong>Current Training Session:</strong> PHP Development Best Practices
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">What would you like me to learn?</label>
                                            <textarea id="trainingInput" class="form-control" rows="3" placeholder="Explain a concept, provide code examples, or describe a workflow..."></textarea>
                                        </div>

                                        <button class="btn btn-success" onclick="startTraining()">
                                            <i class="fas fa-play me-2"></i>Start Training Session
                                        </button>
                                    </div>

                                    <div class="feedback-section">
                                        <h6>Response Feedback</h6>
                                        <div class="mb-3">
                                            <label class="form-label">Rate my last response:</label>
                                            <div class="rating-stars">
                                                <div class="star-rating">
                                                    <input type="radio" id="5-stars" name="rating" value="5">
                                                    <label for="5-stars">⭐</label>
                                                    <input type="radio" id="4-stars" name="rating" value="4">
                                                    <label for="4-stars">⭐</label>
                                                    <input type="radio" id="3-stars" name="rating" value="3">
                                                    <label for="3-stars">⭐</label>
                                                    <input type="radio" id="2-stars" name="rating" value="2">
                                                    <label for="2-stars">⭐</label>
                                                    <input type="radio" id="1-stars" name="rating" value="1">
                                                    <label for="1-stars">⭐</label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Feedback (optional):</label>
                                            <textarea id="feedbackText" class="form-control" rows="2" placeholder="What did I do well? What should I improve?"></textarea>
                                        </div>

                                        <button class="btn btn-primary" onclick="submitFeedback()">
                                            <i class="fas fa-send me-2"></i>Submit Feedback
                                        </button>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <h6>Learning Progress</h6>
                                            <div class="progress mb-3">
                                                <div class="progress-bar bg-success" role="progressbar" style="width: 75%" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                            <p class="mb-2"><strong>75%</strong> Complete</p>

                                            <h6>Recent Achievements</h6>
                                            <ul class="list-unstyled">
                                                <li><i class="fas fa-check text-success me-2"></i>Mastered PHP Development</li>
                                                <li><i class="fas fa-check text-success me-2"></i>Learned Deployment Strategies</li>
                                                <li><i class="fas fa-clock text-warning me-2"></i>Learning Security Practices</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Learning Analytics Tab -->
                <div class="tab-pane fade" id="analytics">
                    <div class="card shadow-sm">
                        <div class="card-header bg-gradient-info text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-chart-bar me-2"></i>
                                Learning Analytics
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card bg-light">
                                        <div class="card-body text-center">
                                            <h3 class="text-primary" id="totalInteractions">247</h3>
                                            <p>Total Interactions</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card bg-light">
                                        <div class="card-body text-center">
                                            <h3 class="text-success" id="knowledgeEntries">89</h3>
                                            <p>Knowledge Entries</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-4">
                                <div class="col-md-12">
                                    <h6>Interaction Trends (Last 30 Days)</h6>
                                    <canvas id="interactionChart" width="400" height="200"></canvas>
                                </div>
                            </div>

                            <div class="row mt-4">
                                <div class="col-md-6">
                                    <h6>Top Topics</h6>
                                    <ul class="list-group" id="topTopics">
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            PHP Development
                                            <span class="badge bg-primary rounded-pill">42</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            Database Management
                                            <span class="badge bg-primary rounded-pill">28</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            Deployment
                                            <span class="badge bg-primary rounded-pill">23</span>
                                        </li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <h6>Performance Metrics</h6>
                                    <div class="performance-metrics">
                                        <div class="metric-item">
                                            <span class="metric-label">Response Accuracy</span>
                                            <div class="progress">
                                                <div class="progress-bar bg-success" style="width: 94%"></div>
                                            </div>
                                            <span class="metric-value">94%</span>
                                        </div>
                                        <div class="metric-item">
                                            <span class="metric-label">User Satisfaction</span>
                                            <div class="progress">
                                                <div class="progress-bar bg-info" style="width: 87%"></div>
                                            </div>
                                            <span class="metric-value">87%</span>
                                        </div>
                                        <div class="metric-item">
                                            <span class="metric-label">Learning Rate</span>
                                            <div class="progress">
                                                <div class="progress-bar bg-warning" style="width: 76%"></div>
                                            </div>
                                            <span class="metric-value">76%</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Personality Settings Tab -->
                <div class="tab-pane fade" id="personality">
                    <div class="card shadow-sm">
                        <div class="card-header bg-gradient-warning text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-user-cog me-2"></i>
                                AI Personality Settings
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-warning">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Note:</strong> Personality changes affect how I communicate and behave. Changes are applied gradually based on your feedback.
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <h6>Communication Style</h6>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="communication_style" id="formal" checked>
                                        <label class="form-check-label" for="formal">
                                            Formal & Professional
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="communication_style" id="friendly">
                                        <label class="form-check-label" for="friendly">
                                            Friendly & Approachable
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="communication_style" id="technical">
                                        <label class="form-check-label" for="technical">
                                            Technical & Detailed
                                        </label>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <h6>Response Characteristics</h6>
                                    <div class="mb-3">
                                        <label class="form-label">Response Length</label>
                                        <select class="form-select" id="responseLength">
                                            <option value="concise">Concise</option>
                                            <option value="balanced" selected>Balanced</option>
                                            <option value="comprehensive">Comprehensive</option>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Technical Depth</label>
                                        <select class="form-select" id="technicalDepth">
                                            <option value="simple">Simple</option>
                                            <option value="moderate" selected>Moderate</option>
                                            <option value="advanced">Advanced</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4">
                                <h6>Current Personality Profile</h6>
                                <div class="personality-traits">
                                    <div class="trait-item">
                                        <span class="trait-label">Helpfulness</span>
                                        <div class="progress">
                                            <div class="progress-bar bg-success" style="width: 95%"></div>
                                        </div>
                                        <span class="trait-value">95%</span>
                                    </div>
                                    <div class="trait-item">
                                        <span class="trait-label">Accuracy</span>
                                        <div class="progress">
                                            <div class="progress-bar bg-info" style="width: 98%"></div>
                                        </div>
                                        <span class="trait-value">98%</span>
                                    </div>
                                    <div class="trait-item">
                                        <span class="trait-label">Creativity</span>
                                        <div class="progress">
                                            <div class="progress-bar bg-warning" style="width: 85%"></div>
                                        </div>
                                        <span class="trait-value">85%</span>
                                    </div>
                                    <div class="trait-item">
                                        <span class="trait-label">Empathy</span>
                                        <div class="progress">
                                            <div class="progress-bar bg-danger" style="width: 88%"></div>
                                        </div>
                                        <span class="trait-value">88%</span>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4 text-center">
                                <button class="btn btn-warning" onclick="updatePersonality()">
                                    <i class="fas fa-sync-alt me-2"></i>Update Personality Settings
                                </button>
                                <button class="btn btn-outline-secondary ms-2" onclick="resetPersonality()">
                                    <i class="fas fa-undo me-2"></i>Reset to Default
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Knowledge Base Tab -->
                <div class="tab-pane fade" id="knowledge">
                    <div class="card shadow-sm">
                        <div class="card-header bg-gradient-dark text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-database me-2"></i>
                                AI Knowledge Base
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row mb-4">
                                <div class="col-md-8">
                                    <div class="input-group">
                                        <input type="text" id="knowledgeSearch" class="form-control" placeholder="Search knowledge base...">
                                        <button class="btn btn-outline-secondary" onclick="searchKnowledge()">
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-4 text-end">
                                    <button class="btn btn-success" onclick="addKnowledge()">
                                        <i class="fas fa-plus me-2"></i>Add Knowledge
                                    </button>
                                </div>
                            </div>

                            <div class="knowledge-entries" id="knowledgeEntries">
                                <!-- Knowledge entries will be loaded here -->
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Workflow Patterns Tab -->
                <div class="tab-pane fade" id="workflows">
                    <div class="card shadow-sm">
                        <div class="card-header bg-gradient-secondary text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-cogs me-2"></i>
                                Workflow Patterns
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <h6>Detected Patterns</h6>
                                    <div class="workflow-patterns" id="workflowPatterns">
                                        <div class="pattern-item">
                                            <div class="pattern-header">
                                                <strong>Project Deep Scan</strong>
                                                <span class="badge bg-success">High Automation</span>
                                            </div>
                                            <div class="pattern-description">
                                                Automated project analysis and reporting workflow
                                            </div>
                                            <div class="pattern-stats">
                                                Used 15 times • Last used: 2 hours ago
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <h6>Automation Suggestions</h6>
                                    <div class="automation-suggestions">
                                        <div class="suggestion-item">
                                            <div class="suggestion-title">Database Setup Automation</div>
                                            <div class="suggestion-description">
                                                Can be fully automated based on patterns detected
                                            </div>
                                            <button class="btn btn-sm btn-primary">Implement</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- AI Agent Modals and Scripts -->
<div class="modal fade" id="aiReflectionModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-brain me-2"></i>
                    AI Self-Reflection
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="reflectionContent">
                <!-- Reflection content will be loaded here -->
            </div>
        </div>
    </div>
</div>

<script>
let chatHistory = [];

function sendMessage() {
    const userMessage = document.getElementById('userMessage').value.trim();
    if (!userMessage) return;

    // Add user message to chat
    addMessageToChat('user', userMessage);

    // Clear input
    document.getElementById('userMessage').value = '';

    // Show typing indicator
    showTypingIndicator();

    // Send to AI and get response
    fetch('api/ai_agent_chat.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'message=' + encodeURIComponent(userMessage) + '&context=' + encodeURIComponent(JSON.stringify(chatHistory.slice(-5)))
    })
    .then(response => response.json())
    .then(data => {
        hideTypingIndicator();

        if (data.response) {
            addMessageToChat('ai', data.response);
            chatHistory.push({
                user: userMessage,
                ai: data.response,
                timestamp: new Date().toISOString()
            });

            // Learn from this interaction
            learnFromInteraction(userMessage, data.response);
        } else {
            addMessageToChat('ai', 'I apologize, but I\'m having trouble responding right now. Please try again.');
        }
    })
    .catch(error => {
        hideTypingIndicator();
        addMessageToChat('ai', 'Sorry, I encountered an error. Please try again.');
        console.error('Chat error:', error);
    });
}

function addMessageToChat(sender, message) {
    const chatMessages = document.getElementById('chatMessages');
    const messageDiv = document.createElement('div');
    messageDiv.className = `message ${sender}-message`;

    const avatar = sender === 'ai' ?
        '<i class="fas fa-robot text-primary"></i>' :
        '<i class="fas fa-user text-secondary"></i>';

    messageDiv.innerHTML = `
        <div class="message-avatar">
            ${avatar}
        </div>
        <div class="message-content">
            <div class="message-bubble">
                <strong>${sender === 'ai' ? 'APS Assistant:' : 'You:'}</strong> ${message}
            </div>
            <div class="message-time">${new Date().toLocaleTimeString()}</div>
        </div>
    `;

    chatMessages.appendChild(messageDiv);
    chatMessages.scrollTop = chatMessages.scrollHeight;
}

function showTypingIndicator() {
    const chatMessages = document.getElementById('chatMessages');
    const typingDiv = document.createElement('div');
    typingDiv.className = 'message ai-message typing-indicator';
    typingDiv.id = 'typingIndicator';
    typingDiv.innerHTML = `
        <div class="message-avatar">
            <i class="fas fa-robot text-primary"></i>
        </div>
        <div class="message-content">
            <div class="message-bubble">
                <strong>APS Assistant:</strong> <i class="fas fa-circle-notch fa-spin"></i> Thinking...
            </div>
        </div>
    `;
    chatMessages.appendChild(typingDiv);
    chatMessages.scrollTop = chatMessages.scrollHeight;
}

function hideTypingIndicator() {
    const typingIndicator = document.getElementById('typingIndicator');
    if (typingIndicator) {
        typingIndicator.remove();
    }
}

function askQuickQuestion(question) {
    document.getElementById('userMessage').value = question;
    sendMessage();
}

// Enter key to send message
document.getElementById('userMessage').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        sendMessage();
    }
});

function learnFromInteraction(userInput, aiResponse) {
    // Send learning data to server
    fetch('api/ai_learn_interaction.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            user_input: userInput,
            ai_response: aiResponse,
            context: chatHistory.slice(-3)
        })
    })
    .then(response => response.json())
    .then(data => {
        console.log('Learning recorded:', data);
    })
    .catch(error => {
        console.error('Learning error:', error);
    });
}

function generateReport() {
    alert('Generating comprehensive AI learning report...');
    // Implement report generation
}

function runDiagnostics() {
    alert('Running AI system diagnostics...');
    // Implement diagnostics
}

function backupKnowledge() {
    alert('Backing up AI knowledge base...');
    // Implement backup
}

function showRecommendations() {
    fetch('api/ai_recommendations.php')
    .then(response => response.json())
    .then(data => {
        alert('Personalized recommendations: ' + JSON.stringify(data, null, 2));
    })
    .catch(error => {
        console.error('Recommendations error:', error);
    });
}

function startTraining() {
    const trainingInput = document.getElementById('trainingInput').value;
    if (!trainingInput) {
        alert('Please enter training content');
        return;
    }

    alert('Starting training session with: ' + trainingInput.substring(0, 50) + '...');
    // Implement training logic
}

function submitFeedback() {
    const rating = document.querySelector('input[name="rating"]:checked');
    const feedback = document.getElementById('feedbackText').value;

    if (!rating) {
        alert('Please select a rating');
        return;
    }

    // Send feedback to AI learning system
    fetch('api/ai_feedback.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            rating: rating.value,
            feedback: feedback
        })
    })
    .then(response => response.json())
    .then(data => {
        alert('Thank you for your feedback! I\'m learning from this.');
        document.querySelector('input[name="rating"]:checked').checked = false;
        document.getElementById('feedbackText').value = '';
    })
    .catch(error => {
        console.error('Feedback error:', error);
    });
}

function updatePersonality() {
    alert('Updating AI personality settings...');
    // Implement personality update
}

function resetPersonality() {
    if (confirm('Are you sure you want to reset AI personality to default settings?')) {
        alert('Resetting AI personality...');
        // Implement personality reset
    }
}

// Load initial data
document.addEventListener('DOMContentLoaded', function() {
    loadAnalyticsData();
    loadKnowledgeBase();
    loadWorkflowPatterns();
});

function loadAnalyticsData() {
    // Load real analytics data from server
    fetch('api/ai_analytics.php')
    .then(response => response.json())
    .then(data => {
        document.getElementById('totalInteractions').textContent = data.total_interactions || '0';
        document.getElementById('knowledgeEntries').textContent = data.knowledge_entries || '0';
    })
    .catch(error => {
        console.error('Analytics error:', error);
    });
}

function loadKnowledgeBase() {
    fetch('api/ai_knowledge_base.php')
    .then(response => response.json())
    .then(data => {
        const container = document.getElementById('knowledgeEntries');
        container.innerHTML = data.entries.map(entry =>
            `<div class="knowledge-item">
                <h6>${entry.title}</h6>
                <p>${entry.content.substring(0, 100)}...</p>
                <small class="text-muted">${entry.category} • ${entry.difficulty}</small>
            </div>`
        ).join('');
    })
    .catch(error => {
        console.error('Knowledge base error:', error);
    });
}

function loadWorkflowPatterns() {
    fetch('api/ai_workflow_patterns.php')
    .then(response => response.json())
    .then(data => {
        const container = document.getElementById('workflowPatterns');
        container.innerHTML = data.patterns.map(pattern =>
            `<div class="pattern-item">
                <div class="pattern-header">
                    <strong>${pattern.name}</strong>
                    <span class="badge bg-${pattern.automation === 'high' ? 'success' : 'warning'}">${pattern.automation} Automation</span>
                </div>
                <div class="pattern-description">${pattern.description}</div>
                <div class="pattern-stats">Used ${pattern.usage_count} times</div>
            </div>`
        ).join('');
    })
    .catch(error => {
        console.error('Workflow patterns error:', error);
    });
}

// Add some CSS for the chat interface
const style = document.createElement('style');
style.textContent = `
    .chat-messages {
        background: #f8f9fa;
    }
    .message {
        display: flex;
        margin-bottom: 15px;
        padding: 10px;
    }
    .message-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #e9ecef;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 10px;
        flex-shrink: 0;
    }
    .message-content {
        flex: 1;
    }
    .message-bubble {
        background: white;
        padding: 10px 15px;
        border-radius: 15px;
        margin-bottom: 5px;
        box-shadow: 0 1px 2px rgba(0,0,0,0.1);
    }
    .user-message .message-bubble {
        background: #007bff;
        color: white;
        margin-left: auto;
        margin-right: 0;
    }
    .message-time {
        font-size: 0.75rem;
        color: #6c757d;
    }
    .typing-indicator {
        opacity: 0.7;
    }
    .quick-questions {
        border-top: 1px solid #dee2e6;
        padding-top: 15px;
    }
    .personality-traits .trait-item {
        margin-bottom: 15px;
    }
    .trait-label {
        display: block;
        margin-bottom: 5px;
        font-weight: 500;
    }
    .trait-value {
        float: right;
        font-weight: bold;
    }
    .performance-metrics .metric-item {
        margin-bottom: 15px;
    }
    .metric-label {
        display: block;
        margin-bottom: 5px;
    }
    .metric-value {
        float: right;
        font-weight: bold;
    }
    .star-rating {
        display: flex;
        flex-direction: row-reverse;
        justify-content: center;
    }
    .star-rating input {
        display: none;
    }
    .star-rating label {
        font-size: 1.5rem;
        color: #ddd;
        cursor: pointer;
    }
    .star-rating input:checked ~ label {
        color: #ffd700;
    }
`;
document.head.appendChild(style);
</script>

<?php include 'includes/enhanced_universal_template.php'; ?>
