<?php
include 'config.php';

// फाइल अपलोड सुरक्षा
$target_dir = "uploads/";
if (!file_exists($target_dir)) {
    mkdir($target_dir, 0777, true);
}

$allowed_types = array('jpg', 'jpeg', 'png', 'gif');
$max_size = 5 * 1024 * 1024; // 5MB

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["property_image"])) {
    $file_name = basename($_FILES["property_image"]["name"]);
    $file_size = $_FILES["property_image"]["size"];
    $file_tmp = $_FILES["property_image"]["tmp_name"];
    $file_type = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    $target_file = $target_dir . time() . '_' . $file_name;
    
    // चेक करें कि फाइल टाइप अनुमति प्राप्त है
    if (!in_array($file_type, $allowed_types)) {
        echo "केवल JPG, JPEG, PNG और GIF फाइलें अनुमति प्राप्त हैं।";
        exit;
    }
    
    // चेक करें कि फाइल साइज सीमा के भीतर है
    if ($file_size > $max_size) {
        echo "फाइल साइज 5MB से कम होनी चाहिए।";
        exit;
    }
    
    if (move_uploaded_file($file_tmp, $target_file)) {
        // डेटाबेस में फाइल पाथ सेव करें
        $stmt = $con->prepare("INSERT INTO property_images (property_id, image_path) VALUES (?, ?)");
        $stmt->bind_param("is", $property_id, $target_file);
        $stmt->execute();
        echo "फाइल सफलतापूर्वक अपलोड की गई।";
    } else {
        echo "फाइल अपलोड में त्रुटि।";
    }
}
?>