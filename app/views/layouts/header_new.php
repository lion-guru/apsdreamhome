<?php
/* Header */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'APS Dream Home'; ?></title>
    <meta name="description" content="<?php echo $page_description ?? 'APS Dream Home - Premium Real Estate in Gorakhpur'; ?>">
    <meta name="keywords" content="<?php echo $page_keywords ?? 'real estate, properties, gorakhpur, lucknow, up'; ?>">
    
    <!-- Bootstrap CSS -->
    <link href="<?php echo BASE_URL; ?>public/assets/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="<?php echo BASE_URL; ?>public/assets/plugins/font-awesome/css/all.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="<?php echo BASE_URL; ?>public/css/style.css" rel="stylesheet">
    <link href="<?php echo BASE_URL; ?>public/css/header.css" rel="stylesheet">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?php echo BASE_URL; ?>public/assets/img/favicon.ico">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">
        <div class="container">
            <a class="navbar-brand" href="<?php echo BASE_URL; ?>">
                <img src="<?php echo BASE_URL; ?>public/assets/img/logo.png" alt="APS Dream Home" height="30">
                APS Dream Home
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>">
                            <i class="fas fa-home me-1"></i>Home
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="propertiesDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-building me-1"></i>Properties
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="propertiesDropdown">
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/properties">All Properties</a></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/projects">Projects</a></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/resell">Resell</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>/about">
                            <i class="fas fa-info-circle me-1"></i>About
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>/contact">
                            <i class="fas fa-phone me-1"></i>Contact
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="moreDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-th me-1"></i>More
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="moreDropdown">
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/blog">Blog</a></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/careers">Careers</a></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/team">Team</a></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/testimonials">Testimonials</a></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/faq">FAQ</a></li>
                        </ul>
                    </li>
                </ul>
                
                <!-- Search Bar -->
                <form class="d-flex ms-3" method="GET" action="<?php echo BASE_URL; ?>/properties">
                    <input class="form-control me-2" type="search" name="search" placeholder="Search properties..." aria-label="Search">
                    <button class="btn btn-outline-light" type="submit">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
                
                <!-- User Account -->
                <div class="dropdown ms-3">
                    <a class="btn btn-outline-light dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user"></i> Account
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/login">Login</a></li>
                        <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/register">Register</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/dashboard">Dashboard</a></li>
                        <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/profile">Profile</a></li>
                        <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/logout">Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>
    
    <!-- Breadcrumb for inner pages -->
    <?php if (isset($breadcrumb) && !empty($breadcrumb)): ?>
    <nav class="breadcrumb-nav">
        <div class="container">
            <ol class="breadcrumb">
                <?php foreach ($breadcrumb as $item): ?>
                    <li class="breadcrumb-item">
                        <?php if (isset($item['url'])): ?>
                            <a href="<?php echo $item['url']; ?>"><?php echo $item['title']; ?></a>
                        <?php else: ?>
                            <?php echo $item['title']; ?>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ol>
        </div>
    </nav>
    <?php endif; ?>
