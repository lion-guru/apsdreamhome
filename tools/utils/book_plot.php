<?php
/**
 * APS Dream Home - Customer Booking Portal
 * Direct booking system for APS own colonies
 */

require_once 'includes/db_connection.php';
require_once 'includes/enhanced_universal_template.php';

$template = new EnhancedUniversalTemplate();

// Page metadata
$page_title = 'Book Your Plot - APS Dream Home';
$page_description = 'Book premium plots in APS Dream Homes colonies across Uttar Pradesh. Easy booking process with flexible payment plans.';

$template->setTitle($page_title);
$template->setDescription($page_description);

// Add CSS
$css_assets = [
    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css',
    'https://unpkg.com/aos@2.3.1/dist/aos.css',
    '/assets/css/modern-style.css'
];

foreach ($css_assets as $css) {
    $template->addCSS($css);
}

// Add JS
$js_assets = [
    ['url' => 'https://unpkg.com/aos@2.3.1/dist/aos.js', 'defer' => true, 'async' => true],
    ['url' => '/assets/js/booking-system.js', 'defer' => true, 'async' => true]
];

foreach ($js_assets as $js) {
    $template->addJS($js['url'], $js['defer'], $js['async']);
}

// Sample plots data (replace with database queries)
$available_plots = [
    [
        'id' => 1,
        'colony_name' => 'APS Dream City Gorakhpur',
        'plot_number' => 'A-125',
        'plot_size' => '150 sq yards',
        'plot_area' => 150,
        'facing' => 'North-East',
        'price_per_sqyard' => 10000,
        'total_price' => 1500000,
        'booking_amount' => 150000,
        'status' => 'available',
        'features' => ['Corner Plot', 'Park Facing', 'Main Road'],
        'image' => '/assets/images/plots/plot-a125.jpg'
    ],
    [
        'id' => 2,
        'colony_name' => 'APS Dream City Gorakhpur',
        'plot_number' => 'B-203',
        'plot_size' => '120 sq yards',
        'plot_area' => 120,
        'facing' => 'South',
        'price_per_sqyard' => 9500,
        'total_price' => 1140000,
        'booking_amount' => 114000,
        'status' => 'available',
        'features' => ['Garden View', 'Near Club House'],
        'image' => '/assets/images/plots/plot-b203.jpg'
    ],
    [
        'id' => 3,
        'colony_name' => 'APS Royal Residency',
        'plot_number' => 'C-045',
        'plot_size' => '200 sq yards',
        'plot_area' => 200,
        'facing' => 'East',
        'price_per_sqyard' => 12500,
        'total_price' => 2500000,
        'booking_amount' => 250000,
        'status' => 'available',
        'features' => ['Premium Location', 'Hill View', 'Large Frontage'],
        'image' => '/assets/images/plots/plot-c045.jpg'
    ]
];

// Payment plans
$payment_plans = [
    [
        'name' => 'Standard Plan',
        'description' => 'Traditional payment schedule',
        'installments' => [
            ['percentage' => 20, 'description' => 'Booking Amount'],
            ['percentage' => 30, 'description' => 'Within 30 days'],
            ['percentage' => 25, 'description' => 'Foundation complete'],
            ['percentage' => 25, 'description' => 'Possession']
        ]
    ],
    [
        'name' => 'Easy EMI Plan',
        'description' => 'Monthly EMI after booking',
        'installments' => [
            ['percentage' => 15, 'description' => 'Booking Amount'],
            ['percentage' => 85, 'description' => '24 Monthly EMIs']
        ]
    ],
    [
        'name' => 'Flexi Plan',
        'description' => 'Flexible payment schedule',
        'installments' => [
            ['percentage' => 25, 'description' => 'Booking Amount'],
            ['percentage' => 75, 'description' => 'Flexible timeline']
        ]
    ]
];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <meta name="description" content="<?php echo $page_description; ?>">

    <style>
        .booking-hero {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 60px 0;
            text-align: center;
        }

        .plot-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            margin-bottom: 30px;
        }

        .plot-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.15);
        }

        .plot-image {
            height: 200px;
            background: linear-gradient(45deg, #667eea, #764ba2);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            position: relative;
        }

        .plot-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .plot-overlay {
            position: absolute;
            top: 10px;
            left: 10px;
        }

        .plot-status {
            background: rgba(40, 167, 69, 0.9);
            color: white;
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .plot-content {
            padding: 20px;
        }

        .plot-title {
            color: #1a237e;
            font-size: 1.3rem;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .plot-colony {
            color: #667eea;
            font-size: 0.9rem;
            margin-bottom: 15px;
        }

        .plot-specs {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
            gap: 10px;
            margin-bottom: 15px;
        }

        .spec-item {
            background: #f8f9fa;
            padding: 8px;
            border-radius: 8px;
            text-align: center;
        }

        .spec-value {
            font-size: 1rem;
            font-weight: 700;
            color: #667eea;
            display: block;
        }

        .spec-label {
            font-size: 0.7rem;
            color: #666;
            text-transform: uppercase;
        }

        .plot-features {
            margin-bottom: 15px;
        }

        .feature-tag {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            padding: 3px 8px;
            border-radius: 10px;
            font-size: 0.7rem;
            margin-right: 5px;
            margin-bottom: 5px;
            display: inline-block;
        }

        .plot-pricing {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 15px;
            border-radius: 0 0 15px 15px;
        }

        .total-price {
            font-size: 1.5rem;
            font-weight: 800;
            margin-bottom: 5px;
        }

        .booking-amount {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .btn-book-now {
            background: #28a745;
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 20px;
            font-weight: 600;
            transition: all 0.3s ease;
            width: 100%;
        }

        .btn-book-now:hover {
            background: #218838;
            transform: translateY(-2px);
        }

        .payment-plans {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
        }

        .plan-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            height: 100%;
        }

        .plan-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }

        .plan-header {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 15px;
            border-radius: 10px 10px 0 0;
            text-align: center;
        }

        .plan-title {
            font-size: 1.2rem;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .plan-description {
            font-size: 0.9rem;
            opacity: 0.9;
        }

        .installment-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }

        .installment-percentage {
            font-weight: 700;
            color: #667eea;
        }

        .booking-process {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }

        .process-step {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }

        .step-number {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            margin-right: 15px;
        }

        .step-content h6 {
            color: #1a237e;
            margin-bottom: 5px;
        }

        .step-content p {
            color: #666;
            margin: 0;
        }

        .calculator-section {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 40px 0;
        }

        .calculator-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }

        .form-control {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            padding: 12px 15px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        .calculation-result {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
        }

        .result-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .result-value {
            font-weight: 700;
        }

        @media (max-width: 768px) {
            .plot-specs {
                grid-template-columns: repeat(2, 1fr);
            }

            .process-step {
                flex-direction: column;
                text-align: center;
            }

            .step-number {
                margin-right: 0;
                margin-bottom: 10px;
            }
        }
    </style>
</head>
<body>
    <!-- Hero Section -->
    <section class="booking-hero">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <h1 class="display-5 fw-bold mb-4" data-aos="fade-up">
                        Book Your Dream <span class="text-warning">Plot</span>
                    </h1>
                    <p class="lead mb-4" data-aos="fade-up" data-aos-delay="100">
                        Choose from premium plots in APS Dream Homes colonies.
                        Easy booking process with flexible payment plans and dedicated support.
                    </p>
                    <div class="d-flex justify-content-center">
                        <a href="#available-plots" class="btn btn-warning btn-lg px-4 py-3">
                            <i class="fas fa-search me-2"></i>Browse Available Plots
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Available Plots -->
    <section class="py-5" id="available-plots">
        <div class="container">
            <div class="row mb-4">
                <div class="col-12 text-center">
                    <h2 class="mb-3">Available Plots</h2>
                    <p class="text-muted">Handpicked premium plots in our exclusive colonies</p>
                </div>
            </div>

            <div class="row">
                <?php foreach ($available_plots as $plot): ?>
                    <div class="col-lg-4 col-md-6" data-aos="fade-up">
                        <div class="plot-card">
                            <div class="plot-image">
                                <img src="<?php echo $plot['image']; ?>" alt="Plot <?php echo $plot['plot_number']; ?>" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <div style="display: none;">
                                    <i class="fas fa-map fa-2x"></i>
                                    <p>Plot <?php echo $plot['plot_number']; ?></p>
                                </div>
                                <div class="plot-overlay">
                                    <span class="plot-status">
                                        <i class="fas fa-check-circle me-1"></i>Available
                                    </span>
                                </div>
                            </div>

                            <div class="plot-content">
                                <h3 class="plot-title">Plot <?php echo $plot['plot_number']; ?></h3>
                                <div class="plot-colony"><?php echo $plot['colony_name']; ?></div>

                                <div class="plot-specs">
                                    <div class="spec-item">
                                        <span class="spec-value"><?php echo $plot['plot_size']; ?></span>
                                        <span class="spec-label">Size</span>
                                    </div>
                                    <div class="spec-item">
                                        <span class="spec-value"><?php echo $plot['facing']; ?></span>
                                        <span class="spec-label">Facing</span>
                                    </div>
                                    <div class="spec-item">
                                        <span class="spec-value">₹<?php echo number_format($plot['price_per_sqyard']); ?></span>
                                        <span class="spec-label">Per Sq Yard</span>
                                    </div>
                                </div>

                                <div class="plot-features">
                                    <?php foreach ($plot['features'] as $feature): ?>
                                        <span class="feature-tag"><?php echo $feature; ?></span>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <div class="plot-pricing">
                                <div class="total-price">₹<?php echo number_format($plot['total_price']); ?></div>
                                <div class="booking-amount">Booking: ₹<?php echo number_format($plot['booking_amount']); ?></div>
                                <button class="btn btn-book-now mt-3" onclick="openBookingModal(<?php echo $plot['id']; ?>)">
                                    <i class="fas fa-calendar-check me-2"></i>Book Now
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Payment Plans -->
    <section class="py-5">
        <div class="container">
            <div class="row mb-4">
                <div class="col-12 text-center">
                    <h2 class="mb-3">Payment Plans</h2>
                    <p class="text-muted">Choose a payment plan that suits your financial planning</p>
                </div>
            </div>

            <div class="row">
                <?php foreach ($payment_plans as $plan): ?>
                    <div class="col-lg-4 col-md-6" data-aos="fade-up">
                        <div class="plan-card">
                            <div class="plan-header">
                                <div class="plan-title"><?php echo $plan['name']; ?></div>
                                <div class="plan-description"><?php echo $plan['description']; ?></div>
                            </div>
                            <div class="card-body">
                                <?php foreach ($plan['installments'] as $installment): ?>
                                    <div class="installment-item">
                                        <span><?php echo $installment['description']; ?></span>
                                        <span class="installment-percentage"><?php echo $installment['percentage']; ?>%</span>
                                    </div>
                                <?php endforeach; ?>
                                <button class="btn btn-outline-primary w-100 mt-3" onclick="selectPaymentPlan('<?php echo $plan['name']; ?>')">
                                    Select This Plan
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- EMI Calculator -->
    <section class="calculator-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="calculator-card">
                        <h3 class="text-center mb-4"><i class="fas fa-calculator me-2"></i>EMI Calculator</h3>
                        <p class="text-center text-muted mb-4">Calculate your monthly EMI for easy planning</p>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Plot Value (₹)</label>
                                    <input type="number" class="form-control" id="plotValue" placeholder="Enter plot value" value="1500000">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Down Payment (₹)</label>
                                    <input type="number" class="form-control" id="downPayment" placeholder="Enter down payment" value="150000">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Interest Rate (%)</label>
                                    <input type="number" class="form-control" id="interestRate" placeholder="Enter interest rate" value="8.5" step="0.1">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Tenure (Years)</label>
                                    <select class="form-control" id="tenure">
                                        <option value="5">5 Years</option>
                                        <option value="10">10 Years</option>
                                        <option value="15" selected>15 Years</option>
                                        <option value="20">20 Years</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="text-center">
                            <button class="btn btn-primary px-4 py-2" onclick="calculateEMI()">
                                <i class="fas fa-calculator me-2"></i>Calculate EMI
                            </button>
                        </div>

                        <div class="calculation-result" id="emiResult" style="display: none;">
                            <h5 class="mb-3">EMI Calculation Result</h5>
                            <div class="result-item">
                                <span>Loan Amount:</span>
                                <span class="result-value" id="loanAmount">₹0</span>
                            </div>
                            <div class="result-item">
                                <span>Monthly EMI:</span>
                                <span class="result-value" id="monthlyEMI">₹0</span>
                            </div>
                            <div class="result-item">
                                <span>Total Interest:</span>
                                <span class="result-value" id="totalInterest">₹0</span>
                            </div>
                            <div class="result-item">
                                <span>Total Amount:</span>
                                <span class="result-value" id="totalAmount">₹0</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Booking Process -->
    <section class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="booking-process">
                        <h3 class="text-center mb-4">Simple Booking Process</h3>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="process-step">
                                    <div class="step-number">1</div>
                                    <div class="step-content">
                                        <h6>Choose Your Plot</h6>
                                        <p>Select from available premium plots</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="process-step">
                                    <div class="step-number">2</div>
                                    <div class="step-content">
                                        <h6>Pay Booking Amount</h6>
                                        <p>Online payment or visit our office</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="process-step">
                                    <div class="step-number">3</div>
                                    <div class="step-content">
                                        <h6>Document Verification</h6>
                                        <p>Submit required documents</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="process-step">
                                    <div class="step-number">4</div>
                                    <div class="step-content">
                                        <h6>Get Possession</h6>
                                        <p>Complete payments and take possession</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Booking Modal -->
    <div class="modal fade" id="bookingModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Book Your Plot</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="bookingForm">
                        <input type="hidden" id="selected_plot_id" name="plot_id">

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Full Name *</label>
                                    <input type="text" class="form-control" name="customer_name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Phone Number *</label>
                                    <input type="tel" class="form-control" name="customer_phone" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Email Address *</label>
                                    <input type="email" class="form-control" name="customer_email" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Aadhar Number *</label>
                                    <input type="text" class="form-control" name="aadhar_number" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Address *</label>
                            <textarea class="form-control" name="customer_address" rows="3" required></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Payment Plan *</label>
                                    <select class="form-control" name="payment_plan" required>
                                        <option value="">Select Payment Plan</option>
                                        <option value="standard">Standard Plan</option>
                                        <option value="emi">Easy EMI Plan</option>
                                        <option value="flexi">Flexi Plan</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Preferred Booking Date</label>
                                    <input type="date" class="form-control" name="preferred_date">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Additional Notes</label>
                            <textarea class="form-control" name="notes" rows="3" placeholder="Any specific requirements or questions..."></textarea>
                        </div>

                        <div class="text-center">
                            <button type="submit" class="btn btn-success btn-lg px-5">
                                <i class="fas fa-check-circle me-2"></i>Confirm Booking
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Booking modal
        function openBookingModal(plotId) {
            document.getElementById('selected_plot_id').value = plotId;

            // Find plot details and populate modal
            const plots = <?php echo json_encode($available_plots); ?>;
            const plot = plots.find(p => p.id == plotId);

            if (plot) {
                document.querySelector('.modal-title').innerHTML = `Book Plot ${plot.plot_number} - ₹${plot.total_price.toLocaleString()}`;
            }

            new bootstrap.Modal(document.getElementById('bookingModal')).show();
        }

        // EMI Calculator
        function calculateEMI() {
            const plotValue = parseFloat(document.getElementById('plotValue').value) || 0;
            const downPayment = parseFloat(document.getElementById('downPayment').value) || 0;
            const interestRate = parseFloat(document.getElementById('interestRate').value) || 0;
            const tenure = parseInt(document.getElementById('tenure').value) || 0;

            const loanAmount = plotValue - downPayment;
            const monthlyRate = interestRate / (12 * 100);
            const numPayments = tenure * 12;

            if (loanAmount > 0 && monthlyRate > 0 && numPayments > 0) {
                const monthlyEMI = loanAmount * monthlyRate * Math.pow(1 + monthlyRate, numPayments) / (Math.pow(1 + monthlyRate, numPayments) - 1);
                const totalAmount = monthlyEMI * numPayments;
                const totalInterest = totalAmount - loanAmount;

                document.getElementById('loanAmount').textContent = '₹' + loanAmount.toLocaleString();
                document.getElementById('monthlyEMI').textContent = '₹' + Math.round(monthlyEMI).toLocaleString();
                document.getElementById('totalInterest').textContent = '₹' + Math.round(totalInterest).toLocaleString();
                document.getElementById('totalAmount').textContent = '₹' + Math.round(totalAmount).toLocaleString();

                document.getElementById('emiResult').style.display = 'block';
            } else {
                alert('Please enter valid values for calculation');
            }
        }

        // Payment plan selection
        function selectPaymentPlan(planName) {
            document.querySelector('select[name="payment_plan"]').value = planName.toLowerCase().replace(' ', '_');
            alert(`Selected: ${planName}`);
        }

        // Form submission
        document.getElementById('bookingForm').addEventListener('submit', function(e) {
            e.preventDefault();

            // Here you would send the data to your server
            alert('Booking request submitted successfully! We will contact you within 24 hours.');

            // Close modal
            bootstrap.Modal.getInstance(document.getElementById('bookingModal')).hide();

            // Reset form
            this.reset();
            document.getElementById('emiResult').style.display = 'none';
        });

        // Initialize AOS
        AOS.init();

        // Auto-calculate EMI when inputs change
        document.querySelectorAll('#plotValue, #downPayment, #interestRate, #tenure').forEach(input => {
            input.addEventListener('change', calculateEMI);
        });
    </script>
</body>
</html>
