<?php
/**
 * Admin WhatsApp Conversation Live Stream View
 */
$page_title = $page_title ?? 'WhatsApp Conversation Stream';
$logs = $logs ?? [];
?>

<div class="container-fluid py-3">
    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <div>
            <h4 class="mb-0 fw-bold">
                <i class="fas fa-stream text-primary me-2"></i>WhatsApp Conversation Live Stream
            </h4>
            <small class="text-muted">Real-time incoming customer inquiries and automated bot responses.</small>
        </div>
        <div class="d-flex gap-2">
            <a href="/admin/whatsapp/flows" class="btn btn-outline-secondary btn-sm">
                <i class="fab fa-whatsapp me-1"></i>Manage Bot Flows
            </a>
            <button onclick="location.reload();" class="btn btn-primary btn-sm">
                <i class="fas fa-sync-alt me-1"></i>Refresh Stream
            </button>
        </div>
    </div>

    <!-- Live Stream Table -->
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Time</th>
                        <th>Direction</th>
                        <th>Phone Number</th>
                        <th>Matched Flow</th>
                        <th>Message Content</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($logs)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="fab fa-whatsapp fa-2x mb-2 d-block text-success"></i>
                                No WhatsApp conversation logs recorded yet. Inbound or outbound messages will appear here live.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($logs as $l): ?>
                            <?php
                            $isInbound = ($l['direction'] ?? '') === 'inbound';
                            ?>
                            <tr>
                                <td class="small text-muted font-monospace" style="white-space: nowrap;">
                                    <?= date('d M H:i:s', strtotime($l['created_at'])) ?>
                                </td>
                                <td>
                                    <?php if ($isInbound): ?>
                                        <span class="badge bg-warning text-dark">
                                            <i class="fas fa-arrow-down me-1"></i>Inbound
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-success">
                                            <i class="fas fa-arrow-up me-1"></i>Outbound
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="fw-semibold font-monospace"><?= htmlspecialchars($l['phone_number']) ?></span>
                                </td>
                                <td>
                                    <?php if (!empty($l['flow_matched'])): ?>
                                        <span class="badge bg-light text-dark border font-monospace">
                                            <?= htmlspecialchars($l['flow_matched']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted small">—</span>
                                    <?php endif; ?>
                                </td>
                                <td style="max-width: 450px;">
                                    <div class="p-2 rounded small <?= $isInbound ? 'bg-light' : 'bg-success bg-opacity-10 text-dark' ?>">
                                        <?= nl2br(htmlspecialchars($l['message_body'])) ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-secondary"><?= htmlspecialchars($l['status'] ?? 'delivered') ?></span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
