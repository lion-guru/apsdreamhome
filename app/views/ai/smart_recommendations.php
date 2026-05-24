<?php
/**
 * AI Smart Recommendations View
 */
$recommendations = $recommendations ?? [];
$recommendation_engine = $recommendation_engine ?? [];
$page_title = $page_title ?? 'Smart Property Recommendations - APS Dream Home';
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
    <style>
        .recommendation-card {
            transition: transform 0.2s;
            border: 1px solid #e0e0e0;
        }
        .recommendation-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .match-score {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(13, 110, 253, 0.9);
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: bold;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1"><i class="fas fa-magic me-2 text-primary"></i>Smart Recommendations</h2>
                <p class="text-muted mb-0">AI-powered property suggestions based on your preferences</p>
            </div>
            <a href="<?php echo $base; ?>/customer/dashboard" class="btn btn-outline-secondary">My Dashboard</a>
        </div>

        <!-- Engine Info -->
        <?php if (!empty($recommendation_engine)): ?>
            <div class="alert alert-info mb-4">
                <i class="fas fa-info-circle me-2"></i>
                <strong><?php echo htmlspecialchars($recommendation_engine['name'] ?? 'AI Engine'); ?></strong> - 
                <?php echo htmlspecialchars($recommendation_engine['description'] ?? 'Analyzing your preferences'); ?>
            </div>
        <?php endif; ?>

        <!-- Recommendations Grid -->
        <?php if (!empty($recommendations)): ?>
            <div class="row g-4">
                <?php foreach ($recommendations as $property): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card recommendation-card h-100 position-relative">
                            <span class="match-score"><?php echo $property['match_score'] ?? 95; ?>% Match</span>
                            <img src="<?php echo $property['image'] ?? '/assets/images/property-placeholder.jpg'; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($property['title'] ?? ''); ?>" style="height: 200px; object-fit: cover;">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($property['title'] ?? 'Property'); ?></h5>
                                <p class="text-muted mb-2"><i class="fas fa-map-marker-alt me-1"></i><?php echo htmlspecialchars($property['location'] ?? 'Location N/A'); ?></p>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="text-primary fw-bold">₹<?php echo number_format($property['price'] ?? 0); ?></span>
                                    <span class="badge bg-secondary"><?php echo htmlspecialchars($property['type'] ?? 'N/A'); ?></span>
                                </div>
                                <div class="mb-3">
                                    <small class="text-muted">
                                        <i class="fas fa-bed me-1"></i><?php echo $property['bedrooms'] ?? 0; ?> BHK
                                        <span class="mx-2">|</span>
                                        <i class="fas fa-ruler-combined me-1"></i><?php echo $property['area'] ?? 0; ?> sq ft
                                    </small>
                                </div>
                                <p class="card-text small text-muted"><?php echo htmlspecialchars(substr($property['description'] ?? '', 0, 100)); ?>...</p>
                                <div class="d-grid">
                                    <a href="<?php echo $base; ?>/properties/<?php echo $property['id']; ?>" class="btn btn-outline-primary">
                                        <i class="fas fa-eye me-2"></i>View Details
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5">
                    <i class="fas fa-search fa-3x text-muted mb-3"></i>
                    <h4>No Recommendations Yet</h4>
                    <p class="text-muted">Start browsing properties or update your preferences to get personalized recommendations.</p>
                    <div class="d-flex justify-content-center gap-2">
                        <a href="<?php echo $base; ?>/properties" class="btn btn-primary">Browse Properties</a>
                        <a href="<?php echo $base; ?>/customer/settings" class="btn btn-outline-secondary">Update Preferences</a>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- How It Works -->
        <div class="card border-0 shadow-sm mt-5">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-cogs me-2"></i>How AI Recommendations Work</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 text-center">
                        <i class="fas fa-user-circle fa-2x text-primary mb-2"></i>
                        <h6>Your Profile</h6>
                        <small class="text-muted">Analyzing your preferences and history</small>
                    </div>
                    <div class="col-md-3 text-center">
                        <i class="fas fa-brain fa-2x text-success mb-2"></i>
                        <h6>AI Analysis</h6>
                        <small class="text-muted">Machine learning algorithms process data</small>
                    </div>
                    <div class="col-md-3 text-center">
                        <i class="fas fa-filter fa-2x text-info mb-2"></i>
                        <h6>Smart Filtering</h6>
                        <small class="text-muted">Matching properties to your needs</small>
                    </div>
                    <div class="col-md-3 text-center">
                        <i class="fas fa-thumbs-up fa-2x text-warning mb-2"></i>
                        <h6>Ranked Results</h6>
                        <small class="text-muted">Best matches shown first</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
