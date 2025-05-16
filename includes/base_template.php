<?php
// Base Template for Consistent UI/UX
function render_base_template($title, $content, $additional_css = [], $additional_js = []) {
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title); ?> - APS Dream Home</title>
    
    <!-- Core Stylesheets -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/modern-ui.css">
    
    <!-- Additional CSS -->
    <?php foreach($additional_css as $css): ?>
        <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/<?php echo $css; ?>">
    <?php endforeach; ?>

    <!-- Performance and Core Scripts -->
    <script src="<?php echo BASE_URL; ?>assets/js/performance-optimizer.js"></script>
    
    <!-- Additional JS -->
    <?php foreach($additional_js as $js): ?>
        <script src="<?php echo BASE_URL; ?>assets/js/<?php echo $js; ?>"></script>
    <?php endforeach; ?>
</head>
<body>
    <div class="container animate-fade-in">
        <?php echo $content; ?>
    </div>

    <!-- Global Error Handling -->
    <script>
        window.addEventListener('error', function(event) {
            showToast('An unexpected error occurred', 'error');
            console.error('Unhandled error:', event.error);
        });
    </script>
</body>
</html>
<?php
}
?>
