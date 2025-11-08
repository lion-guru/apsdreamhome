<?php
session_start();
require("config.php");

// Secure authentication check
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

// Initialize variables
$error = "";
$msg = "";

// CSRF token generation
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if (isset($_POST['plot_id'])) {
    $plotId = $_POST['plot_id'];
    $result = $con->query("SELECT area, plot_price, plot_dimension, plot_facing FROM plot_master WHERE plot_id = $plotId");

    if ($result->num_rows > 0) {
        $plotDetails = $result->fetch_assoc();
        echo json_encode($plotDetails);
    } else {
        echo json_encode([]);
    }
}

if (isset($_POST['submit_booking'])) {
    // Validate CSRF token
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $error = "Invalid CSRF token.";
    } else {
        // Validate and sanitize inputs
        $customer_name = htmlspecialchars(trim($_POST['customer_name']));
        $customer_email = filter_var(trim($_POST['customer_email']), FILTER_SANITIZE_EMAIL);
        $customer_phone = htmlspecialchars(trim($_POST['customer_phone']));
        $customer_address = htmlspecialchars(trim($_POST['customer_address']));
        $aadhar_number = htmlspecialchars(trim($_POST['aadhar_number']));
        $pan_number = htmlspecialchars(trim($_POST['pan_number']));
        $nominee_name = htmlspecialchars(trim($_POST['nominee_name']));
        $nominee_relationship = htmlspecialchars(trim($_POST['nominee_relationship']));
        $booking_type = htmlspecialchars(trim($_POST['booking_type']));
        $payment_plan = htmlspecialchars(trim($_POST['payment_plan']));
        $plot_number = htmlspecialchars(trim($_POST['plot_number']));
        $plot_area = htmlspecialchars(trim($_POST['plot_area']));
        $plot_dimension = htmlspecialchars(trim($_POST['plot_dimension']));
        $plot_facing = htmlspecialchars(trim($_POST['plot_facing']));
        $total_amount = htmlspecialchars(trim($_POST['total_amount']));
        
        $cash_amount = htmlspecialchars(trim($_POST['cash_amount'])) ?: 0;
        $cheque_amount = htmlspecialchars(trim($_POST['cheque_amount'])) ?: 0;
        $online_amount = htmlspecialchars(trim($_POST['online_transaction_amount'])) ?: 0;
        $paid_amount = $cash_amount + $cheque_amount + $online_amount;

        $rest_amount = htmlspecialchars(trim($_POST['rest_amount']));
        $payment_mode = isset($_POST['payment_mode']) ? implode(", ", $_POST['payment_mode']) : '';
        $referral_by = htmlspecialchars(trim($_POST['referral_by']));

        // Error handling for required fields
        if (empty($customer_name) || empty($customer_email) || empty($customer_phone) || empty($total_amount)) {
            $error = "Please fill in all required fields.";
        } elseif (!filter_var($customer_email, FILTER_VALIDATE_EMAIL)) {
            $error = "Please enter a valid email address.";
        } elseif (!preg_match('/^[0-9]{10}$/', $customer_phone)) {
            $error = "Please enter a valid phone number.";
        } else {
            // Insert into database
            $stmt = $con->prepare("INSERT INTO bookings (customer_name, customer_email, customer_phone, customer_address, aadhar_number, pan_number, nominee_name, nominee_relationship, booking_type, payment_plan, plot_number, plot_area, plot_dimension, plot_facing, total_amount, paid_amount, rest_amount, payment_mode, referral_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssssssssssssssss", $customer_name, $customer_email, $customer_phone, $customer_address, $aadhar_number, $pan_number, $nominee_name, $nominee_relationship, $booking_type, $payment_plan, $plot_number, $plot_area, $plot_dimension, $plot_facing, $total_amount, $paid_amount, $rest_amount, $payment_mode, $referral_by);

            if ($stmt->execute()) {
                require_once __DIR__ . '/../includes/functions/notification_util.php';
                addNotification($con, 'Booking', 'Booking created or updated.', $_SESSION['auser'] ?? null);
                log_admin_activity('add_booking', 'Booking for customer name: ' . $customer_name . ', customer email: ' . $customer_email . ', customer phone: ' . $customer_phone . ', total amount: ' . $total_amount);
                $msg = "Booking added successfully!";
            } else {
                $error = "Error while adding booking: " . $stmt->error;
            }

            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Plot Booking Form</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
     <!-- Favicon -->
    <link rel="shortcut icon" type="image/x-icon" href="assets/<?php echo get_asset_url('aps.png', 'images'); ?>">
     <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="<?php echo get_asset_url('css/bootstrap.min.css', 'css'); ?>">
     <!-- Fontawesome CSS -->
    <link rel="stylesheet" href="<?php echo get_asset_url('css/font-awesome.min.css', 'css'); ?>">
    <!-- Main CSS -->
    <link rel="stylesheet" href="<?php echo get_asset_url('css/style.css', 'css'); ?>">
    <link rel="shortcut icon" type="image/x-icon" href="assets/<?php echo get_asset_url('favicon.png', 'images'); ?>">
    <!-- Feathericon CSS -->
    <link rel="stylesheet" href="<?php echo get_asset_url('css/feathericon.min.css', 'css'); ?>">
    <link rel="stylesheet" href="assets/plugins/morris/morris.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .card {
            padding: 30px 40px;
            margin-top: 20px;
            background-color: #A7BEAE;
            border: none;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
        }
        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }
        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #0056b3;
        }
        .form-control {
            border-radius: 5px;
        }
        .form-control:focus {
            border-color: #00BCD4;
            box-shadow: none;
        }
        .error {
            color: red;
        }
        /* Responsive Styles */
@media (max-width: 1200px) {
    .container {
        padding: 15px;
    }
}

@media (max-width: 768px) {
    body {
        padding: 10px;
    }

    input[type="text"], button {
        font-size: 14px; /* Slightly smaller font size */
    }
}

@media (max-width: 480px) {
    input[type="text"], button {
        font-size: 12px; /* Further reduce font size on very small devices */
        padding: 10px; /* Reduce padding for smaller devices */
    }

    .container {
        padding: 10px; /* Less padding on smaller screens */
    }
}
    </style>
</head>
<body>
    <?php include __DIR__ . '/../includes/templates/dynamic_header.php'; ?>
    
    <div class="page-wrapper">
        <div class="content container-fluid">
            <div class="container-fluid px-1 py-5 mx-auto">
                <div class="row d-flex justify-content-center">
                    <div class="col-xl-12 col-lg-12 col-md-12 col-11 text-center">
                        <div class="card">
                            <h3 style="color:green; font-weight:bold; text-decoration:underline">Plot Booking Form</h3>
                            <?php if ($error): ?>
                                <div class="alert alert-danger"><?php echo $error; ?></div>
                            <?php endif; ?>
                            <?php if ($msg): ?>
                                <div class="alert alert-success"><?php echo $msg; ?></div>
                            <?php endif; ?>
                            <form method="post" id="bookingForm">
                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                                <div class="row justify-content-between text-left">
                                    <div class="form-group col-md-3 col-sm-6">
                                        <form id="bookingForm">
    <label for="sponsor_id">Enter Sponsor ID:</label>
    <input type="text" id="sponsor_id" name="sponsor_id" autocomplete="off">
    <div id="sponsorSuggestions" style="display:none; border: 1px solid #ccc; max-height: 150px; overflow-y: auto;"></div>
    <div id="selectedSponsorName"></div>
    
</form>

                                    </div>
                                    <div class="form-group col-md-3 col-sm-6">
                                        <label for="site_name">Site Name</label>
                                        <select class="form-select" id="site_name" name="site_name" onchange="check()">
                                            <option value="">Select Site</option>
                                            <?php
                                            $query1 = "SELECT * FROM site_master";
                                            $result = $con->query($query1);
                                            if ($result->num_rows > 0) {
                                                while ($row = mysqli_fetch_assoc($result)) {
                                                    echo "<option value='{$row['site_id']}'>{$row['site_name']}</option>";
                                                }
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-3 col-sm-6">
                                         <label for="plot_no" class="form-control-label">Plot</label> 
                                         <select class="form-control" id="plot_no" name="plot_no" onchange="updatePlotDetails()">
                                             <option value="">Select Plot</option>
                                             <!-- Populate this with PHP -->
                                         </select>
                                    </div>
                                </div>

                                <div class="row justify-content-between text-left">
                                    <div class="form-group col-md-3 col-sm-6">
                                        <label class="form-control-label">Plot Area (sqft)<span class="text-danger"> *</span></label>
                                        <input type="number" class="form-control" id="plot_area" name="plot_area" required oninput="calculateTotalAmount()" readonly>
                                    </div>
                                    <div class="form-group col-md-3 col-sm-6">
                                        <label class="form-control-label">Plot Rate (Per/sqft)<span class="text-danger"> *</span></label>
                                        <input type="number" class="form-control" id="plot_rate" name="plot_rate" required oninput="calculateTotalAmount()" readonly>
                                    </div>
                                    <div class="form-group col-md-3 col-sm-6">
                                        <label class="form-control-label">Plot Dimension<span class="text-danger"> *</span></label>
                                        <input type="text" class="form-control" id="plot_dimension" name="plot_dimension" readonly required>
                                    </div>
                                    <div class="form-group col-md-3 col-sm-6">
                                        <label class="form-control-label">Plot Facing<span class="text-danger"> *</span></label>
                                        <input type="text" class="form-control" id="plot_facing" name="plot_facing" readonly required>
                                    </div>
                                </div>

                                <div class="row justify-content-between text-left">
                                    <div class="form-group col-md-3 col-sm-6">
                                        <label class="form-control-label">Customer Name<span class="text-danger"> *</span></label>
                                        <input type="text" class="form-control" id="customer_name" name="customer_name" required>
                                    </div>
                                    <div class="form-group col-md-3 col-sm-6">
                                        <label class="form-control-label">Customer Email<span class="text-danger"> *</span></label>
                                        <input type="email" class="form-control" id="customer_email" name="customer_email" >
                                    </div>
                                    <div class="form-group col-md-3 col-sm-6">
                                        <label class="form-control-label">Customer Phone<span class="text-danger"> *</span></label>
                                        <input type="text" class="form-control" id="customer_phone" name="customer_phone" required>
                                    </div>
                                    <div class="form-group col-md-3 col-sm-6">
                                        <label class="form-control-label">Customer Address<span class="text-danger"> *</span></label>
                                        <textarea class="form-control" id="customer_address" name="customer_address" required></textarea>
                                    </div>
                                </div>

                                <div class="row justify-content-between text-left">
                                    <div class="form-group col-md-3 col-sm-6">
                                        <label class="form-control-label">Aadhar Number<span class="text-danger"> *</span></label>
                                        <input type="text" class="form-control" id="aadhar_number" name="aadhar_number" required>
                                    </div>
                                    <div class="form-group col-md-3 col-sm-6">
                                        <label class="form-control-label">PAN Number<span class="text-danger"> *</span></label>
                                        <input type="text" class="form-control" id="pan_number" name="pan_number" required>
                                    </div>
                                </div>

                                <div class="row justify-content-between text-left">
                                    <div class="form-group col-md-4 col-sm-9">
                                        <label class="form-control-label">Payment Mode<span class="text-danger"> *</span></label>
                                        <div>
                                            <input type="checkbox" id="cash" name="payment_mode[]" value="Cash" onchange="togglePaymentFields()"> Cash
                                            <input type="checkbox" id="cheque" name="payment_mode[]" value="Cheque" onchange="togglePaymentFields()"> Cheque
                                            <input type="checkbox" id="online" name="payment_mode[]" value="Online" onchange="togglePaymentFields()"> Online
                                        </div>
                                    </div>
                                </div>

                                <!-- Cash Fields -->
                                <div id="cash_fields" style="display:none;">
                                    <div class="row justify-content-between text-left">
                                        <div class="form-group col-md-3 col-sm-6">
                                            <label class="form-control-label">Cash Receipt No.<span class="text-danger"> *</span></label>
                                            <input type="text" class="form-control" id="cash_receipt_number" name="cash_receipt_number">
                                        </div>
                                        <div class="form-group col-md-3 col-sm-6">
                                            <label class="form-control-label">Cash Deposit To<span class="text-danger"> *</span></label>
                                            <input type="text" class="form-control" id="cash_deposit_to" name="cash_deposit_to">
                                        </div>
                                        <div class="form-group col-md-3 col-sm-6">
                                            <label class="form-control-label">Cash Amount<span class="text-danger"> *</span></label>
                                            <input type="text" class="form-control" id="cash_amount" name="cash_amount" oninput="calculatePaidAmount()">
                                        </div>
                                        <div class="form-group col-md-3 col-sm-6">
                                            <label class="form-control-label">Cash Deposit Date<span class="text-danger"> *</span></label>
                                            <input type="date" class="form-control" id="transaction_date" name="transaction_date">
                                        </div>
                                    </div>
                                </div>

                                <!-- Cheque Fields -->
                                <div id="cheque_fields" style="display:none;">
                                    <div class="row justify-content-between text-left">
                                        <div class="form-group col-md-3 col-sm-6">
                                            <label class="form-control-label">Cheque Bank Name<span class="text-danger"> *</span></label>
                                            <input type="text" class="form-control" id="cheque_bank_name" name="cheque_bank_name">
                                        </div>
                                        <div class="form-group col-md-3 col-sm-6">
                                            <label class="form-control-label">Cheque Number<span class="text-danger"> *</span></label>
                                            <input type="text" class="form-control" id="cheque_number" name="cheque_number">
                                        </div>
                                        <div class="form-group col-md-3 col-sm-6">
                                            <label class="form-control-label">Cheque Amount<span class="text-danger"> *</span></label>
                                            <input type="text" class="form-control" id="cheque_amount" name="cheque_amount" oninput="calculatePaidAmount()">
                                        </div>
                                        <div class="form-group col-md-3 col-sm-6">
                                            <label class="form-control-label">Cheque Date<span class="text-danger"> *</span></label>
                                            <input type="date" class="form-control" id="cheque_date" name="cheque_date">
                                        </div>
                                    </div>
                                </div>

                                <!-- Online Fields -->
                                <div id="online_fields" style="display:none;">
                                    <div class="row justify-content-between text-left">
                                        <div class="form-group col-md-3 col-sm-6">
                                            <label class="form-control-label">Transaction Number / UTR Number<span class="text-danger"> *</span></label>
                                            <input type="text" class="form-control" id="transaction_number" name="transaction_number">
                                        </div>
                                        <div class="form-group col-md-3 col-sm-6">
                                            <label class="form-control-label">Online Transaction To<span class="text-danger"> *</span></label>
                                            <input type="text" class="form-control" id="online_transaction_to" name="online_transaction_to">
                                        </div>
                                        <div class="form-group col-md-3 col-sm-6">
                                            <label class="form-control-label">Online Transaction Amount<span class="text-danger"> *</span></label>
                                            <input type="text" class="form-control" id="online_transaction_amount" name="online_transaction_amount" oninput="calculatePaidAmount()">
                                        </div>
                                        <div class="form-group col-md-3 col-sm-6">
                                            <label class="form-control-label">Online Transaction Date<span class="text-danger"> *</span></label>
                                            <input type="date" class="form-control" id="transaction_date" name="transaction_date">
                                        </div>
                                    </div>
                                </div>

                                <div class="row justify-content-between text-left">
                                   <div class="form-group col-md-3 col-sm-6">
                                       <label class="form-control-label">Total Amount<span class="text-danger"> *</span></label>
                                       <input type="number" class="form-control" id="total_amount" name="total_amount" required readonly>
                                   </div>
                                   <div class="form-group col-md-3 col-sm-6">
                                       <label class="form-control-label">Discount Amount<span class="text-danger"> *</span></label>
                                       <input type="number" class="form-control" id="discount_amount" name="discount_amount" required oninput="calculateRemaining()">
                                   </div>
                                   <div class="form-group col-md-3 col-sm-6">
                                       <label class="form-control-label">Paid Amount<span class="text-danger"> *</span></label>
                                       <input type="number" class="form-control" id="paid_amount" name="paid_amount" required readonly>
                                   </div>
                                  
                                    
                                    <div class="form-group col-md-3 col-sm-6">
                                        <label class="form-control-label">Remaining Amount<span class="text-danger"> *</span></label>
                                        <input type="number" class="form-control" id="rest_amount" name="rest_amount" required readonly>
                                    </div>
                                </div>
                               <div class="row justify-content-between text-left">
    <div class="form-group col-md-12">
        <label class="form-control-label">Payment Plan</label>
        <div>
            <input type="checkbox" id="full_payment" name="payment_plan[]" value="Cash" onchange="handlePaymentPlanChange()"> Full Payment Plan
            <input type="checkbox" id="emi_payment" name="payment_plan[]" value="EMI" onchange="handlePaymentPlanChange()"> EMI Plan
        </div>
    </div>
</div>
<div id="emi_section" style="display:none;">
    <div class="row justify-content-between text-left">
       <div class="form-group col-md-6 col-sm-6">
            <label for="emi_months">Number of Months (Max 60)</label>
            <input type="number" class="form-control" id="emi_months" name="emi_months" min="1" max="60" oninput="calculateEMI()">
        </div>
          <div class="form-group col-md-3 col-sm-6">
            <label for="interest_rate">Interest Rate (%)</label>
            <input type="number" class="form-control" id="interest_rate" name="interest_rate" oninput="calculateEMI()">
        </div>
        <div class="form-group col-md-3 col-sm-6">
            <label for="total_interest">Total Interest Amount</label>
            <input type="number" class="form-control" id="total_interest" name="total_interest" readonly>
        </div>
         <div class="form-group col-md-3 col-sm-6">
            <label for="emi_amount">Monthly EMI</label>
            <input type="number" class="form-control" id="emi_amount" name="emi_amount" readonly>
    </div>
</div>
</div>
                            <div class="row justify-content-between text-left">
                                
                                    <div class="form-group col-sm-12">
                                       <button type="submit" name="submit_booking" class="btn btn-primary btn-block">Submit Booking</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </div>
        

        <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

        <script>
        
    document.getElementById('sponsor_id').addEventListener('keypress', function(event) {
    if (event.key === 'Enter') {
        event.preventDefault(); // Prevent the default form submission

        const sponsorId = this.value;

        // Check if sponsor ID is valid
        fetch('fetch_sponsor_names.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'sponsor_id=' + encodeURIComponent(sponsorId)
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success' && data.sponsors.length > 0) {
                // Valid sponsor ID
                document.getElementById('selectedSponsorName').textContent = 'Selected Sponsor: ' + data.sponsors[0].uname; // Display name
                document.getElementById('next_input').focus(); // Move to the next input
            } else {
                // Invalid sponsor ID
                alert('Invalid Sponsor ID. Please try again.');
            }
        })
        .catch(error => console.error('Error:', error));
    }
});

// Existing code for sponsor ID input handling
document.getElementById('sponsor_id').addEventListener('input', function() {
    const sponsorId = this.value;

    if (sponsorId.length > 0) {
        fetch('fetch_sponsor_names.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'sponsor_id=' + encodeURIComponent(sponsorId)
        })
        .then(response => response.json())
        .then(data => {
            const suggestionsDiv = document.getElementById('sponsorSuggestions');
            suggestionsDiv.innerHTML = ''; // Clear previous suggestions

            if (data.status === 'success') {
                data.sponsors.forEach(sponsor => {
                    const suggestionItem = document.createElement('div');
                    suggestionItem.textContent = sponsor.uname; // Display username
                    suggestionItem.style.cursor = 'pointer';
                    suggestionItem.onclick = function() {
                        document.getElementById('sponsor_id').value = sponsor.sponsor_id; // Set the sponsor ID
                        document.getElementById('selectedSponsorName').textContent = 'Selected Sponsor: ' + sponsor.uname; // Display name
                        suggestionsDiv.style.display = 'none'; // Hide suggestions
                    };
                    suggestionsDiv.appendChild(suggestionItem);
                });
                suggestionsDiv.style.display = 'block'; // Show suggestions
            } else {
                suggestionsDiv.style.display = 'none'; // Hide if no sponsors found
            }
        })
        .catch(error => console.error('Error:', error));
    } else {
        document.getElementById('sponsorSuggestions').style.display = 'none'; // Hide suggestions if input is empty
    }
});

// Function to check if all required fields are filled
function checkFormValidity() {
    const sponsorId = document.getElementById('sponsor_id').value;
    const submitButton = document.getElementById('submitButton');

    // Enable the submit button only if sponsor ID is filled
    if (sponsorId.trim() !== '') {
        submitButton.disabled = false; // Enable submit button
    } else {
        submitButton.disabled = true; // Disable submit button
    }
}

// Add an event listener to the sponsor ID input to check validity on input
document.getElementById('sponsor_id').addEventListener('input', checkFormValidity);



        
        function updatePlotDetails() {
    const plotId = document.getElementById('plot_no').value;
    if (plotId) {
        $.ajax({
            method: "POST",
            url: "fetch_plot_details.php",
            data: { plot_id: plotId },
            dataType: "json",
            success: function(data) {
                if (data) {
                    document.getElementById('plot_area').value = data.area || ''; // Ensure area is set
                    document.getElementById('plot_rate').value = data.plot_price || ''; // Ensure rate is set
                    document.getElementById('plot_dimension').value = data.plot_dimension || ''; // Ensure dimension is set
                    document.getElementById('plot_facing').value = data.plot_facing || ''; // Ensure facing is set
                    calculateTotalAmount(); // Update total amount based on new values
                }
            },
            error: function(xhr, status, error) {
                console.error("Error fetching plot details:", error);
            }
        });
    } else {
        // Clear fields if no plot is selected
        document.getElementById('plot_area').value = '';
        document.getElementById('plot_rate').value = '';
        document.getElementById('plot_dimension').value = '';
        document.getElementById('plot_facing').value = '';
    }
}

        
        function check(){
			var site_name = document.getElementById("site_name").value;
			
			$.ajax({
				
				method: "POST",
				url: "fetch_gata.php",
				data: {
					id: site_name
				},
				datatype: "html",
				success: function(data) {
					$("#gata_a").html(data);
					$("#plot_no").html('<option value="">Select Plot</option>');
					

				}
			});
			$.ajax({
            method: "POST",
            url: "fetch_farmer.php",
            data: {
                site_id: site_name
            },
            datatype: "html",
            success: function(data) {

				$("#farmer").html(data);

            }

        });
		$.ajax({
            method: "POST",
            url: "fetch_plot.php",
            data: {
                site_id: site_name
            },
            datatype: "html",
            success: function(data) {

				$("#plot_no").html(data);

            }

        });
		
	
	$("#gata_a").on('change', function() {
        var gata_id = $(this).val();
		//alert(gata_id);
        $.ajax({
            method: "POST",
            url: "fetch_gata.php",
            data: {
                sid: gata_id
            },
            datatype: "html",
            success: function(data) {
                $("#plot_no").html(data);
				

            }

        });
		$.ajax({
            method: "POST",
            url: "fetch_farmer.php",
            data: {
                sid: gata_id
            },
            datatype: "html",
            success: function(data) {

				$("#farmer").html(data);

            }

        });
		
		
    });
	}
        
            $(document).ready(function() {
                $('#referral_by').on('input', function() {
                    var sponsor_id = $(this).val();
                    $.ajax({
                        type: 'POST',
                        url: 'fetch_sponsor_list.php',
                        data: {sponsor_id: sponsor_id},
                        success: function(data) {
                            $('#sponsor_list').html(data);
                        }
                    });
                });

                $(document).on('click', '.sponsor_item', function() {
                    var sponsor_id = $(this).data('sponsor_id');
                    $.ajax({
                        type: 'POST',
                        url: 'fetch_sponsor_name.php',
                        data: {sponsor_id: sponsor_id},
                        success: function(data) {
                            $('#sponsor_name').html(data);
                            $('#referral_by').val(sponsor_id);
                        }
                    });
                });
            });

            function updatePlotArea(plotNumber) {
                const select = document.getElementById('plot_no');
                const areaInput = document.getElementById('plot_area');
                for (let option of select.options) {
                    if (option.value === plotNumber) {
                        areaInput.value = option.getAttribute('data-area');
                        break;
                    }
                }
            }

            function calculateTotalAmount() {
    const plotArea = parseFloat(document.getElementById('plot_area').value) || 0;
    const plotRate = parseFloat(document.getElementById('plot_rate').value) || 0;
    const totalAmount = plotArea * plotRate;

    document.getElementById('total_amount').value = totalAmount.toFixed(2); // Set total amount with two decimal places
}

           function calculatePaidAmount() {
            const cashAmount = parseFloat(document.getElementById('cash_amount').value) || 0;
            const chequeAmount = parseFloat(document.getElementById('cheque_amount').value) || 0;
            const onlineAmount = parseFloat(document.getElementById('online_transaction_amount').value) || 0;
            const totalPaid = cashAmount + chequeAmount + onlineAmount;
            document.getElementById('paid_amount').value = totalPaid;
            calculateRemaining();
        }

       function handlePaymentPlanChange() {
    const emiCheckbox = document.getElementById('emi_payment');
    const emiSection = document.getElementById('emi_section');

    if (emiCheckbox.checked) {
        emiSection.style.display = 'block';
    } else {
        emiSection.style.display = 'none';
        document.getElementById('emi_months').value = '';
        document.getElementById('emi_amount').value = '';
    }
    
    // If Full Payment checkbox is checked, ensure EMI section is hidden
    const fullPaymentCheckbox = document.getElementById('full_payment');
    if (fullPaymentCheckbox.checked) {
        emiSection.style.display = 'none';
        document.getElementById('emi_months').value = '';
        document.getElementById('emi_amount').value = '';
        emiCheckbox.checked = false; // Uncheck EMI if Full Payment is selected
    }
}


   function calculateEMI() {
    const remainingAmount = parseFloat(document.getElementById('rest_amount').value) || 0;
    const months = parseInt(document.getElementById('emi_months').value) || 0;
    const interestRateInput = document.getElementById('interest_rate');
    let interestRate = parseFloat(interestRateInput.value) || 0;

    // Autofill interest rate if applicable
    if (months > 36 && interestRate === 0) {
        interestRate = 12; // Set a predefined interest rate (e.g., 10%) for months > 36
        interestRateInput.value = interestRate; // Autofill the input
    }

    if (months > 0) {
        let emiAmount = 0;
        let totalInterest = 0;

        if (months > 36) {
            const monthlyInterestRate = interestRate / (12 * 100); // Convert annual rate to monthly and percentage to decimal
            
            // Calculate EMI
            emiAmount = (remainingAmount * monthlyInterestRate * Math.pow(1 + monthlyInterestRate, months)) /
                        (Math.pow(1 + monthlyInterestRate, months) - 1);
            // Calculate Total Interest Amount
            const totalPayment = emiAmount * months;
            totalInterest = totalPayment - remainingAmount;
        } else {
            // If months <= 36, calculate EMI as remainingAmount divided by months
            emiAmount = remainingAmount / months;
            totalInterest = 0; // No interest if months <= 36
        }

        document.getElementById('emi_amount').value = emiAmount.toFixed(2);
        document.getElementById('total_interest').value = totalInterest.toFixed(2);
    } else {
        document.getElementById('emi_amount').value = '';
        document.getElementById('total_interest').value = '';
    }
}



        function calculateRemaining() {
    const totalAmount = parseFloat(document.getElementById('total_amount').value) || 0;
    const paidAmount = parseFloat(document.getElementById('paid_amount').value) || 0;
    const discountAmount = parseFloat(document.getElementById('discount_amount').value) || 0;
    const remainingAmount = totalAmount - paidAmount - discountAmount ;

    // Update the rest_amount input field with the remaining amount
    document.getElementById('rest_amount').value = remainingAmount.toFixed(2); // Format to two decimal places
}

            function togglePaymentFields() {
                const cashChecked = document.getElementById('cash').checked;
                const chequeChecked = document.getElementById('cheque').checked;
                const onlineChecked = document.getElementById('online').checked;
                const cashFields = document.getElementById('cash_fields');
                const chequeFields = document.getElementById('cheque_fields');
                const onlineFields = document.getElementById('online_fields');

                cashFields.style.display = cashChecked ? 'block' : 'none';
                chequeFields.style.display = chequeChecked ? 'block' : 'none';
                onlineFields.style.display = onlineChecked ? 'block' : 'none';
            }
        </script>
         <!-- jQuery -->
    <script src="<?php echo get_asset_url('js/jquery-3.2.1.min.js', 'js'); ?>"></script>
         <!-- Bootstrap Core JS -->
    <script src="<?php echo get_asset_url('js/popper.min.js', 'js'); ?>"></script>
    <script src="<?php echo get_asset_url('js/bootstrap.min.js', 'js'); ?>"></script>
         <!-- Slimscroll JS -->
    <script src="assets/plugins/slimscroll/jquery.slimscroll.min.js"></script>

    <script src="assets/plugins/raphael/raphael.min.js"></script>    
    <script src="assets/plugins/morris/morris.min.js"></script>  
    <script src="<?php echo get_asset_url('js/chart.morris.js', 'js'); ?>"></script>
         <!-- Custom JS -->
    <script src="<?php echo get_asset_url('js/script.js', 'js'); ?>"></script>
    </div>
    <?php include __DIR__ . '/../includes/templates/new_footer.php'; ?>
</body>
</html>
