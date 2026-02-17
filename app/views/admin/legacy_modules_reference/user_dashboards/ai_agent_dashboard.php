<?php
/**
 * APS Dream Home - AI Agent Control Panel
 * Advanced AI agent management, training, and monitoring interface
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/');
}

if (!defined('BASE_URL')) {
    define('BASE_URL', 'https://apsdreamhomes.com');
}

require_once __DIR__ . '/../core/init.php';

// Authentication and role check
if (!isAuthenticated()) {
    header('Location: login.php');
    exit();
}

$user_name = getAuthFullName() ?? 'Guest';
$user_role = getAuthRole() ?? 'guest';

// Initialize AI system
$ai_enabled = $config['ai']['enabled'] ?? false;
$ai_model = $config['ai']['model'] ?? 'qwen/qwen3-coder:free';
$ai_provider = $config['ai']['provider'] ?? 'openrouter';

$whatsapp_enabled = $config['whatsapp']['enabled'] ?? false;
$whatsapp_phone = $config['whatsapp']['phone_number'] ?? '9277121112';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Agent Control Panel - APS Dream Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js" rel="stylesheet">

    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --success-gradient: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            --warning-gradient: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);
            --danger-gradient: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
        }

        body {
            background: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .ai-header {
            background: var(--primary-gradient);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
        }

        .ai-status-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            border: none;
            margin-bottom: 1.5rem;
            overflow: hidden;
        }

        .ai-status-header {
            background: var(--primary-gradient);
            color: white;
            padding: 1rem 1.5rem;
            border-bottom: none;
        }

        .ai-avatar {
            width: 60px;
            height: 60px;
            background: var(--primary-gradient);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
        }

        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            margin: 0.25rem;
        }

        .learning-progress {
            background: #e9ecef;
            border-radius: 10px;
            height: 8px;
            overflow: hidden;
        }

        .learning-bar {
            background: var(--primary-gradient);
            height: 100%;
            border-radius: 10px;
            transition: width 0.5s ease;
        }

        .chat-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            height: 500px;
            display: flex;
            flex-direction: column;
        }

        .chat-messages {
            flex: 1;
            padding: 1rem;
            overflow-y: auto;
            background: #f8f9fa;
        }

        .chat-input {
            padding: 1rem;
            background: white;
            border-top: 1px solid #e9ecef;
        }

        .message {
            margin-bottom: 1rem;
            padding: 0.75rem 1rem;
            border-radius: 10px;
            max-width: 80%;
        }

        .message.user {
            background: var(--primary-gradient);
            color: white;
            margin-left: auto;
        }

        .message.ai {
            background: white;
            border: 1px solid #e9ecef;
        }

        .personality-slider {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin: 1rem 0;
        }

        .slider-container {
            flex: 1;
            position: relative;
        }

        .slider-track {
            width: 100%;
            height: 6px;
            background: #e9ecef;
            border-radius: 3px;
            position: relative;
        }

        .slider-fill {
            height: 100%;
            background: var(--primary-gradient);
            border-radius: 3px;
            position: absolute;
            left: 0;
            top: 0;
        }

        .slider-thumb {
            width: 20px;
            height: 20px;
            background: var(--primary-gradient);
            border-radius: 50%;
            position: absolute;
            top: 50%;
            transform: translate(-50%, -50%);
            cursor: pointer;
        }

        .quick-action-btn {
            background: white;
            border: 2px solid #e9ecef;
            border-radius: 15px;
            padding: 1rem;
            text-align: center;
            transition: all 0.3s ease;
            text-decoration: none;
            color: #333;
            display: block;
            margin-bottom: 1rem;
        }

        .quick-action-btn:hover {
            border-color: #667eea;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.2);
        }

        .quick-action-btn i {
            font-size: 1.5rem;
            color: #667eea;
            margin-bottom: 0.5rem;
        }

        .metric-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border: 1px solid #e9ecef;
        }

        .metric-value {
            font-size: 2rem;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 0.5rem;
        }

        .metric-label {
            color: #666;
            font-size: 0.9rem;
        }

        .nav-pills .nav-link {
            border-radius: 25px;
            margin: 0 0.25rem;
            padding: 0.5rem 1rem;
        }

        .nav-pills .nav-link.active {
            background: var(--primary-gradient);
        }
    </style>
</head>
<body>
    <!-- AI Header -->
    <div class="ai-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="mb-2">
                        <i class="fas fa-robot me-3"></i>
                        AI Agent Control Panel
                    </h1>
                    <p class="mb-0">Advanced AI management, training, and monitoring system</p>
                </div>
                <div class="col-md-4 text-end">
                    <div class="ai-status-badges">
                        <span class="badge bg-light text-dark me-2">
                            <i class="fas fa-user me-1"></i><?php echo h($user_name); ?>
                        </span>
                        <span class="badge bg-light text-dark">
                            <i class="fas fa-shield-alt me-1"></i><?php echo h($user_role); ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3">
                <!-- AI Status Card -->
                <div class="card ai-status-card">
                    <div class="card-header ai-status-header">
                        <h5 class="mb-0">
                            <i class="fas fa-brain me-2"></i>
                            APS Assistant
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="ai-avatar me-3">
                                <i class="fas fa-robot"></i>
                            </div>
                            <div>
                                <h6 class="mb-1">AI Agent v2.1</h6>
                                <div class="ai-version">
                                    <span class="status-badge bg-success">🧠 Learning Active</span>
                                    <span class="status-badge bg-info">💬 Interactive</span>
                                </div>
                            </div>
                        </div>

                        <!-- Learning Progress -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <small class="text-muted">Learning Progress</small>
                                <small class="text-muted">75%</small>
                            </div>
                            <div class="learning-progress">
                                <div class="learning-bar" style="width: 75%"></div>
                            </div>
                        </div>

                        <!-- Quick Actions -->
                        <div class="quick-actions">
                            <a href="ai_demo.php" class="quick-action-btn">
                                <i class="fas fa-play-circle"></i>
                                <div>AI Demo</div>
                            </a>
                            <a href="test_whatsapp_integration.php" class="quick-action-btn">
                                <i class="fab fa-whatsapp"></i>
                                <div>WhatsApp Test</div>
                            </a>
                            <a href="comprehensive_system_test.php" class="quick-action-btn">
                                <i class="fas fa-cog"></i>
                                <div>System Test</div>
                            </a>
                            <a href="management_dashboard.php" class="quick-action-btn">
                                <i class="fas fa-tachometer-alt"></i>
                                <div>Management</div>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- System Status -->
                <div class="card ai-status-card">
                    <div class="card-header ai-status-header">
                        <h6 class="mb-0">
                            <i class="fas fa-server me-2"></i>
                            System Status
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="status-item d-flex justify-content-between align-items-center mb-2">
                            <span><i class="fas fa-robot text-primary me-2"></i>AI System</span>
                            <span class="badge <?php echo $ai_enabled ? 'bg-success' : 'bg-danger'; ?>">
                                <?php echo $ai_enabled ? 'Active' : 'Disabled'; ?>
                            </span>
                        </div>
                        <div class="status-item d-flex justify-content-between align-items-center mb-2">
                            <span><i class="fab fa-whatsapp text-success me-2"></i>WhatsApp</span>
                            <span class="badge <?php echo $whatsapp_enabled ? 'bg-success' : 'bg-warning'; ?>">
                                <?php echo $whatsapp_enabled ? 'Active' : 'Ready'; ?>
                            </span>
                        </div>
                        <div class="status-item d-flex justify-content-between align-items-center mb-2">
                            <span><i class="fas fa-envelope text-info me-2"></i>Email</span>
                            <span class="badge bg-success">Active</span>
                        </div>
                        <div class="status-item d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-database text-secondary me-2"></i>Database</span>
                            <span class="badge bg-success">Connected</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9">
                <!-- Navigation Tabs -->
                <div class="card ai-status-card">
                    <div class="card-body">
                        <ul class="nav nav-pills mb-4" id="aiTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="chat-tab" data-bs-toggle="pill" data-bs-target="#chat" type="button">
                                    <i class="fas fa-comments me-1"></i>Interactive Chat
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="training-tab" data-bs-toggle="pill" data-bs-target="#training" type="button">
                                    <i class="fas fa-graduation-cap me-1"></i>Training Mode
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="analytics-tab" data-bs-toggle="pill" data-bs-target="#analytics" type="button">
                                    <i class="fas fa-chart-bar me-1"></i>Analytics
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="personality-tab" data-bs-toggle="pill" data-bs-target="#personality" type="button">
                                    <i class="fas fa-user-cog me-1"></i>Personality
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="knowledge-tab" data-bs-toggle="pill" data-bs-target="#knowledge" type="button">
                                    <i class="fas fa-database me-1"></i>Knowledge Base
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="workflows-tab" data-bs-toggle="pill" data-bs-target="#workflows" type="button">
                                    <i class="fas fa-cogs me-1"></i>Workflows
                                </button>
                            </li>
                        </ul>

                        <!-- Tab Content -->
                        <div class="tab-content" id="aiTabsContent">
                            <!-- Interactive Chat Tab -->
                            <div class="tab-pane fade show active" id="chat" role="tabpanel">
                                <div class="chat-container">
                                    <div class="chat-messages" id="chatMessages">
                                        <div class="message ai">
                                            <strong>APS Assistant:</strong> Hello! I'm your AI assistant for APS Dream Home. I'm here to help you with development, deployment, analysis, and any questions you might have. I've been learning about your workflows and I'm excited to assist you! 🚀
                                        </div>
                                        <div class="message user">
                                            <strong>You:</strong> hi
                                        </div>
                                        <div class="message ai">
                                            <strong>APS Assistant:</strong> I apologize, but I'm having trouble responding right now. Please try again.
                                        </div>
                                        <div class="message user">
                                            <strong>You:</strong> kya karu
                                        </div>
                                        <div class="message ai">
                                            <strong>APS Assistant:</strong> I apologize, but I'm having trouble responding right now. Please try again.
                                        </div>
                                    </div>
                                    <div class="chat-input">
                                        <div class="input-group">
                                            <input type="text" class="form-control" id="chatInput" placeholder="Type your message here...">
                                            <button class="btn btn-primary" id="sendMessage">
                                                <i class="fas fa-paper-plane"></i> Send
                                            </button>
                                        </div>
                                        <div class="mt-2">
                                            <small class="text-muted">
                                                💡 Tip: I learn from every interaction! The more you chat with me, the better I understand your needs.
                                            </small>
                                        </div>
                                    </div>
                                </div>

                                <!-- Quick Questions -->
                                <div class="mt-3">
                                    <h6>Quick Questions:</h6>
                                    <div class="d-flex flex-wrap gap-2">
                                        <button class="btn btn-outline-primary btn-sm quick-question" data-question="🚀 How do I deploy this system?">
                                            🚀 Deployment
                                        </button>
                                        <button class="btn btn-outline-warning btn-sm quick-question" data-question="🐛 I'm having issues with the system">
                                            🐛 Issues
                                        </button>
                                        <button class="btn btn-outline-info btn-sm quick-question" data-question="📋 What are the current priorities?">
                                            📋 Priorities
                                        </button>
                                        <button class="btn btn-outline-success btn-sm quick-question" data-question="🔍 Can you analyze the code?">
                                            🔍 Code Analysis
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Training Mode Tab -->
                            <div class="tab-pane fade" id="training" role="tabpanel">
                                <div class="row">
                                    <div class="col-md-8">
                                        <h5>AI Training Interface</h5>
                                        <p>Help me learn by providing feedback and corrections to my responses.</p>

                                        <div class="training-session mb-4">
                                            <div class="d-flex justify-content-between mb-2">
                                                <strong>Current Training Session:</strong>
                                                <span class="badge bg-primary">PHP Development Best Practices</span>
                                            </div>
                                            <p>What would you like me to learn?</p>
                                            <div class="input-group">
                                                <input type="text" class="form-control" id="trainingTopic" placeholder="Enter a topic or skill to learn...">
                                                <button class="btn btn-primary" id="startTraining">
                                                    Start Training Session
                                                </button>
                                            </div>
                                        </div>

                                        <div class="response-feedback">
                                            <h6>Response Feedback</h6>
                                            <p>Rate my last response:</p>
                                            <div class="rating mb-3">
                                                <div class="stars">
                                                    ⭐⭐⭐⭐⭐
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Feedback (optional):</label>
                                                <textarea class="form-control" rows="3" placeholder="Tell me how I can improve..."></textarea>
                                            </div>
                                            <button class="btn btn-primary">Submit Feedback</button>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card">
                                            <div class="card-header">
                                                <h6>Learning Progress</h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="progress-circle mb-3">
                                                    <div class="circle">
                                                        <span class="percentage">75%</span>
                                                    </div>
                                                    <p class="text-center">Complete</p>
                                                </div>

                                                <h6>Recent Achievements</h6>
                                                <div class="achievement-list">
                                                    <div class="achievement-item">
                                                        <i class="fas fa-trophy text-warning me-2"></i>
                                                        <span>Mastered PHP Development</span>
                                                    </div>
                                                    <div class="achievement-item">
                                                        <i class="fas fa-medal text-info me-2"></i>
                                                        <span>Learned Deployment Strategies</span>
                                                    </div>
                                                    <div class="achievement-item">
                                                        <i class="fas fa-certificate text-success me-2"></i>
                                                        <span>Learning Security Practices</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Learning Analytics Tab -->
                            <div class="tab-pane fade" id="analytics" role="tabpanel">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="metric-card">
                                            <div class="metric-value">0</div>
                                            <div class="metric-label">Total Interactions</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="metric-card">
                                            <div class="metric-value">0</div>
                                            <div class="metric-label">Knowledge Entries</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mt-4">
                                    <div class="col-12">
                                        <div class="card">
                                            <div class="card-header">
                                                <h6>Interaction Trends (Last 30 Days)</h6>
                                            </div>
                                            <div class="card-body">
                                                <canvas id="interactionChart" width="400" height="200"></canvas>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mt-4">
                                    <div class="col-md-6">
                                        <div class="card">
                                            <div class="card-header">
                                                <h6>Top Topics</h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="topic-item d-flex justify-content-between">
                                                    <span>PHP Development</span>
                                                    <span class="badge bg-primary">42</span>
                                                </div>
                                                <div class="topic-item d-flex justify-content-between">
                                                    <span>Database Management</span>
                                                    <span class="badge bg-info">28</span>
                                                </div>
                                                <div class="topic-item d-flex justify-content-between">
                                                    <span>Deployment</span>
                                                    <span class="badge bg-success">23</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card">
                                            <div class="card-header">
                                                <h6>Performance Metrics</h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="metric-item">
                                                    <strong>Response Accuracy:</strong> 94%
                                                </div>
                                                <div class="metric-item">
                                                    <strong>User Satisfaction:</strong> 87%
                                                </div>
                                                <div class="metric-item">
                                                    <strong>Learning Rate:</strong> 76%
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- AI Personality Settings Tab -->
                            <div class="tab-pane fade" id="personality" role="tabpanel">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong>Note:</strong> Personality changes affect how I communicate and behave. Changes are applied gradually based on your feedback.
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="card">
                                            <div class="card-header">
                                                <h6>Communication Style</h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="radio" name="commStyle" id="formal" checked>
                                                    <label class="form-check-label" for="formal">
                                                        📋 Formal & Professional
                                                    </label>
                                                </div>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="radio" name="commStyle" id="friendly">
                                                    <label class="form-check-label" for="friendly">
                                                        😊 Friendly & Approachable
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="commStyle" id="technical">
                                                    <label class="form-check-label" for="technical">
                                                        ⚙️ Technical & Detailed
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card">
                                            <div class="card-header">
                                                <h6>Response Characteristics</h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="personality-slider mb-3">
                                                    <label>Response Length:</label>
                                                    <div class="slider-container">
                                                        <div class="slider-track">
                                                            <div class="slider-fill" style="width: 60%"></div>
                                                            <div class="slider-thumb" style="left: 60%"></div>
                                                        </div>
                                                        <div class="d-flex justify-content-between mt-1">
                                                            <small>Concise</small>
                                                            <small>Comprehensive</small>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="personality-slider">
                                                    <label>Technical Depth:</label>
                                                    <div class="slider-container">
                                                        <div class="slider-track">
                                                            <div class="slider-fill" style="width: 75%"></div>
                                                            <div class="slider-thumb" style="left: 75%"></div>
                                                        </div>
                                                        <div class="d-flex justify-content-between mt-1">
                                                            <small>Simple</small>
                                                            <small>Advanced</small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mt-4">
                                    <div class="col-12">
                                        <div class="card">
                                            <div class="card-header">
                                                <h6>Current Personality Profile</h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-md-3">
                                                        <div class="text-center">
                                                            <div class="metric-circle">
                                                                <span class="percentage">95%</span>
                                                            </div>
                                                            <p class="mt-2">Helpfulness</p>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="text-center">
                                                            <div class="metric-circle">
                                                                <span class="percentage">98%</span>
                                                            </div>
                                                            <p class="mt-2">Accuracy</p>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="text-center">
                                                            <div class="metric-circle">
                                                                <span class="percentage">85%</span>
                                                            </div>
                                                            <p class="mt-2">Creativity</p>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="text-center">
                                                            <div class="metric-circle">
                                                                <span class="percentage">88%</span>
                                                            </div>
                                                            <p class="mt-2">Empathy</p>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="text-center mt-4">
                                                    <button class="btn btn-primary me-2">Update Personality Settings</button>
                                                    <button class="btn btn-outline-secondary">Reset to Default</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Knowledge Base Tab -->
                            <div class="tab-pane fade" id="knowledge" role="tabpanel">
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <h5>AI Knowledge Base</h5>
                                    <button class="btn btn-primary">
                                        <i class="fas fa-plus me-2"></i>Add Knowledge
                                    </button>
                                </div>

                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="card">
                                            <div class="card-header">
                                                <h6>Knowledge Entries</h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="knowledge-item mb-3 p-3 border rounded">
                                                    <div class="d-flex justify-content-between">
                                                        <strong>PHP Development Best Practices</strong>
                                                        <span class="badge bg-primary">Active</span>
                                                    </div>
                                                    <p class="text-muted mb-2">Comprehensive guide to PHP development standards, security practices, and optimization techniques.</p>
                                                    <div class="knowledge-meta">
                                                        <small class="text-muted">
                                                            <i class="fas fa-clock me-1"></i>Updated 2 hours ago
                                                            <i class="fas fa-user ms-3 me-1"></i>Admin User
                                                        </small>
                                                    </div>
                                                </div>

                                                <div class="knowledge-item mb-3 p-3 border rounded">
                                                    <div class="d-flex justify-content-between">
                                                        <strong>Database Management</strong>
                                                        <span class="badge bg-info">Active</span>
                                                    </div>
                                                    <p class="text-muted mb-2">MySQL optimization, connection management, and security best practices.</p>
                                                    <div class="knowledge-meta">
                                                        <small class="text-muted">
                                                            <i class="fas fa-clock me-1"></i>Updated 1 day ago
                                                            <i class="fas fa-user ms-3 me-1"></i>System
                                                        </small>
                                                    </div>
                                                </div>

                                                <div class="text-center">
                                                    <button class="btn btn-outline-primary">Load More Knowledge</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card">
                                            <div class="card-header">
                                                <h6>Knowledge Statistics</h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="stat-item d-flex justify-content-between mb-2">
                                                    <span>Total Entries:</span>
                                                    <strong>0</strong>
                                                </div>
                                                <div class="stat-item d-flex justify-content-between mb-2">
                                                    <span>Active Topics:</span>
                                                    <strong>0</strong>
                                                </div>
                                                <div class="stat-item d-flex justify-content-between mb-2">
                                                    <span>Last Updated:</span>
                                                    <strong>Never</strong>
                                                </div>
                                                <div class="stat-item d-flex justify-content-between">
                                                    <span>Categories:</span>
                                                    <strong>0</strong>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="card mt-3">
                                            <div class="card-header">
                                                <h6>Quick Actions</h6>
                                            </div>
                                            <div class="card-body">
                                                <button class="btn btn-outline-primary w-100 mb-2">
                                                    <i class="fas fa-upload me-2"></i>Import Knowledge
                                                </button>
                                                <button class="btn btn-outline-warning w-100 mb-2">
                                                    <i class="fas fa-sync me-2"></i>Refresh Cache
                                                </button>
                                                <button class="btn btn-outline-danger w-100">
                                                    <i class="fas fa-trash me-2"></i>Clear Knowledge
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Workflow Patterns Tab -->
                            <div class="tab-pane fade" id="workflows" role="tabpanel">
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <h5>Workflow Patterns</h5>
                                    <button class="btn btn-primary">
                                        <i class="fas fa-plus me-2"></i>Create Pattern
                                    </button>
                                </div>

                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="card">
                                            <div class="card-header">
                                                <h6>Detected Patterns</h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="workflow-pattern mb-4 p-3 border rounded">
                                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                                        <div>
                                                            <h6 class="mb-1">Project Deep Scan</h6>
                                                            <p class="text-muted mb-0">High Automation</p>
                                                        </div>
                                                        <div class="text-end">
                                                            <span class="badge bg-success">Used 15 times</span>
                                                            <br>
                                                            <small class="text-muted">Last used: 2 hours ago</small>
                                                        </div>
                                                    </div>
                                                    <p class="mb-2">Automated project analysis and reporting workflow</p>
                                                    <div class="pattern-actions">
                                                        <button class="btn btn-sm btn-outline-primary me-2">Edit</button>
                                                        <button class="btn btn-sm btn-outline-success me-2">Execute</button>
                                                        <button class="btn btn-sm btn-outline-danger">Delete</button>
                                                    </div>
                                                </div>

                                                <div class="workflow-pattern mb-4 p-3 border rounded">
                                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                                        <div>
                                                            <h6 class="mb-1">Database Setup Automation</h6>
                                                            <p class="text-muted mb-0">Can be fully automated</p>
                                                        </div>
                                                        <div class="text-end">
                                                            <span class="badge bg-warning">Suggested</span>
                                                            <br>
                                                            <small class="text-muted">Based on patterns detected</small>
                                                        </div>
                                                    </div>
                                                    <p class="mb-2">Automated database schema creation and configuration workflow</p>
                                                    <div class="pattern-actions">
                                                        <button class="btn btn-sm btn-success">Implement</button>
                                                        <button class="btn btn-sm btn-outline-secondary">Preview</button>
                                                    </div>
                                                </div>

                                                <div class="text-center">
                                                    <button class="btn btn-outline-primary">Discover More Patterns</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card">
                                            <div class="card-header">
                                                <h6>Automation Suggestions</h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="suggestion-item mb-3 p-2 border rounded">
                                                    <div class="d-flex align-items-center">
                                                        <i class="fas fa-lightbulb text-warning me-2"></i>
                                                        <div>
                                                            <strong>Code Review Automation</strong>
                                                            <br>
                                                            <small class="text-muted">Based on frequent code analysis requests</small>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="suggestion-item mb-3 p-2 border rounded">
                                                    <div class="d-flex align-items-center">
                                                        <i class="fas fa-cog text-info me-2"></i>
                                                        <div>
                                                            <strong>Deployment Pipeline</strong>
                                                            <br>
                                                            <small class="text-muted">Automated deployment workflow</small>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="suggestion-item p-2 border rounded">
                                                    <div class="d-flex align-items-center">
                                                        <i class="fas fa-database text-success me-2"></i>
                                                        <div>
                                                            <strong>Database Migration</strong>
                                                            <br>
                                                            <small class="text-muted">Automated schema updates</small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="card mt-3">
                                            <div class="card-header">
                                                <h6>AI Self-Reflection</h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="reflection-log">
                                                    <div class="reflection-item mb-2">
                                                        <small class="text-muted">2 hours ago</small>
                                                        <br>
                                                        <span class="text-success">✅ Successfully fixed WhatsApp integration configuration</span>
                                                    </div>
                                                    <div class="reflection-item mb-2">
                                                        <small class="text-muted">5 hours ago</small>
                                                        <br>
                                                        <span class="text-info">🤔 Learning about advanced PHP authentication patterns</span>
                                                    </div>
                                                    <div class="reflection-item">
                                                        <small class="text-muted">1 day ago</small>
                                                        <br>
                                                        <span class="text-warning">⚠️ Encountered duplicate function declaration issue</span>
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
        </div>
    </div>

    <!-- AI Reflection Modal -->
    <div class="modal fade" id="reflectionModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">AI Self-Reflection</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="reflectionContent">
                        <p>I'm analyzing my recent performance and learning from our interactions...</p>
                        <div class="text-center">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary">Save Reflection</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>

    <script>
        // Chat functionality
        document.getElementById('sendMessage').addEventListener('click', function() {
            const input = document.getElementById('chatInput');
            const message = input.value.trim();

            if (message) {
                // Add user message
                const messagesContainer = document.getElementById('chatMessages');
                const userMessage = document.createElement('div');
                userMessage.className = 'message user';
                userMessage.innerHTML = `<strong>You:</strong> ${message}`;
                messagesContainer.appendChild(userMessage);

                // Clear input
                input.value = '';

                // Scroll to bottom
                messagesContainer.scrollTop = messagesContainer.scrollHeight;

                // Simulate AI response
                setTimeout(() => {
                    const aiMessage = document.createElement('div');
                    aiMessage.className = 'message ai';
                    aiMessage.innerHTML = `<strong>APS Assistant:</strong> I apologize, but I'm having trouble responding right now. Please try again.`;
                    messagesContainer.appendChild(aiMessage);
                    messagesContainer.scrollTop = messagesContainer.scrollHeight;
                }, 1000);
            }
        });

        // Quick question buttons
        document.querySelectorAll('.quick-question').forEach(button => {
            button.addEventListener('click', function() {
                const question = this.getAttribute('data-question');
                document.getElementById('chatInput').value = question;
            });
        });

        // Training session
        document.getElementById('startTraining').addEventListener('click', function() {
            const topic = document.getElementById('trainingTopic').value;
            if (topic) {
                alert('Training session started for: ' + topic);
                // Here you would implement actual training logic
            }
        });

        // Personality sliders
        document.querySelectorAll('.slider-thumb').forEach(thumb => {
            thumb.addEventListener('mousedown', function() {
                const track = this.parentElement;
                const container = track.parentElement;

                document.addEventListener('mousemove', moveThumb);
                document.addEventListener('mouseup', stopMove);

                function moveThumb(e) {
                    const rect = track.getBoundingClientRect();
                    const x = Math.max(0, Math.min(1, (e.clientX - rect.left) / rect.width));
                    thumb.style.left = (x * 100) + '%';
                    track.querySelector('.slider-fill').style.width = (x * 100) + '%';
                }

                function stopMove() {
                    document.removeEventListener('mousemove', moveThumb);
                    document.removeEventListener('mouseup', stopMove);
                }
            });
        });

        // Initialize charts
        const ctx = document.getElementById('interactionChart');
        if (ctx) {
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['Day 1', 'Day 2', 'Day 3', 'Day 4', 'Day 5'],
                    datasets: [{
                        label: 'Interactions',
                        data: [0, 0, 0, 0, 0],
                        borderColor: '#667eea',
                        backgroundColor: 'rgba(102, 126, 234, 0.1)',
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }

        // Auto-refresh every 30 seconds
        setInterval(function() {
            // Refresh AI status and analytics
            console.log('Refreshing AI data...');
        }, 30000);
    </script>
</body>
</html>
