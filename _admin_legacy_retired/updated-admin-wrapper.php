<?php
/**
 * Updated Admin Page Wrapper for APS Dream Homes
 * This file wraps the content with header, sidebar and footer
 */

// Start output buffering to capture content
ob_start();

// Include the content file
if (isset($content_file) && file_exists($content_file)) {
    include($content_file);
}

// Get the buffered content
$content = ob_get_clean();

// Include header
include('updated-admin-header.php');

// Include sidebar
include('updated-admin-sidebar.php');
?>

<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content container-fluid">
        <?php 
        // Display page title if set
        if (isset($page_title)): 
        ?>
        <div class="page-header">
            <div class="row">
                <div class="col-sm-12">
                    <h3 class="page-title"><?php echo $page_title; ?></h3>
                    <?php if (isset($breadcrumbs)): ?>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                        <?php foreach ($breadcrumbs as $label => $url): ?>
                            <?php if ($url): ?>
                                <li class="breadcrumb-item"><a href="<?php echo $url; ?>"><?php echo $label; ?></a></li>
                            <?php else: ?>
                                <li class="breadcrumb-item active"><?php echo $label; ?></li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Content -->
        <?php echo $content; ?>
        <!-- /Content -->
    </div>
</div>
<!-- /Page Wrapper -->

<?php
// Include footer
include('updated-admin-footer.php');
?>