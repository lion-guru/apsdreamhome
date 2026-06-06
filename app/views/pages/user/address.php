<?php
$layout = 'layouts/customer';
$page_title = $page_title ?? 'My Address - APS Dream Home';
$current_page = 'address';

$userId = (int)($_SESSION['user_id'] ?? 0);
$addresses = [];
try {
    $pdo = \App\Core\Database\Database::getInstance()->getConnection();
    $stmt = $pdo->prepare("SELECT * FROM user_addresses WHERE user_id = ? ORDER BY is_primary DESC, id DESC");
    $stmt->execute([$userId]);
    $addresses = $stmt->fetchAll(\PDO::FETCH_ASSOC);
} catch (\Throwable $e) {
    try {
        $pdo = \App\Core\Cache::getStats() ? new \PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '') : null;
    } catch (\Throwable $e2) {}
}

ob_start();
?>
<div class="aps-cp-page-header">
    <h2><i class="fas fa-map-marker-alt"></i> My Addresses</h2>
    <p>Save addresses for property visits, deliveries, and KYC.</p>
</div>

<div class="aps-cp-card">
    <div class="aps-cp-card-header">
        <h3>Saved Addresses</h3>
        <button class="aps-cp-btn aps-cp-btn-primary" onclick="document.getElementById('addAddrModal').classList.add('show')">
            <i class="fas fa-plus"></i> Add Address
        </button>
    </div>
    <div class="aps-cp-card-body">
        <?php if (empty($addresses)): ?>
        <div class="aps-cp-empty">
            <i class="fas fa-map-marker-alt aps-cp-empty-icon"></i>
            <p>No addresses saved yet. Add one to get started.</p>
        </div>
        <?php else: ?>
        <div class="aps-cp-info-grid">
            <?php foreach ($addresses as $a): ?>
            <div class="aps-cp-info-card">
                <div class="aps-cp-info-card-head">
                    <h4><?= htmlspecialchars($a['label'] ?? $a['address_type'] ?? 'Address') ?></h4>
                    <?php if (!empty($a['is_primary'])): ?>
                    <span class="aps-cp-badge aps-cp-badge-primary">Primary</span>
                    <?php endif; ?>
                </div>
                <p><?= htmlspecialchars($a['address_line1'] ?? '') ?><br>
                <?php if (!empty($a['address_line2'])): ?><?= htmlspecialchars($a['address_line2']) ?><br><?php endif; ?>
                <?= htmlspecialchars($a['city'] ?? '') ?>, <?= htmlspecialchars($a['state'] ?? '') ?> - <?= htmlspecialchars($a['pincode'] ?? '') ?></p>
                <small><i class="fas fa-phone"></i> <?= htmlspecialchars($a['phone'] ?? '—') ?></small>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<div class="aps-cp-alert aps-cp-alert-info mt-3">
    <i class="fas fa-info-circle"></i> Tip: Add pincode-based autofill for faster address entry. (Coming soon)
</div>

<?php
$content = ob_get_clean();
include APP_ROOT . '/app/views/layouts/customer.php';
