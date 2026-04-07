<?php
if (!defined('BASE_URL')) {
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    define('BASE_URL', $protocol . '://' . $host . '/apsdreamhome');
}
?>
<header class="site-header">
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="<?php echo BASE_URL; ?>/">
                <img src="<?php echo BASE_URL; ?>/assets/images/logo/apslogonew.jpg" alt="APS Dream Home" class="logo">
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/">Home</a></li>
                    
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Buy</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/properties?type=residential">Residential</a></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/properties?type=commercial">Commercial</a></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/properties?type=plot">Plot/Land</a></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/properties?type=house">House/Villa</a></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/properties?type=flat">Flat/Apartment</a></li>
                        </ul>
                    </li>
                    
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Rent</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/properties?listing=rent&type=residential">House/Apartment</a></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/properties?listing=rent&type=commercial">Shop/Office</a></li>
                        </ul>
                    </li>
                    
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Projects</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/company/projects">All Projects</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/projects/gorakhpur">Gorakhpur (3)</a></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/projects/lucknow">Lucknow (1)</a></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/projects/kushinagar">Kushinagar (1)</a></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/projects/varanasi">Varanasi (1)</a></li>
                        </ul>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>/list-property">Post Property FREE</a>
                    </li>
                    
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Services</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/financial-services">Home Loan</a></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/legal-services">Legal Services</a></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/interior-design">Interior Design</a></li>
                        </ul>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>/contact">Contact</a>
                    </li>
                </ul>
                
                <div class="d-flex align-items-center gap-2">
                    <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id']): ?>
                        <div class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle text-primary fw-bold" href="#" data-bs-toggle="dropdown">
                                <i class="fas fa-user-circle me-1"></i>
                                <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'My Account'); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/user/dashboard">
                                    <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                                </a></li>
                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/user/properties">
                                    <i class="fas fa-building me-2"></i>My Properties
                                </a></li>
                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/user/inquiries">
                                    <i class="fas fa-envelope me-2"></i>My Inquiries
                                </a></li>
                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/user/profile">
                                    <i class="fas fa-user-cog me-2"></i>Profile
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="<?php echo BASE_URL; ?>/user/logout">
                                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                                </a></li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <a href="<?php echo BASE_URL; ?>/login" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-user me-1"></i>Login
                        </a>
                        <a href="<?php echo BASE_URL; ?>/register" class="btn btn-primary btn-sm">
                            <i class="fas fa-user-plus me-1"></i>Register
                        </a>
                    <?php endif; ?>
                    <a href="tel:+919277121112" class="btn btn-success btn-sm">
                        <i class="fas fa-phone me-1"></i>
                        <span class="d-none d-md-inline">+91 92771 21112</span>
                    </a>
                </div>
            </div>
        </div>
    </nav>
</header>

<style>
.site-header {
    background: #fff;
    box-shadow: 0 2px 20px rgba(0,0,0,0.08);
    position: sticky;
    top: 0;
    z-index: 1000;
}

.site-header .navbar {
    padding: 8px 0;
}

.site-header .logo {
    height: 45px;
    border-radius: 8px;
}

.site-header .nav-link {
    color: #333;
    font-weight: 500;
    padding: 10px 15px;
    border-radius: 6px;
    transition: all 0.2s;
}

.site-header .nav-link:hover {
    color: #667eea;
    background: rgba(102, 126, 234, 0.08);
}

.site-header .dropdown-menu {
    border: none;
    box-shadow: 0 8px 30px rgba(0,0,0,0.12);
    border-radius: 12px;
    padding: 8px;
    min-width: 200px;
}

.site-header .dropdown-item {
    border-radius: 8px;
    padding: 10px 15px;
    color: #444;
    font-weight: 500;
}

.site-header .dropdown-item:hover {
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
    color: #667eea;
}

.site-header .btn-success {
    background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
    border: none;
    border-radius: 25px;
    padding: 8px 20px;
    font-weight: 600;
}

.site-header .btn-success:hover {
    background: linear-gradient(135deg, #38a169 0%, #2f855a 100%);
    transform: translateY(-1px);
}

@media (max-width: 991px) {
    .site-header .navbar-collapse {
        background: #fff;
        padding: 15px;
        margin-top: 10px;
        border-radius: 12px;
    }
    
    .site-header .nav-item {
        border-bottom: 1px solid #eee;
    }
    
    .site-header .nav-item:last-child {
        border-bottom: none;
    }
}
</style>
