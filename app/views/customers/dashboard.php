<div class="container-fluid mt-4 fade-in">
    <!-- Welcome Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-gradient-primary text-white">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h4 class="card-title mb-2">स्वागत है, <?= htmlspecialchars($customer['name']) ?>!</h4>
                            <p class="card-text mb-2">
                                <i class="fas fa-map-marker-alt me-2"></i>
                                <?= htmlspecialchars($customer['city'] ?? '') ?>, <?= htmlspecialchars($customer['state'] ?? '') ?>
                            </p>
                            <p class="card-text">
                                <i class="fas fa-calendar me-2"></i>
                                मेंबरशिप: <?= date('M Y', strtotime($customer['customer_since'] ?? $customer['created_at'])) ?> से
                                <?php if (isset($customer['occupation'])): ?>
                                    | <i class="fas fa-briefcase me-2"></i><?= htmlspecialchars($customer['occupation']) ?>
                                <?php endif; ?>
                            </p>
                        </div>
                        <div class="col-md-4 text-center">
                            <div class="customer-level">
                                <?php
                                $totalSpent = $stats['total_spent'] ?? 0;
                                $level = 'Bronze';
                                $levelColor = 'bronze';

                                if ($totalSpent >= 1000000) {
                                    $level = 'Diamond';
                                    $levelColor = 'info';
                                } elseif ($totalSpent >= 500000) {
                                    $level = 'Gold';
                                    $levelColor = 'warning';
                                } elseif ($totalSpent >= 200000) {
                                    $level = 'Silver';
                                    $levelColor = 'silver';
                                }
                                ?>
                                <span class="badge bg-<?= $levelColor ?> p-2">
                                    <i class="fas fa-crown me-1"></i><?= $level ?> Member
                                </span>
                                <br>
                                <small class="text-white-50 mt-1 d-block">
                                    कुल खर्च: ₹<?= number_format($totalSpent) ?>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-start border-primary border-3 shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col me-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">फेवरिट प्रॉपर्टीज</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= $stats['total_favorites'] ?? 0 ?>
                            </div>
                            <small class="text-success">
                                <i class="fas fa-heart"></i> सेव्ड लिस्टिंग्स
                            </small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-heart fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-start border-success border-3 shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col me-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">टोटल बुकिंग्स</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= $stats['total_bookings'] ?? 0 ?>
                            </div>
                            <small class="text-info">
                                <i class="fas fa-calendar-check"></i> विजिट शेड्यूल्ड
                            </small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-check fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">टोटल पेमेंट्स</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                ₹<?= number_format($stats['total_spent'] ?? 0) ?>
                            </div>
                            <small class="text-warning">
                                <i class="fas fa-money-bill-wave"></i> कुल खर्च
                            </small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-money-bill-wave fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">रिसेंट एक्टिविटीज</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= $stats['recent_activities'] ?? 0 ?>
                            </div>
                            <small class="text-muted">
                                <i class="fas fa-clock"></i> पिछले 7 दिनों में
                            </small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Row -->
    <div class="row">
        <!-- Recent Activities -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-history mr-2"></i>रिसेंट एक्टिविटीज
                    </h6>
                    <a href="<?= BASE_URL ?>customer/property-views" class="btn btn-sm btn-outline-primary">सभी देखें</a>
                </div>
                <div class="card-body">
                    <?php if (!empty($recent_activities)): ?>
                        <?php foreach ($recent_activities as $activity): ?>
                            <div class="d-flex align-items-center mb-3">
                                <div class="mr-3">
                                    <?php
                                    $activityIcon = 'circle';
                                    $activityColor = 'secondary';

                                    switch ($activity['activity_type']) {
                                        case 'property_view':
                                            $activityIcon = 'eye';
                                            $activityColor = 'info';
                                            break;
                                        case 'favorite_added':
                                            $activityIcon = 'heart';
                                            $activityColor = 'danger';
                                            break;
                                        case 'booking_made':
                                            $activityIcon = 'calendar-check';
                                            $activityColor = 'success';
                                            break;
                                        case 'payment_made':
                                            $activityIcon = 'money-bill-wave';
                                            $activityColor = 'warning';
                                            break;
                                    }
                                    ?>
                                    <div class="icon-circle bg-<?= $activityColor ?>">
                                        <i class="fas fa-<?= $activityIcon ?> text-white"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="small font-weight-bold text-gray-900">
                                        <?= htmlspecialchars($activity['property_title']) ?>
                                    </div>
                                    <div class="small text-gray-500">
                                        <?= ucfirst(str_replace('_', ' ', $activity['activity_type'])) ?>
                                        <?php if (isset($activity['time_spent_seconds']) && $activity['time_spent_seconds'] > 0): ?>
                                            • <?= floor($activity['time_spent_seconds'] / 60) ?>m <?= $activity['time_spent_seconds'] % 60 ?>s
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="text-xs text-gray-500">
                                    <?= date('M d', strtotime($activity['activity_date'])) ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-center text-gray-500 mb-0">कोई रिसेंट एक्टिविटी नहीं</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-bolt mr-2"></i>क्विक एक्शन
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="<?= BASE_URL ?>customer/properties" class="btn btn-outline-primary">
                            <i class="fas fa-search mr-2"></i>प्रॉपर्टी सर्च करें
                        </a>
                        <a href="<?= BASE_URL ?>customer/favorites" class="btn btn-outline-danger">
                            <i class="fas fa-heart mr-2"></i>मेरे फेवरिट्स
                        </a>
                        <a href="<?= BASE_URL ?>customer/bookings" class="btn btn-outline-success">
                            <i class="fas fa-calendar mr-2"></i>मेरी बुकिंग्स
                        </a>
                        <a href="<?= BASE_URL ?>customer/payments" class="btn btn-outline-info">
                            <i class="fas fa-credit-card mr-2"></i>मेरे पेमेंट्स
                        </a>
                        <a href="<?= BASE_URL ?>customer/alerts" class="btn btn-outline-warning">
                            <i class="fas fa-bell mr-2"></i>प्रॉपर्टी अलर्ट्स
                        </a>
                        <a href="<?= BASE_URL ?>customer/emi-calculator" class="btn btn-outline-secondary">
                            <i class="fas fa-calculator mr-2"></i>EMI कैल्कुलेटर
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Property Recommendations -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-thumbs-up mr-2"></i>आपके लिए रेकमेंडेड
                    </h6>
                    <a href="<?= BASE_URL ?>customer/properties" class="btn btn-sm btn-outline-primary">सभी देखें</a>
                </div>
                <div class="card-body">
                    <?php if (!empty($recommendations)): ?>
                        <?php foreach ($recommendations as $property): ?>
                            <div class="property-card-small mb-3">
                                <div class="row no-gutters">
                                    <div class="col-md-4">
                                        <img src="<?= $property['main_image'] ?? BASE_URL . 'assets/images/no-image.jpg' ?>"
                                            class="img-fluid rounded" alt="Property" style="height: 80px; object-fit: cover;">
                                    </div>
                                    <div class="col-md-8">
                                        <div class="property-info">
                                            <h6 class="property-title mb-1">
                                                <a href="<?= BASE_URL ?>customer/property/<?= $property['id'] ?>" class="text-decoration-none">
                                                    <?= htmlspecialchars($property['title']) ?>
                                                </a>
                                            </h6>
                                            <p class="property-location small text-muted mb-1">
                                                <i class="fas fa-map-marker-alt mr-1"></i>
                                                <?= htmlspecialchars($property['city']) ?>
                                            </p>
                                            <p class="property-price mb-0">
                                                <strong class="text-primary">₹<?= number_format($property['price']) ?></strong>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-home fa-3x text-muted mb-2"></i>
                            <p class="text-muted mb-0">कोई रेकमेंडेशन नहीं मिला</p>
                            <a href="<?= BASE_URL ?>customer/properties" class="btn btn-primary btn-sm mt-2">
                                प्रॉपर्टी सर्च करें
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Customer Progress -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-line mr-2"></i>आपकी प्रोग्रेस
                    </h6>
                </div>
                <div class="card-body">
                    <?php
                    $totalSpent = $stats['total_spent'] ?? 0;
                    $nextLevel = 200000; // Silver level
                    $progress = min(($totalSpent / $nextLevel) * 100, 100);

                    if ($totalSpent >= 200000 && $totalSpent < 500000) {
                        $nextLevel = 500000; // Gold level
                        $progress = (($totalSpent - 200000) / 300000) * 100;
                    } elseif ($totalSpent >= 500000 && $totalSpent < 1000000) {
                        $nextLevel = 1000000; // Diamond level
                        $progress = (($totalSpent - 500000) / 500000) * 100;
                    } elseif ($totalSpent >= 1000000) {
                        $progress = 100;
                    }
                    ?>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <small>नेक्स्ट लेवल प्रोग्रेस</small>
                            <small><?= number_format($progress, 1) ?>%</small>
                        </div>
                        <div class="progress mb-2">
                            <div class="progress-bar bg-primary" role="progressbar"
                                style="width: <?= $progress ?>%"></div>
                        </div>
                        <small class="text-muted">
                            ₹<?= number_format($totalSpent) ?> / ₹<?= number_format($nextLevel) ?>
                        </small>
                    </div>

                    <div class="text-center">
                        <?php if ($totalSpent >= 1000000): ?>
                            <span class="badge badge-info">
                                <i class="fas fa-crown mr-1"></i>Diamond Member
                            </span>
                        <?php elseif ($totalSpent >= 500000): ?>
                            <span class="badge badge-warning">
                                <i class="fas fa-star mr-1"></i>Gold Member
                            </span>
                        <?php elseif ($totalSpent >= 200000): ?>
                            <span class="badge badge-secondary">
                                <i class="fas fa-medal mr-1"></i>Silver Member
                            </span>
                        <?php else: ?>
                            <span class="badge badge-bronze">
                                <i class="fas fa-award mr-1"></i>Bronze Member
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Bookings & Favorites -->
        <div class="col-lg-4 mb-4">
            <!-- Recent Bookings -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-calendar-check mr-2"></i>रिसेंट बुकिंग्स
                    </h6>
                    <a href="<?= BASE_URL ?>customer/bookings" class="btn btn-sm btn-outline-primary">सभी देखें</a>
                </div>
                <div class="card-body">
                    <?php if (!empty($recent_bookings)): ?>
                        <?php foreach ($recent_bookings as $booking): ?>
                            <div class="booking-item mb-3">
                                <div class="d-flex align-items-center">
                                    <div class="mr-3">
                                        <img src="<?= $booking['main_image'] ?? BASE_URL . 'assets/images/no-image.jpg' ?>"
                                            class="rounded" alt="Property" style="width: 50px; height: 50px; object-fit: cover;">
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="font-weight-bold small">
                                            <?= htmlspecialchars($booking['title']) ?>
                                        </div>
                                        <div class="small text-muted">
                                            <?= date('M d, Y', strtotime($booking['booking_date'])) ?>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <?php
                                        $status = $booking['status'] ?? 'pending';
                                        $statusClass = 'warning';
                                        if ($status === 'confirmed') $statusClass = 'success';
                                        if ($status === 'cancelled') $statusClass = 'danger';
                                        ?>
                                        <span class="badge badge-<?= $statusClass ?> badge-sm">
                                            <?= ucfirst($status) ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-3">
                            <i class="fas fa-calendar fa-2x text-muted mb-2"></i>
                            <p class="text-muted mb-0 small">कोई बुकिंग नहीं मिली</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Favorite Properties -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-heart mr-2"></i>मेरे फेवरिट्स
                    </h6>
                    <a href="<?= BASE_URL ?>customer/favorites" class="btn btn-sm btn-outline-primary">सभी देखें</a>
                </div>
                <div class="card-body">
                    <?php if (!empty($favorite_properties)): ?>
                        <?php foreach ($favorite_properties as $property): ?>
                            <div class="favorite-item mb-3">
                                <div class="d-flex align-items-center">
                                    <div class="mr-3">
                                        <img src="<?= $property['main_image'] ?? BASE_URL . 'assets/images/no-image.jpg' ?>"
                                            class="rounded" alt="Property" style="width: 50px; height: 50px; object-fit: cover;">
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="font-weight-bold small">
                                            <?= htmlspecialchars($property['title']) ?>
                                        </div>
                                        <div class="small text-muted">
                                            ₹<?= number_format($property['price']) ?>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <a href="<?= BASE_URL ?>customer/property/<?= $property['id'] ?>"
                                            class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-3">
                            <i class="fas fa-heart fa-2x text-muted mb-2"></i>
                            <p class="text-muted mb-0 small">कोई फेवरिट नहीं मिला</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Customer Insights -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-lightbulb mr-2"></i>कस्टमर इनसाइट्स
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="insight-item">
                                <h6 class="text-success">
                                    <i class="fas fa-arrow-up mr-2"></i>आपकी प्रेफरेंसेस
                                </h6>
                                <ul class="list-unstyled">
                                    <li><i class="fas fa-check text-success mr-2"></i>प्रॉपर्टी व्यूज: <?= $stats['properties_viewed_month'] ?? 0 ?> इस महीने</li>
                                    <li><i class="fas fa-check text-success mr-2"></i>ऐवरेज रेटिंग: <?= $stats['avg_rating_given'] ?? 0 ?>/5</li>
                                    <li><i class="fas fa-check text-success mr-2"></i>रिव्यूज दिए: <?= $stats['total_reviews'] ?? 0 ?></li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="insight-item">
                                <h6 class="text-info">
                                    <i class="fas fa-info-circle mr-2"></i>रेकेमेंडेशन्स
                                </h6>
                                <ul class="list-unstyled">
                                    <li><i class="fas fa-circle text-info mr-2"></i>आपकी प्रेफरेंसेस के आधार पर रेकमेंडेड प्रॉपर्टीज</li>
                                    <li><i class="fas fa-circle text-info mr-2"></i>सेव्ड सर्चेस के आधार पर सजेस्टेड</li>
                                    <li><i class="fas fa-circle text-info mr-2"></i>सिमिलर प्रॉपर्टीज आपके इंटरेस्ट में</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-12">
                            <div class="alert alert-info">
                                <h6><i class="fas fa-gift mr-2"></i>स्पेशल ऑफर्स आपके लिए</h6>
                                <div class="row mt-2">
                                    <div class="col-md-4">
                                        <strong>पहली बुकिंग डिस्काउंट:</strong> 10% ऑफ
                                    </div>
                                    <div class="col-md-4">
                                        <strong>फ्री कंसल्टेशन:</strong> प्रॉपर्टी एक्सपर्ट से
                                    </div>
                                    <div class="col-md-4">
                                        <strong>EMI कैल्कुलेशन:</strong> फ्री सर्विस
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

<style>
    .bg-gradient-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    .customer-level .badge {
        font-size: 1.1em;
        padding: 0.5em 1em;
    }

    .icon-circle {
        height: 2.5rem;
        width: 2.5rem;
        border-radius: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .card {
        border-radius: 10px;
        border: none;
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.08);
    }

    .card-header {
        border-radius: 10px 10px 0 0 !important;
        border-bottom: 2px solid rgba(0, 0, 0, 0.1);
    }

    .property-card-small {
        border: 1px solid #e3e6f0;
        border-radius: 8px;
        padding: 10px;
        transition: all 0.3s ease;
    }

    .property-card-small:hover {
        border-color: #667eea;
        box-shadow: 0 2px 8px rgba(102, 126, 234, 0.2);
    }
</style>

<?php require_once 'app/views/layouts/customer_footer.php'; ?>