<?php
// Start session and include necessary files
session_start();
include("config.php");
// include(__DIR__ . '/includes/updated-config-paths.php');
include(__DIR__ . '/includes/functions/common-functions.php');

// Check if user is logged in
if(!isset($_SESSION['uemail']))
{
    header("location:login.php");
    exit;
}

// Process form submission
$error = "";
$msg = "";
if(isset($_POST['add']))
{
    $title = sanitize_input($_POST['title']);
    $content = sanitize_input($_POST['content']);
    $ptype = sanitize_input($_POST['ptype']);
    $bhk = sanitize_input($_POST['bhk']);
    $bed = sanitize_input($_POST['bed']);
    $balc = sanitize_input($_POST['balc']);
    $hall = sanitize_input($_POST['hall']);
    $stype = sanitize_input($_POST['stype']);
    $bath = sanitize_input($_POST['bath']);
    $kitc = sanitize_input($_POST['kitc']);
    $floor = sanitize_input($_POST['floor']);
    $price = sanitize_input($_POST['price']);
    $city = sanitize_input($_POST['city']);
    $asize = sanitize_input($_POST['asize']);
    $loc = sanitize_input($_POST['loc']);
    $state = sanitize_input($_POST['state']);
    $status = sanitize_input($_POST['status']);
    $uid = $_SESSION['uid'];
    $feature = sanitize_input($_POST['feature']);
    
    $totalfloor = sanitize_input($_POST['totalfl']);
    
    $aimage = $_FILES['aimage']['name'];
    $aimage1 = $_FILES['aimage1']['name'];
    $aimage2 = $_FILES['aimage2']['name'];
    $aimage3 = $_FILES['aimage3']['name'];
    $aimage4 = $_FILES['aimage4']['name'];
    
    $fimage = $_FILES['fimage']['name'];
    $fimage1 = $_FILES['fimage1']['name'];
    $fimage2 = $_FILES['fimage2']['name'];
    
    $temp_name  = $_FILES['aimage']['tmp_name'];
    $temp_name1 = $_FILES['aimage1']['tmp_name'];
    $temp_name2 = $_FILES['aimage2']['tmp_name'];
    $temp_name3 = $_FILES['aimage3']['tmp_name'];
    $temp_name4 = $_FILES['aimage4']['tmp_name'];
    
    $temp_name5 = $_FILES['fimage']['tmp_name'];
    $temp_name6 = $_FILES['fimage1']['tmp_name'];
    $temp_name7 = $_FILES['fimage2']['tmp_name'];
    
    move_uploaded_file($temp_name,"admin/property/$aimage");
    move_uploaded_file($temp_name1,"admin/property/$aimage1");
    move_uploaded_file($temp_name2,"admin/property/$aimage2");
    move_uploaded_file($temp_name3,"admin/property/$aimage3");
    move_uploaded_file($temp_name4,"admin/property/$aimage4");
    
    move_uploaded_file($temp_name5,"admin/property/$fimage");
    move_uploaded_file($temp_name6,"admin/property/$fimage1");
    move_uploaded_file($temp_name7,"admin/property/$fimage2");
    
    $sql = "INSERT INTO property (title,pcontent,type,bhk,stype,bedroom,bathroom,balcony,kitchen,hall,floor,size,price,location,city,state,feature,pimage,pimage1,pimage2,pimage3,pimage4,uid,status,mapimage,topmapimage,groundmapimage,totalfloor)
    VALUES ('$title','$content','$ptype','$bhk','$stype','$bed','$bath','$balc','$kitc','$hall','$floor','$asize','$price',
    '$loc','$city','$state','$feature','$aimage','$aimage1','$aimage2','$aimage3','$aimage4','$uid','$status','$fimage','$fimage1','$fimage2','$totalfloor')";
    
    $result = mysqli_query($con, $sql);
    if($result) {
        $msg = "<p class='alert alert-success'>Property Inserted Successfully</p>";
        // --- AUTOMATION START ---
        // 1. Ensure customer dashboard/profile exists (pseudo: create if not exists)
        $user_id = $uid;
        $user_check = mysqli_query($con, "SELECT * FROM users WHERE id = '$user_id'");
        $user_data = mysqli_fetch_assoc($user_check);
        // (Assume dashboard/profile is auto-created by existence in users table)
        // 2. Send booking details via email
        require_once(__DIR__ . '/PHPMailer.php');
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.example.com'; // Update with real SMTP
            $mail->SMTPAuth = true;
            $mail->Username = 'your@email.com';
            $mail->Password = 'yourpassword';
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;
            $mail->setFrom('no-reply@apsdreamhomes.com', 'APS Dream Homes');
            $mail->addAddress($user_data['email'], $user_data['name']);
            $mail->Subject = 'Booking Confirmation - APS Dream Homes';
            $mail->Body = "Dear {$user_data['name']},\n\nYour property booking is successful!\n\nProperty: $title\nLocation: $loc, $city, $state\nPrice: $price\n\nThank you for booking with APS Dream Homes.";
            $mail->send();
        } catch (Exception $e) {
            // Log email failure
        }
        // 3. Send WhatsApp notification (pseudo-code, integrate with real API)
        $whatsapp_number = $user_data['phone'];
        $wa_message = urlencode("Dear {$user_data['name']}, your property booking is successful! Property: $title, Location: $loc, $city, $state, Price: $price. APS Dream Homes");
        // Example API call (replace with real WhatsApp API)
        // file_get_contents("https://api.whatsapp.com/send?phone=$whatsapp_number&text=$wa_message");
        // --- AUTOMATION END ---
    } else {
        $error = "<p class='alert alert-warning'>Property Not Inserted Some Error Occurred</p>";
    }
}

// Set page specific variables
$page_title = "Submit Property - APS Dream Homes";
$meta_description = "Submit your property listing with APS Dream Homes and reach potential buyers and investors across Uttar Pradesh.";

// Additional CSS for this page
$additional_css = '<style>
    /* Submit Property Page Specific Styles */
    .submit-property-section {
        padding: 80px 0;
        background-color: #f8f9fa;
    }
    
    .submit-property-form {
        background-color: #fff;
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
    }
    
    .form-group label {
        font-weight: 600;
        color: #333;
    }
    
    .form-control {
        height: 45px;
        border-radius: 5px;
        border: 1px solid #ddd;
    }
    
    textarea.form-control {
        height: auto;
    }
    
    .custom-file-label {
        height: 45px;
        line-height: 2.2;
    }
    
    .btn-submit {
        background-color: var(--primary-color);
        color: #fff;
        padding: 12px 30px;
        border: none;
        border-radius: 5px;
        font-weight: 600;
        transition: all 0.3s ease;
        margin-top: 20px;
    }
    
    .btn-submit:hover {
        background-color: var(--secondary-color);
        transform: translateY(-3px);
    }
    
    .property-guidelines {
        background-color: #fff;
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        height: 100%;
    }
    
    .property-guidelines h3 {
        color: var(--primary-color);
        margin-bottom: 20px;
    }
    
    .property-guidelines ul {
        padding-left: 20px;
    }
    
    .property-guidelines li {
        margin-bottom: 10px;
        color: #555;
    }
</style>';

// Include the common header
include(__DIR__ . '/includes/templates/dynamic_header.php');
?>

<!-- Page Banner Section -->
<div class="page-banner" style="background-image: url('<?php echo get_asset_url("banner/submit-property-banner.jpg", "images"); ?>')">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h1 class="page-title">Submit Property</h1>
                <ul class="breadcrumb">
                    <li><a href="<?php echo BASE_URL; ?>/index.php">Home</a></li>
                    <li>Submit Property</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Submit Property Section -->
<section class="submit-property-section">
    <div class="container">
        <div class="row">
            <div class="col-lg-8">
                <div class="submit-property-form">
                    <h3 class="mb-4">Property Details</h3>
                    
                    <?php echo $error; ?>
                    <?php echo $msg; ?>
                    
                    <form method="post" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="title">Property Title</label>
                                    <input type="text" name="title" class="form-control" placeholder="Enter Property Title" required>
                                </div>
                            </div>
                            
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="content">Property Description</label>
                                    <textarea name="content" class="form-control" rows="5" placeholder="Enter Property Description" required></textarea>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="ptype">Property Type</label>
                                    <select class="form-control" name="ptype" required>
                                        <option value="">Select Type</option>
                                        <option value="apartment">Apartment</option>
                                        <option value="flat">Flat</option>
                                        <option value="building">Building</option>
                                        <option value="house">House</option>
                                        <option value="villa">Villa</option>
                                        <option value="office">Office</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="stype">Selling Type</label>
                                    <select class="form-control" name="stype" required>
                                        <option value="">Select Status</option>
                                        <option value="rent">Rent</option>
                                        <option value="sale">Sale</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="bhk">BHK</label>
                                    <select class="form-control" name="bhk" required>
                                        <option value="">Select BHK</option>
                                        <option value="1 BHK">1 BHK</option>
                                        <option value="2 BHK">2 BHK</option>
                                        <option value="3 BHK">3 BHK</option>
                                        <option value="4 BHK">4 BHK</option>
                                        <option value="5 BHK">5 BHK</option>
                                        <option value="1,2 BHK">1,2 BHK</option>
                                        <option value="2,3 BHK">2,3 BHK</option>
                                        <option value="2,3,4 BHK">2,3,4 BHK</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="bed">Bedroom</label>
                                    <select class="form-control" name="bed" required>
                                        <option value="">Select Bedroom</option>
                                        <option value="1">1</option>
                                        <option value="2">2</option>
                                        <option value="3">3</option>
                                        <option value="4">4</option>
                                        <option value="5">5</option>
                                        <option value="6">6</option>
                                        <option value="7">7</option>
                                        <option value="8">8</option>
                                        <option value="9">9</option>
                                        <option value="10">10</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="bath">Bathroom</label>
                                    <select class="form-control" name="bath" required>
                                        <option value="">Select Bathroom</option>
                                        <option value="1">1</option>
                                        <option value="2">2</option>
                                        <option value="3">3</option>
                                        <option value="4">4</option>
                                        <option value="5">5</option>
                                        <option value="6">6</option>
                                        <option value="7">7</option>
                                        <option value="8">8</option>
                                        <option value="9">9</option>
                                        <option value="10">10</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="kitc">Kitchen</label>
                                    <select class="form-control" name="kitc" required>
                                        <option value="">Select Kitchen</option>
                                        <option value="1">1</option>
                                        <option value="2">2</option>
                                        <option value="3">3</option>
                                        <option value="4">4</option>
                                        <option value="5">5</option>
                                    </select>
                                </div>
                            </div>
                            
                            <!-- More form fields -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="price">Price</label>
                                    <input type="text" name="price" class="form-control" placeholder="Enter Price" required>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="city">City</label>
                                    <input type="text" name="city" class="form-control" placeholder="Enter City" required>
                                </div>
                            </div>
                            
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="aimage">Featured Image</label>
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" id="aimage" name="aimage" required>
                                        <label class="custom-file-label" for="aimage">Choose file</label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Additional Images</label>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="custom-file mb-3">
                                                <input type="file" class="custom-file-input" id="aimage1" name="aimage1">
                                                <label class="custom-file-label" for="aimage1">Image 1</label>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="custom-file mb-3">
                                                <input type="file" class="custom-file-input" id="aimage2" name="aimage2">
                                                <label class="custom-file-label" for="aimage2">Image 2</label>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="custom-file mb-3">
                                                <input type="file" class="custom-file-input" id="aimage3" name="aimage3">
                                                <label class="custom-file-label" for="aimage3">Image 3</label>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="custom-file mb-3">
                                                <input type="file" class="custom-file-input" id="aimage4" name="aimage4">
                                                <label class="custom-file-label" for="aimage4">Image 4</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="status">Status</label>
                                    <select class="form-control" name="status" required>
                                        <option value="">Select Status</option>
                                        <option value="available">Available</option>
                                        <option value="sold out">Sold Out</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="feature">Features</label>
                                    <textarea class="form-control" name="feature" placeholder="Enter Features" required></textarea>
                                </div>
                            </div>
                            
                            <div class="col-md-12">
                                <div class="form-group">
                                    <button type="submit" name="add" class="btn btn-submit">Submit Property</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="property-guidelines">
                    <h3>Submission Guidelines</h3>
                    <ul>
                        <li>Provide accurate and detailed information about your property.</li>
                        <li>Upload clear, high-quality images of your property.</li>
                        <li>Mention all unique features and amenities of your property.</li>
                        <li>Be honest about the condition and status of your property.</li>
                        <li>Provide correct contact information for potential buyers to reach you.</li>
                        <li>Review all information before submitting to ensure accuracy.</li>
                        <li>Your listing will be reviewed by our team before being published.</li>
                    </ul>
                    
                    <div class="mt-4">
                        <h4>Need Help?</h4>
                        <p>If you need assistance with submitting your property, please contact our support team:</p>
                        <p><i class="fas fa-phone-alt mr-2"></i> +91 7007444842</p>
                        <p><i class="fas fa-envelope mr-2"></i> apsdreamhomes44@gmail.com</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
// Additional JS for this page
$additional_js = '<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Update custom file input labels with selected filename
        $(".custom-file-input").on("change", function() {
            var fileName = $(this).val().// SECURITY: Replaced deprecated function"\\").pop();
            $(this).siblings(".custom-file-label").addClass("selected").html(fileName);
        });
        
        console.log("Submit property page loaded successfully!");
    });
</script>';

// Include the common footer
include(__DIR__ . '/includes/common-footer.php');
?>
