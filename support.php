<?php
// Start session and include configuration
require_once 'includes/config/config.php';
require_once 'includes/functions.php';

// Set page title
$page_title = "Customer Support - APS Dream Home";

// Include header
include 'includes/header.php';

// Check if user is logged in
if (!isset($_SESSION['customer_logged_in']) || $_SESSION['customer_logged_in'] !== true) {
    header('Location: customer_login.php');
    exit();
}

// Handle form submission
$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject = filter_input(INPUT_POST, 'subject', FILTER_SANITIZE_STRING);
    $message = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING);
    $priority = filter_input(INPUT_POST, 'priority', FILTER_SANITIZE_STRING);
    
    if (empty($subject) || empty($message)) {
        $error = 'Please fill in all required fields.';
    } else {
        // Insert ticket into database
        $stmt = $conn->prepare("INSERT INTO support_tickets (customer_id, subject, message, priority, status, created_at) VALUES (?, ?, ?, ?, 'Open', NOW())");
        $stmt->bind_param("isss", $_SESSION['customer_id'], $subject, $message, $priority);
        
        if ($stmt->execute()) {
            $success = true;
            // Reset form
            $subject = $message = '';
            $priority = 'medium';
        } else {
            $error = 'Failed to submit your request. Please try again.';
            error_log("Support ticket submission failed: " . $stmt->error);
        }
        $stmt->close();
    }
}
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-lg border-0 rounded-lg">
                <div class="card-header bg-primary text-white">
                    <h3 class="text-center font-weight-light my-4">Customer Support</h3>
                </div>
                <div class="card-body">
                    <?php if ($success): ?>
                        <div class="alert alert-success" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            Your support request has been submitted successfully. We'll get back to you soon!
                        </div>
                    <?php elseif ($error): ?>
                        <div class="alert alert-danger" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>
                    
                    <form action="support.php" method="post" id="supportForm">
                        <div class="mb-3">
                            <label for="subject" class="form-label">Subject <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-lg" id="subject" name="subject" required 
                                   value="<?php echo isset($subject) ? htmlspecialchars($subject) : ''; ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label for="priority" class="form-label">Priority</label>
                            <select class="form-select form-select-lg" id="priority" name="priority">
                                <option value="low" <?php echo (isset($priority) && $priority === 'low') ? 'selected' : ''; ?>>Low</option>
                                <option value="medium" <?php echo (!isset($priority) || $priority === 'medium') ? 'selected' : ''; ?>>Medium</option>
                                <option value="high" <?php echo (isset($priority) && $priority === 'high') ? 'selected' : ''; ?>>High</option>
                                <option value="urgent" <?php echo (isset($priority) && $priority === 'urgent') ? 'selected' : ''; ?>>Urgent</option>
                            </select>
                        </div>
                        
                        <div class="mb-4">
                            <label for="message" class="form-label">Message <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="message" name="message" rows="6" required><?php echo isset($message) ? htmlspecialchars($message) : ''; ?></textarea>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-paper-plane me-2"></i>Submit Request
                            </button>
                        </div>
                    </form>
                </div>
                <div class="card-footer text-center py-3">
                    <div class="small">
                        Need immediate assistance? Call us at <a href="tel:+911234567890">+91 12345 67890</a> or 
                        <a href="mailto:support@apsdreamhome.com">email us</a>.
                    </div>
                </div>
            </div>
            
            <!-- Support Tickets History -->
            <div class="card shadow-sm border-0 rounded-lg mt-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Your Support Tickets</h5>
                </div>
                <div class="card-body">
                    <?php
                    // Fetch user's support tickets
                    $tickets_query = "SELECT * FROM support_tickets WHERE customer_id = ? ORDER BY created_at DESC LIMIT 5";
                    $stmt = $conn->prepare($tickets_query);
                    $stmt->bind_param("i", $_SESSION['customer_id']);
                    $stmt->execute();
                    $tickets_result = $stmt->get_result();
                    
                    if ($tickets_result->num_rows > 0):
                        while ($ticket = $tickets_result->fetch_assoc()):
                            $status_class = '';
                            switch (strtolower($ticket['status'])) {
                                case 'open':
                                    $status_class = 'bg-primary';
                                    break;
                                case 'in progress':
                                    $status_class = 'bg-warning';
                                    break;
                                case 'resolved':
                                    $status_class = 'bg-success';
                                    break;
                                case 'closed':
                                    $status_class = 'bg-secondary';
                                    break;
                                default:
                                    $status_class = 'bg-info';
                            }
                    ?>
                            <div class="border-bottom pb-3 mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <h6 class="mb-0">
                                        <a href="view_ticket.php?id=<?php echo $ticket['id']; ?>" class="text-decoration-none">
                                            <?php echo htmlspecialchars($ticket['subject']); ?>
                                        </a>
                                    </h6>
                                    <span class="badge <?php echo $status_class; ?> rounded-pill">
                                        <?php echo ucfirst($ticket['status']); ?>
                                    </span>
                                </div>
                                <p class="text-muted small mb-2">
                                    <?php echo date('M d, Y h:i A', strtotime($ticket['created_at'])); ?>
                                    <span class="mx-2">•</span>
                                    Priority: <?php echo ucfirst($ticket['priority']); ?>
                                </p>
                                <p class="mb-0 text-truncate">
                                    <?php echo substr(htmlspecialchars($ticket['message']), 0, 150); ?>
                                    <?php if (strlen($ticket['message']) > 150): ?>...<?php endif; ?>
                                </p>
                            </div>
                    <?php 
                        endwhile; 
                    else: 
                    ?>
                        <div class="text-center py-4">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <p class="mb-0">You haven't submitted any support tickets yet.</p>
                        </div>
                    <?php 
                    endif; 
                    $stmt->close();
                    ?>
                    
                    <div class="text-end mt-3">
                        <a href="support_tickets.php" class="btn btn-outline-primary btn-sm">
                            View All Tickets <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Include TinyMCE for rich text editor -->
<script src="https://cdn.tiny.cloud/1/YOUR_API_KEY/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
    tinymce.init({
        selector: '#message',
        plugins: 'link lists help',
        toolbar: 'undo redo | formatselect | bold italic backcolor | \
                  alignleft aligncenter alignright alignjustify | \
                  bullist numlist outdent indent | removeformat | help',
        menubar: false,
        statusbar: false,
        height: 200,
        content_style: 'body { font-family: "Inter", sans-serif; font-size: 16px; }',
        setup: function(editor) {
            editor.on('change', function() {
                editor.save();
            });
        }
    });
    
    // Form validation
    document.getElementById('supportForm').addEventListener('submit', function(e) {
        const subject = document.getElementById('subject').value.trim();
        const message = tinymce.get('message').getContent().trim();
        
        if (!subject) {
            e.preventDefault();
            alert('Please enter a subject for your support request.');
            document.getElementById('subject').focus();
            return false;
        }
        
        if (!message) {
            e.preventDefault();
            alert('Please enter your message.');
            tinymce.get('message').focus();
            return false;
        }
        
        // Show loading state
        const submitBtn = this.querySelector('button[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Submitting...';
    });
</script>

<?php
// Include footer
include 'includes/footer.php';
?>
