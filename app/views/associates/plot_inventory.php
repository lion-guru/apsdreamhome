<?php
/**
 * Associate Plot Inventory Dashboard
 * Complete interface for associates to manage their plot inventory
 */
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'Plot Inventory - Associate Dashboard'; ?></title>

    <!-- Modern CSS Framework -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <!-- Custom CSS -->
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --success-gradient: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            --warning-gradient: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);
            --info-gradient: linear-gradient(135deg, #17a2b8 0%, #20c997 100%);
        }

        body {
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }

        /* Modern Cards */
        .inventory-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.8);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .inventory-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }

        .card-header-modern {
            background: var(--primary-gradient);
            color: white;
            padding: 2rem;
            border-radius: 20px 20px 0 0;
        }

        .card-body-modern {
            padding: 2rem;
        }

        /* Plot Cards */
        .plot-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            border: 2px solid transparent;
            margin-bottom: 1.5rem;
        }

        .plot-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
            border-color: #667eea;
        }

        .plot-image {
            height: 200px;
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #666;
            font-size: 3rem;
        }

        .plot-content {
            padding: 1.5rem;
        }

        .plot-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: #1a237e;
            margin-bottom: 0.5rem;
        }

        .plot-location {
            color: #667eea;
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }

        .plot-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .plot-detail {
            text-align: center;
            padding: 0.5rem;
            background: #f8f9fa;
            border-radius: 8px;
        }

        .plot-detail-label {
            font-size: 0.8rem;
            color: #666;
            margin-bottom: 0.25rem;
        }

        .plot-detail-value {
            font-size: 1rem;
            font-weight: 700;
            color: #1a237e;
        }

        .plot-actions {
            display: flex;
            gap: 0.5rem;
            margin-top: 1rem;
        }

        .btn-plot-action {
            flex: 1;
            padding: 0.5rem 1rem;
            border-radius: 25px;
            font-size: 0.9rem;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-book {
            background: var(--success-gradient);
            color: white;
        }

        .btn-book:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
        }

        .btn-calculate {
            background: var(--primary-gradient);
            color: white;
        }

        .btn-calculate:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }

        /* Summary Cards */
        .summary-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .summary-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }

        .summary-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            color: white;
            margin: 0 auto 1rem;
        }

        .summary-number {
            font-size: 2rem;
            font-weight: 800;
            color: #1a237e;
            margin-bottom: 0.5rem;
        }

        .summary-label {
            font-size: 0.9rem;
            color: #666;
            font-weight: 600;
        }

        /* Status Badges */
        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-available {
            background: var(--success-gradient);
            color: white;
        }

        .status-allocated {
            background: var(--warning-gradient);
            color: white;
        }

        .status-sold {
            background: var(--info-gradient);
            color: white;
        }

        /* Tabs */
        .nav-tabs-modern {
            border-bottom: 2px solid #e9ecef;
            margin-bottom: 2rem;
        }

        .nav-tabs-modern .nav-link {
            border: none;
            background: none;
            color: #666;
            font-weight: 600;
            padding: 1rem 2rem;
            border-bottom: 3px solid transparent;
            transition: all 0.3s ease;
        }

        .nav-tabs-modern .nav-link.active,
        .nav-tabs-modern .nav-link:hover {
            color: #667eea;
            border-bottom-color: #667eea;
            background: rgba(102, 126, 234, 0.1);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .plot-details {
                grid-template-columns: repeat(2, 1fr);
                gap: 0.5rem;
            }

            .plot-actions {
                flex-direction: column;
            }

            .btn-plot-action {
                font-size: 0.8rem;
                padding: 0.75rem 1rem;
            }
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fade-up {
            animation: fadeInUp 0.8s ease-out;
        }

        /* Loading States */
        .loading-shimmer {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: shimmer 1.5s infinite;
        }

        @keyframes shimmer {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }
    </style>
</head>
<body>

<?php include __DIR__ . '/layouts/associate_header.php'; ?>

<!-- Page Header -->
<section class="py-5" style="background: var(--primary-gradient);">
    <div class="container">
        <div class="row justify-content-center text-center">
            <div class="col-lg-8">
                <h1 class="text-white mb-3">
                    <i class="fas fa-inventory me-3"></i>
                    Plot Inventory Dashboard
                </h1>
                <p class="text-white-50">
                    Manage your allocated plots, track sales, and grow your business
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Summary Cards -->
<section class="py-4">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-3 col-md-6">
                <div class="summary-card animate-fade-up">
                    <div class="summary-icon" style="background: var(--primary-gradient);">
                        <i class="fas fa-home"></i>
                    </div>
                    <div class="summary-number"><?php echo $summary['allocated_plots']; ?></div>
                    <div class="summary-label">Allocated Plots</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="summary-card animate-fade-up" style="animation-delay: 0.1s;">
                    <div class="summary-icon" style="background: var(--success-gradient);">
                        <i class="fas fa-search"></i>
                    </div>
                    <div class="summary-number"><?php echo $summary['available_for_allocation']; ?></div>
                    <div class="summary-label">Available for Allocation</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="summary-card animate-fade-up" style="animation-delay: 0.2s;">
                    <div class="summary-icon" style="background: var(--warning-gradient);">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="summary-number"><?php echo $summary['total_sold']; ?></div>
                    <div class="summary-label">Total Sold</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="summary-card animate-fade-up" style="animation-delay: 0.3s;">
                    <div class="summary-icon" style="background: var(--info-gradient);">
                        <i class="fas fa-coins"></i>
                    </div>
                    <div class="summary-number">₹<?php echo number_format($summary['total_commission']); ?></div>
                    <div class="summary-label">Total Commission</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Main Content -->
<section class="py-4">
    <div class="container">
        <!-- Navigation Tabs -->
        <ul class="nav nav-tabs nav-tabs-modern justify-content-center mb-4" id="inventoryTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="allocated-tab" data-bs-toggle="tab" data-bs-target="#allocated" type="button" role="tab">
                    <i class="fas fa-user-check me-2"></i>My Allocated Plots (<?php echo count($allocated_plots); ?>)
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="available-tab" data-bs-toggle="tab" data-bs-target="#available" type="button" role="tab">
                    <i class="fas fa-search me-2"></i>Available Plots (<?php echo count($available_plots); ?>)
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="sold-tab" data-bs-toggle="tab" data-bs-target="#sold" type="button" role="tab">
                    <i class="fas fa-chart-line me-2"></i>Sales History (<?php echo count($sold_plots); ?>)
                </button>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content" id="inventoryTabContent">
            <!-- Allocated Plots Tab -->
            <div class="tab-pane fade show active" id="allocated" role="tabpanel">
                <?php if (empty($allocated_plots)): ?>
                    <div class="inventory-card">
                        <div class="card-body-modern text-center py-5">
                            <i class="fas fa-home fa-4x text-muted mb-4"></i>
                            <h4 class="text-muted">No Allocated Plots</h4>
                            <p class="text-muted mb-4">You don't have any plots allocated yet. Contact your upline or admin to get plots allocated.</p>
                            <a href="/associate/request-allocation" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>Request Plot Allocation
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="row">
                        <?php foreach ($allocated_plots as $plot): ?>
                            <div class="col-lg-4 col-md-6 mb-4">
                                <div class="plot-card animate-fade-up">
                                    <div class="plot-image">
                                        <i class="fas fa-home"></i>
                                    </div>
                                    <div class="plot-content">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h5 class="plot-title">Plot #<?php echo htmlspecialchars($plot['plot_number']); ?></h5>
                                            <span class="status-badge status-allocated">Allocated</span>
                                        </div>

                                        <div class="plot-location">
                                            <i class="fas fa-map-marker-alt me-1"></i>
                                            <?php echo htmlspecialchars($plot['colony_name'] . ', ' . $plot['colony_location']); ?>
                                        </div>

                                        <div class="plot-details">
                                            <div class="plot-detail">
                                                <div class="plot-detail-label">Area</div>
                                                <div class="plot-detail-value"><?php echo $plot['plot_area']; ?> sqft</div>
                                            </div>
                                            <div class="plot-detail">
                                                <div class="plot-detail-label">Price/sqft</div>
                                                <div class="plot-detail-value">₹<?php echo number_format($plot['price_per_sqft']); ?></div>
                                            </div>
                                            <div class="plot-detail">
                                                <div class="plot-detail-label">Total Value</div>
                                                <div class="plot-detail-value">₹<?php echo number_format($plot['plot_area'] * $plot['price_per_sqft']); ?></div>
                                            </div>
                                            <div class="plot-detail">
                                                <div class="plot-detail-label">Colony Status</div>
                                                <div class="plot-detail-value"><?php echo $plot['available_plots'] . '/' . $plot['total_plots']; ?></div>
                                            </div>
                                        </div>

                                        <div class="plot-actions">
                                            <button class="btn-plot-action btn-book" onclick="bookPlot(<?php echo $plot['id']; ?>)">
                                                <i class="fas fa-shopping-cart me-1"></i>Book Plot
                                            </button>
                                            <button class="btn-plot-action btn-calculate" onclick="calculateCommission(<?php echo $plot['plot_area'] * $plot['price_per_sqft']; ?>, <?php echo $plot['plot_area']; ?>)">
                                                <i class="fas fa-calculator me-1"></i>Commission
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Available Plots Tab -->
            <div class="tab-pane fade" id="available" role="tabpanel">
                <?php if (empty($available_plots)): ?>
                    <div class="inventory-card">
                        <div class="card-body-modern text-center py-5">
                            <i class="fas fa-search fa-4x text-muted mb-4"></i>
                            <h4 class="text-muted">No Available Plots</h4>
                            <p class="text-muted">No plots are currently available for allocation.</p>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="row">
                        <?php foreach ($available_plots as $plot): ?>
                            <div class="col-lg-4 col-md-6 mb-4">
                                <div class="plot-card animate-fade-up">
                                    <div class="plot-image">
                                        <i class="fas fa-home"></i>
                                    </div>
                                    <div class="plot-content">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h5 class="plot-title">Plot #<?php echo htmlspecialchars($plot['plot_number']); ?></h5>
                                            <span class="status-badge status-available">Available</span>
                                        </div>

                                        <div class="plot-location">
                                            <i class="fas fa-map-marker-alt me-1"></i>
                                            <?php echo htmlspecialchars($plot['colony_name'] . ', ' . $plot['colony_location']); ?>
                                        </div>

                                        <div class="plot-details">
                                            <div class="plot-detail">
                                                <div class="plot-detail-label">Area</div>
                                                <div class="plot-detail-value"><?php echo $plot['plot_area']; ?> sqft</div>
                                            </div>
                                            <div class="plot-detail">
                                                <div class="plot-detail-label">Price/sqft</div>
                                                <div class="plot-detail-value">₹<?php echo number_format($plot['price_per_sqft']); ?></div>
                                            </div>
                                            <div class="plot-detail">
                                                <div class="plot-detail-label">Total Value</div>
                                                <div class="plot-detail-value">₹<?php echo number_format($plot['plot_area'] * $plot['price_per_sqft']); ?></div>
                                            </div>
                                            <div class="plot-detail">
                                                <div class="plot-detail-label">Colony Status</div>
                                                <div class="plot-detail-value"><?php echo $plot['available_plots'] . '/' . $plot['total_plots']; ?></div>
                                            </div>
                                        </div>

                                        <div class="plot-actions">
                                            <button class="btn-plot-action btn-calculate" onclick="calculateCommission(<?php echo $plot['plot_area'] * $plot['price_per_sqft']; ?>, <?php echo $plot['plot_area']; ?>)">
                                                <i class="fas fa-calculator me-1"></i>Commission
                                            </button>
                                            <button class="btn-plot-action btn-book" onclick="requestAllocation(<?php echo $plot['id']; ?>)">
                                                <i class="fas fa-plus me-1"></i>Request
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Sold Plots Tab -->
            <div class="tab-pane fade" id="sold" role="tabpanel">
                <?php if (empty($sold_plots)): ?>
                    <div class="inventory-card">
                        <div class="card-body-modern text-center py-5">
                            <i class="fas fa-chart-line fa-4x text-muted mb-4"></i>
                            <h4 class="text-muted">No Sales History</h4>
                            <p class="text-muted">Your sold plots will appear here once you make sales.</p>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="row">
                        <?php foreach ($sold_plots as $plot): ?>
                            <div class="col-lg-6 mb-4">
                                <div class="plot-card animate-fade-up">
                                    <div class="plot-image">
                                        <i class="fas fa-home text-success"></i>
                                    </div>
                                    <div class="plot-content">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h5 class="plot-title">Plot #<?php echo htmlspecialchars($plot['plot_number']); ?></h5>
                                            <span class="status-badge status-sold">Sold</span>
                                        </div>

                                        <div class="plot-location">
                                            <i class="fas fa-map-marker-alt me-1"></i>
                                            <?php echo htmlspecialchars($plot['colony_name'] . ', ' . $plot['colony_location']); ?>
                                        </div>

                                        <div class="plot-details">
                                            <div class="plot-detail">
                                                <div class="plot-detail-label">Sale Price</div>
                                                <div class="plot-detail-value">₹<?php echo number_format($plot['sale_price']); ?></div>
                                            </div>
                                            <div class="plot-detail">
                                                <div class="plot-detail-label">Commission</div>
                                                <div class="plot-detail-value text-success">₹<?php echo number_format($plot['commission_earned']); ?></div>
                                            </div>
                                            <div class="plot-detail">
                                                <div class="plot-detail-label">Sale Date</div>
                                                <div class="plot-detail-value"><?php echo date('d M Y', strtotime($plot['sale_date'])); ?></div>
                                            </div>
                                            <div class="plot-detail">
                                                <div class="plot-detail-label">Area</div>
                                                <div class="plot-detail-value"><?php echo $plot['plot_area']; ?> sqft</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- Commission Calculator Modal -->
<div class="modal fade" id="commissionModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background: var(--primary-gradient); color: white;">
                <h5 class="modal-title">
                    <i class="fas fa-calculator me-2"></i>
                    Commission Calculator
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Plot Details</h6>
                        <p id="plotDetails">Loading...</p>
                    </div>
                    <div class="col-md-6">
                        <h6>Commission Breakdown</h6>
                        <div id="commissionBreakdown">
                            <div class="text-center py-3">
                                <div class="loading-shimmer" style="height: 100px; border-radius: 10px;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/layouts/associate_footer.php'; ?>

<!-- JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

<script>
    // Initialize AOS
    AOS.init({
        duration: 800,
        easing: 'ease-in-out',
        once: true
    });

    // Commission Calculator
    function calculateCommission(plotPrice, plotArea) {
        // Show loading in modal
        document.getElementById('plotDetails').innerHTML = `
            <strong>Plot Area:</strong> ${plotArea} sqft<br>
            <strong>Plot Price:</strong> ₹${plotPrice.toLocaleString()}
        `;

        document.getElementById('commissionBreakdown').innerHTML = `
            <div class="text-center py-3">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Calculating...</span>
                </div>
                <p class="mt-2 mb-0">Calculating commission...</p>
            </div>
        `;

        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('commissionModal'));
        modal.show();

        // Make AJAX call to calculate commission
        fetch('/associate/commission-calculator', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: `calculate=1&plot_price=${plotPrice}&plot_area=${plotArea}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                let breakdownHtml = '<div class="commission-summary">';
                breakdownHtml += `<p class="text-success fw-bold">Total Potential Commission: ₹${data.total_potential.toLocaleString()}</p>`;

                data.calculations.forEach(calc => {
                    breakdownHtml += `
                        <div class="commission-level mb-3 p-3 border rounded">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>${calc.level_name} (Level ${calc.level})</strong><br>
                                    <small class="text-muted">${calc.level_name} Commission</small>
                                </div>
                                <div class="text-end">
                                    <div class="fw-bold">₹${calc.total_commission.toLocaleString()}</div>
                                    <small class="text-muted">${calc.level_name} Share</small>
                                </div>
                            </div>
                        </div>
                    `;
                });

                breakdownHtml += '</div>';
                document.getElementById('commissionBreakdown').innerHTML = breakdownHtml;
            } else {
                document.getElementById('commissionBreakdown').innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Error calculating commission. Please try again.
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('commissionBreakdown').innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Network error. Please check your connection and try again.
                </div>
            `;
        });
    }

    // Book Plot Function
    function bookPlot(plotId) {
        // Redirect to booking page with plot ID
        window.location.href = `/associate/plot-booking?plot_id=${plotId}`;
    }

    // Request Allocation Function
    function requestAllocation(plotId) {
        if (confirm('Are you sure you want to request allocation for this plot?')) {
            // Make AJAX call to request allocation
            fetch('/associate/request-allocation', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: `plot_id=${plotId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Allocation request submitted successfully!');
                    location.reload();
                } else {
                    alert('Error submitting request: ' + data.error);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Network error. Please try again.');
            });
        }
    }

    // Tab switching with animation
    document.querySelectorAll('#inventoryTabs .nav-link').forEach(tab => {
        tab.addEventListener('shown.bs.tab', function(event) {
            // Add animation to newly shown tab content
            const target = event.target.getAttribute('data-bs-target');
            const tabContent = document.querySelector(target + ' .plot-card');
            if (tabContent) {
                tabContent.style.opacity = '0';
                tabContent.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    tabContent.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                    tabContent.style.opacity = '1';
                    tabContent.style.transform = 'translateY(0)';
                }, 100);
            }
        });
    });

    // Intersection Observer for animations
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);

    // Observe cards for animation
    document.querySelectorAll('.plot-card, .summary-card').forEach(card => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(card);
    });
</script>

</body>
</html>
