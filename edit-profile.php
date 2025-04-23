<?php 
if(!isset($_GET['uid'])) {
    die("User ID not provided");
}

ini_set('session.cache_limiter','public');
session_cache_limiter(false);
session_start();
include("config.php");

$uid = $_GET['uid'];
    $stmt = $con->prepare("SELECT * FROM `user` WHERE uid = ?");
    $stmt->bind_param("i", $uid);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_array();
    
    if(!$row) {
        die("User not found");
    }
?>

<head>
<!-- Required meta tags -->
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

<!-- Meta Tags -->
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="description" content="Real Estate PHP">
<meta name="keywords" content="">
<meta name="author" content="Unicoder">
<link rel="shortcut icon" href="images/favicon.ico">

<!-- Fonts -->
<link href="https://fonts.googleapis.com/css?family=Muli:400,400i,500,600,700&amp;display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css?family=Comfortaa:400,700" rel="stylesheet">

<!-- Css Link -->
  
<link rel="stylesheet" href="<?php echo get_asset_url('css/bootstrap.min.css', 'css'); ?>">
<link rel="stylesheet" href="<?php echo get_asset_url('css/bootstrap-slider.css', 'css'); ?>">
<link rel="stylesheet" href="<?php echo get_asset_url('css/jquery-ui.css', 'css'); ?>">
<link rel="stylesheet" href="<?php echo get_asset_url('css/layerslider.css', 'css'); ?>">
<link rel="stylesheet" href="<?php echo get_asset_url('css/color.css', 'css'); ?>" id="color-change">
<link rel="stylesheet" href="<?php echo get_asset_url('css/owl.carousel.min.css', 'css'); ?>">
<link rel="stylesheet" href="<?php echo get_asset_url('css/font-awesome.min.css', 'css'); ?>">
<link rel="stylesheet" type="text/css" href="fonts/flaticon/flaticon.css">
<link rel="stylesheet" href="<?php echo get_asset_url('css/style.css', 'css'); ?>">

<!-- Title -->
<title>Edit Profile</title>
</head>

<div id="page-wrapper">
    <div class="row"> 
        <!--	Header start  -->
		<?php include(__DIR__ . '/includes/header.php');?>
		 <div class="full-row">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <h2 class="text-secondary double-down-line text-center">Edit Profile</h2>
                    </div>
                </div>
                <!-- Profile Picture Section -->
<div class="dashboard-personal-info p-5 bg-white">
    <div class="row">
        <div class="col-lg-6 col-md-12">
            <h5 class="text-secondary border-bottom-on-white pb-3 mb-4">Change Profile Picture</h5>
            <form action="#" method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="profile_picture">Profile Picture:</label>
                    <input type="file" name="profile_picture" id="profile_picture" class="form-control">
                    <img src="assets/images/user" alt="Profile Picture" width="100" height="100" id="image-preview">
                </div>
                <input type="submit" name="update_profile_picture" value="Update Profile Picture" class="btn btn-primary">
            </form>
            
        </div>
        
    </div>
    
</div>

               <?php if($row['utype'] == 'agent' || $row['utype'] == 'user' || $row['utype'] == 'builder') { ?>
                <div class="dashboard-personal-info p-5 bg-white">
                    
                    <div class="row">
                        
                        <div class="col-lg-6 col-md-12">
                            <h5 class="text-secondary border-bottom-on-white pb-3 mb-4">Basic Profile Changes</h5>
                            <form action="#" method="post">
                                <div class="form-group">
                                    <label for="name">Name:</label>
                                    <input type="text" name="name" value="<?php echo $row['3']; ?>" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label for="email">Email:</label>
                                    <input type="email" name="email" value="<?php echo $row['4']; ?>" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label for="phone">Phone:</label>
                                    <input type="number" name="phone" value="<?php echo $row['5']; ?>" class="form-control">
                                </div>
                                <input type="submit" name="update_basic" value="Update Basic Profile" class="btn btn-primary">
                            </form>
                        </div>
                    </div>
                </div>
                else { ?>
    <p class="alert alert-warning">You do not have permission to update your basic profile information.</p>

<?php } ?>
                        <div class="col-lg-6 col-md-12">
                            <h5 class="text-secondary border-bottom-on-white pb-3 mb-4">Password Changes</h5>
                            <form action="#" method="post">
                                <div class="form-group">
                                    <label for="old_password">Old Password:</label>
                                    <input type="password" name="old_password" class="form-control">
                                </div>
                                <div class="form-group">
    <label for="new_password">New Password:</label>
    <input type="password" name="new_password" id="new_password" class="form-control">
    <ul id="password-errors" style="display: none; color: red;"></ul>
</div>
<div class="form-group">
    <label for="confirm_password">Confirm Password:</label>
    <input type="password" name="confirm_password" id="confirm_password" class="form-control">
    <span id="password-match-error" style="display: none; color: red;">Passwords do not match!</span>
    <span id="password-match-success" style="display: none; color: green;">Passwords match!</span>
</div>
                                <input type="submit" name="update_password" value="Update Password" class="btn btn-primary">
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
 <!--	Footer   start-->
		<?php include(__DIR__ . '/includes/footer.php');?>
		<!--	Footer   start-->

<?php 
if(isset($_POST['update_profile_picture']))
{
    $profile_picture = $_FILES['profile_picture'];
    
    // Validate image
    if($profile_picture['type'] == 'image/jpeg' || $profile_picture['type'] == 'image/png')
    {
        // Upload image
        $image_name = $profile_picture['name'];
        $image_tmp = $profile_picture['tmp_name'];
        
        // Compress image
        // Create directory if not exists
if(!is_dir('images/profile_pictures')) {
    mkdir('images/profile_pictures', 0777, true);
}

// Generate unique filename to prevent overwriting
$file_ext = pathinfo($image_name, PATHINFO_EXTENSION);
$new_filename = uniqid().'.'.$file_ext;
$compressed_image = compressImage($image_tmp, 'images/profile_pictures/' . $new_filename);
        
        // Update profile picture
        $stmt = $con->prepare("UPDATE `user` SET profile_picture = ? WHERE uid = ?");
        $stmt->bind_param("si", $compressed_image, $uid);
        $stmt->execute();
        
        if($stmt->affected_rows > 0)
        {
            echo "Profile picture updated successfully!";
            // Redirect to the profile page
            header("Location: profile.php");
            exit;
        }
        else
        {
            echo "Failed to update profile picture!";
        }
    } else {
        echo "Invalid image type!";
    }
}

function compressImage($source, $destination) {
    $image_info = getimagesize($source);
    $image_type = $image_info[2];
    
    if ($image_type == IMAGETYPE_JPEG) {
        $image = imagecreatefromjpeg($source);
        imagejpeg($image, $destination, 50);
    } elseif ($image_type == IMAGETYPE_PNG) {
        $image = imagecreatefrompng($source);
        imagepng($image, $destination, 9);
    }
    
    return $destination;
}
if(isset($_POST['update_basic']))
{
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    
    // Validate user input
    if(!empty($name) && !empty($email) && !empty($phone)) {
        // Update basic profile
        $stmt = $con->prepare("UPDATE `users` SET uname = ?, uemail = ?, uphone = ? WHERE uid = ?");
        $stmt->bind_param("sssi", $name, $email, $phone, $uid);
        $stmt->execute();
        
        if($stmt->affected_rows > 0)
        {
            echo "Basic profile updated successfully!";
            // Redirect to the profile page
            header("Location: profile.php");
            exit;
        }
        else
        {
            echo "Failed to update basic profile!";
        }
    } else {
        echo "Please fill all the fields!";
    }
}

if(isset($_POST['update_password']))
{
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate user input
    if(!empty($old_password) && !empty($new_password) && !empty($confirm_password)) {
        // Check if old password is correct
        $stmt = $con->prepare("SELECT password FROM `users` WHERE uid = ?");
        $stmt->bind_param("i", $uid);
        $stmt->execute();
        $stmt->bind_result($old_password_hash);
        $stmt->fetch();
        
        if(password_verify($old_password, $old_password_hash))
        {
            // Check if new password and confirm password are the same
            if($new_password == $confirm_password)
            {
                // Check if new password is strong enough
                if(strlen($new_password) >= 8 && preg_match("/[a-z]/", $new_password) && preg_match("/[A-Z]/", $new_password) && preg_match("/[0-9]/", $new_password) && preg_match("/[!@#$%^&*()_+=-{};:'<>,./?]/", $new_password))
                {
                    // Update password
                    $new_password_hash = password_hash($new_password, PASSWORD_BCRYPT);
                    $stmt = $con->prepare("UPDATE `users` SET upassword = ? WHERE uid = ?");
                    $stmt->bind_param("si", $new_password_hash, $uid);
                    $stmt->execute();
                    
                    if($stmt->affected_rows > 0)
                    {
                        echo "Password updated successfully!";
                        // Redirect to the profile page
                        header("Location: profile.php");
                        exit;
                    }
                    else
                    {
                        echo "Failed to update password!";
                    }
                }
                else
                {
                    echo "New password is not strong enough. Please use a password that is at least 8 characters long and includes at least one lowercase letter, one uppercase letter, one number, and one special character.";
                }
            }
            else
            {
                echo "New password and confirm password do not match!";
            }
        }
        else
        {
            echo "Old password is incorrect!";
        }
    } else {
        echo "Please fill all the fields!";
    }
}


?>
<script>
// Get the image field
var imageField = document.getElementById('profile_picture');

// Add an event listener to the image field
imageField.addEventListener('change', function() {
    // Get the image file
    var imageFile = imageField.files[0];
    
    // Create a FileReader object
    var reader = new FileReader();
    
    // Add an event listener to the FileReader object
    reader.addEventListener('load', function() {
        // Get the image preview element
        var imagePreview = document.getElementById('image-preview');
        
        // Set the image preview source
        imagePreview.src = reader.result;
    });
    
    // Read the image file
    reader.readAsDataURL(imageFile);
});
function checkPasswordStrength(password) {
    var errors = [];

    // Check if the password is at least 8 characters long
    if (password.length < 8) {
        errors.push("Password must be at least 8 characters long.");
    }

    // Check if the password includes at least one lowercase letter
    if (!/[a-z]/.test(password)) {
        errors.push("Password must include at least one lowercase letter.");
    }

    // Check if the password includes at least one uppercase letter
    if (!/[A-Z]/.test(password)) {
        errors.push("Password must include at least one uppercase letter.");
    }

    // Check if the password includes at least one number
    if (!/[0-9]/.test(password)) {
        errors.push("Password must include at least one number.");
    }

    // Check if the password includes at least one special character
    if (!/[^a-zA-Z0-9]/.test(password)) {
        errors.push("Password must include at least one special character.");
    }

    return errors;
}

// Get the password field
var passwordField = document.getElementById('new_password');

// Add an event listener to the password field
passwordField.addEventListener('input', function() {
    var password = passwordField.value;
    var errors = checkPasswordStrength(password);

    // Display the errors
    var errorList = document.getElementById('password-errors');
    errorList.innerHTML = '';
    if (errors.length > 0) {
        errorList.style.display = 'block';
        errors.forEach(function(error) {
            var errorElement = document.createElement('li');
            errorElement.textContent = error;
            errorList.appendChild(errorElement);
        });
    } else {
        errorList.style.display = 'none';
    }
});

// Get the new password and confirm password fields
var newPassword = document.getElementById('new_password');
var confirmPassword = document.getElementById('confirm_password');

// Add an event listener to the confirm password field
confirmPassword.addEventListener('input', function() {
    // Check if the new password and confirm password match
    if (newPassword.value === confirmPassword.value) {
        // If they match, remove the error message and add a success message
        document.getElementById('password-match-error').style.display = 'none';
        document.getElementById('password-match-success').style.display = 'block';
    } else {
        // If they don't match, remove the success message and add an error message
        document.getElementById('password-match-success').style.display = 'none';
        document.getElementById('password-match-error').style.display = 'block';
    }
});


</script>