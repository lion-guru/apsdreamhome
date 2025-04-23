<?php
/**
 * Page Update Script
 * This script helps update all pages to use the new header and footer structure
 * Run this script to update all pages in the website
 */

// Start session and include necessary files
session_start();
include("config.php");
// include(__DIR__ . '/includes/updated-config-paths.php');
include(__DIR__ . '/includes/common-functions.php');

// Define the pages to update
$pages_to_update = [
    // 'about.php' => 'updated-about.php',
    // 'contact.php' => 'updated-contact.php',
    // 'property.php' => 'updated-property.php',
    // 'index.php' => 'updated-index.php',
    // 'submitproperty.php' => 'updated-submitproperty.php',
    // 'associate_dashboard.php' => 'updated-associate_dashboard.php',
    // 'dash.php' => 'updated-dash.php',
    // 'login.php' => 'updated-login.php',
    // 'user_dashboard.php' => 'updated-user_dashboard.php',
    // Add more pages here as they are created
];

// Function to check if a file exists
function file_exists_check($file_path) {
    // Convert to absolute path if it's not already
    $file_path_abs = realpath($file_path) ?: __DIR__ . '/' . $file_path;
    
    if (file_exists($file_path_abs)) {
        return "<span style='color:green'>✓ Exists</span>";
    } else {
        return "<span style='color:red'>✗ Missing</span>";
    }
}

// Function to check if a file is writable
function is_writable_check($file_path) {
    // Convert to absolute path if it's not already
    $file_path_abs = realpath($file_path) ?: __DIR__ . '/' . $file_path;
    
    if (file_exists($file_path_abs) && is_writable($file_path_abs)) {
        return "<span style='color:green'>✓ Writable</span>";
    } else {
        return "<span style='color:red'>✗ Not writable</span>";
    }
}

// Function to update a page
function update_page($old_page, $new_page) {
    // Convert to absolute paths if they are not already
    $old_page_abs = realpath($old_page) ?: __DIR__ . '/' . $old_page;
    $new_page_abs = realpath($new_page) ?: __DIR__ . '/' . $new_page;
    
    if (!file_exists($new_page_abs)) {
        return "<span style='color:red'>Error: New page template does not exist</span>";
    }
    
    if (!file_exists($old_page_abs)) {
        return "<span style='color:red'>Error: Original page does not exist</span>";
    }
    
    // Create a backup of the old page
    $backup_file = $old_page_abs . '.bak';
    if (!copy($old_page_abs, $backup_file)) {
        return "<span style='color:red'>Error: Could not create backup</span>";
    }
    
    // Replace the old page with the new page
    if (copy($new_page_abs, $old_page_abs)) {
        return "<span style='color:green'>✓ Updated successfully</span>";
    } else {
        return "<span style='color:red'>Error: Could not update page</span>";
    }
}

// Set page specific variables
$page_title = "Page Update Tool - APS Dream Homes";
$meta_description = "Admin tool to update website pages to the new structure.";

// Process update request
$update_results = [];
if (isset($_POST['update_pages'])) {
    foreach ($pages_to_update as $old_page => $new_page) {
        if (isset($_POST['selected_pages']) && in_array($old_page, $_POST['selected_pages'])) {
            $update_results[$old_page] = update_page($old_page, $new_page);
        }
    }
}

// Additional CSS for this page
$additional_css = '<style>
    .update-tool {
        padding: 50px 0;
    }
    
    .tool-card {
        background-color: #fff;
        border-radius: 10px;
        padding: 30px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
    }
    
    .page-table {
        width: 100%;
        margin-top: 20px;
    }
    
    .page-table th, .page-table td {
        padding: 10px;
        border-bottom: 1px solid #eee;
    }
    
    .page-table th {
        text-align: left;
        font-weight: 600;
    }
    
    .btn-update {
        background-color: var(--primary-color);
        color: #fff;
        padding: 10px 20px;
        border: none;
        border-radius: 5px;
        font-weight: 600;
        transition: all 0.3s ease;
        margin-top: 20px;
    }
    
    .btn-update:hover {
        background-color: var(--secondary-color);
        transform: translateY(-3px);
    }
    
    .alert {
        padding: 15px;
        margin-bottom: 20px;
        border-radius: 5px;
    }
    
    .alert-success {
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }
    
    .alert-warning {
        background-color: #fff3cd;
        color: #856404;
        border: 1px solid #ffeeba;
    }
</style>';

// Include the updated common header
// include(__DIR__ . '/includes/updated-common-header.php');
?>

<!-- Page Banner Section -->
<div class="page-banner" style="background-image: url('<?php echo get_asset_url("banner/admin-banner.jpg", "images"); ?>')">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h1 class="page-title">Page Update Tool</h1>
                <ul class="breadcrumb">
                    <li><a href="<?php echo BASE_URL; ?>/index.php">Home</a></li>
                    <li>Update Pages</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Update Tool Section -->
<section class="update-tool">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="tool-card">
                    <h2 class="mb-4">Update Website Pages</h2>
                    <p>This tool helps you update the website pages to use the new header and footer structure, proper asset paths, and consistent styling.</p>
                    
                    <?php if (!empty($update_results)): ?>
                    <div class="alert alert-success">
                        <h4>Update Results:</h4>
                        <ul>
                            <?php foreach ($update_results as $page => $result): ?>
                            <li><strong><?php echo $page; ?>:</strong> <?php echo $result; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>
                    
                    <form method="post" action="">
                        <table class="page-table">
                            <thead>
                                <tr>
                                    <th width="5%"><input type="checkbox" id="select-all"> All</th>
                                    <th width="25%">Page</th>
                                    <th width="25%">New Template</th>
                                    <th width="20%">Original Status</th>
                                    <th width="25%">Update Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pages_to_update as $old_page => $new_page): ?>
                                <tr>
                                    <td><input type="checkbox" name="selected_pages[]" value="<?php echo $old_page; ?>"></td>
                                    <td><?php echo $old_page; ?></td>
                                    <td><?php echo $new_page; ?> <?php echo file_exists_check($new_page); ?></td>
                                    <td><?php echo file_exists_check($old_page); ?> <?php echo is_writable_check($old_page); ?></td>
                                    <td>
                                        <?php if (isset($update_results[$old_page])): ?>
                                            <?php echo $update_results[$old_page]; ?>
                                        <?php else: ?>
                                            <span style='color:blue'>Pending</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        
                        <div class="text-center">
                            <button type="submit" name="update_pages" class="btn btn-update">Update Selected Pages</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
// Additional JS for this page
$additional_js = '<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Select all checkbox functionality
        const selectAllCheckbox = document.getElementById("select-all");
        const pageCheckboxes = document.querySelectorAll("input[name=\"selected_pages[]\"]");
        
        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener("change", function() {
                pageCheckboxes.forEach(function(checkbox) {
                    checkbox.checked = selectAllCheckbox.checked;
                });
            });
            
            // Also update select-all checkbox if all individual checkboxes are selected/deselected
            pageCheckboxes.forEach(function(checkbox) {
                checkbox.addEventListener("change", function() {
                    let allChecked = true;
                    pageCheckboxes.forEach(function(cb) {
                        if (!cb.checked) allChecked = false;
                    });
                    selectAllCheckbox.checked = allChecked;
                });
            });
        }
        
        console.log("Update tool loaded successfully!");
    });
</script>';

// Include the updated common footer
// include(__DIR__ . '/includes/updated-common-footer.php');
?>