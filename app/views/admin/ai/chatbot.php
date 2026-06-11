<?php
$chatbot_stats = $chatbot_stats ?? [];
$conversations = $conversations ?? [];
$page_title = $page_title ?? 'AI Chatbot Management';
$base = defined('BASE_URL') ? BASE_URL : '/apsdreamhome';
?>
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
            <div class="card-body aps-cp-card-body">
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
