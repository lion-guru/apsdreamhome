<?php
/**
 * Create EMI Plan View
 */
$bookings = $bookings ?? [];
$page_title = $page_title ?? 'Create EMI Plan';
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
                <h2 class="mb-1">Create EMI Plan</h2>
                <p class="text-muted mb-0">Set up a new EMI payment plan for a booking</p>
            </div>
            <a href="<?php echo $base; ?>/admin/emi" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to EMI Plans
            </a>
        </div>
        
        <?php if (!empty($bookings)): ?>
        <!-- EMI Form -->
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <form id="emiForm" action="<?php echo $base; ?>/admin/emi/store" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                    
                    <div class="row">
                        <!-- Booking Selection -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Select Booking *</label>
                            <select name="booking_id" class="form-select" required>
                                <option value="">Choose a booking...</option>
                                <?php foreach ($bookings as $booking): ?>
                                    <option value="<?php echo $booking['id']; ?>" data-amount="<?php echo $booking['total_amount']; ?>">
                                        <?php echo htmlspecialchars(booking['booking_number'] ?? ''); ?> - 
                                        <?php echo htmlspecialchars(booking['customer_name'] ?? ''); ?> - 
                                        ₹<?php echo number_format(floatval(booking['total_amount'] ?? 0)); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <!-- Total Amount -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Total Amount (₹) *</label>
                            <input type="number" name="total_amount" class="form-control" step="0.01" min="0" required readonly>
                        </div>
                        
                        <!-- Down Payment -->
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Down Payment (₹) *</label>
                            <input type="number" name="down_payment" class="form-control" step="0.01" min="0" required>
                        </div>
                        
                        <!-- Interest Rate -->
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Interest Rate (%) *</label>
                            <input type="number" name="interest_rate" class="form-control" step="0.01" min="0" max="100" value="8.5" required>
                        </div>
                        
                        <!-- Tenure -->
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Tenure (Months) *</label>
                            <input type="number" name="tenure_months" class="form-control" min="1" max="360" value="120" required>
                        </div>
                    </div>
                    
                    <!-- EMI Preview -->
                    <div class="alert alert-info mt-3" id="emiPreview" style="display: none;">
                        <h6><i class="fas fa-calculator me-2"></i>EMI Preview</h6>
                        <div class="row">
                            <div class="col-md-3">
                                <small>Loan Amount:</small>
                                <p class="mb-0 fw-bold" id="loanAmount">₹0</p>
                            </div>
                            <div class="col-md-3">
                                <small>Monthly EMI:</small>
                                <p class="mb-0 fw-bold" id="monthlyEmi">₹0</p>
                            </div>
                            <div class="col-md-3">
                                <small>Total Interest:</small>
                                <p class="mb-0 fw-bold" id="totalInterest">₹0</p>
                            </div>
                            <div class="col-md-3">
                                <small>Total Payable:</small>
                                <p class="mb-0 fw-bold" id="totalPayable">₹0</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Submit Buttons -->
                    <div class="d-flex justify-content-between mt-4">
                        <a href="<?php echo $base; ?>/admin/emi" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Create EMI Plan
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <?php else: ?>
        <!-- No Bookings Available -->
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle me-2"></i>
            No confirmed bookings available without EMI plans. Please create a booking first.
        </div>
        <?php endif; ?>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-fill total amount when booking is selected
        document.querySelector('select[name="booking_id"]').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const amount = selectedOption.getAttribute('data-amount');
            document.querySelector('input[name="total_amount"]').value = amount || 0;
            calculateEMI();
        });
        
        // Calculate EMI preview
        function calculateEMI() {
            const totalAmount = parseFloat(document.querySelector('input[name="total_amount"]').value) || 0;
            const downPayment = parseFloat(document.querySelector('input[name="down_payment"]').value) || 0;
            const interestRate = parseFloat(document.querySelector('input[name="interest_rate"]').value) || 0;
            const tenureMonths = parseInt(document.querySelector('input[name="tenure_months"]').value) || 0;
            
            if (totalAmount > 0 && tenureMonths > 0) {
                const loanAmount = totalAmount - downPayment;
                const monthlyInterest = interestRate / 12 / 100;
                const emiAmount = loanAmount * monthlyInterest * Math.pow(1 + monthlyInterest, tenureMonths) / 
                    (Math.pow(1 + monthlyInterest, tenureMonths) - 1);
                const totalPayable = downPayment + (emiAmount * tenureMonths);
                const totalInterest = totalPayable - totalAmount;
                
                document.getElementById('loanAmount').textContent = '₹' + loanAmount.toLocaleString('en-IN', {maximumFractionDigits: 2});
                document.getElementById('monthlyEmi').textContent = '₹' + emiAmount.toLocaleString('en-IN', {maximumFractionDigits: 2});
                document.getElementById('totalInterest').textContent = '₹' + totalInterest.toLocaleString('en-IN', {maximumFractionDigits: 2});
                document.getElementById('totalPayable').textContent = '₹' + totalPayable.toLocaleString('en-IN', {maximumFractionDigits: 2});
                document.getElementById('emiPreview').style.display = 'block';
            }
        }
        
        // Recalculate on input change
        document.querySelectorAll('input[name="down_payment"], input[name="interest_rate"], input[name="tenure_months"]').forEach(input => {
            input.addEventListener('input', calculateEMI);
        });
        
        // Form submission
        document.getElementById('emiForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch(this.action, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('EMI plan created successfully!');
                    window.location.href = '<?php echo $base; ?>/admin/emi';
                } else {
                    alert(data.message || 'Failed to create EMI plan');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while creating EMI plan');
            });
        });
    </script>
</body>
</html>
