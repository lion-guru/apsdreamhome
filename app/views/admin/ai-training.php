<?php

/**
 * AI Chatbot Training Interface
 * Admin can train the chatbot with new Q&A patterns
 */

session_start();

// Check admin login
if (!isset($_SESSION['admin_id'])) {
    header('Location: /admin/login');
    exit;
}

require_once __DIR__ . '/../../../app/Core/Database/Database.php';

$db = \App\Core\Database\Database::getInstance();

// Handle form submissions
$success = '';
$error = '';

// Add new Q&A
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add_qa') {
        $category = $_POST['category'] ?? 'general';
        $questionPattern = $_POST['question_pattern'] ?? '';
        $answer = $_POST['answer'] ?? '';
        $keywords = $_POST['keywords'] ?? '';
        $variations = $_POST['variations'] ?? '';

        if (!empty($questionPattern) && !empty($answer)) {
            try {
                $db->execute(
                    "INSERT INTO ai_knowledge_base 
                     (category, question_pattern, answer, keywords, question_variations, created_at, updated_at) 
                     VALUES (?, ?, ?, ?, ?, NOW(), NOW())",
                    [$category, $questionPattern, $answer, $keywords, $variations]
                );
                $success = 'New Q&A added successfully!';

                // Add notification for admin
                $_SESSION['notifications'][] = [
                    'type' => 'success',
                    'message' => "New AI Q&A added: " . substr($questionPattern, 0, 30) . "...",
                    'time' => date('Y-m-d H:i:s'),
                    'link' => '/admin/ai-training'
                ];
            } catch (Exception $e) {
                $error = 'Error adding Q&A: ' . $e->getMessage();
            }
        } else {
            $error = 'Question pattern and answer are required!';
        }
    }

    // Update Q&A
    if ($_POST['action'] === 'update_qa' && isset($_POST['id'])) {
        $id = $_POST['id'];
        $category = $_POST['category'] ?? 'general';
        $questionPattern = $_POST['question_pattern'] ?? '';
        $answer = $_POST['answer'] ?? '';
        $keywords = $_POST['keywords'] ?? '';

        try {
            $db->execute(
                "UPDATE ai_knowledge_base 
                 SET category = ?, question_pattern = ?, answer = ?, keywords = ?, updated_at = NOW() 
                 WHERE id = ?",
                [$category, $questionPattern, $answer, $keywords, $id]
            );
            $success = 'Q&A updated successfully!';
        } catch (Exception $e) {
            $error = 'Error updating Q&A: ' . $e->getMessage();
        }
    }

    // Delete Q&A
    if ($_POST['action'] === 'delete_qa' && isset($_POST['id'])) {
        $id = $_POST['id'];
        try {
            $db->execute("DELETE FROM ai_knowledge_base WHERE id = ?", [$id]);
            $success = 'Q&A deleted successfully!';
        } catch (Exception $e) {
            $error = 'Error deleting Q&A: ' . $e->getMessage();
        }
    }

    // Bulk Import
    if ($_POST['action'] === 'bulk_import' && isset($_POST['bulk_data'])) {
        $lines = explode("\n", $_POST['bulk_data']);
        $category = $_POST['bulk_category'] ?? 'general';
        $added = 0;

        foreach ($lines as $line) {
            $parts = explode('|', $line);
            if (count($parts) >= 2) {
                $question = trim($parts[0]);
                $answer = trim($parts[1]);
                $keywords = isset($parts[2]) ? trim($parts[2]) : '';

                try {
                    $db->execute(
                        "INSERT INTO ai_knowledge_base 
                         (category, question_pattern, answer, keywords, created_at) 
                         VALUES (?, ?, ?, ?, NOW())",
                        [$category, $question, $answer, $keywords]
                    );
                    $added++;
                } catch (Exception $e) {
                    // Skip duplicates or errors
                }
            }
        }
        $success = "$added Q&A patterns imported successfully!";
    }
}

// Fetch all Q&A
$knowledgeBase = [];
try {
    $knowledgeBase = $db->fetchAll(
        "SELECT * FROM ai_knowledge_base ORDER BY category, usage_count DESC, created_at DESC"
    );
} catch (Exception $e) {
    // Table might not exist
}

// Fetch analytics
$analytics = [
    'total_qa' => count($knowledgeBase),
    'categories' => [],
    'most_used' => [],
    'conversations_today' => 0,
    'total_conversations' => 0
];

try {
    // Categories count
    $cats = $db->fetchAll("SELECT category, COUNT(*) as count FROM ai_knowledge_base GROUP BY category");
    foreach ($cats as $cat) {
        $analytics['categories'][$cat['category']] = $cat['count'];
    }

    // Most used Q&A
    $analytics['most_used'] = $db->fetchAll(
        "SELECT question_pattern, usage_count FROM ai_knowledge_base 
         WHERE usage_count > 0 ORDER BY usage_count DESC LIMIT 5"
    );

    // Today's conversations
    $today = $db->fetch(
        "SELECT COUNT(*) as count FROM ai_conversations WHERE DATE(created_at) = CURDATE()"
    );
    $analytics['conversations_today'] = $today['count'] ?? 0;

    // Total conversations
    $total = $db->fetch("SELECT COUNT(*) as count FROM ai_conversations");
    $analytics['total_conversations'] = $total['count'] ?? 0;
} catch (Exception $e) {
    // Ignore errors
}

// Categories for dropdown
$categories = [
    'general' => 'General Inquiry',
    'property' => 'Property Information',
    'pricing' => 'Pricing & Payment',
    'location' => 'Location & Maps',
    'booking' => 'Booking & Registration',
    'loan' => 'Home Loan',
    'legal' => 'Legal Services',
    'contact' => 'Contact Information',
    'services' => 'Other Services',
    'complaint' => 'Complaints & Support'
];

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Chatbot Training | APS Dream Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #667eea;
            --secondary: #764ba2;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
        }

        body {
            background: #f8fafc;
            font-family: 'Segoe UI', sans-serif;
        }

        .sidebar {
            background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
            min-height: 100vh;
            color: white;
            position: fixed;
            width: 260px;
        }

        .sidebar-brand {
            padding: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar-menu {
            padding: 20px 0;
        }

        .sidebar-menu a {
            display: block;
            padding: 12px 20px;
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            transition: all 0.3s;
        }

        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            border-left: 3px solid var(--primary);
        }

        .main-content {
            margin-left: 260px;
            padding: 30px;
        }

        .stats-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s;
        }

        .stats-card:hover {
            transform: translateY(-5px);
        }

        .stats-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 15px;
        }

        .stats-number {
            font-size: 32px;
            font-weight: 700;
            color: #1e293b;
        }

        .form-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }

        .qa-item {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 15px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            border-left: 4px solid var(--primary);
        }

        .category-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .cat-general {
            background: #e0e7ff;
            color: #4338ca;
        }

        .cat-property {
            background: #dcfce7;
            color: #166534;
        }

        .cat-pricing {
            background: #fef3c7;
            color: #92400e;
        }

        .cat-location {
            background: #dbeafe;
            color: #1e40af;
        }

        .cat-booking {
            background: #fce7f3;
            color: #9d174d;
        }

        .cat-loan {
            background: #ccfbf1;
            color: #0f766e;
        }

        .btn-gradient {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            border: none;
            padding: 10px 25px;
            border-radius: 8px;
            font-weight: 600;
        }

        .test-chat-box {
            background: #f1f5f9;
            border-radius: 15px;
            padding: 20px;
            height: 400px;
            overflow-y: auto;
        }

        .chat-message {
            padding: 12px 16px;
            border-radius: 15px;
            margin-bottom: 10px;
            max-width: 80%;
        }

        .chat-user {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            margin-left: auto;
        }

        .chat-bot {
            background: white;
            color: #333;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .training-tips {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 30px;
        }

        .bulk-import-area {
            font-family: monospace;
            font-size: 13px;
        }
    </style>
</head>

<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-brand">
            <h5 class="mb-0"><i class="fas fa-robot me-2"></i>AI Training</h5>
        </div>
        <div class="sidebar-menu">
            <a href="#dashboard" class="active"><i class="fas fa-chart-line me-2"></i>Dashboard</a>
            <a href="#add-qa"><i class="fas fa-plus-circle me-2"></i>Add Q&A</a>
            <a href="#manage-qa"><i class="fas fa-list me-2"></i>Manage Q&A (<?php echo count($knowledgeBase); ?>)</a>
            <a href="#bulk-import"><i class="fas fa-file-import me-2"></i>Bulk Import</a>
            <a href="#test-bot"><i class="fas fa-comments me-2"></i>Test Bot</a>
            <a href="#analytics"><i class="fas fa-chart-bar me-2"></i>Analytics</a>
            <a href="/admin/dashboard"><i class="fas fa-arrow-left me-2"></i>Back to Admin</a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1">🤖 AI Chatbot Training Center</h2>
                <p class="text-muted mb-0">Train your AI assistant with new knowledge</p>
            </div>
            <div class="text-end">
                <span class="badge bg-success px-3 py-2">
                    <i class="fas fa-circle me-1"></i>Bot Active
                </span>
            </div>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Stats Cards -->
        <div class="row mb-4" id="dashboard">
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-icon bg-primary bg-opacity-10 text-primary">
                        <i class="fas fa-database"></i>
                    </div>
                    <div class="stats-number"><?php echo $analytics['total_qa']; ?></div>
                    <div class="text-muted">Total Q&A Patterns</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-icon bg-success bg-opacity-10 text-success">
                        <i class="fas fa-comments"></i>
                    </div>
                    <div class="stats-number"><?php echo $analytics['conversations_today']; ?></div>
                    <div class="text-muted">Chats Today</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-icon bg-info bg-opacity-10 text-info">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="stats-number"><?php echo $analytics['total_conversations']; ?></div>
                    <div class="text-muted">Total Conversations</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-icon bg-warning bg-opacity-10 text-warning">
                        <i class="fas fa-tags"></i>
                    </div>
                    <div class="stats-number"><?php echo count($analytics['categories']); ?></div>
                    <div class="text-muted">Categories</div>
                </div>
            </div>
        </div>

        <!-- Training Tips -->
        <div class="training-tips">
            <h5><i class="fas fa-lightbulb me-2"></i>💡 Training Tips</h5>
            <div class="row">
                <div class="col-md-6">
                    <ul class="mb-0">
                        <li>Use natural language patterns like "kya hai", "kaise", "kitna"</li>
                        <li>Add multiple question variations for same answer</li>
                        <li>Include location names (Gorakhpur, Lucknow, etc.)</li>
                        <li>Use keywords that users might type</li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <ul class="mb-0">
                        <li>Keep answers concise but informative</li>
                        <li>Add emojis for better engagement 🏠 💰</li>
                        <li>Include contact numbers in relevant answers</li>
                        <li>Update prices when they change</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Add New Q&A -->
        <div class="form-card" id="add-qa">
            <h4 class="mb-4"><i class="fas fa-plus-circle me-2 text-primary"></i>Add New Q&A Pattern</h4>
            <form method="POST">
                <input type="hidden" name="action" value="add_qa">
                <div class="row">
                    <div class="col-md-4">
                        <label class="form-label">Category</label>
                        <select name="category" class="form-select" required>
                            <?php foreach ($categories as $key => $label): ?>
                                <option value="<?php echo $key; ?>"><?php echo $label; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">Question Pattern (Main)</label>
                        <input type="text" name="question_pattern" class="form-control"
                            placeholder="e.g., Suryoday Heights ka price kya hai?" required>
                    </div>
                </div>
                <div class="mt-3">
                    <label class="form-label">Question Variations (one per line)</label>
                    <textarea name="variations" class="form-control" rows="2"
                        placeholder="suryoday plot rate&#10;suryoday me plot kitne ka hai"></textarea>
                </div>
                <div class="mt-3">
                    <label class="form-label">Keywords (comma separated)</label>
                    <input type="text" name="keywords" class="form-control"
                        placeholder="suryoday, price, rate, plot, gorakhpur">
                </div>
                <div class="mt-3">
                    <label class="form-label">Answer</label>
                    <textarea name="answer" class="form-control" rows="4" required
                        placeholder="🏠 Suryoday Heights me plots ₹5.5 Lakh se shuru hote hain!&#10;&#10;📍 Location: Gorakhpur&#10;📐 Sizes: 1000-5000 sq ft&#10;&#10;📞 Call karein: +91 92771 21112"></textarea>
                </div>
                <div class="mt-3">
                    <button type="submit" class="btn btn-gradient">
                        <i class="fas fa-save me-2"></i>Save Q&A Pattern
                    </button>
                </div>
            </form>
        </div>

        <!-- Manage Q&A -->
        <div class="form-card" id="manage-qa">
            <h4 class="mb-4"><i class="fas fa-list me-2 text-primary"></i>Manage Q&A Patterns</h4>

            <!-- Category Filter -->
            <div class="mb-3">
                <div class="btn-group">
                    <a href="?" class="btn btn-outline-primary active">All</a>
                    <?php foreach ($categories as $key => $label): ?>
                        <a href="?cat=<?php echo $key; ?>" class="btn btn-outline-primary"><?php echo $label; ?></a>
                    <?php endforeach; ?>
                </div>
            </div>

            <?php if (empty($knowledgeBase)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>No Q&A patterns yet. Add your first one above!
                </div>
            <?php else: ?>
                <?php foreach ($knowledgeBase as $qa): ?>
                    <div class="qa-item">
                        <form method="POST" class="row">
                            <input type="hidden" name="action" value="update_qa">
                            <input type="hidden" name="id" value="<?php echo $qa['id']; ?>">

                            <div class="col-md-12 mb-2">
                                <span class="category-badge cat-<?php echo $qa['category']; ?>">
                                    <?php echo $categories[$qa['category']] ?? $qa['category']; ?>
                                </span>
                                <?php if ($qa['usage_count'] > 0): ?>
                                    <span class="badge bg-success ms-2">
                                        <i class="fas fa-fire me-1"></i><?php echo $qa['usage_count']; ?> uses
                                    </span>
                                <?php endif; ?>
                            </div>

                            <div class="col-md-6 mb-2">
                                <label class="small text-muted">Question Pattern</label>
                                <input type="text" name="question_pattern" class="form-control form-control-sm"
                                    value="<?php echo htmlspecialchars($qa['question_pattern']); ?>">
                            </div>

                            <div class="col-md-3 mb-2">
                                <label class="small text-muted">Category</label>
                                <select name="category" class="form-select form-select-sm">
                                    <?php foreach ($categories as $key => $label): ?>
                                        <option value="<?php echo $key; ?>" <?php echo $qa['category'] == $key ? 'selected' : ''; ?>>
                                            <?php echo $label; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-3 mb-2">
                                <label class="small text-muted">Keywords</label>
                                <input type="text" name="keywords" class="form-control form-control-sm"
                                    value="<?php echo htmlspecialchars($qa['keywords'] ?? ''); ?>">
                            </div>

                            <div class="col-md-12 mb-2">
                                <label class="small text-muted">Answer</label>
                                <textarea name="answer" class="form-control" rows="2"><?php echo htmlspecialchars($qa['answer']); ?></textarea>
                            </div>

                            <div class="col-md-12 text-end">
                                <button type="submit" class="btn btn-sm btn-primary">
                                    <i class="fas fa-save me-1"></i>Update
                                </button>
                                <a href="?delete=<?php echo $qa['id']; ?>" class="btn btn-sm btn-outline-danger"
                                    onclick="return confirm('Delete this Q&A?')">
                                    <i class="fas fa-trash me-1"></i>Delete
                                </a>
                            </div>
                        </form>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Bulk Import -->
        <div class="form-card" id="bulk-import">
            <h4 class="mb-4"><i class="fas fa-file-import me-2 text-primary"></i>Bulk Import Q&A</h4>
            <form method="POST">
                <input type="hidden" name="action" value="bulk_import">
                <div class="mb-3">
                    <label class="form-label">Format: Question | Answer | Keywords (optional)</label>
                    <textarea name="bulk_data" class="form-control bulk-import-area" rows="10"
                        placeholder="suryoday ka price kya hai | Suryoday me plots ₹5.5 Lakh se shuru | suryoday, price, plot&#10;raghunath city center kaha hai | Raghunath City Center Gorakhpur me hai | raghunath, location&#10;emi calculator | EMI calculator ke liye yeh link use karein... | emi, loan, calculator"></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Default Category</label>
                    <select name="bulk_category" class="form-select">
                        <?php foreach ($categories as $key => $label): ?>
                            <option value="<?php echo $key; ?>"><?php echo $label; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-gradient">
                    <i class="fas fa-upload me-2"></i>Import Q&A Patterns
                </button>
            </form>
        </div>

        <!-- Test Bot -->
        <div class="form-card" id="test-bot">
            <h4 class="mb-4"><i class="fas fa-comments me-2 text-primary"></i>Test Your Bot</h4>
            <div class="row">
                <div class="col-md-8">
                    <div class="test-chat-box" id="testChatBox">
                        <div class="chat-message chat-bot">
                            <i class="fas fa-robot me-2"></i>
                            Namaste! Main APS Property Assistant hoon. Kaise madad kar sakta hoon? 🙏
                        </div>
                    </div>
                    <div class="input-group mt-3">
                        <input type="text" id="testInput" class="form-control"
                            placeholder="Type a test message...">
                        <button class="btn btn-primary" onclick="sendTestMessage()">
                            <i class="fas fa-paper-plane"></i> Send
                        </button>
                    </div>
                </div>
                <div class="col-md-4">
                    <h6>Quick Test Questions:</h6>
                    <div class="list-group">
                        <button class="list-group-item list-group-item-action" onclick="fillTest('suryoday ka price kya hai')">
                            suryoday ka price kya hai
                        </button>
                        <button class="list-group-item list-group-item-action" onclick="fillTest('raghunath city center kaha hai')">
                            raghunath city center kaha hai
                        </button>
                        <button class="list-group-item list-group-item-action" onclick="fillTest('plot book kaise karein')">
                            plot book kaise karein
                        </button>
                        <button class="list-group-item list-group-item-action" onclick="fillTest('home loan available hai')">
                            home loan available hai
                        </button>
                        <button class="list-group-item list-group-item-action" onclick="fillTest('contact number batao')">
                            contact number batao
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Analytics -->
        <div class="form-card" id="analytics">
            <h4 class="mb-4"><i class="fas fa-chart-bar me-2 text-primary"></i>Usage Analytics</h4>
            <div class="row">
                <div class="col-md-6">
                    <h6>Most Used Q&A Patterns</h6>
                    <?php if (empty($analytics['most_used'])): ?>
                        <p class="text-muted">No usage data yet. The bot needs to be used more!</p>
                    <?php else: ?>
                        <ul class="list-group">
                            <?php foreach ($analytics['most_used'] as $item): ?>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span><?php echo substr($item['question_pattern'], 0, 40); ?>...</span>
                                    <span class="badge bg-primary"><?php echo $item['usage_count']; ?> uses</span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
                <div class="col-md-6">
                    <h6>Category Distribution</h6>
                    <ul class="list-group">
                        <?php foreach ($analytics['categories'] as $cat => $count): ?>
                            <li class="list-group-item d-flex justify-content-between">
                                <span><?php echo $categories[$cat] ?? $cat; ?></span>
                                <span class="badge bg-info"><?php echo $count; ?> Q&A</span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function fillTest(message) {
            document.getElementById('testInput').value = message;
        }

        async function sendTestMessage() {
            const input = document.getElementById('testInput');
            const chatBox = document.getElementById('testChatBox');
            const message = input.value.trim();

            if (!message) return;

            // Add user message
            chatBox.innerHTML += `
                <div class="chat-message chat-user">
                    <i class="fas fa-user me-2"></i>${message}
                </div>
            `;
            input.value = '';
            chatBox.scrollTop = chatBox.scrollHeight;

            // Call API
            try {
                const response = await fetch('/apsdreamhome/api/gemini/chat', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        message: message,
                        session_id: 'test'
                    })
                });
                const data = await response.json();

                // Add bot response after delay
                setTimeout(() => {
                    chatBox.innerHTML += `
                        <div class="chat-message chat-bot">
                            <i class="fas fa-robot me-2"></i>
                            ${data.reply || data.response || 'Sorry, no response'}
                            <br><small class="text-muted">Source: ${data.source || 'unknown'}</small>
                        </div>
                    `;
                    chatBox.scrollTop = chatBox.scrollHeight;
                }, 1000);
            } catch (e) {
                chatBox.innerHTML += `
                    <div class="chat-message chat-bot text-danger">
                        <i class="fas fa-exclamation-circle me-2"></i>Error: ${e.message}
                    </div>
                `;
            }
        }

        document.getElementById('testInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') sendTestMessage();
        });
    </script>
</body>

</html>