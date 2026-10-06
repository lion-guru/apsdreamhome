<?php
/**
 * Buyer-seller message threads: sent inquiries + inquiries on my listings.
 * @var array $sent
 * @var array $received
 */
$sent = $sent ?? [];
$received = $received ?? [];
$base = defined('BASE_URL') ? BASE_URL : '';
?>
<div class="aps-cp-hero">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h2><i class="fas fa-comments me-2"></i>Message Threads</h2>
            <p>Your property conversations with buyers and sellers — full track record in one place.</p>
        </div>
        <div class="col-md-4 mt-3 mt-md-0">
            <div class="aps-cp-hero-actions justify-content-md-end">
                <a href="<?= $base ?>/properties" class="btn btn-light">
                    <i class="fas fa-search me-2"></i>Browse Properties
                </a>
            </div>
        </div>
    </div>
</div>

<div class="aps-cp-card mb-4">
    <div class="aps-cp-card-header">
        <h5><i class="fas fa-paper-plane text-primary"></i> My Inquiries (I'm buying)</h5>
    </div>
    <div class="aps-cp-card-body p-0">
        <?php if (empty($sent)): ?>
            <div class="aps-cp-empty">
                <div class="aps-cp-empty-icon"><i class="fas fa-paper-plane"></i></div>
                <h5>No inquiries yet</h5>
                <p>Ask about any property and the seller gets notified instantly.</p>
                <a href="<?= $base ?>/properties" class="btn btn-primary">Browse Properties</a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="aps-cp-table">
                    <thead><tr><th>Property</th><th>Seller</th><th>Last Message</th><th></th></tr></thead>
                    <tbody>
                        <?php foreach ($sent as $t): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($t['listing_title'] ?? $t['property_title'] ?? ('#' . ($t['property_id'] ?? $t['listing_id']))) ?></strong></td>
                                <td><?= htmlspecialchars($t['owner_name'] ?? 'APS Team') ?></td>
                                <td><small class="text-muted"><?= htmlspecialchars(mb_substr($t['last_message'] ?? $t['message'] ?? '', 0, 60)) ?></small>
                                    <?php if ((int)($t['unread_count'] ?? 0) > 0): ?>
                                        <span class="badge bg-danger ms-1"><?= (int)$t['unread_count'] ?> new</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end"><a href="<?= $base ?>/user/inquiries/threads/<?= (int)$t['id'] ?>" class="btn btn-sm btn-outline-primary">Open</a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="aps-cp-card mb-4">
    <div class="aps-cp-card-header">
        <h5><i class="fas fa-inbox text-success"></i> Inquiries on My Listings (I'm selling)</h5>
    </div>
    <div class="aps-cp-card-body p-0">
        <?php if (empty($received)): ?>
            <div class="aps-cp-empty">
                <div class="aps-cp-empty-icon"><i class="fas fa-inbox"></i></div>
                <h5>No buyer messages yet</h5>
                <p>When someone inquires on your listing, it appears here with a reply thread.</p>
                <a href="<?= $base ?>/list-property" class="btn btn-primary">List a Property</a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="aps-cp-table">
                    <thead><tr><th>Buyer</th><th>Phone</th><th>Message</th><th></th></tr></thead>
                    <tbody>
                        <?php foreach ($received as $t): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($t['inquirer_name'] ?? $t['name'] ?? 'Guest') ?></strong></td>
                                <td><?= htmlspecialchars($t['phone'] ?? '') ?></td>
                                <td><small class="text-muted"><?= htmlspecialchars(mb_substr($t['message'] ?? '', 0, 60)) ?></small>
                                    <?php if ((int)($t['unread_count'] ?? 0) > 0): ?>
                                        <span class="badge bg-danger ms-1"><?= (int)$t['unread_count'] ?> new</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end"><a href="<?= $base ?>/user/inquiries/threads/<?= (int)$t['id'] ?>" class="btn btn-sm btn-outline-success">Reply</a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
