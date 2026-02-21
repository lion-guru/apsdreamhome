<?php
/**
 * Associate Plot Booking System
 * Complete booking and hold system for associates
 */
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'Plot Booking - Associate Dashboard'; ?></title>

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
            --danger-gradient: linear-gradient(135deg, #dc3545 0%, #e83e8c 100%);
            --info-gradient: linear-gradient(135deg, #17a2b8 0%, #20c997 100%);
        }

        body {
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }

        /* Modern Cards */
        .booking-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.8);
            transition: all 0.3s ease;
        }

        .booking-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
        }

        /* Booking Form */
        .booking-form {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }

        .form-section {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            border-left: 4px solid #667eea;
        }

        .form-section-title {
            font-weight: 700;
            color: #1a237e;
            margin-bottom: 1rem;
        }

        .input-group-modern {
            margin-bottom: 1.5rem;
        }

        .input-label {
            font-weight: 600;
            color: #1a237e;
            margin-bottom: 0.5rem;
            display: block;
        }

        .input-control {
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 0.75rem 1rem;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: white;
        }

        .input-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            outline: none;
        }

        /* Booking Cards */
        .booking-item {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            margin-bottom: 1rem;
            border-left: 4px solid #667eea;
            transition: all 0.3s ease;
        }

        .booking-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }

        .booking-header {
            display: flex;
            justify-content: between;
            align-items: start;
            margin-bottom: 1rem;
        }

        .booking-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: #1a237e;
            margin-bottom: 0.25rem;
        }

        .booking-subtitle {
            font-size: 0.9rem;
            color: #667eea;
            margin-bottom: 0.5rem;
        }

        .booking-status {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-active {
            background: var(--success-gradient);
            color: white;
        }

        .status-expired {
            background: var(--danger-gradient);
            color: white;
        }

        .status-cancelled {
            background: var(--warning-gradient);
            color: white;
        }

        .booking-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .booking-detail {
            text-align: center;
            padding: 0.5rem;
            background: #f8f9fa;
            border-radius: 8px;
        }

        .booking-detail-label {
            font-size: 0.8rem;
            color: #666;
            margin-bottom: 0.25rem;
        }

        .booking-detail-value {
            font-size: 1rem;
            font-weight: 700;
            color: #1a237e;
        }

        .booking-actions {
            display: flex;
            gap: 0.5rem;
            margin-top: 1rem;
        }

        .btn-booking-action {
            padding: 0.5rem 1rem;
            border-radius: 25px;
            font-size: 0.9rem;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-release {
            background: var(--danger-gradient);
            color: white;
        }

        .btn-release:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(220, 53, 69, 0.3);
        }

        /* Plot Selection */
        .plot-selection {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            margin-bottom: 1.5rem;
        }

        .plot-option {
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 1rem;
            margin-bottom: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .plot-option:hover {
            border-color: #667eea;
            background: rgba(102, 126, 234, 0.05);
        }

        .plot-option.selected {
            border-color: #667eea;
            background: rgba(102, 126, 234, 0.1);
        }

        .plot-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .plot-details {
            flex-grow: 1;
        }

        .plot-name {
            font-weight: 600;
            color: #1a237e;
            margin-bottom: 0.25rem;
        }

        .plot-location {
            font-size: 0.9rem;
            color: #666;
        }

        .plot-price {
            font-size: 1.1rem;
            font-weight: 700;
            color: #28a745;
        }

        /* Booking Summary */
        .booking-summary {
            background: var(--primary-gradient);
            color: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .summary-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .summary-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
        }

        .summary-label {
            opacity: 0.9;
        }

        .summary-value {
            font-weight: 600;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .booking-details {
                grid-template-columns: repeat(2, 1fr);
                gap: 0.5rem;
            }

            .booking-actions {
                flex-direction: column;
            }

            .btn-booking-action {
                font-size: 0.8rem;
                padding: 0.75rem;
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
                    <i class="fas fa-calendar-check me-3"></i>
                    Plot Booking System
                </h1>
                <p class="text-white-50">
                    Book plots for customers or hold them temporarily for sales
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Main Content -->
<section class="py-5">
    <div class="container">
        <?php if (isset($_GET['plot_id'])): ?>
            <!-- Booking Form for Specific Plot -->
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="booking-form animate-fade-up">
                        <h3 class="text-center mb-4">
                            <i class="fas fa-edit me-2"></i>
                            Book Plot
                        </h3>

                        <?php
                        $plot_id = $_GET['plot_id'];
                        $selected_plot = null;

                        // Find the plot in available plots
                        foreach ($available_plots as $plot) {
                            if ($plot['id'] == $plot_id) {
                                $selected_plot = $plot;
                                break;
                            }
                        }
                        ?>

                        <?php if ($selected_plot): ?>
                            <form method="POST" action="">
                                <input type="hidden" name="plot_id" value="<?php echo $plot_id; ?>">

                                <!-- Plot Information -->
                                <div class="form-section">
                                    <h5 class="form-section-title">
                                        <i class="fas fa-info-circle me-2"></i>
                                        Plot Information
                                    </h5>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="plot-info">
                                                <div class="plot-name">Plot #<?php echo htmlspecialchars($selected_plot['plot_number']); ?></div>
                                                <div class="plot-location">
                                                    <?php echo htmlspecialchars($selected_plot['colony_name'] . ', ' . $selected_plot['colony_location']); ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 text-end">
                                            <div class="plot-price">
                                                ₹<?php echo number_format($selected_plot['plot_area'] * $selected_plot['price_per_sqft']); ?>
                                            </div>
                                            <small class="text-muted">
                                                <?php echo $selected_plot['plot_area']; ?> sqft @ ₹<?php echo number_format($selected_plot['price_per_sqft']); ?>/sqft
                                            </small>
                                        </div>
                                    </div>
                                </div>

                                <!-- Customer Information -->
                                <div class="form-section">
                                    <h5 class="form-section-title">
                                        <i class="fas fa-user me-2"></i>
                                        Customer Information
                                    </h5>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="input-group-modern">
                                                <label class="input-label">Customer Name *</label>
                                                <input type="text" class="input-control" name="customer_name" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="input-group-modern">
                                                <label class="input-label">Customer Phone *</label>
                                                <input type="tel" class="input-control" name="customer_phone" required>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Booking Type -->
                                <div class="form-section">
                                    <h5 class="form-section-title">
                                        <i class="fas fa-clipboard-check me-2"></i>
                                        Booking Type
                                    </h5>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="booking_type" id="hold" value="hold" checked>
                                                <label class="form-check-label" for="hold">
                                                    <strong>Hold Plot</strong><br>
                                                    <small class="text-muted">Reserve plot for 7 days for customer consideration</small>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="booking_type" id="book" value="book">
                                                <label class="form-check-label" for="book">
                                                    <strong>Book Plot</strong><br>
                                                    <small class="text-muted">Confirm booking for immediate sale</small>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Notes -->
                                <div class="form-section">
                                    <h5 class="form-section-title">
                                        <i class="fas fa-sticky-note me-2"></i>
                                        Additional Notes
                                    </h5>

                                    <textarea class="input-control" name="notes" rows="3"
                                              placeholder="Any additional notes or requirements..."></textarea>
                                </div>

                                <!-- Submit Button -->
                                <div class="text-center">
                                    <button type="submit" name="book_plot" class="btn btn-success btn-lg px-5 py-3">
                                        <i class="fas fa-check-circle me-2"></i>
                                        Confirm Booking
                                    </button>
                                </div>
                            </form>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                                <h4>Plot Not Found</h4>
                                <p class="text-muted">The selected plot is not available for booking.</p>
                                <a href="/associate/plot-booking" class="btn btn-primary">
                                    <i class="fas fa-arrow-left me-2"></i>Back to Booking
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <!-- Booking Management Dashboard -->
            <div class="row">
                <!-- Active Bookings -->
                <div class="col-lg-8">
                    <div class="booking-card animate-fade-up">
                        <div class="card-header-modern">
                            <h3 class="mb-0">
                                <i class="fas fa-list-check me-2"></i>
                                My Active Bookings
                            </h3>
                        </div>

                        <div class="card-body-modern">
                            <?php if (empty($bookings)): ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-calendar-times fa-4x text-muted mb-4"></i>
                                    <h4 class="text-muted">No Active Bookings</h4>
                                    <p class="text-muted mb-4">You don't have any active plot bookings at the moment.</p>
                                    <a href="#available-plots" class="btn btn-primary">
                                        <i class="fas fa-plus me-2"></i>Book a Plot
                                    </a>
                                </div>
                            <?php else: ?>
                                <?php foreach ($bookings as $booking): ?>
                                    <div class="booking-item animate-fade-up">
                                        <div class="booking-header">
                                            <div>
                                                <div class="booking-title">
                                                    Plot #<?php echo htmlspecialchars($booking['plot_number']); ?> - <?php echo htmlspecialchars($booking['booking_type']); ?>ed
                                                </div>
                                                <div class="booking-subtitle">
                                                    <?php echo htmlspecialchars($booking['colony_name'] . ', ' . $booking['colony_location']); ?>
                                                </div>
                                            </div>
                                            <div>
                                                <span class="booking-status status-active">
                                                    <?php echo ucfirst($booking['status']); ?>
                                                </span>
                                            </div>
                                        </div>

                                        <div class="booking-details">
                                            <div class="booking-detail">
                                                <div class="booking-detail-label">Customer</div>
                                                <div class="booking-detail-value"><?php echo htmlspecialchars($booking['customer_name']); ?></div>
                                            </div>
                                            <div class="booking-detail">
                                                <div class="booking-detail-label">Phone</div>
                                                <div class="booking-detail-value"><?php echo htmlspecialchars($booking['customer_phone']); ?></div>
                                            </div>
                                            <div class="booking-detail">
                                                <div class="booking-detail-label">Expires</div>
                                                <div class="booking-detail-value">
                                                    <?php echo date('d M Y', strtotime($booking['expires_at'])); ?>
                                                </div>
                                            </div>
                                            <div class="booking-detail">
                                                <div class="booking-detail-label">Booked On</div>
                                                <div class="booking-detail-value">
                                                    <?php echo date('d M Y', strtotime($booking['created_at'])); ?>
                                                </div>
                                            </div>
                                        </div>

                                        <?php if ($booking['notes']): ?>
                                            <div class="mb-3">
                                                <strong>Notes:</strong> <?php echo htmlspecialchars($booking['notes']); ?>
                                            </div>
                                        <?php endif; ?>

                                        <div class="booking-actions">
                                            <button class="btn-booking-action btn-release"
                                                    onclick="releaseBooking(<?php echo $booking['id']; ?>)">
                                                <i class="fas fa-times me-1"></i>Release
                                            </button>
                                            <a href="/associate/commission-calculator?plot_area=<?php echo $booking['plot_area']; ?>"
                                               class="btn btn-outline-primary btn-booking-action">
                                                <i class="fas fa-calculator me-1"></i>Calculate Commission
                                            </a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Available Plots for Booking -->
                <div class="col-lg-4">
                    <div class="booking-card animate-fade-up" id="available-plots">
                        <div class="card-header-modern">
                            <h4 class="mb-0">
                                <i class="fas fa-search me-2"></i>
                                Available Plots
                            </h4>
                        </div>

                        <div class="card-body-modern">
                            <?php if (empty($available_plots)): ?>
                                <div class="text-center py-4">
                                    <i class="fas fa-home fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">No plots available for booking</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($available_plots as $plot): ?>
                                    <div class="plot-selection animate-fade-up">
                                        <div class="plot-option" onclick="selectPlot(<?php echo $plot['id']; ?>)">
                                            <div class="plot-info">
                                                <div class="plot-details">
                                                    <div class="plot-name">Plot #<?php echo htmlspecialchars($plot['plot_number']); ?></div>
                                                    <div class="plot-location">
                                                        <?php echo htmlspecialchars($plot['colony_name'] . ', ' . $plot['colony_location']); ?>
                                                    </div>
                                                </div>
                                                <div class="plot-price">
                                                    ₹<?php echo number_format($plot['plot_area'] * $plot['price_per_sqft']); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

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

    // Plot Selection
    function selectPlot(plotId) {
        // Remove selected class from all options
        document.querySelectorAll('.plot-option').forEach(option => {
            option.classList.remove('selected');
        });

        // Add selected class to clicked option
        event.currentTarget.classList.add('selected');

        // Redirect to booking form
        window.location.href = `/associate/plot-booking?plot_id=${plotId}`;
    }

    // Release Booking
    function releaseBooking(bookingId) {
        if (confirm('Are you sure you want to release this booking? The plot will become available again.')) {
            window.location.href = `/associate/release-booking/${bookingId}`;
        }
    }

    // Form Validation
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('form');
        if (form) {
            form.addEventListener('submit', function(e) {
                const customerName = document.querySelector('input[name="customer_name"]').value.trim();
                const customerPhone = document.querySelector('input[name="customer_phone"]').value.trim();

                if (!customerName || !customerPhone) {
                    e.preventDefault();
                    alert('Please fill in all required fields');
                    return;
                }

                if (customerPhone.length < 10) {
                    e.preventDefault();
                    alert('Please enter a valid phone number');
                    return;
                }

                // Show loading state
                const submitBtn = document.querySelector('button[type="submit"]');
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';
                submitBtn.disabled = true;
            });
        }
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

    // Observe elements for animation
    document.querySelectorAll('.booking-item, .plot-selection').forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(20px)';
        el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(el);
    });
</script>

</body>
</html>
