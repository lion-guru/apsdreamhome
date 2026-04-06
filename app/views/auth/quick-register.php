<?php
/**
 * Quick Register - APS Dream Home
 * Easy registration with multiple options
 */

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include bootstrap
require_once __DIR__ . '/../../config/bootstrap.php';

// Check if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: /dashboard');
    exit;
}

// Handle form submission
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'register';
    
    switch ($action) {
        case 'send_otp':
            $phone = preg_replace('/[^0-9]/', '', $_POST['phone'] ?? '');
            
            if (strlen($phone) !== 10) {
                $error = 'Please enter valid 10 digit mobile number';
            } else {
                // Generate OTP
                $otp = rand(100000, 999999);
                $_SESSION['otp'] = $otp;
                $_SESSION['otp_phone'] = $phone;
                $_SESSION['otp_expires'] = time() + 300; // 5 minutes
                
                // Send WhatsApp message
                $wa_message = "Your APS Dream Home OTP is: $otp. Valid for 5 minutes. Don't share with anyone.";
                $wa_url = "https://api.whatsapp.com/send?phone=91$phone&text=" . urlencode($wa_message);
                
                $success = "OTP sent to your WhatsApp! Phone: $phone";
                $redirect_url = $wa_url;
            }
            break;
            
        case 'verify_otp':
            $otp = $_POST['otp'] ?? '';
            $name = $_POST['name'] ?? '';
            $email = $_POST['email'] ?? '';
            $phone = $_SESSION['otp_phone'] ?? '';
            
            if ($otp == $_SESSION['otp'] && time() < $_SESSION['otp_expires']) {
                // OTP verified, create user
                $db = getDB();
                
                // Check if user exists
                $stmt = $db->prepare("SELECT id FROM users WHERE phone = ? OR email = ?");
                $stmt->execute([$phone, $email]);
                if ($stmt->fetch()) {
                    $error = 'User already exists with this phone or email';
                } else {
                    // Create user
                    $password = bin2hex(random_bytes(4));
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    $userId = generateUserId();
                    
                    $stmt = $db->prepare("INSERT INTO users (user_id, name, email, phone, password, user_type, status, created_at) VALUES (?, ?, ?, ?, ?, 'customer', 'active', NOW())");
                    $stmt->execute([$userId, $name, $email, $phone, $hashedPassword]);
                    
                    // Send credentials via WhatsApp
                    $wa_message = "Welcome to APS Dream Home!\n\nYour Login ID: $phone\nPassword: $password\n\nLogin: https://apsdreamhome.com/login\n\nSave these credentials safely.";
                    $wa_url = "https://api.whatsapp.com/send?phone=91$phone&text=" . urlencode($wa_message);
                    
                    $success = 'Registration successful! Check WhatsApp for credentials.';
                    $_SESSION['user_id'] = $db->lastInsertId();
                    $_SESSION['user_phone'] = $phone;
                    
                    // Clear OTP session
                    unset($_SESSION['otp'], $_SESSION['otp_phone'], $_SESSION['otp_expires']);
                }
            } else {
                $error = 'Invalid or expired OTP';
            }
            break;
    }
}

function getDB() {
    $host = getenv('DB_HOST') ?: '127.0.0.1';
    $port = getenv('DB_PORT') ?: '3307';
    $dbname = getenv('DB_DATABASE') ?: 'apsdreamhome';
    $user = getenv('DB_USERNAME') ?: 'root';
    $pass = getenv('DB_PASSWORD') ?: '';
    
    return new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $user, $pass);
}

function generateUserId() {
    return 'APS' . date('Ymd') . rand(1000, 9999);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - APS Dream Home | Quick & Easy</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; }
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
        }
        .otp-input {
            letter-spacing: 8px;
            font-size: 24px;
            text-align: center;
        }
    </style>
</head>
<body class="gradient-bg min-h-screen">
    
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-md mx-auto">
            
            <!-- Logo -->
            <div class="text-center mb-8">
                <h1 class="text-4xl font-bold text-white mb-2">APS Dream Home</h1>
                <p class="text-white opacity-80">Quick Registration - Just 30 Seconds!</p>
            </div>
            
            <?php if ($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?= htmlspecialchars($error) ?>
            </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <?= htmlspecialchars($success) ?>
                <?php if (isset($redirect_url)): ?>
                <a href="<?= $redirect_url ?>" target="_blank" class="block mt-2 text-green-700 underline">Open WhatsApp to see OTP</a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            
            <!-- Step 1: Phone Number -->
            <div class="glass-card rounded-2xl shadow-2xl p-8">
                <h2 class="text-2xl font-semibold text-gray-800 mb-6 text-center">
                    <span class="bg-purple-100 text-purple-600 w-8 h-8 rounded-full inline-flex items-center justify-center text-sm mr-2">1</span>
                    Enter Mobile Number
                </h2>
                
                <form method="POST" class="space-y-4">
                    <input type="hidden" name="action" value="send_otp">
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">WhatsApp Number</label>
                        <div class="flex">
                            <span class="bg-gray-100 border border-r-0 border-gray-300 rounded-l-lg px-4 py-3 text-gray-600">+91</span>
                            <input type="tel" name="phone" placeholder="9876543210" maxlength="10" required
                                   class="flex-1 border border-gray-300 rounded-r-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-purple-500">
                        </div>
                        <p class="text-xs text-gray-500 mt-2">OTP will be sent on WhatsApp</p>
                    </div>
                    
                    <button type="submit" class="w-full bg-gradient-to-r from-purple-600 to-indigo-600 text-white py-4 rounded-lg font-semibold hover:opacity-90 transition">
                        Send OTP on WhatsApp
                    </button>
                </form>
                
                <!-- Social Login -->
                <div class="mt-6">
                    <div class="relative">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-gray-300"></div>
                        </div>
                        <div class="relative flex justify-center text-sm">
                            <span class="px-2 bg-white text-gray-500">Or continue with</span>
                        </div>
                    </div>
                    
                    <div class="mt-4 grid grid-cols-2 gap-3">
                        <a href="/auth/google?type=customer" class="flex items-center justify-center px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                            <img src="https://www.google.com/favicon.ico" class="w-5 h-5 mr-2">
                            Google
                        </a>
                        <a href="/auth/facebook?type=customer" class="flex items-center justify-center px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                            <svg class="w-5 h-5 mr-2 text-blue-600" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                            Facebook
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- OTP Verification (shown after OTP sent) -->
            <?php if (isset($_SESSION['otp'])): ?>
            <div class="glass-card rounded-2xl shadow-2xl p-8 mt-6" id="otp-section">
                <h2 class="text-2xl font-semibold text-gray-800 mb-6 text-center">
                    <span class="bg-purple-100 text-purple-600 w-8 h-8 rounded-full inline-flex items-center justify-center text-sm mr-2">2</span>
                    Verify OTP
                </h2>
                
                <form method="POST" class="space-y-4">
                    <input type="hidden" name="action" value="verify_otp">
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Enter 6-digit OTP</label>
                        <input type="text" name="otp" class="otp-input w-full border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-purple-500" 
                               maxlength="6" required placeholder="------">
                        <p class="text-xs text-gray-500 mt-2">OTP sent to <?= $_SESSION['otp_phone'] ?? '' ?></p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Your Name (Optional)</label>
                        <input type="text" name="name" class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-purple-500" 
                               placeholder="Enter your name">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Email (Optional)</label>
                        <input type="email" name="email" class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-purple-500" 
                               placeholder="Enter your email">
                    </div>
                    
                    <button type="submit" class="w-full bg-gradient-to-r from-green-600 to-emerald-600 text-white py-4 rounded-lg font-semibold hover:opacity-90 transition">
                        Verify & Register
                    </button>
                    
                    <button type="button" onclick="location.reload()" class="w-full text-gray-600 py-2 hover:text-gray-800 transition">
                        Resend OTP
                    </button>
                </form>
            </div>
            <?php endif; ?>
            
            <!-- Login Link -->
            <div class="text-center mt-6">
                <p class="text-white opacity-80">
                    Already have an account? 
                    <a href="/login" class="text-white font-semibold hover:underline">Login here</a>
                </p>
            </div>
            
        </div>
    </div>
    
    <script>
        // Auto-format phone number
        document.querySelector('input[name="phone"]')?.addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);
        });
        
        // Auto-format OTP
        document.querySelector('input[name="otp"]')?.addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '').slice(0, 6);
        });
    </script>
    
</body>
</html>
