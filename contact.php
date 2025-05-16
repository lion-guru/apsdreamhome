<?php
// Start session and include necessary files
session_start();
require_once(__DIR__ . '/includes/templates/dynamic_header.php');
require_once(__DIR__ . '/includes/log_admin_activity.php');
include("config.php");
include(__DIR__ . '/includes/updated-config-paths.php');
include(__DIR__ . '/includes/functions/common-functions.php');

// Process contact form submission
$error = "";
$msg = "";
if(isset($_POST['send']))
{
    $name = sanitize_input($_POST['name']);
    $email = sanitize_input($_POST['email']);
    $phone = sanitize_input($_POST['phone']);
    $subject = sanitize_input($_POST['subject']);
    $message = sanitize_input($_POST['message']);
    
    if(!empty($name) && !empty($email) && !empty($phone) && !empty($subject) && !empty($message))
    {
        $sql = "INSERT INTO contact (name, email, phone, subject, message) VALUES ('$name', '$email', '$phone', '$subject', '$message')";
        $result = mysqli_query($con, $sql);
        if($result){
            $msg = "<p class='alert alert-success'>Message Sent Successfully</p>";
        }
        else{
            $error = "<p class='alert alert-warning'>Message Not Sent Successfully</p>";
        }
        log_admin_activity('submit_contact', 'Contact form submitted by: ' . $name . ', email: ' . $email);
    }else{
        $error = "<p class='alert alert-warning'>Please Fill All the Fields</p>";
    }
}

// Set page specific variables
$page_title = "Contact Us - APS Dream Homes";
$meta_description = "Get in touch with APS Dream Homes for all your real estate needs in Gorakhpur, Lucknow, and across Uttar Pradesh.";

// Additional CSS for this page
$additional_css = '<style>
    /* Contact Page Specific Styles */
    .contact-section {
        padding: 80px 0;
        background-color: #f8f9fa;
    }
    
    .contact-form {
        background-color: #fff;
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
    }
    
    .contact-info {
        background-color: var(--primary-color);
        padding: 30px;
        border-radius: 10px;
        color: #fff;
        height: 100%;
    }
    
    .contact-info h3 {
        margin-bottom: 20px;
        color: #fff;
        position: relative;
        padding-bottom: 15px;
    }
    
    .contact-info h3:after {
        content: "";
        position: absolute;
        bottom: 0;
        left: 0;
        width: 50px;
        height: 2px;
        background-color: #fff;
    }
    
    .contact-info-item {
        margin-bottom: 20px;
        display: flex;
        align-items: flex-start;
    }
    
    .contact-info-item i {
        margin-right: 15px;
        font-size: 20px;
        color: #fff;
    }
    
    .contact-info-item p {
        margin-bottom: 0;
        color: rgba(255, 255, 255, 0.9);
    }
    
    .contact-form .form-control {
        border-radius: 5px;
        margin-bottom: 20px;
        height: 50px;
        border: 1px solid #ddd;
    }
    
    .contact-form textarea.form-control {
        height: 150px;
    }
    
    .contact-form .btn-submit {
        background-color: var(--primary-color);
        color: #fff;
        padding: 12px 30px;
        border: none;
        border-radius: 5px;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    
    .contact-form .btn-submit:hover {
        background-color: var(--secondary-color);
        transform: translateY(-3px);
    }
    
    .map-section {
        padding: 80px 0;
    }
    
    .map-container {
        height: 450px;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }
    
    .map-container iframe {
        width: 100%;
        height: 100%;
        border: 0;
    }
    
    @media (max-width: 767px) {
        .contact-info {
            margin-bottom: 30px;
        }
    }
</style>';

// Page Banner Section
?>
<!-- Page Banner Section -->
<div class="page-banner" style="background-image: url('<?php echo get_asset_url("banner/contact-banner.jpg", "images"); ?>')">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h1 class="page-title">Contact Us</h1>
                <ul class="breadcrumb">
                    <li><a href="<?php echo BASE_URL; ?>/index.php">Home</a></li>
                    <li>Contact Us</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Contact Section -->
<section class="contact-section">
    <div class="container">
        <div class="row">
            <div class="col-md-12 text-center mb-5">
                <h2 class="section-title">Get In Touch</h2>
                <p class="lead">We'd love to hear from you. Contact us for any inquiries or assistance.</p>
            </div>
        </div>
        
        <div class="row">
            <div class="col-lg-8">
                <div class="contact-form">
                    <h3 class="mb-4">Send Us a Message</h3>
                    <?php echo $msg; ?>
                    <?php echo $error; ?>
                    <form method="post">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <input type="text" class="form-control" name="name" placeholder="Your Name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <input type="email" class="form-control" name="email" placeholder="Your Email" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <input type="text" class="form-control" name="phone" placeholder="Your Phone" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <input type="text" class="form-control" name="subject" placeholder="Subject" required>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <textarea class="form-control" name="message" placeholder="Your Message" required></textarea>
                        </div>
                        <div class="form-group">
                            <input type="submit" name="send" value="Send Message" class="btn btn-submit">
                        </div>
                    </form>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="contact-info">
                    <h3>Contact Information</h3>
                    <div class="contact-info-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <p>APS Dream Homes Pvt. Ltd.,<br>Gorakhpur, Uttar Pradesh, India</p>
                    </div>
                    <div class="contact-info-item">
                        <i class="fas fa-phone-alt"></i>
                        <p>+91 7007444842</p>
                    </div>
                    <div class="contact-info-item">
                        <i class="fas fa-envelope"></i>
                        <p>apsdreamhomes44@gmail.com</p>
                    </div>
                    <div class="contact-info-item">
                        <i class="fas fa-clock"></i>
                        <p>Monday - Saturday: 9:00 AM - 6:00 PM<br>Sunday: Closed</p>
                    </div>
                    <div class="social-icons mt-4">
                        <a href="https://www.facebook.com/AbhaySinghSuryawansi/" target="_blank" rel="noopener"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-linkedin-in"></i></a>
                        <a href="https://www.instagram.com/apsdreamhomes" target="_blank" rel="noopener"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Map Section -->
<section class="map-section">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="map-container">
                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d114487.30332296328!2d83.2993368871579!3d26.75384754001935!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3991446a0c332489%3A0x1ff3f97fdcc6bfa2!2sGorakhpur%2C%20Uttar%20Pradesh!5e0!3m2!1sen!2sin!4v1648123456789!5m2!1sen!2sin" allowfullscreen="" loading="lazy"></iframe>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
// Additional JS for this page
$additional_js = '<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Form validation
        const contactForm = document.querySelector("form");
        if (contactForm) {
            contactForm.addEventListener("submit", function(event) {
                const name = document.querySelector("input[name=\'name\']").value;
                const email = document.querySelector("input[name=\'email\']").value;
                const phone = document.querySelector("input[name=\'phone\']").value;
                const subject = document.querySelector("input[name=\'subject\']").value;
                const message = document.querySelector("textarea[name=\'message\']").value;
                
                if (!name || !email || !phone || !subject || !message) {
                    event.preventDefault();
                    alert("Please fill all the fields");
                }
            });
        }
        
        console.log("Contact page loaded successfully!");
    });
</script>';

require_once(__DIR__ . '/includes/templates/new_footer.php'); ?>
</body>
</html>