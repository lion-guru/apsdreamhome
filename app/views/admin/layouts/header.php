<?php
/**
 * Modern Admin Header (upgraded from old header.php)
 * Replicates unified_start.php functionality
 */

if (!defined('BASE_URL')) {
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    define('BASE_URL', $protocol . '://' . $host . '/apsdreamhome');
}

$admin_name = $admin_name ?? ($_SESSION['admin_name'] ?? 'Admin');
$admin_role = $admin_role ?? ($_SESSION['admin_role'] ?? 'admin');
$base = BASE_URL;
$page_title = $page_title ?? 'APS Dream Home Admin';
$active_page = $active_page ?? basename($_SERVER['REQUEST_URI'] ?? '');

$success = $_SESSION['success'] ?? null;
$error = $_SESSION['error'] ?? null;
unset($_SESSION['success'], $_SESSION['error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:'Inter',sans-serif;background:#f1f5f9;overflow-x:hidden}
        .sidebar{position:fixed;top:0;left:0;width:280px;height:100vh;background:linear-gradient(180deg, #1e1b4b 0%, #312e81 100%);z-index:1000;overflow-y:auto;transition:transform .3s;box-shadow:4px 0 15px rgba(0,0,0,0.1)}
        .sidebar::-webkit-scrollbar{width:4px}
        .sidebar::-webkit-scrollbar-thumb{background:rgba(255,255,255,.2);border-radius:2px}
        .sidebar-header{padding:20px;border-bottom:1px solid rgba(255,255,255,.1)}
        .sidebar-logo{color:#fff;font-size:1.1rem;font-weight:700;text-decoration:none;display:flex;align-items:center;gap:10px}
        .sidebar-logo i{font-size:1.3rem;color:#a5b4fc}
        .sidebar-sub{color:rgba(255,255,255,.5);font-size:.75rem;margin-top:4px}
        .sidebar-sec{padding:15px 15px 5px;font-size:.7rem;text-transform:uppercase;color:rgba(255,255,255,.4);font-weight:600;letter-spacing:.05em}
        .sidebar-menu{list-style:none;padding:0 10px;margin:0}
        .sidebar-item{margin-bottom:2px}
        .sidebar-link{display:flex;align-items:center;padding:10px 12px;color:#c7d2fe;text-decoration:none;border-radius:8px;font-size:.88rem;font-weight:500;transition:all .2s}
        .sidebar-link:hover{background:rgba(79,70,229,.3);color:#fff}
        .sidebar-link.active{background:#4f46e5;color:#fff}
        .sidebar-link i{width:22px;margin-right:10px;font-size:1rem;color:#a5b4fc;text-align:center}
        .sidebar-link.active i,.sidebar-link:hover i{color:#fff}
        .sidebar-sec-arrow{font-size:.6rem;transition:transform .25s;color:rgba(255,255,255,.3)}
        .sidebar-sec-arrow.collapsed{transform:rotate(-90deg)}
        .main-content{margin-left:280px;min-height:100vh;transition:margin-left .3s}
        .top-nav{background:#fff;height:60px;padding:0 24px;display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid #e2e8f0;position:sticky;top:0;z-index:100}
        .nav-left{display:flex;align-items:center;gap:15px}
        .toggle-btn{background:none;border:none;font-size:1.2rem;color:#64748b;cursor:pointer;display:none;padding:8px;border-radius:8px}
        .toggle-btn:hover{background:#f1f5f9}
        .nav-right{display:flex;align-items:center;gap:15px}
        .nav-icon{position:relative;background:none;border:none;font-size:1.1rem;color:#64748b;cursor:pointer;padding:8px;border-radius:8px}
        .nav-icon:hover{background:#f1f5f9}
        .nav-icon .badge{position:absolute;top:0;right:0;font-size:.65rem;padding:2px 5px;border-radius:10px;background:#ef4444;color:#fff}
        .user-box{display:flex;align-items:center;gap:10px;padding:5px 10px;border-radius:8px;cursor:pointer}
        .user-box:hover{background:#f1f5f9}
        .user-av{width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg, #4f46e5, #7c3aed);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:600;font-size:.85rem}
        .page-content{padding:24px}
        @media(max-width:991px){
            .sidebar{transform:translateX(-100%)}
            .sidebar.show{transform:translateX(0)}
            .main-content{margin-left:0}
            .toggle-btn{display:block}
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/rbac_sidebar.php'; ?>

    <main class="main-content">
        <nav class="top-nav">
            <div class="nav-left">
                <button class="toggle-btn" onclick="document.getElementById('sidebarMenu').classList.toggle('show')">
                    <i class="fas fa-bars"></i>
                </button>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0" style="font-size:.85rem">
                        <li class="breadcrumb-item"><a href="<?php echo $base; ?>/admin/dashboard">Admin</a></li>
                        <li class="breadcrumb-item active"><?php echo htmlspecialchars($active_page); ?></li>
                    </ol>
                </nav>
            </div>
            <div class="nav-right">
                <!-- Language Switcher -->
                <div class="dropdown me-2">
                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" style="font-size:.8rem">
                        <i class="fas fa-globe me-1"></i> EN
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="<?php echo $base; ?>/language/set/en">🇬🇧 English</a></li>
                        <li><a class="dropdown-item" href="<?php echo $base; ?>/language/set/hi">🇮🇳 हिंदी</a></li>
                    </ul>
                </div>
                <?php
                $notifCount = 0;
                try {
                    $ndb = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome;charset=utf8mb4', 'root', '');
                    $ndb->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    $notifCount = (int)$ndb->query("SELECT COUNT(*) FROM inquiries WHERE status = 'new'")->fetchColumn();
                } catch (Exception $e) {}
                ?>
                <button class="nav-icon" title="Notifications">
                    <i class="fas fa-bell"></i>
                    <?php if ($notifCount > 0): ?><span class="badge"><?php echo $notifCount; ?></span><?php endif; ?>
                </button>
                <div class="dropdown">
                    <div class="user-box" data-bs-toggle="dropdown">
                        <div class="user-av"><?php echo strtoupper(substr($admin_name, 0, 1)); ?></div>
                        <div>
                            <div style="font-size:.85rem;font-weight:600;color:#1e293b"><?php echo htmlspecialchars($admin_name); ?></div>
                            <div style="font-size:.7rem;color:#64748b"><?php echo ucfirst(str_replace('_', ' ', $admin_role)); ?></div>
                        </div>
                        <i class="fas fa-chevron-down ms-2" style="font-size:.7rem;color:#64748b"></i>
                    </div>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="<?php echo $base; ?>/admin/profile"><i class="fas fa-user me-2"></i>Profile</a></li>
                        <li><a class="dropdown-item" href="<?php echo $base; ?>/admin/settings"><i class="fas fa-cog me-2"></i>Settings</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="<?php echo $base; ?>/admin/logout"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                    </ul>
                </div>
            </div>
        </nav>

        <div class="page-content">
            <?php if (!empty($success)): ?>
            <div class="alert alert-success alert-dismissible fade show mb-4">
                <i class="fas fa-check-circle me-2"></i>
                <?php echo htmlspecialchars($success); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show mb-4">
                <i class="fas fa-exclamation-circle me-2"></i>
                <?php echo htmlspecialchars($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>
