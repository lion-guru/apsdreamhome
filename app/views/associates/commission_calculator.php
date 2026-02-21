<?php
/**
 * Associate Commission Calculator
 * Real-time commission calculation for plot sales
 */
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'Commission Calculator - Associate Dashboard'; ?></title>

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

        /* Modern Calculator Card */
        .calculator-card {
            background: white;
            border-radius: 25px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.8);
            overflow: hidden;
        }

        .calculator-header {
            background: var(--primary-gradient);
            color: white;
            padding: 2.5rem;
            text-align: center;
        }

        .calculator-body {
            padding: 2.5rem;
        }

        /* Input Groups */
        .input-group-modern {
            margin-bottom: 2rem;
        }

        .input-label {
            font-weight: 600;
            color: #1a237e;
            margin-bottom: 0.5rem;
            display: block;
        }

        .input-control {
            border: 2px solid #e9ecef;
            border-radius: 15px;
            padding: 1rem 1.5rem;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            background: white;
        }

        .input-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            outline: none;
        }

        /* Commission Display */
        .commission-display {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            border-radius: 20px;
            padding: 2rem;
            margin-top: 2rem;
            border-left: 5px solid #667eea;
        }

        .commission-total {
            font-size: 2.5rem;
            font-weight: 800;
            color: #28a745;
            text-align: center;
            margin-bottom: 1rem;
        }

        .commission-breakdown {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-top: 2rem;
        }

        .commission-level {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            border-left: 4px solid #667eea;
        }

        .level-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #e9ecef;
        }

        .level-name {
            font-weight: 700;
            color: #1a237e;
        }

        .level-number {
            background: var(--primary-gradient);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .commission-amount {
            font-size: 1.5rem;
            font-weight: 800;
            color: #28a745;
            text-align: center;
        }

        .commission-type {
            font-size: 0.9rem;
            color: #666;
            text-align: center;
            margin-top: 0.5rem;
        }

        /* Quick Calculator */
        .quick-calc {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            margin-bottom: 1.5rem;
        }

        .calc-button {
            background: var(--primary-gradient);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 25px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
        }

        .calc-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }

        /* Level Information */
        .level-info {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            border-radius: 15px;
            padding: 2rem;
            margin-top: 2rem;
        }

        .level-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
        }

        .level-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            text-align: center;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
            border-left: 4px solid #667eea;
        }

        .level-badge {
            background: var(--primary-gradient);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .level-commission {
            font-size: 1.5rem;
            font-weight: 800;
            color: #667eea;
            margin-bottom: 0.5rem;
        }

        .level-requirements {
            font-size: 0.9rem;
            color: #666;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .calculator-body {
                padding: 1.5rem;
            }

            .commission-breakdown {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .level-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .commission-total {
                font-size: 2rem;
            }
        }

        /* Animations */
        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-slide-up {
            animation: slideInUp 0.8s ease-out;
        }

        /* Loading States */
        .loading-pulse {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
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
                    <i class="fas fa-calculator me-3"></i>
                    Commission Calculator
                </h1>
                <p class="text-white-50">
                    Calculate your potential earnings from plot sales with real-time commission breakdown
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Main Content -->
<section class="py-5">
    <div class="container">
        <div class="row">
            <!-- Calculator -->
            <div class="col-lg-8">
                <div class="calculator-card animate-slide-up">
                    <div class="calculator-header">
                        <h2 class="mb-3">
                            <i class="fas fa-coins me-2"></i>
                            Plot Sale Commission Calculator
                        </h2>
                        <p class="mb-0 opacity-90">
                            Enter plot details to calculate your commission across all MLM levels
                        </p>
                    </div>

                    <div class="calculator-body">
                        <form id="commissionForm">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="input-group-modern">
                                        <label class="input-label">
                                            <i class="fas fa-ruler-combined me-2"></i>
                                            Plot Area (sqft)
                                        </label>
                                        <input type="number" class="input-control" id="plotArea" name="plot_area"
                                               placeholder="Enter plot area in square feet" min="1" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="input-group-modern">
                                        <label class="input-label">
                                            <i class="fas fa-indian-rupee-sign me-2"></i>
                                            Price per sqft (₹)
                                        </label>
                                        <input type="number" class="input-control" id="pricePerSqft" name="price_per_sqft"
                                               placeholder="Enter price per square foot" min="1" required>
                                    </div>
                                </div>
                            </div>

                            <div class="text-center">
                                <button type="submit" class="btn btn-primary btn-lg px-5 py-3">
                                    <i class="fas fa-calculator me-2"></i>
                                    Calculate Commission
                                </button>
                            </div>
                        </form>

                        <!-- Commission Results -->
                        <div id="commissionResults" style="display: none;">
                            <div class="commission-display animate-slide-up">
                                <h4 class="text-center mb-4">
                                    <i class="fas fa-chart-pie me-2"></i>
                                    Commission Breakdown
                                </h4>

                                <div class="commission-total" id="totalCommission">
                                    ₹0
                                </div>

                                <p class="text-center text-muted">
                                    Total potential commission across all levels
                                </p>

                                <div class="commission-breakdown" id="commissionBreakdown">
                                    <!-- Commission levels will be populated here -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Your Level Info -->
                <div class="quick-calc animate-slide-up">
                    <h5 class="text-center mb-4">
                        <i class="fas fa-user-graduate me-2"></i>
                        Your Current Level
                    </h5>

                    <div class="text-center mb-4">
                        <div class="level-badge">
                            Level <?php echo $associate_level; ?> - <?php echo $commission_rates[0]['level_name'] ?? 'Associate'; ?>
                        </div>
                    </div>

                    <div class="text-center">
                        <div class="level-commission">
                            <?php echo $commission_rates[0]['commission_percentage'] ?? '5'; ?>%
                        </div>
                        <p class="text-muted mb-0">Base Commission Rate</p>
                    </div>
                </div>

                <!-- Quick Calculations -->
                <div class="quick-calc animate-slide-up" style="animation-delay: 0.2s;">
                    <h5 class="text-center mb-4">
                        <i class="fas fa-bolt me-2"></i>
                        Quick Calculations
                    </h5>

                    <button class="calc-button mb-3" onclick="quickCalc(1000, 15000)">
                        <i class="fas fa-home me-2"></i>
                        1000 sqft @ ₹15,000/sqft
                    </button>

                    <button class="calc-button mb-3" onclick="quickCalc(1500, 20000)">
                        <i class="fas fa-home me-2"></i>
                        1500 sqft @ ₹20,000/sqft
                    </button>

                    <button class="calc-button mb-3" onclick="quickCalc(2000, 25000)">
                        <i class="fas fa-home me-2"></i>
                        2000 sqft @ ₹25,000/sqft
                    </button>

                    <button class="calc-button" onclick="quickCalc(3000, 35000)">
                        <i class="fas fa-home me-2"></i>
                        3000 sqft @ ₹35,000/sqft
                    </button>
                </div>

                <!-- Level Structure -->
                <div class="level-info animate-slide-up" style="animation-delay: 0.4s;">
                    <h5 class="text-center mb-4">
                        <i class="fas fa-layer-group me-2"></i>
                        MLM Level Structure
                    </h5>

                    <div class="level-grid">
                        <?php foreach ($commission_rates as $level): ?>
                            <div class="level-card">
                                <div class="level-badge">
                                    Level <?php echo $level['level_number']; ?> - <?php echo $level['level_name']; ?>
                                </div>

                                <div class="level-commission">
                                    <?php echo $level['commission_percentage']; ?>%
                                </div>

                                <div class="level-requirements">
                                    Min Team: <?php echo $level['min_team_size']; ?> members<br>
                                    Min Sales: ₹<?php echo number_format($level['min_personal_sales']); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
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

    // Commission Calculator
    document.getElementById('commissionForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const plotArea = parseFloat(document.getElementById('plotArea').value);
        const pricePerSqft = parseFloat(document.getElementById('pricePerSqft').value);
        const totalPrice = plotArea * pricePerSqft;

        if (isNaN(plotArea) || isNaN(pricePerSqft) || plotArea <= 0 || pricePerSqft <= 0) {
            alert('Please enter valid plot area and price per sqft');
            return;
        }

        calculateCommission(totalPrice, plotArea);
    });

    function calculateCommission(plotPrice, plotArea) {
        // Show loading state
        document.getElementById('totalCommission').textContent = '₹' + plotPrice.toLocaleString();
        document.getElementById('commissionResults').style.display = 'block';

        // Show loading in breakdown
        document.getElementById('commissionBreakdown').innerHTML = `
            <div class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Calculating...</span>
                </div>
                <p class="mt-3 mb-0">Calculating commission breakdown...</p>
            </div>
        `;

        // Make AJAX call
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
                displayCommissionBreakdown(data.calculations, data.total_potential);
            } else {
                document.getElementById('commissionBreakdown').innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Error calculating commission: ${data.error}
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

    function displayCommissionBreakdown(calculations, total) {
        let breakdownHtml = '';

        calculations.forEach(calc => {
            breakdownHtml += `
                <div class="commission-level animate-slide-up">
                    <div class="level-header">
                        <div>
                            <div class="level-name">${calc.level_name}</div>
                            <div class="level-number">Level ${calc.level}</div>
                        </div>
                    </div>

                    <div class="commission-amount">
                        ₹${calc.total_commission.toLocaleString()}
                    </div>

                    <div class="commission-type">
                        ${calc.level_name} Commission
                    </div>

                    <div class="mt-3">
                        <div class="row text-center">
                            <div class="col-4">
                                <small class="text-muted">Base</small><br>
                                <span class="fw-bold">₹${calc.base_commission.toLocaleString()}</span>
                            </div>
                            <div class="col-4">
                                <small class="text-muted">Bonus</small><br>
                                <span class="fw-bold">₹${calc.bonus_commission.toLocaleString()}</span>
                            </div>
                            <div class="col-4">
                                <small class="text-muted">Override</small><br>
                                <span class="fw-bold">₹${calc.override_commission.toLocaleString()}</span>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });

        document.getElementById('commissionBreakdown').innerHTML = breakdownHtml;
        document.getElementById('totalCommission').textContent = '₹' + total.toLocaleString();

        // Animate total
        document.getElementById('totalCommission').style.animation = 'pulse 2s infinite';
        setTimeout(() => {
            document.getElementById('totalCommission').style.animation = '';
        }, 2000);
    }

    // Quick Calculation Functions
    function quickCalc(area, pricePerSqft) {
        document.getElementById('plotArea').value = area;
        document.getElementById('pricePerSqft').value = pricePerSqft;

        const event = new Event('submit');
        document.getElementById('commissionForm').dispatchEvent(event);
    }

    // Real-time calculation on input change
    document.getElementById('plotArea').addEventListener('input', function() {
        const area = parseFloat(this.value);
        const price = parseFloat(document.getElementById('pricePerSqft').value);

        if (area > 0 && price > 0) {
            const total = area * price;
            // Optional: Show running total without full calculation
        }
    });

    document.getElementById('pricePerSqft').addEventListener('input', function() {
        const price = parseFloat(this.value);
        const area = parseFloat(document.getElementById('plotArea').value);

        if (area > 0 && price > 0) {
            const total = area * price;
            // Optional: Show running total without full calculation
        }
    });
</script>

</body>
</html>
