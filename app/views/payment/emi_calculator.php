<?php include __DIR__ . "/../layouts/header.php"; ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-header">
                <h1 class="h2 mb-4">
                    <i class="fas fa-credit-card"></i> Emi calculator
                </h1>
            </div>
            
            <div class="card">
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Emi calculator - APS Dream Home Payment System
                    </div>
                    <div class="row">
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">EMI Calculator</h5>
                                    <form id="emiCalculatorForm">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="principal" class="form-label">Principal Amount (₹)</label>
                                                    <input type="number" class="form-control" id="principal" name="principal" value="1000000" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="rate" class="form-label">Interest Rate (%)</label>
                                                    <input type="number" class="form-control" id="rate" name="rate" value="8.5" step="0.1" required>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="tenure" class="form-label">Tenure (Months)</label>
                                                    <input type="number" class="form-control" id="tenure" name="tenure" value="12" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="down_payment" class="form-label">Down Payment (%)</label>
                                                    <input type="number" class="form-control" id="down_payment" name="down_payment" value="20" step="0.1">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="text-center">
                                            <button type="button" class="btn btn-primary" onclick="calculateEMI()">
                                                <i class="fas fa-calculator"></i> Calculate EMI
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h5 class="card-title">EMI Details</h5>
                                    <div id="emiResult">
                                        <div class="mb-3">
                                            <label>Monthly EMI:</label>
                                            <h4 class="text-primary">₹0</h4>
                                        </div>
                                        <div class="mb-3">
                                            <label>Total Interest:</label>
                                            <h5 class="text-warning">₹0</h5>
                                        </div>
                                        <div class="mb-3">
                                            <label>Total Amount:</label>
                                            <h5 class="text-success">₹0</h5>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <script>
                    function calculateEMI() {
                        const principal = parseFloat(document.getElementById("principal").value);
                        const rate = parseFloat(document.getElementById("rate").value);
                        const tenure = parseInt(document.getElementById("tenure").value);
                        const downPayment = parseFloat(document.getElementById("down_payment").value);
                        
                        const loanAmount = principal - (principal * downPayment / 100);
                        const monthlyRate = rate / 12 / 100;
                        const emi = loanAmount * monthlyRate * Math.pow(1 + monthlyRate, tenure) / (Math.pow(1 + monthlyRate, tenure) - 1);
                        const totalAmount = emi * tenure;
                        const totalInterest = totalAmount - loanAmount;
                        
                        document.getElementById("emiResult").innerHTML = `
                            <div class="mb-3">
                                <label>Monthly EMI:</label>
                                <h4 class="text-primary">₹${emi.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",")}</h4>
                            </div>
                            <div class="mb-3">
                                <label>Total Interest:</label>
                                <h5 class="text-warning">₹${totalInterest.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",")}</h5>
                            </div>
                            <div class="mb-3">
                                <label>Total Amount:</label>
                                <h5 class="text-success">₹${totalAmount.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",")}</h5>
                            </div>
                        `;
                    }
                    
                    // Calculate on load
                    calculateEMI();
                    </script>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . "/../layouts/footer.php"; ?>