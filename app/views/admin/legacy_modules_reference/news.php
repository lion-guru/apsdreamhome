<?php
// Redirect for backward compatibility
header('Location: /admin/admin-news.php');
exit;

require_once __DIR__ . '/core/init.php';
if (!isAdmin()) {
    header('Location: login.php');
    exit();
}
if(isset($_POST['add_news'])) {
    // CSRF validation
    if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
        $msg = "<p class='alert alert-danger'>Invalid CSRF token!</p>";
    } else {
        $db = \App\Core\App::database();
        $title = $_POST['title'];
        $content = $_POST['content'];
        $sql = "INSERT INTO news (title, content) VALUES (?, ?)";
        if ($db->execute($sql, [$title, $content])) {
            log_admin_activity('add_news', 'Added news: ' . $title);
            $msg = "<p class='alert alert-success'>News Added Successfully</p>";
        } else {
            $msg = "<p class='alert alert-warning'>* News Not Added</p>";
        }
    }
}
if(isset($_POST['edit_news'])) {
    // CSRF validation
    if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
        $msg = "<p class='alert alert-danger'>Invalid CSRF token!</p>";
    } else {
        $db = \App\Core\App::database();
        $news_id = (int)$_POST['news_id'];
        $title = $_POST['title'];
        $content = $_POST['content'];
        $sql = "UPDATE news SET title = ?, content = ? WHERE id = ?";
        if ($db->execute($sql, [$title, $content, $news_id])) {
            log_admin_activity('edit_news', 'Edited news ID: ' . $news_id);
            $msg = "<p class='alert alert-success'>News Updated Successfully</p>";
        } else {
            $msg = "<p class='alert alert-warning'>* News Not Updated</p>";
        }
    }
}
// Delete via GET is disabled for security. Use delete.php with POST.
if(isset($_GET['delete_news'])) {
    $msg = "<p class='alert alert-danger'>GET deletion is disabled. Please use the secure delete form.</p>";
}
?>
{{ ... }}
