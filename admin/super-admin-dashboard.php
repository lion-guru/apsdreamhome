<?php
/**
 * Super Admin Dashboard
 * Visual interface for managing website content and settings
 */

session_start();
require_once(__DIR__ . '/../includes/db_config.php');
$conn = getDbConnection();
global $con;
$con = $conn;

require_once 'config/super-admin-config.php';
require_once 'controllers/SuperAdminController.php';

// Initialize controller
$superAdmin = new SuperAdminController();

// Check if user is logged in and has super admin permissions
if (!isset($_SESSION['utype']) || $_SESSION['utype'] !== 'superadmin') {
    header('Location: ../login.php');
    exit;
}

// Use standardized admin header
include __DIR__ . '/../includes/templates/dynamic_header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super Admin Dashboard - APS Dream Homes</title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="Super Admin Dashboard for APS Dream Homes - manage admins, users, content, and settings.">
    <meta name="theme-color" content="#007bff">
    
    <!-- Include necessary CSS -->
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="assets/plugins/summernote/summernote-bs4.min.css">
    <link rel="stylesheet" href="assets/plugins/toastr/toastr.min.css">
    
    <!-- CSS Compatibility Fixes -->
    <style>
        html {
            -webkit-text-size-adjust: 100%;
            text-size-adjust: 100%;
        }
        .visual-editor {
            border: 1px solid #ddd;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .component-library {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
        }
        .draggable-component {
            cursor: move;
            padding: 10px;
            margin: 5px 0;
            background: white;
            border: 1px solid #ddd;
            border-radius: 3px;
            -webkit-user-select: none;
            user-select: none;
        }
        .preview-area {
            min-height: 400px;
            border: 2px dashed #ccc;
            padding: 20px;
            margin: 20px 0;
        }
    </style>
</head>
<body>

<div class="wrapper">
    <!-- Sidebar -->
    <nav id="sidebar">
        <div class="sidebar-header">
            <h3>Super Admin Panel</h3>
        </div>

        <ul class="list-unstyled components">
            <li class="active">
                <a href="#contentSubmenu" data-toggle="collapse" aria-expanded="false">
                    <i class="fas fa-edit"></i> Content Management
                </a>
                <ul class="collapse list-unstyled" id="contentSubmenu">
                    <li><a href="#" data-section="pages">Pages</a></li>
                    <li><a href="#" data-section="posts">Posts</a></li>
                    <li><a href="#" data-section="media">Media</a></li>
                </ul>
            </li>
            <li>
                <a href="#layoutSubmenu" data-toggle="collapse" aria-expanded="false">
                    <i class="fas fa-th-large"></i> Layout Builder
                </a>
                <ul class="collapse list-unstyled" id="layoutSubmenu">
                    <li><a href="#" data-section="templates">Templates</a></li>
                    <li><a href="#" data-section="components">Components</a></li>
                </ul>
            </li>
            <li>
                <a href="#" data-section="users">
                    <i class="fas fa-users"></i> User Management
                </a>
            </li>
            <li>
                <a href="#" data-section="settings">
                    <i class="fas fa-cog"></i> Settings
                </a>
            </li>
        </ul>
    </nav>

    <!-- Page Content -->
    <div id="content">
        <nav class="navbar navbar-expand-lg navbar-light bg-light">
            <div class="container-fluid">
                <button type="button" id="sidebarCollapse" class="btn btn-info">
                    <i class="fas fa-align-left"></i>
                    <span>Toggle Sidebar</span>
                </button>
                
                <div class="ml-auto">
                    <div class="btn-group">
                        <button class="btn btn-success" id="saveChanges">
                            <i class="fas fa-save"></i> Save Changes
                        </button>
                        <button class="btn btn-warning" id="previewChanges">
                            <i class="fas fa-eye"></i> Preview
                        </button>
                        <a href="/march2025apssite/admin/logout.php" class="btn btn-danger">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Visual Editor Section -->
        <div class="container-fluid">
            <div class="row">
                <!-- Component Library -->
                <div class="col-md-3">
                    <div class="component-library">
                        <h5>Components</h5>
                        <div class="draggable-component" draggable="true" data-component="header">
                            Header
                        </div>
                        <div class="draggable-component" draggable="true" data-component="text-block">
                            Text Block
                        </div>
                        <div class="draggable-component" draggable="true" data-component="image">
                            Image
                        </div>
                        <div class="draggable-component" draggable="true" data-component="gallery">
                            Gallery
                        </div>
                        <div class="draggable-component" draggable="true" data-component="contact-form">
                            Contact Form
                        </div>
                    </div>
                </div>

                <!-- Preview Area -->
                <div class="col-md-9">
                    <div class="preview-area" id="previewArea">
                        <div class="text-center text-muted">
                            <h4>Drag components here</h4>
                            <p>or click to edit existing content</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Include necessary JavaScript -->
<script src="js/jquery.min.js"></script>
<script src="js/bootstrap.bundle.min.js"></script>
<script src="assets/plugins/summernote/summernote-bs4.min.js"></script>
<script src="assets/plugins/toastr/toastr.min.js"></script>
<script src="assets/plugins/sortablejs/Sortable.min.js"></script>

<script>
$(document).ready(function() {
    // Initialize drag and drop
    const previewArea = document.getElementById('previewArea');
    new Sortable(previewArea, {
        group: {
            name: 'shared',
            pull: true,
            put: true
        },
        animation: 150,
        onAdd: function(evt) {
            const item = evt.item;
            const componentType = item.getAttribute('data-component');
            initializeComponent(item, componentType);
        }
    });

    // Initialize components in library
    document.querySelectorAll('.component-library').forEach(container => {
        new Sortable(container, {
            group: {
                name: 'shared',
                pull: 'clone',
                put: false
            },
            sort: false,
            animation: 150
        });
    });

    // Initialize component after drag
    function initializeComponent(element, type) {
        switch(type) {
            case 'text-block':
                $(element).html('<div class="editable" contenteditable="true">Enter your text here</div>');
                break;
            case 'image':
                $(element).html(`
                    <div class="image-upload">
                        <input type="file" accept="image/*" style="display: none">
                        <div class="upload-placeholder">
                            <i class="fas fa-image"></i>
                            <p>Click to upload image</p>
                        </div>
                    </div>
                `);
                break;
            // Add more component initializations as needed
        }
    }

    // Save changes
    $('#saveChanges').click(function() {
        const content = $('#previewArea').html();
        $.ajax({
            url: 'ajax/save-content.php',
            method: 'POST',
            data: { content: content },
            success: function(response) {
                toastr.success('Changes saved successfully');
            },
            error: function() {
                toastr.error('Failed to save changes');
            }
        });
    });

    // Preview changes
    $('#previewChanges').click(function() {
        window.open('preview.php', '_blank');
    });

    // Toggle sidebar
    $('#sidebarCollapse').on('click', function () {
        $('#sidebar').toggleClass('active');
    });
});
</script>

</body>
</html>