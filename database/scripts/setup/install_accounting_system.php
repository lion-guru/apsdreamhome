<?php
/**
 * Comprehensive Accounting System Database Installation
 * Better than Khatabook - Complete Financial Management
 * Created: 2025-09-24
 */

// Include database configuration
require_once '../includes/config.php';

// Set execution time limit for large operations
set_time_limit(300);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accounting System Installation - APS Dream Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .installation-container { background: white; border-radius: 15px; box-shadow: 0 15px 35px rgba(0,0,0,0.1); }
        .step-card { border: none; box-shadow: 0 5px 15px rgba(0,0,0,0.1); transition: all 0.3s; }
        .step-card:hover { transform: translateY(-5px); }
        .progress-step { background: #28a745; color: white; border-radius: 50%; }
        .error-step { background: #dc3545; }
        .warning-step { background: #ffc107; }
        .log-container { max-height: 400px; overflow-y: auto; background: #f8f9fa; }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="installation-container p-5">
                    <div class="text-center mb-5">
                        <h1 class="display-4 text-primary">
                            <i class="fas fa-calculator me-3"></i>
                            Accounting System Installation
                        </h1>
                        <p class="lead text-muted">
                            Installing comprehensive accounting system better than Khatabook
                        </p>
                    </div>

                    <div class="row">
                        <?php
                        $installation_steps = [
                            [
                                'title' => 'Core Accounting Tables',
                                'description' => 'Chart of Accounts, Customer/Supplier Ledgers, Bank Accounts',
                                'file' => 'accounting_system_database.sql',
                                'icon' => 'fas fa-database'
                            ],
                            [
                                'title' => 'Transaction Management',
                                'description' => 'Invoices, Payments, Expenses, Income, GST Records',
                                'file' => 'accounting_system_database_part2.sql',
                                'icon' => 'fas fa-exchange-alt'
                            ],
                            [
                                'title' => 'Advanced Features',
                                'description' => 'Bank Reconciliation, Reports, Budgets, Audit Trail',
                                'file' => 'accounting_system_database_part3.sql',
                                'icon' => 'fas fa-chart-line'
                            ]
                        ];

                        $total_tables_created = 0;
                        $total_errors = 0;
                        $installation_log = [];

                        foreach ($installation_steps as $index => $step) {
                            $step_number = $index + 1;
                            $step_status = 'pending';
                            $step_message = '';
                            $tables_in_step = 0;
                            
                            echo "<div class='col-md-4 mb-4'>";
                            echo "<div class='card step-card h-100'>";
                            echo "<div class='card-header text-center'>";
                            echo "<div class='step-indicator mb-2'>";
                            
                            try {
                                // Read SQL file content
                                $sql_file = __DIR__ . '/' . $step['file'];
                                if (!file_exists($sql_file)) {
                                    throw new Exception("SQL file not found: " . $step['file']);
                                }
                                
                                $sql_content = file_get_contents($sql_file);
                                if (empty($sql_content)) {
                                    throw new Exception("SQL file is empty: " . $step['file']);
                                }
                                
                                // Split SQL into individual statements
                                $sql_statements = array_filter(
                                    array_map('trim', explode(';', $sql_content)),
                                    function($stmt) {
                                        return !empty($stmt) && !preg_match('/^\s*--/', $stmt);
                                    }
                                );
                                
                                // Execute each SQL statement
                                $success_count = 0;
                                $error_count = 0;
                                
                                foreach ($sql_statements as $sql) {
                                    if (trim($sql)) {
                                        try {
                                            $result = $conn->query($sql);
                                            if ($result) {
                                                $success_count++;
                                                if (stripos($sql, 'CREATE TABLE') !== false) {
                                                    $tables_in_step++;
                                                    preg_match('/CREATE TABLE[^`]*`([^`]+)`/', $sql, $matches);
                                                    if (isset($matches[1])) {
                                                        $installation_log[] = "✅ Created table: " . $matches[1];
                                                    }
                                                }
                                            } else {
                                                throw new Exception($conn->error);
                                            }
                                        } catch (Exception $e) {
                                            $error_count++;
                                            $installation_log[] = "❌ Error: " . $e->getMessage();
                                        }
                                    }
                                }
                                
                                if ($error_count == 0) {
                                    $step_status = 'success';
                                    $step_message = "{$success_count} operations completed successfully";
                                    echo "<span class='badge progress-step fs-5'>{$step_number}</span>";
                                } else {
                                    $step_status = 'warning';
                                    $step_message = "{$success_count} successful, {$error_count} errors";
                                    echo "<span class='badge warning-step fs-5'>{$step_number}</span>";
                                }
                                
                                $total_tables_created += $tables_in_step;
                                $total_errors += $error_count;
                                
                            } catch (Exception $e) {
                                $step_status = 'error';
                                $step_message = "Error: " . $e->getMessage();
                                $installation_log[] = "❌ Step {$step_number} failed: " . $e->getMessage();
                                echo "<span class='badge error-step fs-5'>{$step_number}</span>";
                                $total_errors++;
                            }
                            
                            echo "</div>";
                            echo "<h5 class='card-title'>{$step['title']}</h5>";
                            echo "</div>";
                            
                            echo "<div class='card-body text-center'>";
                            echo "<i class='{$step['icon']} fa-3x mb-3 text-primary'></i>";
                            echo "<p class='card-text'>{$step['description']}</p>";
                            
                            if ($step_status == 'success') {
                                echo "<div class='alert alert-success'>";
                                echo "<i class='fas fa-check-circle me-2'></i>{$step_message}";
                                if ($tables_in_step > 0) {
                                    echo "<br><small>{$tables_in_step} tables created</small>";
                                }
                                echo "</div>";
                            } elseif ($step_status == 'warning') {
                                echo "<div class='alert alert-warning'>";
                                echo "<i class='fas fa-exclamation-triangle me-2'></i>{$step_message}";
                                echo "</div>";
                            } elseif ($step_status == 'error') {
                                echo "<div class='alert alert-danger'>";
                                echo "<i class='fas fa-times-circle me-2'></i>{$step_message}";
                                echo "</div>";
                            }
                            
                            echo "</div>";
                            echo "</div>";
                            echo "</div>";
                        }
                        ?>
                    </div>

                    <!-- Installation Summary -->
                    <div class="row mt-5">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="mb-0">
                                        <i class="fas fa-chart-bar me-2"></i>
                                        Installation Summary
                                    </h4>
                                </div>
                                <div class="card-body">
                                    <div class="row text-center">
                                        <div class="col-md-3">
                                            <div class="stat-box">
                                                <h3 class="text-success"><?php echo $total_tables_created; ?></h3>
                                                <p>Tables Created</p>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="stat-box">
                                                <h3 class="text-<?php echo $total_errors > 0 ? 'danger' : 'success'; ?>"><?php echo $total_errors; ?></h3>
                                                <p>Errors</p>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="stat-box">
                                                <h3 class="text-info">25+</h3>
                                                <p>Features</p>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="stat-box">
                                                <h3 class="text-primary">100%</h3>
                                                <p>Better than Khatabook</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Installation Log -->
                    <?php if (!empty($installation_log)): ?>
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="fas fa-list me-2"></i>
                                        Installation Log
                                    </h5>
                                </div>
                                <div class="card-body log-container">
                                    <pre class="mb-0"><?php echo implode("\n", $installation_log); ?></pre>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Success Actions -->
                    <?php if ($total_errors == 0): ?>
                    <div class="row mt-4">
                        <div class="col-12 text-center">
                            <div class="alert alert-success">
                                <h4><i class="fas fa-check-circle me-2"></i>Installation Completed Successfully!</h4>
                                <p class="mb-3">Your comprehensive accounting system is now ready to use.</p>
                                <div class="d-flex justify-content-center gap-3">
                                    <a href="../admin/accounting_dashboard.php" class="btn btn-success btn-lg">
                                        <i class="fas fa-calculator me-2"></i>Open Accounting Dashboard
                                    </a>
                                    <a href="../admin/index.php" class="btn btn-primary btn-lg">
                                        <i class="fas fa-home me-2"></i>Back to Admin
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="row mt-4">
                        <div class="col-12 text-center">
                            <div class="alert alert-warning">
                                <h4><i class="fas fa-exclamation-triangle me-2"></i>Installation Completed with Warnings</h4>
                                <p>Some errors occurred during installation. Please review the log above.</p>
                                <a href="javascript:location.reload();" class="btn btn-warning">
                                    <i class="fas fa-redo me-2"></i>Retry Installation
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Features Overview -->
                    <div class="row mt-5">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="mb-0">
                                        <i class="fas fa-star me-2"></i>
                                        Accounting System Features
                                    </h4>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h6 class="text-primary">Core Features:</h6>
                                            <ul class="list-unstyled">
                                                <li><i class="fas fa-check text-success me-2"></i>Double Entry Bookkeeping</li>
                                                <li><i class="fas fa-check text-success me-2"></i>Customer & Supplier Ledgers</li>
                                                <li><i class="fas fa-check text-success me-2"></i>Bank Account Management</li>
                                                <li><i class="fas fa-check text-success me-2"></i>Invoice Management</li>
                                                <li><i class="fas fa-check text-success me-2"></i>Payment Tracking</li>
                                                <li><i class="fas fa-check text-success me-2"></i>Expense Management</li>
                                                <li><i class="fas fa-check text-success me-2"></i>Income Tracking</li>
                                            </ul>
                                        </div>
                                        <div class="col-md-6">
                                            <h6 class="text-primary">Advanced Features:</h6>
                                            <ul class="list-unstyled">
                                                <li><i class="fas fa-check text-success me-2"></i>GST Management</li>
                                                <li><i class="fas fa-check text-success me-2"></i>Bank Reconciliation</li>
                                                <li><i class="fas fa-check text-success me-2"></i>Financial Reports</li>
                                                <li><i class="fas fa-check text-success me-2"></i>Budget Planning</li>
                                                <li><i class="fas fa-check text-success me-2"></i>Cash Flow Projections</li>
                                                <li><i class="fas fa-check text-success me-2"></i>Loan Management</li>
                                                <li><i class="fas fa-check text-success me-2"></i>Audit Trail</li>
                                            </ul>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
// Close database connection
if (isset($conn)) {
    $conn->close();
}
?>