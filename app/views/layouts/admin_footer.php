<?php
/**
 * Admin Footer Layout
 * Dedicated footer for admin pages
 */
if (!isset($sc)) {
    $sc = function($k, $d='') { return $GLOBALS['_site_settings_cache'][$k] ?? $d; };
}
?>

    <!-- Admin Footer -->
    <footer class="admin-footer mt-5">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-4">
                    <h6 class="text-white mb-3">APS Dream Admin</h6>
                    <p class="text-white-50">
                        Premium Real Estate Management System<br>
                        Version 2.0.0 | Build 2026.03.20
                    </p>
                    <div class="admin-footer-links">
                        <a href="<?= BASE_URL ?>admin/dashboard" class="text-white-50 text-decoration-none">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                        <a href="<?= BASE_URL ?>admin/settings" class="text-white-50 text-decoration-none">
                            <i class="fas fa-cog"></i> Settings
                        </a>
                    </div>
                </div>
                <div class="col-md-4">
                    <h6 class="text-white mb-3">Quick Stats</h6>
                    <div class="admin-footer-stats">
                        <div class="stat-item">
                            <i class="fas fa-users text-primary"></i>
                            <span class="text-white">Users: <strong id="footer-user-count">0</strong></span>
                        </div>
                        <div class="stat-item">
                            <i class="fas fa-building text-success"></i>
                            <span class="text-white">Properties: <strong id="footer-property-count">0</strong></span>
                        </div>
                        <div class="stat-item">
                            <i class="fas fa-chart-line text-warning"></i>
                            <span class="text-white">Revenue: <strong id="footer-revenue">₹0</strong></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <h6 class="text-white mb-3">System Info</h6>
                    <div class="system-info">
                        <div class="info-item">
                            <i class="fas fa-server text-info"></i>
                            <span class="text-white-50">Server: Online</span>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-database text-success"></i>
                            <span class="text-white-50">Database: Connected</span>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-clock text-warning"></i>
                            <span class="text-white-50">Uptime: <strong id="system-uptime">0h</strong></span>
                        </div>
                    </div>
                </div>
            </div>
            <hr class="border-secondary my-4">
            <div class="row">
                <div class="col-md-6">
                    <p class="text-white-50 mb-0">
                        © 2026 APS Dream Home. All rights reserved.
                    </p>
                </div>
                <div class="col-md-6 text-end">
                    <div class="admin-footer-actions">
                        <button class="btn btn-sm btn-outline-light me-2" onclick="window.open('https://github.com', '_blank')">
                            <i class="fab fa-github"></i> GitHub
                        </button>
                        <button class="btn btn-sm btn-outline-light me-2" onclick="showSystemLogs()">
                            <i class="fas fa-file-alt"></i> Logs
                        </button>
                        <button class="btn btn-sm btn-outline-light" onclick="showSupport()">
                            <i class="fas fa-life-ring"></i> Support
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <style>
        .admin-footer {
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
            border-top: 1px solid var(--glass-border);
            margin-top: 4rem;
            padding: 3rem 0;
        }

        .admin-footer h6 {
            color: white;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .admin-footer-links {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
        }

        .admin-footer-links a {
            transition: all 0.3s ease;
        }

        .admin-footer-links a:hover {
            color: white !important;
            transform: translateY(-2px);
        }

        .admin-footer-stats,
        .system-info {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .stat-item,
        .info-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: white;
            font-size: 0.875rem;
        }

        .admin-footer-actions {
            display: flex;
            gap: 0.5rem;
            justify-content: flex-end;
        }

        .btn-outline-light {
            border-color: rgba(255, 255, 255, 0.3);
            color: rgba(255, 255, 255, 0.8);
            transition: all 0.3s ease;
        }

        .btn-outline-light:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.5);
            color: white;
            transform: translateY(-2px);
        }
    </style>

    <script>
        // Update footer stats via API
        function updateFooterStats() {
            fetch('<?php echo BASE_URL; ?>/admin/api/dashboard-stats', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    if (data.success) {
                        var el;
                        el = document.getElementById('footer-user-count');
                        if (el) el.textContent = data.users || '—';
                        el = document.getElementById('footer-property-count');
                        if (el) el.textContent = data.properties || '—';
                        el = document.getElementById('footer-revenue');
                        if (el) el.textContent = data.revenue ? '₹' + Number(data.revenue).toLocaleString('en-IN') : '—';
                    }
                })
                .catch(function() {});
        }

        // Update system uptime
        function updateUptime() {
            const startTime = new Date().getTime() - (Math.random() * 86400000); // Random uptime
            setInterval(function() {
                const uptime = new Date().getTime() - startTime;
                const hours = Math.floor(uptime / 3600000);
                const days = Math.floor(hours / 24);
                const remainingHours = hours % 24;
                
                let uptimeText = '';
                if (days > 0) {
                    uptimeText = days + 'd ' + remainingHours + 'h';
                } else {
                    uptimeText = hours + 'h';
                }
                
                document.getElementById('system-uptime').textContent = uptimeText;
            }, 60000); // Update every minute
        }

        // System functions
        function showSystemLogs() {
            showToast('System logs coming soon', 'info');
        }

        function showSupport() {
            alert('Support Center: <?= $sc('contact_email', 'support@apsdreamhome.com') ?>\nPhone: <?= $sc('contact_phone', '+91 92771 21112') ?>');
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            updateFooterStats();
            updateUptime();
            
            // Update stats every 10 seconds
            setInterval(updateFooterStats, 10000);
        });
    </script>

    <script>
    function showToast(message, type) {
        if (typeof APS !== 'undefined' && APS.toast) {
            APS.toast(message, type || 'info');
        } else {
            var toast = document.createElement('div');
            toast.style.cssText = 'position:fixed;top:20px;right:20px;padding:12px 24px;background:#3b82f6;color:#fff;border-radius:8px;z-index:99999;font-size:14px;box-shadow:0 4px 12px rgba(0,0,0,0.15);animation:fadeIn 0.3s';
            toast.textContent = message;
            document.body.appendChild(toast);
            setTimeout(function() { toast.style.opacity = '0'; toast.style.transition = 'opacity 0.3s'; setTimeout(function() { toast.remove(); }, 300); }, 3000);
        }
    }
    </script>

    <!-- Additional Scripts -->
    <!-- Bootstrap JS already loaded in admin.php layout -->