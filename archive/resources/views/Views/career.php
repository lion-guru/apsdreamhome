<?php
ini_set('session.cache_limiter','public');
session_cache_limiter(false);
session_start();
include("config.php");
include(__DIR__ . '/includes/updated-config-paths.php');
include(__DIR__ . '/includes/functions/common-functions.php');
require_once(__DIR__ . '/includes/templates/dynamic_header.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Meta Tags -->
    <link rel="shortcut icon" href="images/favicon.ico">
    <link href="https://fonts.googleapis.com/css?family=Muli:400,400i,500,600,700&amp;display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Comfortaa:400,700" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo get_asset_url('css/bootstrap.min.css', 'css'); ?>">
    <link rel="stylesheet" href="<?php echo get_asset_url('css/bootstrap-slider.css', 'css'); ?>">
    <link rel="stylesheet" href="<?php echo get_asset_url('css/jquery-ui.css', 'css'); ?>">
    <link rel="stylesheet" href="<?php echo get_asset_url('css/layerslider.css', 'css'); ?>">
    <link rel="stylesheet" href="<?php echo get_asset_url('css/color.css', 'css'); ?>" id="color-change">
    <link rel="stylesheet" href="<?php echo get_asset_url('css/owl.carousel.min.css', 'css'); ?>">
    <link rel="stylesheet" href="<?php echo get_asset_url('css/font-awesome.min.css', 'css'); ?>">
    <link rel="stylesheet" type="text/css" href="fonts/flaticon/flaticon.css">
    <link rel="stylesheet" href="<?php echo get_asset_url('css/style.css', 'css'); ?>">
    <link rel="stylesheet" href="<?php echo get_asset_url('css/career.css', 'css'); ?>">

    <title>APS Dream Homes</title>
</head>
<body>

<div id="page-wrapper">
    <div class="row">
        <!-- Header -->
        
        <div class="full-row">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <h2 class="text-secondary double-down-line text-center mb-5">Career</h2>
                    </div>
                </div>
                <div class="row">
                    <div class="clearfix"></div>

                    <section>
                        <div class="header-inner two">
                            <div class="inner text-center">
                                <h4 class="title text-white uppercase">WORK WITH US</h4>
                            </div>
                            <div class="overlay bg-opacity-5"></div>
                            <img src="assets/<?php echo get_asset_url('career.jpg', 'images'); ?>" alt="" class="img-responsive"/>
                        </div>
                    </section>
                    
                    <div class="clearfix"></div><br><br>

                    <section class="section-light section-side-image clearfix">
                        <div class="img-holder col-md-6 col-sm-3 pull-left">
                            <div class="background-imgholder" style="background:url(<?php echo get_asset_url('work.jpg', 'images'); ?>);">
                                <img class="nodisplay-image" src="assets/<?php echo get_asset_url('work.jpg', 'images'); ?>" alt=""/>
                            </div>
                        </div>
                        <div class="container-fluid">
                            <div class="row">
                                <div class="col-md-7 col-md-offset-5 col-sm-8 col-sm-offset-4 text-inner clearfix align-left">
                                    <div class="text-box white padding-7">
                                        <div class="col-xs-12 text-left" style="padding:0px; margin:0px;">
                                            <h4>WORKING CULTURE</h4>
                                            <div class="title-line-4"></div>
                                        </div>
                                        <p>We take pride in our HR policies, which is one of the best in the industry. Our world-class creation is a reflection of our workforce, both high up on standards and sincerity. We have a young and vibrant work culture. The spirit of teamwork has been so deeply imbibed in every employee that a sense to grow together becomes the agenda of every employee.</p>
                                        <div class="clearfix"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="sec-padding">
                        <div class="container-fluid">
                            <div class="row">
                                <div class="col-sm-6" style="padding-top:40px; padding-left:50px;">
                                    <h4>WORKING CULTURE</h4>
                                    <p><b>Sales executive, Senior Sales Executive</b><br>
                                    Candidate having experience for 0 to 3 years in the financial sector, telecom sector, retail & real estate sector. <br><br>
                                    <b>Roles & Responsibility</b><br>
                                    1. Key responsibility would be to generate sales & market penetration.<br>
                                    2. Qualification: Bachelor degree in any discipline preferably MBA in sales/marketing.<br>
                                    3. Location: Gorakhpur, Lucknow & Eastern U.P, Bihar<br>
                                    <p>You can also mail your resume to: <a href="mailto:apsdreamhomes44@gmail.com">apsdreamhomes44@gmail.com</a></p>
                                </div>
                                <div class="col-sm-6" style="padding:0px; margin:0px;">
                                    <img src="assets/<?php echo get_asset_url('open.jpg', 'images'); ?>" class="img-responsive">
                                </div>
                                <div class="clearfix"></div>
                                <div class="col-sm-6" style="padding-top:25px; padding-left:0px;">
                                    <img src="assets/<?php echo get_asset_url('apply.jpg', 'images'); ?>" class="img-responsive">
                                </div>
                                <div class="col-sm-6" style="padding-top:40px; padding-right:50px;">
                                   
                                    <h4>APPLY ONLINE</h4>

                                        <!-- Process form data -->
                                    <?php
                                    
                                    
// Define constants for allowed file types and maximum file size
const ALLOWED_FILE_TYPES = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
const MAX_FILE_SIZE = 1024 * 1024 * 5; // 5MB

// Initialize an array to hold error messages
$errors = [];
$thankYouMessage = '';

// Process form data
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = htmlspecialchars(trim($_POST['name']));
    $phone = htmlspecialchars(trim($_POST['phone']));
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $file = $_FILES['file'];
    $msg = htmlspecialchars(trim($_POST['msg']));

    // Validate the form data
    if (empty($name) || empty($phone) || empty($email) || empty($file['name']) || empty($msg)) {
        $errors[] = "Please fill in all the fields.";
    }

  // Validate name (only alphabetic characters)
if (empty($name)) {
    $errors['name'] = "Please fill out this field for Name.";
} elseif (!preg_match("/^[A-Za-z\s]+$/", $name)) {
    $errors['name'] = "Please enter only alphabetic characters in the Name field.";
}

   // Validate phone (only 10 digits)
if (empty($phone)) {
    $errors['phone'] = "Please fill out this field for Mobile Number.";
} elseif (!preg_match("/^\d{10}$/", $phone)) {
    $errors['phone'] = "Please enter a valid 10-digit Mobile Number.";
}

    // Validate the file type
    if (!in_array($file['type'], ALLOWED_FILE_TYPES)) {
        $errors[] = "Invalid file type. Please upload a PDF, DOC, or DOCX file.";
    }

    // Check if the file was uploaded successfully
    if ($file['error'] != 0) {
        $errors[] = "Error uploading file.";
    }

    // If there are no errors, proceed with database insertion
    if (empty($errors)) {
        // Sanitize file name
        $file_name = htmlspecialchars($file['name']);

        // Save data to database
        $sql = "INSERT INTO career_applications (name, phone, email, file_name, file_type, file_size, comments) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("sssssss", $name, $phone, $email, $file_name, $file['type'], $file['size'], $msg);
            if ($stmt->execute()) {
                $thankYouMessage = "Thank you for applying!";
            } else {
                $errors[] = "Error executing query.";
            }
        } else {
            $errors[] = "Error preparing statement.";
        }
    }
    // Close database connection
        $conn->close();
}
 
?>

<!-- HTML Form -->
<form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" id="smart-form" name="smart-form" enctype="multipart/form-data">
    <label for="name">Name:</label>
<input type="text" name="name" value="<?php echo isset($name) ? htmlspecialchars($name) : ''; ?>" required>
<div id="name-error" style="color: red;">
    <?php echo isset($errors['name']) ? htmlspecialchars($errors['name']) : ''; ?>
</div><br>

    <label for="phone">Phone:</label>
<input type="tel" name="phone" value="<?php echo isset($phone) ? htmlspecialchars($phone) : ''; ?>" required>
<div id="phone-error" style="color: red;">
    <?php echo isset($errors['phone']) ? htmlspecialchars($errors['phone']) : ''; ?>
</div><br>

    <label for="email">Email:</label>
    <input type="email" name="email" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required>
    <div id="email-error" style="color: red;">
        <?php echo isset($errors['email']) ? htmlspecialchars($errors['email']) : ''; ?>
    </div><br>

    <label for="file">Resume:</label>
    <input type="file" name="file" required>
    <div id="file-error" style="color: red;">
        <?php echo isset($errors['file']) ? htmlspecialchars($errors['file']) : ''; ?>
    </div><br>

    <label for="msg">Comments:</label>
    <textarea name="msg" required><?php echo isset($msg) ? htmlspecialchars($msg) : ''; ?></textarea>
    <div id="msg-error" style="color: red;">
               <?php echo isset($errors['msg']) ? htmlspecialchars($errors['msg']) : ''; ?>
    </div><br>

    <input type="submit" value="Submit" class="btn btn-primary">
</form>

<!-- Error Messages -->
<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <ul>
            <?php foreach ($errors as $error): ?>
                <li><?php echo htmlspecialchars($error); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<!-- Thank You Message Modal -->
<?php if ($thankYouMessage): ?>
    <div id="thankYouModal" class="modal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <h2>Thank You!</h2>
                    <p>We will contact you as soon as possible.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <script type="text/javascript">
        $(document).ready(function() {
            $("#thankYouModal").modal("show");
        });
    </script>
<?php endif; ?>
                                  
                                    <?php if (isset($thankYouMessage)) {
                                        echo '<script type="text/javascript">
                                                $(document).ready(function() {
                                                    $("#thankYouModal").modal("show");
                                                });
                                                
                                               // Show the modal when the form is submitted
document.getElementById("yourFormId").onsubmit = function() {
    // Prevent form submission for demonstration
    event.preventDefault();
    
    // Show the modal
    document.getElementById("thankYouModal").style.display = "block";
    // Add the animation class
    setTimeout(() => {
        document.getElementById("thankYouModal").classList.add("show");
    }, 10);
};
document.getElementById("smart-form").onsubmit = function(event) {
    let name = document.forms["smart-form"]["name"].value;
    let namePattern = /^[A-Za-z\s]+$/; // Only allows letters and spaces
    
    if (!namePattern.test(name)) {
        document.getElementById("name-error").innerHTML = "Please enter only alphabetic characters in the Name field.";
        event.preventDefault(); // Prevent form submission
    }
};
document.getElementById("smart-form").onsubmit = function(event) {
    let phone = document.forms["smart-form"]["phone"].value;
    let phonePattern = /^\d{10}$/; // Only allows 10 digits
    
    if (!phonePattern.test(phone)) {
        document.getElementById("phone-error").innerHTML = "Please enter a valid 10-digit phone number.";
        event.preventDefault(); // Prevent form submission
    }
};
 
                                                
                                              </script>';
                                    } ?>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>
<!-- Modal styles are now in assets/css/career.css -->
        
        <!-- Footer -->
        
        <?php require_once(__DIR__ . '/includes/dynamic_footer.php'); ?>

        <!-- Scroll to top -->
        <a href="#" class="bg-secondary text-white hover-text-secondary" id="scroll"><i class="fas fa-angle-up"></i></a>
    </div>
</div>


<!--	Js Link
============================================================-->
<script src="<?php echo get_asset_url('js/jquery.min.js', 'js'); ?>"></script>
<!--jQuery Layer Slider -->
<script src="<?php echo get_asset_url('js/greensock.js', 'js'); ?>"></script>
<script src="<?php echo get_asset_url('js/layerslider.transitions.js', 'js'); ?>"></script>
<script src="<?php echo get_asset_url('js/layerslider.kreaturamedia.jquery.js', 'js'); ?>"></script>
<!--jQuery Layer Slider -->
<script src="<?php echo get_asset_url('js/popper.min.js', 'js'); ?>"></script>
<script src="<?php echo get_asset_url('js/bootstrap.min.js', 'js'); ?>"></script>
<script src="<?php echo get_asset_url('js/owl.carousel.min.js', 'js'); ?>"></script>
<script src="<?php echo get_asset_url('js/tmpl.js', 'js'); ?>"></script>
<script src="<?php echo get_asset_url('js/jquery.dependClass-0.1.js', 'js'); ?>"></script>
<script src="<?php echo get_asset_url('js/draggable-0.1.js', 'js'); ?>"></script>
<script src="<?php echo get_asset_url('js/jquery.slider.js', 'js'); ?>"></script>
<script src="<?php echo get_asset_url('js/wow.js', 'js'); ?>"></script>
<script src="<?php echo get_asset_url('js/jquery.cookie.js', 'js'); ?>"></script>
<script src="<?php echo get_asset_url('js/custom.js', 'js'); ?>"></script>
</body>
</html>
