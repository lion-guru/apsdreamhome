<div class="container-fluid mt-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb bg-transparent p-0 mb-2">
                    <li class="breadcrumb-item"><a href="/customer/dashboard">डैशबोर्ड</a></li>
                    <li class="breadcrumb-item active" aria-current="page">EMI कैलकुलेटर</li>
                </ol>
            </nav>
            <h2 class="font-weight-bold text-gray-900">EMI कैलकुलेटर (EMI Calculator)</h2>
            <p class="text-muted">अपनी प्रॉपर्टी के लिए मासिक किस्तों (EMI) की गणना करें।</p>
        </div>
    </div>

    <div class="row">
        <!-- Calculator Form -->
        <div class="col-lg-5 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3 bg-primary text-white">
                    <h6 class="m-0 font-weight-bold">विवरण भरें</h6>
                </div>
                <div class="card-body">
                    <form id="emiForm">
                        <div class="form-group mb-4">
                            <label class="font-weight-bold text-gray-800">प्रॉपर्टी की कुल कीमत (₹)</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">₹</span>
                                </div>
                                <input type="number" id="totalPrice" class="form-control form-control-lg" value="<?= $property['price'] ?? 1000000 ?>" placeholder="जैसे: 2500000">
                            </div>
                        </div>

                        <div class="form-group mb-4">
                            <label class="font-weight-bold text-gray-800 d-flex justify-content-between">
                                <span>डाउन पेमेंट (Down Payment)</span>
                                <span id="downPaymentPercent" class="text-primary small">20%</span>
                            </label>
                            <div class="input-group mb-2">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">₹</span>
                                </div>
                                <input type="number" id="downPayment" class="form-control" value="<?= ($property['price'] ?? 1000000) * 0.2 ?>" placeholder="जैसे: 500000">
                            </div>
                            <input type="range" class="custom-range" id="downPaymentRange" min="0" max="100" value="20">
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-4">
                                    <label class="font-weight-bold text-gray-800">ब्याज दर (Interest Rate %)</label>
                                    <div class="input-group">
                                        <input type="number" id="interestRate" class="form-control" value="8.5" step="0.1" placeholder="जैसे: 8.5">
                                        <div class="input-group-append">
                                            <span class="input-group-text">%</span>
                                        </div>
                                    </div>
                                    <input type="range" class="custom-range mt-2" id="interestRange" min="5" max="20" step="0.1" value="8.5">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-4">
                                    <label class="font-weight-bold text-gray-800">लोन अवधि (Years)</label>
                                    <div class="input-group">
                                        <input type="number" id="loanTenure" class="form-control" value="15" placeholder="जैसे: 20">
                                        <div class="input-group-append">
                                            <span class="input-group-text">साल</span>
                                        </div>
                                    </div>
                                    <input type="range" class="custom-range mt-2" id="tenureRange" min="1" max="30" value="15">
                                </div>
                            </div>
                        </div>

                        <button type="button" id="calculateBtn" class="btn btn-primary btn-block btn-lg font-weight-bold mt-2">
                            गणना करें (Calculate)
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Results Display -->
        <div class="col-lg-7 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">गणना का परिणाम</h6>
                    <button class="btn btn-sm btn-outline-secondary" onclick="window.print()">
                        <i class="fas fa-print mr-1"></i> प्रिंट करें
                    </button>
                </div>
                <div class="card-body">
                    <div class="row text-center mb-4">
                        <div class="col-md-4 mb-3 mb-md-0">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">मासिक EMI</div>
                            <div class="h3 mb-0 font-weight-bold text-gray-800" id="monthlyEMI">₹0</div>
                        </div>
                        <div class="col-md-4 mb-3 mb-md-0">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">कुल लोन राशि</div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800" id="loanAmount">₹0</div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">कुल ब्याज</div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800" id="totalInterest">₹0</div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <canvas id="emiChart" height="250"></canvas>
                        </div>
                        <div class="col-md-6 d-flex align-items-center">
                            <div class="w-100">
                                <div class="mb-3 d-flex justify-content-between align-items-center">
                                    <div class="small font-weight-bold"><i class="fas fa-circle text-primary mr-1"></i> मूल राशि (Principal)</div>
                                    <div class="small" id="principalPercent">0%</div>
                                </div>
                                <div class="progress progress-sm mb-4">
                                    <div class="progress-bar bg-primary" id="principalBar" role="progressbar" style="width: 0%"></div>
                                </div>
                                <div class="mb-3 d-flex justify-content-between align-items-center">
                                    <div class="small font-weight-bold"><i class="fas fa-circle text-info mr-1"></i> कुल ब्याज (Interest)</div>
                                    <div class="small" id="interestPercent">0%</div>
                                </div>
                                <div class="progress progress-sm mb-4">
                                    <div class="progress-bar bg-info" id="interestBar" role="progressbar" style="width: 0%"></div>
                                </div>
                                <div class="card bg-light border-0">
                                    <div class="card-body p-3">
                                        <div class="d-flex justify-content-between mb-1">
                                            <span class="small font-weight-bold">कुल भुगतान:</span>
                                            <span class="small font-weight-bold text-dark" id="totalPayment">₹0</span>
                                        </div>
                                        <div class="small text-muted text-xs">लोन अवधि के अंत तक चुकाई जाने वाली कुल राशि।</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Amortization Table (Preview) -->
                    <div class="mt-4">
                        <h6 class="font-weight-bold text-gray-900 mb-3">सालाना भुगतान विवरण (Amortization Schedule)</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered" id="amortizationTable">
                                <thead class="bg-light text-center">
                                    <tr>
                                        <th class="small">वर्ष</th>
                                        <th class="small">ब्याज</th>
                                        <th class="small">मूल राशि</th>
                                        <th class="small">कुल भुगतान</th>
                                        <th class="small">बकाया राशि</th>
                                    </tr>
                                </thead>
                                <tbody class="text-center small">
                                    <!-- Dynamic Rows -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <form action="/customer/emi-save" method="POST">
                            <?php echo getCsrfField(); ?>
                            <input type="hidden" name="property_id" value="<?= $property['id'] ?? '' ?>">
                            <input type="hidden" name="total_price" id="save_totalPrice">
                            <input type="hidden" name="down_payment" id="save_downPayment">
                            <input type="hidden" name="interest_rate" id="save_interestRate">
                            <input type="hidden" name="tenure" id="save_loanTenure">
                            <input type="hidden" name="monthly_emi" id="save_monthlyEMI">
                            <button type="submit" class="btn btn-outline-primary btn-block">
                                <i class="fas fa-save mr-1"></i> इस गणना को सुरक्षित (Save) करें
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
$extra_js = "
<script src='https://cdn.jsdelivr.net/npm/chart.js'></script>
<script>
    $(document).ready(function() {
        var emiChart;

        function calculateEMI() {
            var totalPrice = parseFloat($('#totalPrice').val()) || 0;
            var downPayment = parseFloat($('#downPayment').val()) || 0;
            var interestRate = parseFloat($('#interestRate').val()) || 0;
            var loanTenure = parseFloat($('#loanTenure').val()) || 0;

            var principal = totalPrice - downPayment;
            if (principal <= 0) principal = 0;

            var monthlyRate = interestRate / (12 * 100);
            var months = loanTenure * 12;

            var emi = 0;
            if (principal > 0 && monthlyRate > 0) {
                emi = principal * monthlyRate * Math.pow(1 + monthlyRate, months) / (Math.pow(1 + monthlyRate, months) - 1);
            } else if (principal > 0 && monthlyRate == 0) {
                emi = principal / months;
            }

            var totalPayment = emi * months;
            var totalInterest = totalPayment - principal;

            // Update display
            $('#monthlyEMI').text('₹' + emi.toLocaleString('en-IN', {maximumFractionDigits: 0}));
            $('#loanAmount').text('₹' + principal.toLocaleString('en-IN', {maximumFractionDigits: 0}));
            $('#totalInterest').text('₹' + totalInterest.toLocaleString('en-IN', {maximumFractionDigits: 0}));
            $('#totalPayment').text('₹' + totalPayment.toLocaleString('en-IN', {maximumFractionDigits: 0}));

            var principalPerc = totalPayment > 0 ? (principal / totalPayment * 100) : 0;
            var interestPerc = totalPayment > 0 ? (totalInterest / totalPayment * 100) : 0;

            $('#principalPercent').text(principalPerc.toFixed(1) + '%');
            $('#interestPercent').text(interestPerc.toFixed(1) + '%');
            $('#principalBar').css('width', principalPerc + '%');
            $('#interestBar').css('width', interestPerc + '%');

            // Update hidden fields for saving
            $('#save_totalPrice').val(totalPrice);
            $('#save_downPayment').val(downPayment);
            $('#save_interestRate').val(interestRate);
            $('#save_loanTenure').val(loanTenure);
            $('#save_monthlyEMI').val(emi.toFixed(2));

            updateChart(principal, totalInterest);
            updateAmortizationTable(principal, monthlyRate, months, emi);
        }

        function updateChart(principal, interest) {
            var ctx = document.getElementById('emiChart').getContext('2d');
            
            if (emiChart) {
                emiChart.destroy();
            }

            emiChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['मूल राशि (Principal)', 'कुल ब्याज (Interest)'],
                    datasets: [{
                        data: [principal, interest],
                        backgroundColor: ['#4e73df', '#36b9cc'],
                        hoverBackgroundColor: ['#2e59d9', '#2c9faf'],
                        hoverBorderColor: 'rgba(234, 236, 244, 1)',
                    }],
                },
                options: {
                    maintainAspectRatio: false,
                    tooltips: {
                        backgroundColor: 'rgb(255,255,255)',
                        bodyFontColor: '#858796',
                        borderColor: '#dddfeb',
                        borderWidth: 1,
                        xPadding: 15,
                        yPadding: 15,
                        displayColors: false,
                        caretPadding: 10,
                    },
                    legend: {
                        display: false
                    },
                    cutoutPercentage: 70,
                },
            });
        }

        function updateAmortizationTable(principal, monthlyRate, months, emi) {
            var tbody = $('#amortizationTable tbody');
            tbody.empty();
            
            if (principal <= 0 || months <= 0) return;

            var balance = principal;
            var years = Math.ceil(months / 12);
            
            for (var i = 1; i <= years; i++) {
                var yearlyInterest = 0;
                var yearlyPrincipal = 0;
                
                for (var j = 1; j <= 12 && (i-1)*12+j <= months; j++) {
                    var interest = balance * monthlyRate;
                    var principalPaid = emi - interest;
                    
                    yearlyInterest += interest;
                    yearlyPrincipal += principalPaid;
                    balance -= principalPaid;
                }
                
                if (balance < 0) balance = 0;

                tbody.append(
                    '<tr>' +
                    '<td>' + i + '</td>' +
                    '<td>₹' + yearlyInterest.toLocaleString('en-IN', {maximumFractionDigits: 0}) + '</td>' +
                    '<td>₹' + yearlyPrincipal.toLocaleString('en-IN', {maximumFractionDigits: 0}) + '</td>' +
                    '<td>₹' + (yearlyInterest + yearlyPrincipal).toLocaleString('en-IN', {maximumFractionDigits: 0}) + '</td>' +
                    '<td>₹' + balance.toLocaleString('en-IN', {maximumFractionDigits: 0}) + '</td>' +
                    '</tr>'
                );
            }
        }

        // Event Listeners
        $('#totalPrice').on('input', function() {
            var val = $(this).val();
            var perc = $('#downPaymentRange').val();
            $('#downPayment').val((val * perc / 100).toFixed(0));
            calculateEMI();
        });

        $('#downPayment').on('input', function() {
            var val = $(this).val();
            var total = $('#totalPrice').val();
            var perc = (val / total * 100);
            $('#downPaymentRange').val(perc);
            $('#downPaymentPercent').text(perc.toFixed(0) + '%');
            calculateEMI();
        });

        $('#downPaymentRange').on('input', function() {
            var perc = $(this).val();
            var total = $('#totalPrice').val();
            $('#downPayment').val((total * perc / 100).toFixed(0));
            $('#downPaymentPercent').text(perc + '%');
            calculateEMI();
        });

        $('#interestRate').on('input', function() {
            $('#interestRange').val($(this).val());
            calculateEMI();
        });

        $('#interestRange').on('input', function() {
            $('#interestRate').val($(this).val());
            calculateEMI();
        });

        $('#loanTenure').on('input', function() {
            $('#tenureRange').val($(this).val());
            calculateEMI();
        });

        $('#tenureRange').on('input', function() {
            $('#loanTenure').val($(this).val());
            calculateEMI();
        });

        $('#calculateBtn').click(calculateEMI);

        // Initial calculation
        calculateEMI();
    });
</script>
";
?>
