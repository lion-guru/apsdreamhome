<?php
session_start();
require_once(__DIR__ . "/includes/config/config.php");
require_once(__DIR__ . "/includes/functions/common-functions.php");
include 'includes/base_template.php';

error_reporting(E_ERROR | E_PARSE);

// Enhanced security checks
if (!isset($_SESSION['uid']) || !isset($_SESSION['usertype']) || $_SESSION['usertype'] != 'assosiate') {
    header("location:login.php");
    exit();
}

$associate_id = $_SESSION['uid'];

// Enhanced logging function
function logDashboardError($message, $context = []) {
    $logFile = __DIR__ . '/logs/dashboard_errors.log';
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] $message ";
    
    if (!empty($context)) {
        $logMessage .= json_encode($context);
    }
    
    error_log($logMessage . PHP_EOL, 3, $logFile);
}

// Fetch associate details with prepared statement and enhanced error handling
try {
    // Validate associate_id before query
    if (!is_numeric($associate_id)) {
        throw new InvalidArgumentException('Invalid associate ID');
    }

    $query_asso_details = "SELECT 
        uid, uname, uemail, uphone, sponsor_id, sponsored_by, 
        bank_name, account_number, ifsc_code, address, join_date, 
        kyc_status, total_investments, last_login 
    FROM user 
    WHERE uid = ? AND status = 'active'";
    
    $stmt = $conn->prepare($query_asso_details);
    if (!$stmt) {
        throw new RuntimeException('Failed to prepare statement: ' . $conn->error);
    }
    
    $stmt->bind_param('i', $associate_id);
    $stmt->execute();
    
    if ($stmt->errno) {
        throw new RuntimeException('Statement execution failed: ' . $stmt->error);
    }
    
    $result_asso_details = $stmt->get_result();
    
    // Default values with more comprehensive data
    $associate_details = [
        'uid' => $associate_id,
        'uname' => 'Associate',
        'uemail' => '',
        'uphone' => '',
        'sponsor_id' => '',
        'sponsored_by' => '',
        'bank_name' => '',
        'account_number' => '',
        'ifsc_code' => '',
        'address' => '',
        'join_date' => '',
        'kyc_status' => 'Pending',
        'total_investments' => 0,
        'last_login' => null,
        'account_status' => 'inactive'
    ];
    
    if ($row_asso_details = $result_asso_details->fetch_assoc()) {
        // Sanitize and merge details
        $associate_details = array_merge($associate_details, array_map('htmlspecialchars', $row_asso_details));
    } else {
        // Log when no user found
        logDashboardError('No user found for ID', ['associate_id' => $associate_id]);
    }
    
    $stmt->close();

} catch (InvalidArgumentException $e) {
    // Handle invalid input
    logDashboardError('Invalid input', ['message' => $e->getMessage()]);
    header('location: login.php');
    exit();
} catch (RuntimeException $e) {
    // Handle database errors
    logDashboardError('Database error', ['message' => $e->getMessage()]);
    // Redirect to error page or show generic error
    header('location: error.php');
    exit();
} catch (Exception $e) {
    // Catch any other unexpected errors
    logDashboardError('Unexpected error', ['message' => $e->getMessage()]);
    header('location: error.php');
    exit();
}

// Enhanced dashboard stats retrieval with comprehensive error handling
try {
    // Detailed stats query with more comprehensive information
    $stats_query = "
        SELECT 
            (SELECT COUNT(*) FROM plots WHERE associate_id = ? AND status = 'active') as active_plots_count,
            (SELECT COUNT(*) FROM plots WHERE associate_id = ? AND status = 'pending') as pending_plots_count,
            (SELECT COUNT(*) FROM documents WHERE associate_id = ? AND status = 'Pending') as pending_docs_count,
            (SELECT COUNT(*) FROM documents WHERE associate_id = ? AND status = 'Approved') as approved_docs_count,
            (SELECT COUNT(*) FROM notifications WHERE associate_id = ? AND is_read = 0) as unread_notifications,
            (SELECT COUNT(*) FROM transactions WHERE associate_id = ? AND status = 'pending') as pending_transactions,
            (SELECT SUM(amount) FROM transactions WHERE associate_id = ? AND status = 'completed') as total_investments
    ";

    // Prepare and execute the statement with enhanced error checking
    $stmt = $conn->prepare($stats_query);

    if (!$stmt) {
        throw new RuntimeException('Failed to prepare stats statement: ' . $conn->error);
    }

    // Bind parameters multiple times to match query placeholders
    $stmt->bind_param('iiiiiii', 
        $associate_id, $associate_id, $associate_id, 
        $associate_id, $associate_id, $associate_id, 
        $associate_id
    );

    $stmt->execute();

    if ($stmt->errno) {
        throw new RuntimeException('Stats statement execution failed: ' . $stmt->error);
    }

    $stats_result = $stmt->get_result()->fetch_assoc();

    $stmt->close();

    
    // Determine KYC status dynamically
    $kyc_query = "SELECT status FROM kyc_verification WHERE associate_id = ? ORDER BY created_at DESC LIMIT 1";

    $kyc_stmt = $conn->prepare($kyc_query);

    if (!$kyc_stmt) {
        throw new RuntimeException('Failed to prepare KYC statement: ' . $conn->error);
    }

    $kyc_stmt->bind_param('i', $associate_id);

    $kyc_stmt->execute();

    $kyc_result = $kyc_stmt->get_result()->fetch_assoc();

    $kyc_stmt->close();

    
    // Compile comprehensive dashboard stats
    $dashboard_stats = [

        // Plot-related stats

        'active_plots' => $stats_result['active_plots_count'] ?? 0,

        'pending_plots' => $stats_result['pending_plots_count'] ?? 0,

        
        // Document-related stats

        'pending_docs' => $stats_result['pending_docs_count'] ?? 0,

        'approved_docs' => $stats_result['approved_docs_count'] ?? 0,

        
        // Financial stats

        'total_investments' => $stats_result['total_investments'] ?? 0,

        'pending_transactions' => $stats_result['pending_transactions'] ?? 0,

        
        // Notification stats

        'unread_notifications' => $stats_result['unread_notifications'] ?? 0,

        
        // KYC status

        'kyc_status' => $kyc_result['status'] ?? 'Not Submitted',

    ];

    
    // Log stats retrieval for monitoring

    logDashboardError('Dashboard stats retrieved successfully', [

        'associate_id' => $associate_id,

        'active_plots' => $dashboard_stats['active_plots'],

        'pending_docs' => $dashboard_stats['pending_docs'],

        'total_investments' => $dashboard_stats['total_investments']

    ]);

    
} catch (RuntimeException $e) {

    // Log and handle database-related errors

    logDashboardError('Dashboard stats retrieval error', [

        'message' => $e->getMessage(),

        'associate_id' => $associate_id

    ]);

    
    // Set default stats in case of error

    $dashboard_stats = [

        'active_plots' => 0,

        'pending_plots' => 0,

        'pending_docs' => 0,

        'approved_docs' => 0,

        'total_investments' => 0,

        'pending_transactions' => 0,

        'unread_notifications' => 0,

        'kyc_status' => 'Error'

    ];

} catch (Exception $e) {

    // Catch any unexpected errors

    logDashboardError('Unexpected dashboard stats error', [

        'message' => $e->getMessage(),

        'associate_id' => $associate_id

    ]);

    
    // Set minimal default stats

    $dashboard_stats = [

        'active_plots' => 0,

        'pending_plots' => 0,

        'pending_docs' => 0,

        'approved_docs' => 0,

        'total_investments' => 0,

        'pending_transactions' => 0,

        'unread_notifications' => 0,

        'kyc_status' => 'Error'

    ];

}

// Prepare content for base template
ob_start();
?>
<div class="dashboard-container">
    <div class="dashboard-header">
        <div class="greeting">
            <h2>नमस्ते, <?php echo htmlspecialchars($associate_details['uname']); ?></h2>
            <p class="subtitle">आपका व्यक्तिगत डैशबोर्ड</p>
        </div>
        <div class="header-actions">
            <div class="profile-completion">
                <?php 
                // Determine KYC status and styling
                $kycStatusClass = match($dashboard_stats['kyc_status']) {
                    'Verified' => 'bg-success',
                    'Pending' => 'bg-warning',
                    'Rejected' => 'bg-danger',
                    default => 'bg-secondary'
                };

                // Determine KYC action button
                $kycActionText = match($dashboard_stats['kyc_status']) {
                    'Verified' => 'अद्यतन करें',
                    'Pending' => 'स्थिति देखें',
                    'Rejected' => 'पुन: दाखिल करें',
                    default => 'क्यू दाखिल करें'
                };
                ?>
                <div class="kyc-status-container">
                    <span class="badge <?php echo $kycStatusClass; ?> kyc-status-badge">
                        <?php echo $dashboard_stats['kyc_status']; ?>
                    </span>
                    <a href="kyc-upload.php" class="btn btn-sm btn-outline-primary kyc-action-btn">
                        <?php echo $kycActionText; ?>
                    </a>
                </div>
                <div class="profile-completion-progress">
                    <?php 
                    // Calculate profile completion percentage
                    $completionPercentage = 0;
                    $completionSteps = [
                        'personal_info' => !empty($associate_details['uname']) && !empty($associate_details['uemail']),
                        'contact_details' => !empty($associate_details['uphone']) && !empty($associate_details['address']),
                        'bank_details' => !empty($associate_details['bank_name']) && !empty($associate_details['account_number']),
                        'kyc_verified' => $dashboard_stats['kyc_status'] === 'Verified'
                    ];

                    $completedSteps = array_filter($completionSteps);
                    $completionPercentage = round((count($completedSteps) / count($completionSteps)) * 100);
                    ?>
                    <div class="progress" role="progressbar" aria-label="Profile Completion" aria-valuenow="<?php echo $completionPercentage; ?>" aria-valuemin="0" aria-valuemax="100">
                        <div class="progress-bar <?php echo $completionPercentage === 100 ? 'bg-success' : 'bg-primary'; ?>" style="width: <?php echo $completionPercentage; ?>%">
                            <?php echo $completionPercentage; ?>%
                        </div>
                    </div>
                    <small class="text-muted">प्रोफाइल पूरा करें</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Comprehensive Stats Grid -->
    <div class="stats-grid">
        <div class="stat-card plot-stats">
            <div class="card-header">
                <i class="fas fa-map-marked-alt"></i>
                <h3>प्लॉट्स</h3>
            </div>
            <div class="card-body">
                <div class="stat-group">
                    <div class="stat-item">
                        <span class="stat-value"><?php echo $dashboard_stats['active_plots']; ?></span>
                        <span class="stat-label">सक्रिय</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-value"><?php echo $dashboard_stats['pending_plots']; ?></span>
                        <span class="stat-label">लंबित</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="stat-card document-stats">
            <div class="card-header">
                <i class="fas fa-file-alt"></i>
                <h3>दस्तावेज़</h3>
            </div>
            <div class="card-body">
                <div class="stat-group">
                    <div class="stat-item">
                        <span class="stat-value"><?php echo $dashboard_stats['pending_docs']; ?></span>
                        <span class="stat-label">लंबित</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-value"><?php echo $dashboard_stats['approved_docs']; ?></span>
                        <span class="stat-label">स्वीकृत</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="stat-card financial-stats">
            <div class="card-header">
                <i class="fas fa-rupee-sign"></i>
                <h3>वित्तीय</h3>
            </div>
            <div class="card-body">
                <div class="stat-group">
                    <div class="stat-item">
                        <span class="stat-value">₹<?php echo number_format($dashboard_stats['total_investments'], 2); ?></span>
                        <span class="stat-label">कुल निवेश</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-value"><?php echo $dashboard_stats['pending_transactions']; ?></span>
                        <span class="stat-label">लंबित लेनदेन</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="stat-card notification-stats">
            <div class="card-header">
                <i class="fas fa-bell"></i>
                <h3>अधिसूचनाएँ</h3>
            </div>
            <div class="card-body">
                <div class="stat-group">
                    <div class="stat-item">
                        <span class="stat-value"><?php echo $dashboard_stats['unread_notifications']; ?></span>
                        <span class="stat-label">अपठित</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions Section -->
    <div class="quick-actions">
        <div class="action-grid">
            <a href="profile.php" class="action-card">
                <i class="fas fa-user-edit"></i>
                <span>प्रोफ़ाइल अपडेट करें</span>
            </a>
            <a href="documents.php" class="action-card">
                <i class="fas fa-file-upload"></i>
                <span>दस्तावेज़ अपलोड करें</span>
            </a>
            <a href="support.php" class="action-card">
                <i class="fas fa-headset"></i>
                <span>सहायता केंद्र</span>
            </a>
            <a href="property-list.php" class="action-card">
                <i class="fas fa-home"></i>
                <span>प्रॉपर्टी देखें</span>
            </a>
        </div>
    </div>

    <!-- AI Chatbot Section -->
    <div class="ai-chatbot-section">
        <div class="chatbot-header">
            <h3><i class="fas fa-robot"></i> AI सहायक</h3>
            <p>मुझसे कुछ भी पूछें</p>
        </div>
        <div class="chatbot-interface">
            <div id="chatMessages" class="chat-messages"></div>
            <form id="aiChatForm" class="chat-input-form">
                <input type="text" id="aiChatInput" placeholder="अपना प्रश्न यहाँ लिखें..." required>
                <button type="submit"><i class="fas fa-paper-plane"></i></button>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const chatForm = document.getElementById('aiChatForm');
    const chatInput = document.getElementById('aiChatInput');
    const chatMessages = document.getElementById('chatMessages');

    chatForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const userMessage = chatInput.value.trim();
        
        if (userMessage) {
            // Add user message to chat
            const userMessageEl = document.createElement('div');
            userMessageEl.classList.add('chat-message', 'user-message');
            userMessageEl.textContent = userMessage;
            chatMessages.appendChild(userMessageEl);
            
            // Simulate AI response (replace with actual AI integration)
            const aiMessageEl = document.createElement('div');
            aiMessageEl.classList.add('chat-message', 'ai-message');
            aiMessageEl.textContent = 'मैं आपकी सहायता कर सकता हूँ। कृपया अपना प्रश्न विस्तार से बताएं।';
            chatMessages.appendChild(aiMessageEl);
            
            // Clear input
            chatInput.value = '';
            
            // Scroll to bottom
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }
    });
});
</script>                <button class="btn btn-success" onclick="sendAIQuery()"><i class="fa-solid fa-paper-plane"></i></button>
            </form>
            <div id="aiChatResponse" class="mt-2 text-secondary small">Try: 'Show my KYC status'</div>
        </div>
        <!-- Existing Dashboard Content (Profile, Plot Details, etc.) -->
        <div class="row">
            <div class="col-md-6">
                <div class="card dashboard-card p-4">
                    <h5>Profile & Settings</h5>
                    <ul>
                        <li><b>Name:</b> <?php echo htmlspecialchars($asso_name); ?></li>
                        <li><b>Email:</b> <?php echo htmlspecialchars($asso_email); ?></li>
                        <li><b>Phone:</b> <?php echo htmlspecialchars($asso_phone); ?></li>
                        <li><b>KYC Status:</b> <?php echo htmlspecialchars($dashboard_stats['kyc_status']); ?></li>
                        <li><a href="#">Edit Profile</a></li>
                        <li><a href="#">Change Password</a></li>
                    </ul>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card dashboard-card p-4">
                    <h5>Bank Details</h5>
                    <ul>
                        <li><b>Bank Name:</b> <?php echo htmlspecialchars($bank_name); ?></li>
                        <li><b>Account Number:</b> <?php echo htmlspecialchars($account_number); ?></li>
                        <li><b>IFSC:</b> <?php echo htmlspecialchars($ifsc_code); ?></li>
                        <li><b>PAN:</b> <?php echo htmlspecialchars($pan); ?></li>
                    </ul>
                </div>
            </div>
        </div>
        <!-- AI Suggestions Panel -->
        <div class="dashboard-card p-4 bg-white rounded shadow-sm">
            <h4 class="mb-3"><i class="fa fa-magic me-2"></i>AI Suggestions & Reminders</h4>
            <div id="aiSuggestionsPanel">
                <div class="text-center text-muted">Loading personalized suggestions...</div>
            </div>
        </div>
        <!-- Export & Share -->
        <div class="dashboard-card">
            <h4><i class="fa-solid fa-share-nodes"></i> Export & Share</h4>
            <button class="btn btn-outline-secondary me-2"><i class="fa-solid fa-file-csv"></i> Export CSV</button>
            <button class="btn btn-outline-secondary me-2"><i class="fa-solid fa-file-pdf"></i> Export PDF</button>
            <button class="btn btn-outline-secondary"><i class="fa-solid fa-qrcode"></i> Share via QR</button>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    // AI Chatbot JS (simulate response)
    function sendAIQuery() {
        const input = document.getElementById('aiChatInput').value.trim();
        if (!input) return;
        document.getElementById('aiChatResponse').innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Thinking...';
        setTimeout(() => {
            document.getElementById('aiChatResponse').innerHTML = '<b>AI:</b> This is a sample AI-powered answer to: <code>' + input + '</code>';
        }, 1200);
    }
    // AI Suggestions Panel (AJAX)
    function logAIInteraction(action, suggestion, feedback, notes) {
        $.post('admin/log_ai_interaction.php', {
            action: action,
            suggestion: suggestion,
            feedback: feedback||'',
            notes: notes||''
        });
    }
    $(function(){
        $.get('user_ai_suggestions.php', function(resp) {
            if(resp.success) {
                let html = '';
                if(resp.status && resp.status.length) {
                    html += '<div class="mb-2"><b>Reminders:</b><ul>';
                    resp.status.forEach(function(rem) { html += '<li>'+rem+' <span class="badge bg-light text-dark pointer ms-1" onclick="logAIInteraction(\'feedback\', `'+rem.replace(/'/g,"&#39;")+'`,\'like\')">👍</span> <span class="badge bg-light text-dark pointer" onclick="logAIInteraction(\'feedback\', `'+rem.replace(/'/g,"&#39;")+'`,\'dislike\')">👎</span></li>'; });
                    html += '</ul></div>';
                }
                if(resp.suggestions && resp.suggestions.length) {
                    html += '<div><b>AI Suggestions:</b><ul>';
                    resp.suggestions.forEach(function(sugg) { html += '<li>'+sugg+' <span class="badge bg-light text-dark pointer ms-1" onclick="logAIInteraction(\'feedback\', `'+sugg.replace(/'/g,"&#39;")+'`,\'like\')">👍</span> <span class="badge bg-light text-dark pointer" onclick="logAIInteraction(\'feedback\', `'+sugg.replace(/'/g,"&#39;")+'`,\'dislike\')">👎</span></li>'; });
                    html += '</ul></div>';
                }
                if(!html) html = '<div class="text-success">You are all caught up!</div>';
                $('#aiSuggestionsPanel').html(html);
                if(resp.suggestions) resp.suggestions.forEach(function(sugg){ logAIInteraction('view', sugg); });
                if(resp.status) resp.status.forEach(function(rem){ logAIInteraction('view', rem); });
            } else {
                $('#aiSuggestionsPanel').html('<div class="text-danger">Could not load suggestions.</div>');
            }
        },'json');
    });
    </script>
</body>
</html>
