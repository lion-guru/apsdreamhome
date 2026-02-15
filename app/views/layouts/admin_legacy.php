<?php
/**
 * Legacy Admin Layout Wrapper
 * Wraps modern views in the legacy admin template
 */
?>
<?php require_once __DIR__ . '/../admin/admin_header.php'; ?>
<?php require_once __DIR__ . '/../admin/admin_sidebar.php'; ?>

<!-- Page Wrapper -->
<?php 
// The content variable contains the view content
// If the view starts with <div class="page-wrapper">, we output it directly
// Otherwise we wrap it
if (strpos(trim($content), '<div class="page-wrapper">') === 0) {
    echo $content;
} else {
    ?>
    <div class="page-wrapper">
        <div class="content container-fluid">
            <?php echo $content; ?>
        </div>
    </div>
    <?php
}
?>
<!-- /Page Wrapper -->

<?php require_once __DIR__ . '/../admin/admin_footer.php'; ?>
