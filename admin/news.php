<?php
// Redirect for backward compatibility
header('Location: /admin/admin-news.php');
exit;

session_start();
require_once __DIR__ . '/includes/session_manager.php';
require_once __DIR__ . '/../includes/db_config.php';
require_once __DIR__ . '/../includes/log_admin_activity.php';
// Secure authentication check
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}
if(isset($_POST['add_news'])) {
    $title = $_POST['title'];
    $content = $_POST['content'];
    // ... (other news details and validation)
    $sql = "INSERT INTO news (title, content) VALUES ('$title', '$content')";
    $result = mysqli_query($con, $sql);
    if($result) {
        log_admin_activity('add_news', 'Added news: ' . $title);
        $msg = "<p class='alert alert-success'>News Added Successfully</p>";
    } else {
        $msg = "<p class='alert alert-warning'>* News Not Added</p>";
    }
}
if(isset($_POST['edit_news'])) {
    $news_id = $_POST['news_id'];
    $title = $_POST['title'];
    $content = $_POST['content'];
    $sql = "UPDATE news SET title = '$title', content = '$content' WHERE id = $news_id";
    $result = mysqli_query($con, $sql);
    if($result) {
        log_admin_activity('edit_news', 'Edited news ID: ' . $news_id);
        $msg = "<p class='alert alert-success'>News Updated Successfully</p>";
    } else {
        $msg = "<p class='alert alert-warning'>* News Not Updated</p>";
    }
}
if(isset($_GET['delete_news'])) {
    $news_id = $_GET['delete_news'];
    $sql = "DELETE FROM news WHERE id = $news_id";
    $result = mysqli_query($con, $sql);
    if($result) {
        log_admin_activity('delete_news', 'Deleted news ID: ' . $news_id);
        $msg = "<p class='alert alert-success'>News Deleted Successfully</p>";
    } else {
        $msg = "<p class='alert alert-warning'>* News Not Deleted</p>";
    }
}
?>
{{ ... }}
