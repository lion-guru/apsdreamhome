<?php
session_start();
include 'config.php';

// Company Owner has ultimate access - no restrictions
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

// For demonstration, we'll assume company owner role
$_SESSION['admin_role'] = 'company_owner';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🏗️ Hybrid Real Estate Control Center - APS Dream Homes</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

    <style>
        :root {
            --primary-color: #667eea;
            --secondary-color: #764ba2;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --info-color: #17a2b8;
            --dark-color: #343a40;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .hero-section {
            background: linear-gradient(135deg, rgba(255,255,255,0.1), rgba(255,255,255,0.05));
            border-radius: 20px;
            padding: 3rem;
            margin: 2rem 0;
            text-align: center;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.2);
        }

        .feature-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin: 1rem 0;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            border-left: 5px solid var(--info-color);
            transition: all 0.3s ease;
            text-decoration: none;
            color: inherit;
            display: block;
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
            text-decoration: none;
            color: inherit;
        }

        .feature-card.company {
            border-left-color: var(--success-color);
        }

        .feature-card.resell {
            border-left-color: var(--warning-color);
        }

        .feature-card.calculator {
            border-left-color: var(--info-color);
        }

        .feature-card.dashboard {
            border-left-color: var(--primary-color);
        }

        .feature-icon {
            width: 60px;
            height: 60px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 1rem;
        }

        .company-icon {
            background: linear-gradient(135deg, var(--success-color), #20c997);
            color: white;
        }

        .resell-icon {
            background: linear-gradient(135deg, var(--warning-color), #fd7e14);
            color: white;
        }

        .calculator-icon {
            background: linear-gradient(135deg, var(--info-color), #6f42c1);
            color: white;
        }

        .dashboard-icon {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
        }

        .stats-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            margin: 1rem 0;
        }

        .stats-number {
            font-size: 2rem;
            font-weight: bold;
            color: var(--primary-color);
        }

        .section-title {
            color: white;
            text-align: center;
            margin: 3rem 0 2rem;
            font-size: 2.5rem;
            font-weight: 300;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }

        .subsection-title {
            color: white;
            font-size: 1.5rem;
            margin: 2rem 0 1rem;
            font-weight: 500;
        }

        .btn-custom {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border: none;
            color: white;
            padding: 0.75rem 2rem;
            border-radius: 25px;
            font-size: 1.1rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
            color: white;
        }

        .business-model {
            background: rgba(255,255,255,0.1);
            border-radius: 15px;
            padding: 2rem;
            margin: 1rem 0;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.2);
        }

        .business-model h4 {
            color: white;
            margin-bottom: 1rem;
        }

        .business-model .feature-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }

        .feature-item {
            background: rgba(255,255,255,0.1);
            padding: 1rem;
            border-radius: 10px;
            text-align: center;
            color: white;
        }

        .feature-item i {
            font-size: 2rem;
            margin-bottom: 0.5rem;
            display: block;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="company_owner_dashboard.php">
                <i class="fas fa-crown me-2"></i>APS Dream Homes - Owner Portal
            </a>
            <div class="navbar-nav ms-auto">
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle me-1"></i>
                        <?php echo $_SESSION['admin_username'] ?? 'Company Owner'; ?>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="company_owner_dashboard.php">
                            <i class="fas fa-tachometer-alt me-2"></i>Main Dashboard
                        </a></li>
                        <li><a class="dropdown-item" href="hybrid_real_estate_dashboard.php">
                            <i class="fas fa-building me-2"></i>Hybrid Real Estate
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="logout.php">
                            <i class="fas fa-sign-out-alt me-2"></i>Logout
                        </a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="container-fluid mt-5">
        <div class="hero-section">
            <h1 class="display-4 text-white mb-3">
                <i class="fas fa-crown text-warning me-3"></i>
                Hybrid Real Estate Control Center
            </h1>
            <p class="lead text-white-50 mb-4">
                Complete command center for your dual business model - Company colony plotting with MLM commissions
                and Resell properties with fixed commissions
            </p>
            <div class="d-flex justify-content-center gap-3 flex-wrap">
                <a href="#company-features" class="btn btn-custom">
                    <i class="fas fa-building me-2"></i>Company Properties (MLM)
                </a>
                <a href="#resell-features" class="btn btn-custom">
                    <i class="fas fa-home me-2"></i>Resell Properties (Fixed)
                </a>
                <a href="#tools" class="btn btn-custom">
                    <i class="fas fa-tools me-2"></i>Management Tools
                </a>
            </div>
        </div>

        <!-- Business Overview Stats -->
        <div class="row mb-5">
            <div class="col-md-3">
                <div class="stats-card">
                    <i class="fas fa-building text-success fa-2x mb-2"></i>
                    <div class="stats-number" id="companyCount">Loading...</div>
                    <p class="mb-0">Company Properties</p>
                    <small class="text-muted">MLM Structure</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <i class="fas fa-home text-warning fa-2x mb-2"></i>
                    <div class="stats-number" id="resellCount">Loading...</div>
                    <p class="mb-0">Resell Properties</p>
                    <small class="text-muted">Fixed Commission</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <i class="fas fa-coins text-info fa-2x mb-2"></i>
                    <div class="stats-number" id="commissionTotal">Loading...</div>
                    <p class="mb-0">Commission Paid</p>
                    <small class="text-muted">Hybrid System</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <i class="fas fa-chart-line text-primary fa-2x mb-2"></i>
                    <div class="stats-number">+300%</div>
                    <p class="mb-0">Business Growth</p>
                    <small class="text-muted">Expected Impact</small>
                </div>
            </div>
        </div>

        <!-- Company Properties Section -->
        <div id="company-features">
            <h2 class="section-title">🏗️ Company Properties (MLM Structure)</h2>
            <div class="business-model">
                <h4 class="text-success">
                    <i class="fas fa-building me-2"></i>
                    Colony Plotting with Multi-Level Marketing
                </h4>
                <p class="text-white-50">
                    Your developed colonies with 7-level MLM commission structure. Perfect for team building and exponential growth.
                </p>

                <div class="feature-list">
                    <div class="feature-item">
                        <i class="fas fa-sitemap"></i>
                        <h6>7-Level Hierarchy</h6>
                        <small>Associate to Site Manager</small>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-percentage"></i>
                        <h6>Up to 46% Commission</h6>
                        <small>Multiple bonus types</small>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-users"></i>
                        <h6>Team Building</h6>
                        <small>Incentivized growth</small>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-chart-line"></i>
                        <h6>Performance Bonuses</h6>
                        <small>Leadership incentives</small>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-md-6">
                        <a href="../development_cost_calculator.php" class="feature-card company">
                            <div class="d-flex align-items-center">
                                <div class="feature-icon company-icon">
                                    <i class="fas fa-calculator"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="mb-1">💰 Development Cost Calculator</h5>
                                    <p class="mb-0 text-muted">Calculate plot rates with integrated commission costs</p>
                                </div>
                                <i class="fas fa-arrow-right text-muted"></i>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-6">
                        <a href="../commission_plan_builder.php" class="feature-card company">
                            <div class="d-flex align-items-center">
                                <div class="feature-icon company-icon">
                                    <i class="fas fa-cogs"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="mb-1">⚙️ Commission Plan Builder</h5>
                                    <p class="mb-0 text-muted">Create and customize MLM commission structures</p>
                                </div>
                                <i class="fas fa-arrow-right text-muted"></i>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Resell Properties Section -->
        <div id="resell-features">
            <h2 class="section-title">🏠 Resell Properties (Fixed Commission)</h2>
            <div class="business-model">
                <h4 class="text-warning">
                    <i class="fas fa-home me-2"></i>
                    External Property Sales with Fixed Commissions
                </h4>
                <p class="text-white-50">
                    Resell properties from external sources with simple, transparent fixed commission rates.
                </p>

                <div class="feature-list">
                    <div class="feature-item">
                        <i class="fas fa-percentage"></i>
                        <h6>Fixed Commission Rates</h6>
                        <small>2-5% based on property type</small>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-clock"></i>
                        <h6>Quick Turnaround</h6>
                        <small>Fast sales processing</small>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-balance-scale"></i>
                        <h6>Transparent System</h6>
                        <small>Clear commission structure</small>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-expand-arrows-alt"></i>
                        <h6>Market Expansion</h6>
                        <small>Diversified income streams</small>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-md-6">
                        <a href="../property_management.php" class="feature-card resell">
                            <div class="d-flex align-items-center">
                                <div class="feature-icon resell-icon">
                                    <i class="fas fa-building"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="mb-1">🏢 Property Management</h5>
                                    <p class="mb-0 text-muted">Manage both company and resell properties</p>
                                </div>
                                <i class="fas fa-arrow-right text-muted"></i>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-6">
                        <a href="../commission_plan_calculator.php" class="feature-card resell">
                            <div class="d-flex align-items-center">
                                <div class="feature-icon resell-icon">
                                    <i class="fas fa-calculator"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="mb-1">🎯 Commission Calculator</h5>
                                    <p class="mb-0 text-muted">Test different commission scenarios</p>
                                </div>
                                <i class="fas fa-arrow-right text-muted"></i>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Management Tools Section -->
        <div id="tools">
            <h2 class="section-title">🛠️ Management Tools & Analytics</h2>
            <div class="business-model">
                <h4 class="text-info">
                    <i class="fas fa-tools me-2"></i>
                    Complete Business Management Suite
                </h4>
                <p class="text-white-50">
                    Powerful tools to manage, analyze, and optimize your hybrid real estate business.
                </p>

                <div class="row mt-4">
                    <div class="col-md-4">
                        <a href="../hybrid_commission_dashboard.php" class="feature-card dashboard">
                            <div class="d-flex align-items-center">
                                <div class="feature-icon dashboard-icon">
                                    <i class="fas fa-chart-line"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="mb-1">📊 Hybrid Commission Dashboard</h5>
                                    <p class="mb-0 text-muted">Real-time performance analytics for both business types</p>
                                </div>
                                <i class="fas fa-arrow-right text-muted"></i>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="associates_management.php" class="feature-card dashboard">
                            <div class="d-flex align-items-center">
                                <div class="feature-icon dashboard-icon">
                                    <i class="fas fa-users"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="mb-1">👥 Associate Management</h5>
                                    <p class="mb-0 text-muted">Manage MLM associates, levels, and performance</p>
                                </div>
                                <i class="fas fa-arrow-right text-muted"></i>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="business_intelligence.php" class="feature-card dashboard">
                            <div class="d-flex align-items-center">
                                <div class="feature-icon dashboard-icon">
                                    <i class="fas fa-chart-pie"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="mb-1">📈 Business Intelligence</h5>
                                    <p class="mb-0 text-muted">Advanced analytics and business insights</p>
                                </div>
                                <i class="fas fa-arrow-right text-muted"></i>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Commission Structure Overview -->
        <div class="row mt-5">
            <div class="col-12">
                <h2 class="section-title">💰 Commission Structures</h2>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6 mb-4">
                <div class="business-model">
                    <h4 class="text-success">Company Properties (MLM)</h4>
                    <div class="table-responsive">
                        <table class="table table-dark table-striped">
                            <thead>
                                <tr>
                                    <th>Level</th>
                                    <th>Direct</th>
                                    <th>Team</th>
                                    <th>Level Bonus</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Associate (1)</td>
                                    <td>6%</td>
                                    <td>2%</td>
                                    <td>0%</td>
                                    <td class="text-warning"><strong>8%</strong></td>
                                </tr>
                                <tr>
                                    <td>BDM (3)</td>
                                    <td>10%</td>
                                    <td>4%</td>
                                    <td>2%</td>
                                    <td class="text-warning"><strong>20%</strong></td>
                                </tr>
                                <tr>
                                    <td>Site Manager (7)</td>
                                    <td>20%</td>
                                    <td>8%</td>
                                    <td>6%</td>
                                    <td class="text-warning"><strong>46%</strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-lg-6 mb-4">
                <div class="business-model">
                    <h4 class="text-warning">Resell Properties (Fixed)</h4>
                    <div class="table-responsive">
                        <table class="table table-dark table-striped">
                            <thead>
                                <tr>
                                    <th>Property Type</th>
                                    <th>Commission Rate</th>
                                    <th>Value Range</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Plots</td>
                                    <td class="text-warning"><strong>3-5%</strong></td>
                                    <td>₹0-5Cr+</td>
                                </tr>
                                <tr>
                                    <td>Flats</td>
                                    <td class="text-warning"><strong>2-3%</strong></td>
                                    <td>₹0-5Cr+</td>
                                </tr>
                                <tr>
                                    <td>House</td>
                                    <td class="text-warning"><strong>3%</strong></td>
                                    <td>All Values</td>
                                </tr>
                                <tr>
                                    <td>Commercial</td>
                                    <td class="text-warning"><strong>4%</strong></td>
                                    <td>All Values</td>
                                </tr>
                                <tr>
                                    <td>Land</td>
                                    <td class="text-warning"><strong>2%</strong></td>
                                    <td>All Values</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row mt-5">
            <div class="col-12 text-center">
                <h2 class="section-title">⚡ Quick Actions</h2>
                <div class="d-flex justify-content-center gap-3 flex-wrap">
                    <a href="../development_cost_calculator.php" class="btn btn-success btn-lg">
                        <i class="fas fa-calculator me-2"></i>Calculate Costs
                    </a>
                    <a href="../property_management.php" class="btn btn-primary btn-lg">
                        <i class="fas fa-building me-2"></i>Manage Properties
                    </a>
                    <a href="../hybrid_commission_dashboard.php" class="btn btn-warning btn-lg">
                        <i class="fas fa-chart-line me-2"></i>View Dashboard
                    </a>
                    <a href="../commission_plan_builder.php" class="btn btn-info btn-lg">
                        <i class="fas fa-cogs me-2"></i>Build Plans
                    </a>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center mt-5 pb-4">
            <p class="text-white-50">
                <i class="fas fa-crown me-2"></i>
                <strong>APS Dream Homes - Hybrid Real Estate Empire</strong><br>
                Company Properties (MLM) + Resell Properties (Fixed) = Unlimited Growth Potential
            </p>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Load real-time stats
        document.addEventListener('DOMContentLoaded', function() {
            // Simulate loading stats (in real app, this would be AJAX calls)
            setTimeout(() => {
                document.getElementById('companyCount').textContent = '25';
                document.getElementById('resellCount').textContent = '18';
                document.getElementById('commissionTotal').textContent = '₹15.25L';
            }, 1000);
        });
    </script>
</body>
</html>
