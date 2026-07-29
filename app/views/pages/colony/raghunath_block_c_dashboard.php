<?php
/**
 * Raghunath Nagri Block C — Interactive Booking Dashboard
 * Embeds the standalone real-time suite via iframe.
 * Firebase config + MySQL plot data passed from controller.
 */
$colony   = $colony ?? null;
$plots    = $plots ?? [];
$stats    = $stats ?? ['total'=>0,'available'=>0,'booked'=>0,'corners'=>0,'row_a'=>0,'row_b'=>0,'row_w'=>0];
$firebase = $firebase ?? [];
$app_id   = $app_id ?? 'aps-dream-homes';
if (!isset($sc)) {
    $sc = function ($k, $d = '') {
        return $GLOBALS['_site_settings_cache'][$k] ?? $d;
    };
}
$dashboardUrl = BASE_URL . '/raghunath_nagri_layout_walkthrough_suite.html';
?>
<style>
    #dashboard-frame {
        width: 100%;
        height: calc(100vh - 80px);
        border: none;
        border-radius: 12px;
        box-shadow: 0 8px 32px rgba(0,0,0,0.3);
    }
    .dash-header {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        padding: 16px 24px;
        border-radius: 12px 12px 0 0;
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-bottom: 1px solid rgba(13,148,136,0.3);
    }
    .dash-header h2 {
        color: #0d9488;
        font-size: 1.1rem;
        font-weight: 700;
        margin: 0;
    }
    .dash-header .stats-row {
        display: flex;
        gap: 16px;
        font-size: 0.8rem;
        color: #94a3b8;
    }
    .dash-header .stats-row .stat-pill {
        background: rgba(13,148,136,0.15);
        border: 1px solid rgba(13,148,136,0.3);
        padding: 4px 12px;
        border-radius: 20px;
        color: #5eead4;
        font-weight: 600;
    }
    .dash-header .stats-row .stat-pill.booked {
        background: rgba(239,68,68,0.15);
        border-color: rgba(239,68,68,0.3);
        color: #fca5a5;
    }
    .dash-wrapper {
        background: #0f172a;
        padding: 0;
        border-radius: 12px;
        overflow: hidden;
    }
    .loading-overlay {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #05080e;
        z-index: 10;
        transition: opacity 0.4s;
    }
    .loading-overlay.hidden { opacity: 0; pointer-events: none; }
    .loader-ring {
        width: 48px; height: 48px;
        border: 3px solid rgba(13,148,136,0.2);
        border-top-color: #0d9488;
        border-radius: 50%;
        animation: spin 0.8s linear infinite;
    }
    @keyframes spin { to { transform: rotate(360deg); } }
</style>

<div class="container-fluid px-4 py-3" style="max-width:1400px;">
    <div class="dash-wrapper position-relative">
        <div class="dash-header">
            <div>
                <h2><i class="fas fa-map-marked-alt me-2"></i>Raghunath Nagri — Block C Live Dashboard</h2>
                <small style="color:#64748b;">Real-time Firebase booking sync for field team &amp; admin</small>
            </div>
            <div class="stats-row">
                <span>Total: <span class="stat-pill"><?= $stats['total'] ?></span></span>
                <span>Available: <span class="stat-pill"><?= $stats['available'] ?></span></span>
                <span>Booked: <span class="stat-pill booked"><?= $stats['booked'] ?></span></span>
                <span>Corners: <span class="stat-pill"><?= $stats['corners'] ?></span></span>
            </div>
        </div>

        <div class="loading-overlay" id="dashLoader">
            <div class="text-center">
                <div class="loader-ring mx-auto mb-3"></div>
                <p style="color:#64748b;font-size:0.85rem;">Loading interactive dashboard...</p>
            </div>
        </div>

        <iframe
            id="dashboard-frame"
            src="<?= $dashboardUrl ?>?colony_id=<?= $colony['id'] ?? 4 ?>&app_id=<?= htmlspecialchars($app_id) ?>"
            loading="lazy"
            allowfullscreen
        ></iframe>
    </div>

    <div class="text-center mt-3" style="color:#475569;font-size:0.75rem;">
        <p class="mb-1">
            <i class="fas fa-building me-1"></i>
            <?= h($colony['name'] ?? 'Raghunath Nagri') ?> &middot;
            Block C &middot;
            <?= $stats['total'] ?> Plots &middot;
            CIN: U70109UP2022PTC163047
        </p>
        <p class="mb-0">
            <i class="fas fa-phone me-1"></i> <?= $sc('contact_phone', '919277121112') ?? '919277121112' ?> &middot;
            <i class="fas fa-envelope me-1"></i> apsdreamhome@gmail.com
        </p>
    </div>
</div>

<script>
(function() {
    const frame = document.getElementById('dashboard-frame');
    const loader = document.getElementById('dashLoader');
    if (frame && loader) {
        frame.addEventListener('load', function() {
            loader.classList.add('hidden');
        });
        // Fallback: hide loader after 5s regardless
        setTimeout(function() { loader.classList.add('hidden'); }, 5000);
    }

    // Pass Firebase config to iframe via postMessage
    const firebaseConfig = <?= json_encode($firebase) ?>;
    const app_id = <?= json_encode($app_id) ?>;
    if (frame) {
        frame.addEventListener('load', function() {
            try {
                frame.contentWindow.postMessage({
                    type: 'APS_FIREBASE_CONFIG',
                    config: firebaseConfig,
                    app_id: app_id
                }, '*');
            } catch(e) {}
        });
    }

    // Listen for booking events from iframe and sync to MySQL
    window.addEventListener('message', function(event) {
        if (event.data && event.data.type === 'APS_BOOKING_SYNC') {
            fetch('<?= BASE_URL ?>/api/colony/raghunath-nagri/sync-booking', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(event.data.payload)
            })
            .then(r => r.json())
            .catch(e => console.error('Sync failed:', e));
        }
    });
})();
</script>
