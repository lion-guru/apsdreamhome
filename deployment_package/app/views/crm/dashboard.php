<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'CRM Dashboard'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .crm-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .lead-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        .lead-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }
        .status-badge {
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.85em;
        }
        .status-new { background: #ffeaa7; color: #d63031; }
        .status-contacted { background: #74b9ff; color: #0984e3; }
        .status-qualified { background: #55a3ff; color: #0652dd; }
        .status-proposal { background: #fd79a8; color: #e84393; }
        .status-negotiation { background: #fdcb6e; color: #e17055; }
        .status-closed-won { background: #00b894; color: #00cec9; }
        .status-closed-lost { background: #e17055; color: #d63031; }
        .priority-high { border-left: 5px solid #e74c3c; }
        .priority-medium { border-left: 5px solid #f39c12; }
        .priority-low { border-left: 5px solid #27ae60; }
        .follow-up-alert {
            background: linear-gradient(135deg, #ff7675 0%, #fdcb6e 100%);
            color: white;
            border-radius: 10px;
            padding: 15px;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/layouts/header.php'; ?>

    <div class="container-fluid mt-4">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="crm-card p-4">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h2><i class="fas fa-users-cog me-2"></i>CRM Dashboard</h2>
                            <p class="mb-0">Manage leads, track conversions, and grow your business</p>
                        </div>
                        <div class="col-md-4 text-end">
                            <a href="<?php echo BASE_URL; ?>crm/leads/create" class="btn btn-light">
                                <i class="fas fa-plus me-2"></i>Add New Lead
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row mb-4">
            <?php if (!empty($stats['status_distribution'])): ?>
                <?php foreach ($stats['status_distribution'] as $status): ?>
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body text-center">
                                <div class="mb-2">
                                    <?php
                                    $icon_class = 'fas fa-question-circle';
                                    switch ($status['lead_status']) {
                                        case 'new': $icon_class = 'fas fa-star'; break;
                                        case 'contacted': $icon_class = 'fas fa-phone'; break;
                                        case 'qualified': $icon_class = 'fas fa-check-circle'; break;
                                        case 'proposal': $icon_class = 'fas fa-file-contract'; break;
                                        case 'negotiation': $icon_class = 'fas fa-handshake'; break;
                                        case 'closed_won': $icon_class = 'fas fa-trophy'; break;
                                        case 'closed_lost': $icon_class = 'fas fa-times-circle'; break;
                                    }
                                    ?>
                                    <i class="<?php echo $icon_class; ?> fa-2x text-primary"></i>
                                </div>
                                <h4><?php echo $status['count']; ?></h4>
                                <p class="text-muted mb-0"><?php echo ucfirst(str_replace('_', ' ', $status['lead_status'])); ?></p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Follow-up Alerts -->
        <?php if (!empty($follow_up_leads)): ?>
            <div class="row mb-4">
                <div class="col-12">
                    <div class="follow-up-alert">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
                            <div>
                                <h5 class="mb-1">Follow-up Required</h5>
                                <p class="mb-0"><?php echo count($follow_up_leads); ?> leads need follow-up attention</p>
                            </div>
                            <div class="ms-auto">
                                <a href="<?php echo BASE_URL; ?>crm/leads?status=new" class="btn btn-light btn-sm">
                                    View Leads <i class="fas fa-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Recent Leads -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-clock me-2"></i>Recent Leads</h5>
                        <a href="<?php echo BASE_URL; ?>crm/leads" class="btn btn-outline-primary btn-sm">
                            View All <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($recent_leads)): ?>
                            <div class="row">
                                <?php foreach ($recent_leads as $lead): ?>
                                    <div class="col-md-6 mb-3">
                                        <div class="card lead-card h-100 priority-<?php echo $lead['priority']; ?>">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                    <h6 class="card-title mb-0"><?php echo htmlspecialchars($lead['customer_name']); ?></h6>
                                                    <span class="status-badge status-<?php echo $lead['lead_status']; ?>">
                                                        <?php echo ucfirst(str_replace('_', ' ', $lead['lead_status'])); ?>
                                                    </span>
                                                </div>
                                                <p class="card-text text-muted mb-2">
                                                    <i class="fas fa-envelope me-2"></i><?php echo htmlspecialchars($lead['customer_email']); ?><br>
                                                    <i class="fas fa-phone me-2"></i><?php echo htmlspecialchars($lead['customer_phone']); ?>
                                                    <?php if (!empty($lead['customer_city'])): ?>
                                                        <br><i class="fas fa-map-marker-alt me-2"></i><?php echo htmlspecialchars($lead['customer_city']); ?>
                                                    <?php endif; ?>
                                                </p>
                                                <?php if (!empty($lead['property_interest'])): ?>
                                                    <p class="mb-2"><strong>Interest:</strong> <?php echo htmlspecialchars($lead['property_interest']); ?></p>
                                                <?php endif; ?>
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <small class="text-muted">
                                                        <?php echo ucfirst($lead['lead_source']); ?> • <?php echo date('M d, Y', strtotime($lead['created_at'])); ?>
                                                    </small>
                                                    <a href="<?php echo BASE_URL; ?>crm/leads/<?php echo $lead['id']; ?>" class="btn btn-primary btn-sm">
                                                        View Details
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fas fa-users fa-2x text-muted mb-2"></i>
                                <p class="text-muted">No leads yet. Start by adding your first lead!</p>
                                <a href="<?php echo BASE_URL; ?>crm/leads/create" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i>Add First Lead
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <a href="<?php echo BASE_URL; ?>crm/leads/create" class="btn btn-outline-primary w-100">
                                    <i class="fas fa-plus me-2"></i>Add New Lead
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="<?php echo BASE_URL; ?>crm/leads?status=new" class="btn btn-outline-success w-100">
                                    <i class="fas fa-star me-2"></i>View New Leads
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="<?php echo BASE_URL; ?>crm/analytics" class="btn btn-outline-warning w-100">
                                    <i class="fas fa-chart-line me-2"></i>View Analytics
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="<?php echo BASE_URL; ?>crm/leads/export" class="btn btn-outline-info w-100">
                                    <i class="fas fa-download me-2"></i>Export Leads
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
