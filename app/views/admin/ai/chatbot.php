<?php
/**
 * AI Chatbot Management View
 */
$chatbot_stats = $chatbot_stats ?? [];
$conversations = $conversations ?? [];
$page_title = $page_title ?? 'AI Chatbot Management';
$base = defined('BASE_URL') ? BASE_URL : '/apsdreamhome';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1">AI Chatbot Management</h2>
                <p class="text-muted mb-0">Customer support automation</p>
            </div>
            <a href="<?php echo $base; ?>/admin/ai/hub" class="btn btn-outline-secondary">Back to AI Hub</a>
        </div>
        
        <!-- Stats Cards -->
        <div class="row g-4 mb-4">
            <div class="col-md-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <i class="fas fa-comments fa-2x text-primary mb-2"></i>
                        <h3 class="mb-1"><?php echo $chatbot_stats['daily_conversations'] ?? 0; ?></h3>
                        <p class="text-muted mb-0">Daily Conversations</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <i class="fas fa-smile fa-2x text-success mb-2"></i>
                        <h3 class="mb-1"><?php echo $chatbot_stats['avg_satisfaction'] ?? 0; ?>/5</h3>
                        <p class="text-muted mb-0">Avg. Satisfaction</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Recent Conversations -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-history me-2"></i>Recent Conversations</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($conversations)): ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($conversations as $conv): ?>
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1"><?php echo htmlspecialchars($conv['user_name'] ?? 'Guest'); ?></h6>
                                        <p class="text-muted small mb-1"><?php echo htmlspecialchars(substr($conv['message'] ?? '', 0, 100)) . '...'; ?></p>
                                    </div>
                                    <small class="text-muted"><?php echo isset($conv['created_at']) ? date('M d, H:i', strtotime($conv['created_at'])) : '-'; ?></small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fas fa-robot fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No recent conversations</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
