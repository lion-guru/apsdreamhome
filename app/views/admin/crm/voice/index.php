<?php $page_title = $page_title ?? 'Voice CRM'; $recent = $recent ?? []; $stats = $stats ?? []; ?>
<style>.voice-card{background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);color:#fff;border-radius:14px;padding:24px}.voice-stat{background:#fff;border-radius:14px;border:1px solid #f0f0f5;padding:16px;text-align:center}.voice-stat .val{font-size:24px;font-weight:800}.voice-stat .lbl{font-size:11px;color:#888;text-transform:uppercase}</style>

<div class="container-fluid px-4 py-4">
    <div class="voice-card mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div><h4 class="fw-bold mb-1"><i class="fas fa-microphone me-2"></i>Voice CRM</h4><p class="mb-0 opacity-75">Hindi voice commands, call logging, and voice notes</p></div>
            <a href="<?= BASE_URL ?>/admin/voice-bot" class="btn btn-light btn-lg"><i class="fas fa-phone-alt me-2"></i>Open Voice Bot</a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3"><div class="voice-stat"><div class="val text-primary"><?= $stats['calls'] ?? 0 ?></div><div class="lbl">Voice Calls</div></div></div>
        <div class="col-md-3"><div class="voice-stat"><div class="val text-success"><?= $stats['notes'] ?? 0 ?></div><div class="lbl">Voice Notes</div></div></div>
        <div class="col-md-3"><div class="voice-stat"><div class="val text-info"><?= $stats['total_calls'] ?? 0 ?></div><div class="lbl">Total Interactions</div></div></div>
        <div class="col-md-3"><div class="voice-stat"><div class="val text-warning"><?= $stats['total_duration'] ?? 0 ?>s</div><div class="lbl">Total Duration</div></div></div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm"><div class="card-header"><h6 class="mb-0"><i class="fas fa-terminal me-1"></i>Voice Commands (Hindi)</h6></div>
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-6"><div class="p-2 bg-light rounded"><small class="fw-bold">“अगली बैठक”</small><br><span class="text-muted">Next meeting info</span></div></div>
                        <div class="col-6"><div class="p-2 bg-light rounded"><small class="fw-bold">“हॉट लीड”</small><br><span class="text-muted">Hot lead count</span></div></div>
                        <div class="col-6"><div class="p-2 bg-light rounded"><small class="fw-bold">“नोट जोड़ो”</small><br><span class="text-muted">Dictate a note</span></div></div>
                        <div class="col-6"><div class="p-2 bg-light rounded"><small class="fw-bold">“कॉल करो”</small><br><span class="text-muted">Open call interface</span></div></div>
                        <div class="col-6"><div class="p-2 bg-light rounded"><small class="fw-bold">“अनुसूची बनाओ”</small><br><span class="text-muted">Open scheduler</span></div></div>
                        <div class="col-6"><div class="p-2 bg-light rounded"><small class="fw-bold">“रिपोर्ट दो”</small><br><span class="text-muted">Daily report</span></div></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm"><div class="card-header d-flex justify-content-between align-items-center"><h6 class="mb-0"><i class="fas fa-history me-1"></i>Recent Voice Activity</h6><a href="<?= BASE_URL ?>/admin/crm/voice/call" class="btn btn-sm btn-outline-primary"><i class="fas fa-plus me-1"></i>Start Call</a></div>
                <div class="card-body">
                <?php if (empty($recent)): ?>
                    <div class="text-center py-4">
                        <i class="fas fa-microphone-slash fa-2x text-muted mb-2"></i>
                        <p class="text-muted mb-2">No recent activity</p>
                        <a href="<?= BASE_URL ?>/admin/crm/voice/call" class="btn btn-sm btn-primary"><i class="fas fa-phone me-1"></i>Start Dictation</a>
                    </div>
                <?php else: foreach ($recent as $r): ?>
                    <div class="d-flex align-items-center gap-2 mb-2 p-2 bg-light rounded">
                        <i class="fas fa-<?= $r['interaction_type']==='call' ? 'phone' : 'sticky-note' ?> text-<?= $r['interaction_type']==='call' ? 'success' : 'info' ?>"></i>
                        <div class="flex-grow-1"><small class="fw-bold"><?= htmlspecialchars($r['lead_name'] ?? 'Unknown') ?></small><br><small class="text-muted"><?= htmlspecialchars(substr($r['subject'] ?? $r['body'] ?? '', 0, 50)) ?></small></div>
                        <small class="text-muted"><?= date('d M H:i', strtotime($r['created_at'])) ?></small>
                    </div>
                <?php endforeach; endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
