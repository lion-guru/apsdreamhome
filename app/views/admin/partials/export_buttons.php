<?php

/**
 * Standard Export Buttons Partial
 * Consistent export functionality across all admin pages
 */

$currentUrl = esc_url($_SERVER['REQUEST_URI'] ?? '');
$pageName = basename($currentUrl, '.php');
?>

<div class="dropdown">
    <button class="btn btn-success dropdown-toggle" type="button" data-bs-toggle="dropdown">
        <i class="fas fa-download me-2"></i>Export
    </button>
    <ul class="dropdown-menu">
        <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/admin/export/<?php echo e($pageName); ?>/excel">
                <i class="fas fa-file-excel me-2 text-success"></i>Export as Excel
            </a></li>
        <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/admin/export/<?php echo e($pageName); ?>/csv">
                <i class="fas fa-file-csv me-2 text-primary"></i>Export as CSV
            </a></li>
        <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/admin/export/<?php echo e($pageName); ?>/pdf">
                <i class="fas fa-file-pdf me-2 text-danger"></i>Export as PDF
            </a></li>
        <li>
            <hr class="dropdown-divider">
        </li>
        <li><a class="dropdown-item" href="#" onclick="window.print()">
                <i class="fas fa-print me-2"></i>Print
            </a></li>
    </ul>
</div>