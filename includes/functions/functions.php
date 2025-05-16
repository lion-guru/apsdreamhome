<?php
// Include configuration
require_once __DIR__ . '/../config.php';

// Existing sanitize_input function
function sanitize_input($data) {
    global $con;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return mysqli_real_escape_string($con, $data);
}

// Enhanced token generation with expiry
function generate_token($expiry = 3600) {
    $token = bin2hex(random_bytes(32));
    $_SESSION['token'] = $token;
    $_SESSION['token_expiry'] = time() + $expiry;
    return $token;
}

// Validate token
function validate_token($token) {
    if (!isset($_SESSION['token']) || !isset($_SESSION['token_expiry'])) {
        return false;
    }
    if ($_SESSION['token'] !== $token) {
        return false;
    }
    if (time() > $_SESSION['token_expiry']) {
        return false;
    }
    return true;
}

// Enhanced file upload validation
function validate_file_upload($file, $allowed_types = ['jpg', 'jpeg', 'png']) {
    if (!isset($file['error']) || is_array($file['error'])) {
        log_error('Invalid file parameter');
        return false;
    }

    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!in_array($file_extension, $allowed_types)) {
        log_error('Invalid file type: ' . $file_extension);
        return false;
    }
    
    if ($file['size'] > MAX_FILE_SIZE) {
        log_error('File too large: ' . $file['size']);
        return false;
    }

    // Check MIME type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    $allowed_mimes = [
        'image/jpeg',
        'image/png'
    ];

    if (!in_array($mime_type, $allowed_mimes)) {
        log_error('Invalid MIME type: ' . $mime_type);
        return false;
    }
    
    return true;
}

// Enhanced error logging
function log_error($message) {
    $log_file = __DIR__ . '/../logs/error.log';
    $timestamp = date('Y-m-d H:i:s');
    $user_ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
    $log_message = "[$timestamp] IP: $user_ip | Agent: $user_agent | Message: $message\n";
    error_log($log_message, 3, $log_file);
}

// Secure file upload handler
function handle_file_upload($file, $destination_path) {
    if (!validate_file_upload($file)) {
        return false;
    }

    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $new_filename = uniqid() . '.' . $file_extension;
    $destination = $destination_path . '/' . $new_filename;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        log_error('Failed to move uploaded file');
        return false;
    }

    return $new_filename;
}

// Session security check
function check_session_security() {
    if (!isset($_SESSION['last_activity'])) {
        $_SESSION['last_activity'] = time();
    }
    
    if (time() - $_SESSION['last_activity'] > 1800) { // 30 minutes
        session_destroy();
        return false;
    }
    
    $_SESSION['last_activity'] = time();
    return true;
}

// Password strength validation
function validate_password($password) {
    if (strlen($password) < 8) {
        return false;
    }
    if (!preg_match("/[A-Z]/", $password)) {
        return false;
    }
    if (!preg_match("/[a-z]/", $password)) {
        return false;
    }
    if (!preg_match("/[0-9]/", $password)) {
        return false;
    }
    if (!preg_match("/[^A-Za-z0-9]/", $password)) {
        return false;
    }
    return true;
}

// Fetch latest news from database (fallback to static array if DB fails)
function get_latest_news($limit = 3, $allow_html = false, $offset = 0) {
    global $conn;
    $news = [];
    // Try DB first
    if (isset($conn) && $conn) {
        $sql = "SELECT id, title, date, summary, image, content FROM news ORDER BY date DESC, id DESC LIMIT ? OFFSET ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param('ii', $limit, $offset);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $news[] = [
                    'title' => $row['title'],
                    'date' => $row['date'],
                    'summary' => $row['summary'],
                    'url' => '/news-detail.php?id=' . $row['id'],
                    'image' => $row['image'],
                    'content' => $allow_html ? $row['content'] : strip_tags($row['content'])
                ];
            }
            $stmt->close();
        }
    }
    // Fallback to static array if DB fails
    if (empty($news)) {
        $news = [
            [
                'title' => 'APS Dream Homes Launches New Smart Villas',
                'date' => '2025-04-10',
                'summary' => 'Introducing our latest smart villas equipped with home automation and eco-friendly features.',
                'url' => '/news-detail.php?id=1',
                'image' => '/assets/images/news/villa-launch.jpg',
                'content' => $allow_html ? '<p>APS Dream Homes is excited to announce the launch of our <strong>new Smart Villas</strong>. These state-of-the-art homes feature <ul><li>integrated home automation</li><li>solar panels</li><li>energy-efficient appliances</li></ul>Enjoy modern living with eco-friendly benefits and advanced security systems. <a href="/contact.php">Contact us</a> for a private tour and more details!</p>' : 'APS Dream Homes is excited to announce the launch of our new Smart Villas. These state-of-the-art homes feature integrated home automation, solar panels, and energy-efficient appliances. Enjoy modern living with eco-friendly benefits and advanced security systems. Contact us for a private tour and more details!'
            ],
            [
                'title' => 'Awarded Best Real Estate Agency 2025',
                'date' => '2025-03-22',
                'summary' => 'We are honored to receive the Best Real Estate Agency award for 2025.',
                'url' => '/news-detail.php?id=2',
                'image' => '/assets/images/news/award.jpg',
                'content' => $allow_html ? '<p>APS Dream Homes has been recognized as the <strong>Best Real Estate Agency of 2025</strong> by the National Realty Council. This award celebrates our commitment to <em>client satisfaction</em>, transparency, and innovation in the real estate sector. Thank you to our clients and partners for your trust and support.</p>' : 'APS Dream Homes has been recognized as the Best Real Estate Agency of 2025 by the National Realty Council. This award celebrates our commitment to client satisfaction, transparency, and innovation in the real estate sector. Thank you to our clients and partners for your trust and support.'
            ],
            [
                'title' => 'Now Offering Virtual Property Tours',
                'date' => '2025-02-15',
                'summary' => 'Explore properties from the comfort of your home with our new virtual tour feature.',
                'url' => '/news-detail.php?id=3',
                'image' => '/assets/images/news/virtual-tour.jpg',
                'content' => $allow_html ? '<p>We are thrilled to introduce <strong>virtual property tours</strong> for all our listings. Experience immersive <ul><li>3D walkthroughs</li><li>live video calls with agents</li><li>detailed floor plans</li></ul>all from your device. <a href="/properties.php">Schedule a virtual tour</a> today and find your dream home safely and conveniently.</p>' : 'We are thrilled to introduce virtual property tours for all our listings. Experience immersive 3D walkthroughs, live video calls with agents, and detailed floor plans, all from your device. Schedule a virtual tour today and find your dream home safely and conveniently.'
            ],
            // Add more news as needed
        ];
    }
    return array_slice($news, 0, $limit);
}
?>