<?php
/**
 * Plot Booking Page
 * Standardized with unified admin template
 */
require_once __DIR__ . '/core/init.php';

if (!isAdmin()) {
    header('Location: login.php');
    exit();
}

$page_title = "Plot Booking";

// Standard Admin Header & Sidebar
include 'admin_header.php';
include 'admin_sidebar.php';

// Initialize variables
$error = "";
$msg = "";

if (isset($_POST['submit_booking'])) {
    // Validate CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = "Invalid CSRF token.";
    } else {
        // Validate and sanitize inputs
        $customer_name = h(trim($_POST['customer_name']));
        $customer_email = filter_var(trim($_POST['customer_email']), FILTER_SANITIZE_EMAIL);
        $customer_phone = h(trim($_POST['customer_phone']));
        $customer_address = h(trim($_POST['customer_address']));
        $aadhar_number = h(trim($_POST['aadhar_number']));
        $pan_number = h(trim($_POST['pan_number']));
        $nominee_name = h(trim($_POST['nominee_name']));
        $nominee_relationship = h(trim($_POST['nominee_relationship']));
        $booking_type = h(trim($_POST['booking_type']));
        $payment_plan = h(trim($_POST['payment_plan']));
        $plot_number = h(trim($_POST['plot_number']));
        $plot_area = h(trim($_POST['plot_area']));
        $plot_dimension = h(trim($_POST['plot_dimension']));
        $plot_facing = h(trim($_POST['plot_facing']));
        $total_amount = h(trim($_POST['total_amount']));
        
        $cash_amount = h(trim($_POST['cash_amount'])) ?: 0;
        $cheque_amount = h(trim($_POST['cheque_amount'])) ?: 0;
        $online_amount = h(trim($_POST['online_transaction_amount'])) ?: 0;
        $paid_amount = $cash_amount + $cheque_amount + $online_amount;

        $rest_amount = h(trim($_POST['rest_amount']));
        $payment_mode = isset($_POST['payment_mode']) ? implode(", ", $_POST['payment_mode']) : '';
        $referral_by = h(trim($_POST['referral_by']));

        // Error handling for required fields
        if (empty($customer_name) || empty($customer_email) || empty($customer_phone) || empty($total_amount)) {
            $error = "Please fill in all required fields.";
        } elseif (!filter_var($customer_email, FILTER_VALIDATE_EMAIL)) {
            $error = "Please enter a valid email address.";
        } elseif (!preg_match('/^[0-9]{10}$/', $customer_phone)) {
            $error = "Please enter a valid phone number.";
        } else {
            // Insert into database - Fixed placeholder count (19 columns, 19 placeholders)
            try {
                $db = \App\Core\App::database();
                $params = [
                    'customer_name' => $customer_name,
                    'customer_email' => $customer_email,
                    'customer_phone' => $customer_phone,
                    'customer_address' => $customer_address,
                    'aadhar_number' => $aadhar_number,
                    'pan_number' => $pan_number,
                    'nominee_name' => $nominee_name,
                    'nominee_relationship' => $nominee_relationship,
                    'booking_type' => $booking_type,
                    'payment_plan' => $payment_plan,
                    'plot_number' => $plot_number,
                    'plot_area' => $plot_area,
                    'plot_dimension' => $plot_dimension,
                    'plot_facing' => $plot_facing,
                    'total_amount' => $total_amount,
                    'paid_amount' => $paid_amount,
                    'rest_amount' => $rest_amount,
                    'payment_mode' => $payment_mode,
                    'referral_by' => $referral_by
                ];

                if ($db->execute("INSERT INTO bookings (customer_name, customer_email, customer_phone, customer_address, aadhar_number, pan_number, nominee_name, nominee_relationship, booking_type, payment_plan, plot_number, plot_area, plot_dimension, plot_facing, total_amount, paid_amount, rest_amount, payment_mode, referral_by) VALUES (:customer_name, :customer_email, :customer_phone, :customer_address, :aadhar_number, :pan_number, :nominee_name, :nominee_relationship, :booking_type, :payment_plan, :plot_number, :plot_area, :plot_dimension, :plot_facing, :total_amount, :paid_amount, :rest_amount, :payment_mode, :referral_by)", $params)) {
                    require_once __DIR__ . '/../includes/notification_manager.php';
                    require_once __DIR__ . '/../includes/email_service.php';
                    
                    $nm = new NotificationManager($db->getConnection(), new EmailService());
                    
                    // Notify Customer
                    $nm->send([
                        'email' => $customer_email,
                        'template' => 'PLOT_BOOKING_CREATED',
                        'data' => [
                            'plot_number' => $plot_number,
                            'total_amount' => $total_amount
                        ],
                        'channels' => ['email'] // Customer might not have a user account yet
                    ]);

                    // Internal Notification for Admin
                    $nm->send([
                        'user_id' => 1, // Admin
                        'type' => 'success',
                        'title' => 'New Plot Booking',
                        'message' => "A new plot booking was created for $customer_name. Plot: $plot_number, Amount: $total_amount",
                        'channels' => ['db']
                    ]);

                    log_admin_activity('add_booking', 'Booking for customer name: ' . $customer_name . ', customer email: ' . $customer_email . ', customer phone: ' . $customer_phone . ', total amount: ' . $total_amount);
                    $msg = "Booking added successfully!";
                } else {
                    $error = "Error while adding booking.";
                }
            } catch (Exception $e) {
                $error = "Error while adding booking: " . $e->getMessage();
            }
        }
    }
}
?>

<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row">
                <div class="col-sm-12">
                    <h3 class="page-title"><?php echo $page_title; ?></h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item active"><?php echo $page_title; ?></li>
                    </ul>
                </div>
            </div>
        </div>
        <!-- /Page Header -->

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Plot Booking Form</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <?php echo $error; ?>
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        <?php endif; ?>
                        <?php if ($msg): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <?php echo $msg; ?>
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        <?php endif; ?>

                        <form method="post" id="bookingForm">
                            <?php echo getCsrfField(); ?>

                            <div class="row">
                                <div class="form-group col-md-3 col-sm-6">
                                    <label for="sponsor_id">Enter Sponsor ID:</label>
                                    <input type="text" class="form-control" id="sponsor_id" name="sponsor_id" autocomplete="off" required>
                                    <div id="sponsorSuggestions" style="display:none; border: 1px solid #ccc; max-height: 150px; overflow-y: auto; background: white; position: absolute; width: 90%; z-index: 1000;"></div>
                                    <div id="selectedSponsorName" class="mt-1 small text-muted"></div>
                                </div>
                                <div class="form-group col-md-3 col-sm-6">
                                    <label for="site_name">Site Name</label>
                                    <select class="form-control" id="site_name" name="site_name" onchange="check()">
                                            <option value="">Select Site</option>
                                            <?php
                                            try {
                                                $db = \App\Core\App::database();
                                                $sites = $db->fetchAll("SELECT * FROM site_master");
                                                foreach ($sites as $row) {
                                                    echo "<option value='" . (int)$row['site_id'] . "'>" . h($row['site_name']) . "</option>";
                                                }
                                            } catch (Exception $e) {
                                                // Log error or display message
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
                                            <input type="date" class="form-control" id="cash_transaction_date" name="transaction_date">
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
                                            <input type="date" class="form-control" id="cheque_transaction_date" name="cheque_date">
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
                                            <input type="date" class="form-control" id="online_transaction_date" name="transaction_date">
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
                                       <button type="submit" name="submit_booking" id="submitButton" class="btn btn-primary btn-block">Submit Booking</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </div>
        
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
                document.getElementById('site_name').focus(); // Move to the next input
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
<?php include 'admin_footer.php'; ?>


