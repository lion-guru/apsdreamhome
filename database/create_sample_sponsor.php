<?php
// Script to create a sample sponsor record for associate registration testing

// Include database configuration
include("config.php");

// Create users table if not exists
$create_users_table = "CREATE TABLE IF NOT EXISTS `users` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(100) NOT NULL,
    `email` varchar(100) NOT NULL,
    `password` varchar(255) NOT NULL,
    `phone` varchar(20) DEFAULT NULL,
    `utype` enum('user','agent','builder','associate','admin') NOT NULL DEFAULT 'user',
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    `status` enum('active','inactive') NOT NULL DEFAULT 'active',
    PRIMARY KEY (`id`),
    UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

// Create associates table if not exists
$create_associates_table = "CREATE TABLE IF NOT EXISTS `associates` (
    `associate_id` int(11) NOT NULL AUTO_INCREMENT,
    `uid` varchar(10) UNIQUE NOT NULL,
    `user_id` int(11) NOT NULL,
    `sponsor_id` int(11) DEFAULT NULL,
    `referral_code` varchar(20) UNIQUE NOT NULL,
    PRIMARY KEY (`associate_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

$con->query($create_users_table);
$con->query($create_associates_table);

// Check if the sample sponsor already exists
$check_query = "SELECT * FROM associates WHERE uid = 'APS000001'";
$result = $con->query($check_query);

if ($result->num_rows > 0) {
    echo "<div style='background-color: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin: 20px;'>
            <h3>Sample Sponsor Already Exists</h3>
            <p>A sample sponsor with ID 'APS000001' already exists in the database.</p>
            <p>You can use this ID to register new associates.</p>
          </div>";
} else {
    // Create a sample sponsor record
    $sponsor_id = "APS000001";
    $name = "APS Company Sponsor";
    $email = "sponsor@apsdreamhomes.com";
    $phone = "9876543210";
    
    // Use secure password hashing
    $password = password_hash("Sponsor@123", PASSWORD_DEFAULT);
    
    // First, create a user record
    $insert_user_query = "INSERT INTO users 
                    (name, email, phone, password, utype, status) 
                    VALUES (?, ?, ?, ?, 'associate', 'active')";    
    $stmt_user = $con->prepare($insert_user_query);
    $stmt_user->bind_param("ssss", $name, $email, $phone, $password);
    
    if ($stmt_user->execute()) {
        $user_id = $con->insert_id;
        
        // Generate a unique referral code (6 characters)
        $referral_code = substr(str_shuffle("ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789"), 0, 6);
        
        // Now insert into associates table
        $insert_query = "INSERT INTO associates 
                        (uid, user_id, referral_code) 
                        VALUES (?, ?, ?)";
    
    $stmt = $con->prepare($insert_query);
    $stmt->bind_param("sss", $sponsor_id, $user_id, $referral_code);
    
    if ($stmt->execute()) {
        echo "<div style='background-color: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin: 20px;'>
                <h3>Sample Sponsor Created Successfully</h3>
                <p>A sample sponsor has been created with the following details:</p>
                <ul>
                    <li><strong>Sponsor ID:</strong> $sponsor_id</li>
                    <li><strong>Name:</strong> $name</li>
                    <li><strong>Email:</strong> $email</li>
                    <li><strong>Phone:</strong> $phone</li>
                    <li><strong>Password:</strong> Sponsor@123</li>
                </ul>
                <p>You can now use this Sponsor ID to register new associates.</p>
              </div>";
    } else {
        echo "<div style='background-color: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin: 20px;'>
                <h3>Error Creating Sample Sponsor</h3>
                <p>An error occurred while creating the sample sponsor: " . $stmt->error . "</p>
              </div>";
    }
    
        $stmt->close();
    } else {
        echo "<div style='background-color: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin: 20px;'>
                <h3>Error Creating Sample Sponsor</h3>
                <p>An error occurred while creating the user record: " . $stmt_user->error . "</p>
              </div>";
    }
    
    $stmt_user->close();
}

$con->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Sample Sponsor - APS Dream Homes</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            text-align: center;
        }
        .btn {
            display: inline-block;
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 20px;
        }
        .btn:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Create Sample Sponsor for Associate Registration</h1>
        <p>This page creates a sample sponsor record that can be used for testing the associate registration system.</p>
        <a href="associate_register.php" class="btn">Go to Associate Registration</a>
    </div>
</body>
</html>