<?php
/**
 * AI Property Recommendations View
 */
$recommendations = $recommendations ?? [];
$customer_segments = $customer_segments ?? [];
$page_title = $page_title ?? 'AI Property Recommendations';
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
                <h2 class="mb-1">AI Property Recommendations</h2>
                <p class="text-muted mb-0">Smart property matching system</p>
            </div>
            <a href="<?php echo $base; ?>/admin/ai/hub" class="btn btn-outline-secondary">Back to AI Hub</a>
        </div>
        
        <!-- Recommendations -->
        <div class="row">
            <?php if (!empty($recommendations)): ?>
                <?php foreach ($recommendations as $rec): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h5 class="card-title mb-0"><?php echo htmlspecialchars($rec['title'] ?? 'Property'); ?></h5>
                                    <span class="badge bg-success"><?php echo $rec['match_score'] ?? 0; ?>% Match</span>
                                </div>
                                <p class="text-muted small mb-2"><i class="fas fa-map-marker-alt me-1"></i><?php echo htmlspecialchars($rec['location'] ?? '-'); ?></p>
                                <p class="fw-bold text-primary mb-2">₹<?php echo number_format($rec['price'] ?? 0); ?></p>
                                <div class="progress mb-2" style="height: 8px;">
                                    <div class="progress-bar bg-success" style="width: <?php echo $rec['confidence_score'] ?? 0; ?>%"></div>
                                </div>
                                <small class="text-muted">Confidence: <?php echo $rec['confidence_score'] ?? 0; ?>%</small>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        No property recommendations available. AI will generate recommendations based on customer preferences.
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Customer Segments -->
        <div class="card border-0 shadow-sm mt-4">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-users me-2"></i>Customer Segments</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($customer_segments)): ?>
                    <div class="row">
                        <?php foreach ($customer_segments as $segment): ?>
                            <div class="col-md-3 mb-3">
                                <div class="p-3 bg-light rounded">
                                    <h6 class="mb-1"><?php echo htmlspecialchars($segment['segment_name'] ?? 'Segment'); ?></h6>
                                    <p class="text-muted small mb-0"><?php echo htmlspecialchars($segment['description'] ?? ''); ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-muted mb-0">Customer segments will be generated automatically by AI analysis.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
