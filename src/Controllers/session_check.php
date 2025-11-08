<?php
session_start();

// सेशन टाइमआउट (30 मिनट)
$session_timeout = 1800; // 30 मिनट

// चेक करें कि यूजर लॉग्ड इन है
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// चेक करें कि सेशन एक्सपायर तो नहीं हो गया
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $session_timeout)) {
    // सेशन एक्सपायर हो गया है, सेशन डेस्ट्रॉय करें
    session_unset();
    session_destroy();
    header("Location: login.php?expired=1");
    exit;
}

// अपडेट लास्ट एक्टिविटी टाइम
$_SESSION['last_activity'] = time();

// CSRF प्रोटेक्शन फंक्शन
function csrf_token() {
    return $_SESSION['csrf_token'];
}

function csrf_input() {
    return '<input type="hidden" name="csrf_token" value="' . csrf_token() . '">';
}

function verify_csrf_token() {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF टोकन वेरिफिकेशन फेल हुआ");
    }
    return true;
}
?>
