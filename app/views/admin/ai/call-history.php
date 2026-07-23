<?php
$page_title = $page_title ?? 'Call History - APS Dream Home';
$calls = $calls ?? [];
$totalCalls = $totalCalls ?? 0;
$completedCalls = $completedCalls ?? 0;
$failedCalls = $failedCalls ?? 0;
$interestedCount = $interestedCount ?? 0;
$pagination = $pagination ?? ['page' => 1, 'total_pages' => 1, 'total' => 0, 'per_page' => 25];
$filters = $filters ?? [];
$base = BASE_URL . '/admin/ai-calling/history';
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <h2 class="mb-0"><i class="fas fa-history me-2 text-primary"></i>Call History</h2>
        <a href="<?= BASE_URL ?>/admin/ai-calling/dashboard" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm" style="border-left:4px solid var(--bs-primary);border-radius:10px">
                <div class="card-body py-3"><div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3"><span class="badge bg-primary rounded-pill p-2"><i class="fas fa-phone-volume"></i></span></div>
                    <div><div style="font-size:1.5rem;font-weight:700"><?= number_format($totalCalls) ?></div><div class="small text-muted text-uppercase">Total Calls</div></div>
                </div></div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm" style="border-left:4px solid #16a34a;border-radius:10px">
                <div class="card-body py-3"><div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3"><span class="badge bg-success rounded-pill p-2"><i class="fas fa-check-circle"></i></span></div>
                    <div><div style="font-size:1.5rem;font-weight:700" class="text-success"><?= number_format($completedCalls) ?></div><div class="small text-muted text-uppercase">Completed</div><div class="small text-muted"><?= $totalCalls > 0 ? round($completedCalls/$totalCalls*100) : 0 ?>% rate</div></div>
                </div></div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm" style="border-left:4px solid #dc2626;border-radius:10px">
                <div class="card-body py-3"><div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3"><span class="badge bg-danger rounded-pill p-2"><i class="fas fa-times-circle"></i></span></div>
                    <div><div style="font-size:1.5rem;font-weight:700" class="text-danger"><?= number_format($failedCalls) ?></div><div class="small text-muted text-uppercase">Failed</div></div>
                </div></div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm" style="border-left:4px solid #f59e0b;border-radius:10px">
                <div class="card-body py-3"><div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3"><span class="badge bg-warning rounded-pill p-2"><i class="fas fa-star"></i></span></div>
                    <div><div style="font-size:1.5rem;font-weight:700" class="text-warning"><?= number_format($interestedCount) ?></div><div class="small text-muted text-uppercase">Interested</div></div>
                </div></div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-3">
            <form method="get" action="<?= $base ?>">
                <div class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Search</label>
                        <input type="text" name="q" class="form-control form-control-sm" placeholder="Phone or lead name..." value="<?= htmlspecialchars($filters['q'] ?? '') ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">Status</label>
                        <select name="status" class="form-select form-select-sm">
                            <option value="">All Status</option>
                            <?php foreach (['completed','failed','in_progress','no_answer','scheduled'] as $s): ?>
                                <option value="<?= $s ?>" <?= ($filters['status'] ?? '') === $s ? 'selected' : '' ?>><?= ucfirst(str_replace('_',' ',$s)) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">From</label>
                        <input type="date" name="from" class="form-control form-control-sm" value="<?= htmlspecialchars($filters['from'] ?? '') ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">To</label>
                        <input type="date" name="to" class="form-control form-control-sm" value="<?= htmlspecialchars($filters['to'] ?? '') ?>">
                    </div>
                    <div class="col-md-3 d-flex gap-1">
                        <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search me-1"></i>Filter</button>
                        <a href="<?= $base ?>" class="btn btn-outline-secondary btn-sm">Clear</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Phone</th>
                            <th>Lead</th>
                            <th>Status</th>
                            <th>Response</th>
                            <th>Duration</th>
                            <th>Agent</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($calls)): ?>
                        <tr><td colspan="8" class="text-center text-muted py-5">
                            <i class="fas fa-phone-slash fa-3x mb-3" style="opacity:0.15"></i>
                            <h5 class="text-muted">No call records found</h5>
                            <p class="text-muted mb-0">Calls will appear here after the auto-dialer runs.</p>
                        </td></tr>
                        <?php else: ?>
                        <?php foreach ($calls as $call): ?>
                        <tr style="cursor:pointer" onclick="viewCallDetail(<?= $call['id'] ?>)">
                            <td><?= $call['id'] ?></td>
                            <td><i class="fas fa-phone text-muted me-1"></i><?= htmlspecialchars($call['phone'] ?? '') ?></td>
                            <td><?= htmlspecialchars($call['lead_name'] ?? 'Unknown') ?></td>
                            <td>
                                <?php
                                $sColors = ['completed'=>'success','failed'=>'danger','in_progress'=>'warning','no_answer'=>'secondary','scheduled'=>'info'];
                                $sColor = $sColors[$call['status']] ?? 'secondary';
                                ?>
                                <span class="badge bg-<?= $sColor ?>"><?= ucfirst(str_replace('_',' ',$call['status'] ?? 'unknown')) ?></span>
                            </td>
                            <td>
                                <?php
                                $rColors = ['interested'=>'success','not_interested'=>'danger','callback'=>'info','dnd'=>'warning','no_answer'=>'secondary'];
                                $resp = $call['customer_response'] ?? '';
                                $rColor = $rColors[$resp] ?? 'secondary';
                                ?>
                                <?php if ($resp): ?>
                                    <span class="badge bg-<?= $rColor ?>"><?= ucfirst(str_replace('_',' ',$resp)) ?></span>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td><?= isset($call['duration_seconds']) ? $call['duration_seconds'].'s' : '-' ?></td>
                            <td><small class="text-muted"><?= htmlspecialchars($call['ai_agent_id'] ?? '-') ?></small></td>
                            <td><small class="text-muted"><?= date('d M, H:i', strtotime($call['created_at'] ?? 'now')) ?></small></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php if ($pagination['total_pages'] > 1): ?>
        <div class="card-footer bg-white border-top">
            <nav class="d-flex justify-content-between align-items-center">
                <small class="text-muted">Showing <?= ($pagination['page']-1)*$pagination['per_page']+1 ?>-<?= min($pagination['page']*$pagination['per_page'], $pagination['total']) ?> of <?= number_format($pagination['total']) ?></small>
                <ul class="pagination pagination-sm mb-0">
                    <li class="page-item <?= $pagination['page'] <= 1 ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=<?= $pagination['page']-1 ?>&<?= http_build_query(array_filter(['status'=>$filters['status']??'','from'=>$filters['from']??'','to'=>$filters['to']??'','q'=>$filters['q']??''])) ?>">Prev</a>
                    </li>
                    <?php for ($i = max(1,$pagination['page']-2); $i <= min($pagination['total_pages'],$pagination['page']+2); $i++): ?>
                    <li class="page-item <?= $i == $pagination['page'] ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>&<?= http_build_query(array_filter(['status'=>$filters['status']??'','from'=>$filters['from']??'','to'=>$filters['to']??'','q'=>$filters['q']??''])) ?>"><?= $i ?></a>
                    </li>
                    <?php endfor; ?>
                    <li class="page-item <?= $pagination['page'] >= $pagination['total_pages'] ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=<?= $pagination['page']+1 ?>&<?= http_build_query(array_filter(['status'=>$filters['status']??'','from'=>$filters['from']??'','to'=>$filters['to']??'','q'=>$filters['q']??''])) ?>">Next</a>
                    </li>
                </ul>
            </nav>
        </div>
        <?php endif; ?>
    </div>
</div>

<div class="modal fade" id="callDetailModal" tabindex="-1"><div class="modal-dialog modal-lg"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title">Call Detail</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body" id="callDetailBody"><div class="text-center py-4"><i class="fas fa-spinner fa-spin"></i> Loading...</div></div>
</div></div></div>

<script>
async function viewCallDetail(id) {
    const modal = new bootstrap.Modal(document.getElementById('callDetailModal'));
    document.getElementById('callDetailBody').innerHTML = '<div class="text-center py-4"><i class="fas fa-spinner fa-spin"></i> Loading...</div>';
    modal.show();
    try {
        const res = await fetch('<?= BASE_URL ?>/admin/ai-calling/call-detail?id=' + id);
        const data = await res.json();
        if (!data.success) { document.getElementById('callDetailBody').innerHTML = '<div class="alert alert-danger">Call not found</div>'; return; }
        const c = data.call;
        let html = '<div class="row mb-3"><div class="col-md-6"><strong>Phone:</strong> ' + (c.phone||'') + '</div><div class="col-md-6"><strong>Status:</strong> <span class="badge bg-info">' + (c.status||'') + '</span></div></div>';
        html += '<div class="row mb-3"><div class="col-md-6"><strong>Duration:</strong> ' + (c.duration_seconds||0) + 's</div><div class="col-md-6"><strong>Response:</strong> ' + (c.customer_response||'N/A') + '</div></div>';
        if (c.call_transcript) { html += '<hr><h6>Transcript</h6><div style="max-height:300px;overflow-y:auto;background:#f8fafc;padding:12px;border-radius:8px;font-size:0.85rem;white-space:pre-wrap">' + c.call_transcript + '</div>'; }
        document.getElementById('callDetailBody').innerHTML = html;
    } catch(e) { document.getElementById('callDetailBody').innerHTML = '<div class="alert alert-danger">Failed to load</div>'; }
}
</script>
