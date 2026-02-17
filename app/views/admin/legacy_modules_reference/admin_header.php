<?php
/**
 * Admin Header for APS Dream Homes
 * Contains header elements shared across all admin pages
 */

// Include admin functions
require_once(__DIR__ . '/admin-functions.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>APS Dream Homes | <?php echo h(isset($page_title) ? $page_title : 'Admin Dashboard'); ?></title>

    <!-- Favicon -->
    <link rel="shortcut icon" type="image/x-icon" href="<?php echo h(get_admin_asset_url('favicon.png', 'img')); ?>">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="<?php echo h(get_admin_asset_url('bootstrap.min.css', 'css')); ?>">

    <!-- Fontawesome CSS -->
    <link rel="stylesheet" href="<?php echo h(get_admin_asset_url('font-awesome.min.css', 'css')); ?>">

    <!-- Feathericon CSS -->
    <link rel="stylesheet" href="<?php echo h(get_admin_asset_url('feathericon.min.css', 'css')); ?>">

    <?php if(isset($include_datatables) && $include_datatables): ?>
    <!-- Datatables CSS -->
    <link rel="stylesheet" href="<?php echo h(get_admin_asset_url('datatables/dataTables.bootstrap4.min.css', 'plugins')); ?>">
    <link rel="stylesheet" href="<?php echo h(get_admin_asset_url('datatables/responsive.bootstrap4.min.css', 'plugins')); ?>">
    <link rel="stylesheet" href="<?php echo h(get_admin_asset_url('datatables/select.bootstrap4.min.css', 'plugins')); ?>">
    <link rel="stylesheet" href="<?php echo h(get_admin_asset_url('datatables/buttons.bootstrap4.min.css', 'plugins')); ?>">
    <?php endif; ?>

    <!-- Main CSS -->
    <link rel="stylesheet" href="<?php echo h(get_admin_asset_url('style.css', 'css')); ?>">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="<?php echo h(generateCSRFToken()); ?>">
</head>
<body>
    <!-- Main Wrapper -->
    <div class="main-wrapper">
        <!-- Header -->
        <div class="header">
            <!-- Logo -->
            <div class="header-left">
                <a href="<?php echo h(ADMIN_URL); ?>/dashboard.php" class="logo">
                    <img src="<?php echo h(get_admin_asset_url('aps.png', 'img')); ?>" alt="Logo">
                </a>
                <a href="<?php echo h(ADMIN_URL); ?>/dashboard.php" class="logo logo-small">
                    <img src="<?php echo h(get_admin_asset_url('logo-small.png', 'img')); ?>" alt="Logo" width="30" height="30">
                </a>
            </div>
            <!-- /Logo -->

            <a href="javascript:void(0);" id="toggle_btn">
                <i class="fe fe-text-align-left"></i>
            </a>

            <div class="top-nav-search">
                <form>
                    <input type="text" class="form-control" placeholder="Search here">
                    <button class="btn" type="submit"><i class="fa fa-search"></i></button>
                </form>
            </div>

            <!-- Mobile Menu Toggle -->
            <a class="mobile_btn" id="mobile_btn">
                <i class="fa fa-bars"></i>
            </a>
            <!-- /Mobile Menu Toggle -->

            <!-- Header Right Menu -->
            <ul class="nav user-menu">
                <!-- Notifications -->
                <li class="nav-item dropdown noti-dropdown">
                    <a href="#" class="dropdown-toggle nav-link" data-toggle="dropdown">
                        <i class="fe fe-bell"></i>
                        <?php
                        $db = \App\Core\App::database();
                        $notif_count_row = $db->fetch("SELECT COUNT(*) as total FROM notifications WHERE status = 'unread'");
                        $notif_count = $notif_count_row['total'] ?? 0;
                        if ($notif_count > 0):
                        ?>
                        <span class="badge badge-pill bg-danger"><?php echo h($notif_count); ?></span>
                        <?php endif; ?>
                    </a>
                    <div class="dropdown-menu notifications">
                        <div class="topnav-dropdown-header">
                            <span class="notification-title">Notifications</span>
                            <a href="javascript:void(0)" class="clear-noti"> Clear All </a>
                        </div>
                        <div class="noti-content">
                            <ul class="notification-list">
                                <?php
                                $notifications = $db->fetchAll("SELECT * FROM notifications ORDER BY created_at DESC LIMIT 5");
                                foreach($notifications as $notif):
                                    $is_ai = ($notif['type'] == 'AI_REMINDER');
                                ?>
                                <li class="notification-message">
                                    <a href="#">
                                        <div class="media">
                                            <span class="avatar avatar-sm">
                                                <i class="<?php echo $is_ai ? 'fas fa-robot text-info' : 'fas fa-info-circle text-primary'; ?> fa-2x"></i>
                                            </span>
                                            <div class="media-body">
                                                <p class="noti-details">
                                                    <?php if($is_ai): ?><span class="noti-title text-info">[AI Priority]</span><?php endif; ?>
                                                    <?php echo h($notif['message']); ?>
                                                </p>
                                                <p class="noti-time"><span class="notification-time"><?php echo date('M d, H:i', strtotime($notif['created_at'])); ?></span></p>
                                            </div>
                                        </div>
                                    </a>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <div class="topnav-dropdown-footer">
                            <a href="notifications.php">View all Notifications</a>
                        </div>
                    </div>
                </li>
                <!-- /Notifications -->

                <!-- Theme Toggle -->
                <li class="nav-item">
                    <a href="javascript:void(0);" id="dark-mode-toggle" class="nav-link">
                        <i class="fe fe-moon"></i>
                    </a>
                </li>
                <!-- User Menu -->
                <li class="nav-item dropdown has-arrow">
                    <a href="#" class="dropdown-toggle nav-link" data-toggle="dropdown">
                        <span class="user-img"><img class="rounded-circle" src="<?php echo h(get_admin_asset_url('profiles/avatar-01.png', 'img')); ?>" width="31" alt="Admin"></span>
                    </a>
                    <div class="dropdown-menu">
                        <div class="user-header">
                            <div class="avatar avatar-sm">
                                <img src="<?php echo h(get_admin_asset_url('profiles/avatar-01.png', 'img')); ?>" alt="User Image" class="avatar-img rounded-circle">
                            </div>
                            <div class="user-text">
                                <h6><?php echo h(getAuthFullName() ?: 'Admin'); ?></h6>
                                <p class="text-muted mb-0"><?php echo h(ucfirst(getAuthSubRole() ?: 'Administrator')); ?></p>
                            </div>
                        </div>
                        <a class="dropdown-item" href="<?php echo h(ADMIN_URL); ?>/profile.php">My Profile</a>
                        <a class="dropdown-item" href="<?php echo h(ADMIN_URL); ?>/settings.php">Settings</a>
                        <a class="dropdown-item" href="<?php echo h(ADMIN_URL); ?>/logout.php">Logout</a>
                    </div>
                </li>
                <!-- /User Menu -->
            </ul>
            <!-- /Header Right Menu -->
        </div>
        <!-- /Header -->
