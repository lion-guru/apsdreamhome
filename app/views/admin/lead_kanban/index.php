<?php
$board           = $board ?? [];
$stats           = $stats ?? [];
$totalLeads      = $totalLeads ?? 0;
$totalValue      = $totalValue ?? 0;
$wonValue        = $wonValue ?? 0;
$activeLeads     = $activeLeads ?? 0;
$conversionRate  = $conversionRate ?? 0;
$users           = $users ?? [];
$sources         = $sources ?? [];
$currentFilters  = $currentFilters ?? [];
$base            = BASE_URL ?? '';
$csrfToken       = $_SESSION['csrf_token'] ?? '';
?>
<style>
  :root {
    --kb-new: #10b981; --kb-contacted: #3b82f6; --kb-qualified: #14b8a6;
    --kb-site-visit: #f59e0b; --kb-proposal: #ec4899; --kb-negotiation: #ef4444;
    --kb-booking: #06b6d4; --kb-won: #22c55e; --kb-lost: #64748b; --kb-nurture: #f97316;
  }
  .pipeline-wrapper { display:flex; gap:0; overflow-x:auto; padding:0 0 16px 0; height: calc(100vh - 200px); min-height: 500px; }
  .pipeline-col { min-width:290px; max-width:320px; flex:0 0 300px; display:flex; flex-direction:column; border-radius:10px; margin:0 6px; background:#1a1d29; border:1px solid rgba(255,255,255,0.06); }
  .pipeline-col.collapsed { min-width:48px; max-width:48px; flex:0 0 48px; overflow:hidden; }
  .pipeline-col.collapsed .col-body,
  .pipeline-col.collapsed .col-header-text,
  .pipeline-col.collapsed .col-count,
  .pipeline-col.collapsed .col-value { display:none; }
  .pipeline-col.collapsed .col-header { justify-content:center; padding:10px 4px; }
  .pipeline-col.collapsed .col-header .collapse-icon { transform:rotate(90deg); }
  .col-header { padding:12px 14px; border-bottom:1px solid rgba(255,255,255,0.06); display:flex; align-items:center; justify-content:space-between; flex-shrink:0; border-radius:10px 10px 0 0; }
  .col-header .stage-dot { width:10px; height:10px; border-radius:50%; flex-shrink:0; }
  .col-header .col-title { font-size:13px; font-weight:600; color:#e2e8f0; margin:0 0 0 8px; white-space:nowrap; }
  .col-header .col-count { background:rgba(255,255,255,0.08); color:#94a3b8; font-size:11px; padding:2px 8px; border-radius:10px; margin-left:6px; }
  .col-header .col-value { font-size:11px; color:#94a3b8; margin-top:2px; }
  .col-header .collapse-icon { color:#94a3b8; cursor:pointer; font-size:12px; transition:transform .2s; }
  .col-body { flex:1; overflow-y:auto; padding:8px; }
  .col-body::-webkit-scrollbar { width:4px; }
  .col-body::-webkit-scrollbar-thumb { background:rgba(255,255,255,0.1); border-radius:2px; }
  .col-body.drag-over { background:rgba(99,102,241,0.08); border-radius:0 0 10px 10px; }

  .lead-card { background:#22253a; border:1px solid rgba(255,255,255,0.06); border-radius:8px; padding:10px 12px; margin-bottom:6px; cursor:grab; transition:all .15s; position:relative; }
  .lead-card:hover { border-color:rgba(99,102,241,0.3); transform:translateY(-1px); box-shadow:0 4px 12px rgba(0,0,0,0.3); }
  .lead-card:active { cursor:grabbing; opacity:0.7; }
  .lead-card.dragging { opacity:0.4; transform:rotate(3deg) scale(0.95); }
  .lead-card .card-name { font-size:13px; font-weight:600; color:#e2e8f0; margin-bottom:4px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
  .lead-card .card-phone { font-size:11px; color:#94a3b8; margin-bottom:4px; }
  .lead-card .card-meta { display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:4px; }
  .lead-card .card-budget { font-size:12px; font-weight:600; color:#10b981; }
  .lead-card .card-score { font-size:10px; padding:1px 6px; border-radius:8px; font-weight:600; }
  .lead-card .card-score.hot { background:rgba(239,68,68,0.15); color:#ef4444; }
  .lead-card .card-score.warm { background:rgba(245,158,11,0.15); color:#f59e0b; }
  .lead-card .card-score.cold { background:rgba(100,116,139,0.15); color:#94a3b8; }
  .lead-card .card-source { font-size:9px; padding:1px 5px; border-radius:4px; background:rgba(99,102,241,0.1); color:#818cf8; text-transform:uppercase; letter-spacing:0.3px; }
  .lead-card .card-time { font-size:10px; color:#94a3b8; }
  .lead-card .card-assignee { display:flex; align-items:center; gap:4px; }
  .lead-card .card-assignee .avatar { width:18px; height:18px; border-radius:50%; background:linear-gradient(135deg,#6366f1,#8b5cf6); color:#fff; font-size:8px; display:flex; align-items:center; justify-content:center; font-weight:700; }
  .lead-card .card-priority { position:absolute; top:0; right:12px; width:0; height:0; border-left:6px solid transparent; border-right:6px solid transparent; }
  .lead-card .card-priority.urgent { border-top:8px solid #ef4444; }
  .lead-card .card-priority.high { border-top:8px solid #f59e0b; }

  .empty-col { display:flex; flex-direction:column; align-items:center; justify-content:center; height:120px; color:#64748b; }
  .empty-col i { font-size:24px; margin-bottom:6px; }

  .toast-move { position:fixed; top:20px; right:20px; padding:12px 20px; border-radius:8px; z-index:9999; box-shadow:0 8px 24px rgba(0,0,0,0.4); font-size:13px; font-weight:500; animation:slideIn .3s ease; display:flex; align-items:center; gap:8px; }
  .toast-move.success { background:#10b981; color:#fff; }
  .toast-move.error { background:#ef4444; color:#fff; }
  @keyframes slideIn { from { opacity:0; transform:translateX(40px); } to { opacity:1; transform:translateX(0); } }

  .quickview-modal { position:fixed; inset:0; z-index:1050; display:none; }
  .quickview-modal.show { display:flex; align-items:center; justify-content:center; }
  .quickview-backdrop { position:absolute; inset:0; background:rgba(0,0,0,0.6); }
  .quickview-panel { position:relative; background:#1a1d29; border:1px solid rgba(255,255,255,0.1); border-radius:12px; width:520px; max-width:95vw; max-height:80vh; overflow-y:auto; box-shadow:0 16px 48px rgba(0,0,0,0.5); }
  .quickview-panel::-webkit-scrollbar { width:4px; }
  .quickview-panel::-webkit-scrollbar-thumb { background:rgba(255,255,255,0.1); border-radius:2px; }
</style>

<div class="container-fluid py-3" class="style-21816">
  <!-- Header -->
  <div class="d-flex justify-content-between align-items-center mb-3 flex-shrink-0">
    <div>
      <h4 class="mb-0"><i class="fas fa-project-diagram me-2 text-primary"></i>Pipeline Board</h4>
      <small class="text-muted">Drag leads between stages to update pipeline</small>
    </div>
    <div class="d-flex gap-2 align-items-center">
      <a href="<?= $base ?>/admin/leads" class="btn btn-outline-secondary btn-sm"><i class="fas fa-list me-1"></i>List</a>
      <a href="<?= $base ?>/admin/leads/create" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i>New Lead</a>
    </div>
  </div>

  <!-- Pipeline Stats Bar -->
  <div class="row mb-3 flex-shrink-0">
    <div class="col">
      <div class="d-flex gap-3 align-items-center flex-wrap">
        <div class="stat-pill">
          <span class="stat-label">Total</span>
          <span class="stat-value" id="stat-total"><?= number_format($totalLeads) ?></span>
        </div>
        <div class="stat-pill">
          <span class="stat-label">Active</span>
          <span class="stat-value text-info" id="stat-active"><?= number_format($activeLeads) ?></span>
        </div>
        <div class="stat-pill">
          <span class="stat-label">Pipeline Value</span>
          <span class="stat-value text-success" id="stat-value">₹<?= number_format($totalValue / 100000, 1) ?>L</span>
        </div>
        <div class="stat-pill">
          <span class="stat-label">Won</span>
          <span class="stat-value text-warning" id="stat-won">₹<?= number_format($wonValue / 100000, 1) ?>L</span>
        </div>
        <div class="stat-pill">
          <span class="stat-label">Win Rate</span>
          <span class="stat-value" id="stat-conv"><?= $conversionRate ?>%</span>
        </div>

        <div class="ms-auto d-flex gap-2 align-items-center">
          <!-- Filter: Assigned To -->
          <select id="filterAssignee" class="form-select form-select-sm" class="style-90031">
            <option value="">All Assignees</option>
            <?php foreach ($users as $u): ?>
              <option value="<?= (int)$u['id'] ?>" <?= ($currentFilters['assigned_to'] ?? '') == $u['id'] ? 'selected' : '' ?>><?= htmlspecialchars($u['name'] ?? '') ?></option>
            <?php endforeach; ?>
          </select>

          <!-- Filter: Source -->
          <select id="filterSource" class="form-select form-select-sm" class="style-90031">
            <option value="">All Sources</option>
            <?php foreach ($sources as $src): ?>
              <option value="<?= htmlspecialchars($src ?? '') ?>" <?= ($currentFilters['source'] ?? '') === $src ? 'selected' : '' ?>><?= ucfirst(htmlspecialchars($src ?? '')) ?></option>
            <?php endforeach; ?>
          </select>

          <button id="collapseAllBtn" class="btn btn-sm btn-outline-secondary" title="Collapse/Expand all"><i class="fas fa-compress-alt"></i></button>
        </div>
      </div>
    </div>
  </div>

  <!-- Pipeline Board -->
  <div class="pipeline-wrapper flex-grow-1" id="pipelineBoard">
    <?php foreach ($board as $col):
      $stage     = $col['stage'];
      $slug      = $stage['slug'];
      $label     = $stage['name'];
      $color     = $stage['color'];
      $leads     = $col['leads'];
      $count     = $col['count'];
      $stageVal  = $col['total_value'];
    ?>
      <div class="pipeline-col" data-stage="<?= htmlspecialchars($slug ?? '') ?>">
        <div class="col-header" class="style-71574">
          <div class="d-flex align-items-center">
            <span class="stage-dot" class="style-96004"></span>
            <span class="col-header-text col-title"><?= htmlspecialchars($label ?? '') ?></span>
            <span class="col-count col-count-badge"><?= $count ?></span>
          </div>
          <div class="d-flex align-items-center gap-2">
            <span class="col-value">₹<?= $stageVal > 99999 ? number_format($stageVal / 100000, 1) . 'L' : number_format($stageVal) ?></span>
            <i class="fas fa-chevron-left collapse-icon" title="Collapse"></i>
          </div>
        </div>
        <div class="col-body" data-stage="<?= htmlspecialchars($slug ?? '') ?>">
          <?php if (empty($leads)): ?>
            <div class="empty-col">
              <i class="fas fa-inbox"></i>
              <small>No leads</small>
            </div>
          <?php else: ?>
            <?php foreach ($leads as $lead):
              $lid       = (int)($lead['id'] ?? 0);
              $name      = htmlspecialchars($lead['name'] ?? 'Unknown');
              $phone     = htmlspecialchars($lead['phone'] ?? '');
              $email     = htmlspecialchars($lead['email'] ?? '');
              $budget    = (float)($lead['budget'] ?? 0);
              $score     = (int)($lead['lead_score'] ?? $lead['score'] ?? 0);
              $source    = htmlspecialchars($lead['source'] ?? '');
              $priority  = $lead['priority'] ?? 'medium';
              $category  = $lead['lead_category'] ?? '';
              $assigned  = htmlspecialchars($lead['assigned_to_name'] ?? '');
              $createdAt = $lead['created_at'] ?? '';
              $nextDate  = $lead['next_activity_date'] ?? '';

              // Score badge
              if ($score >= 70) { $scoreClass = 'hot'; }
              elseif ($score >= 40) { $scoreClass = 'warm'; }
              else { $scoreClass = 'cold'; }

              // Avatar initials
              $initials = strtoupper(substr($name, 0, 1));
              if (str_contains($name, ' ')) {
                $parts = explode(' ', $name);
                $initials = strtoupper(substr($parts[0], 0, 1) . end($parts)[0]);
              }

              // Budget formatting
              $budgetStr = '';
              if ($budget >= 10000000) $budgetStr = '₹' . number_format($budget / 10000000, 1) . 'Cr';
              elseif ($budget >= 100000) $budgetStr = '₹' . number_format($budget / 100000, 1) . 'L';
              elseif ($budget > 0) $budgetStr = '₹' . number_format($budget);

              // Time ago
              $timeAgo = '';
              if ($createdAt) {
                $diff = time() - strtotime($createdAt);
                if ($diff < 60) $timeAgo = 'just now';
                elseif ($diff < 3600) $timeAgo = floor($diff / 60) . 'm ago';
                elseif ($diff < 86400) $timeAgo = floor($diff / 3600) . 'h ago';
                elseif ($diff < 604800) $timeAgo = floor($diff / 86400) . 'd ago';
                else $timeAgo = date('M j', strtotime($createdAt));
              }
            ?>
              <div class="lead-card" draggable="true" data-id="<?= $lid ?>" data-stage="<?= htmlspecialchars($slug ?? '') ?>" onclick="quickViewLead(<?= $lid ?>)">
                <?php if ($priority === 'urgent'): ?>
                  <div class="card-priority urgent"></div>
                <?php elseif ($priority === 'high'): ?>
                  <div class="card-priority high"></div>
                <?php endif; ?>

                <div class="card-name" title="<?= $name ?>"><?= $name ?></div>
                <?php if ($phone): ?>
                  <div class="card-phone"><i class="fas fa-phone me-1"></i><?= $phone ?></div>
                <?php endif; ?>

                <div class="card-meta">
                  <div class="d-flex align-items-center gap-1">
                    <?php if ($budgetStr): ?>
                      <span class="card-budget"><?= $budgetStr ?></span>
                    <?php endif; ?>
                    <?php if ($score > 0): ?>
                      <span class="card-score <?= $scoreClass ?>"><?= $score ?></span>
                    <?php endif; ?>
                  </div>
                  <div class="d-flex align-items-center gap-1">
                    <?php if ($source): ?>
                      <span class="card-source"><?= $source ?></span>
                    <?php endif; ?>
                    <span class="card-time"><?= $timeAgo ?></span>
                  </div>
                </div>

                <?php if ($assigned): ?>
                  <div class="card-assignee mt-1">
                    <div class="avatar"><?= strtoupper(substr($assigned, 0, 2)) ?></div>
                    <span class="style-21010"><?= $assigned ?></span>
                  </div>
                <?php endif; ?>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<!-- Quick View Modal -->
<div class="quickview-modal" id="quickviewModal">
  <div class="quickview-backdrop" onclick="closeQuickView()"></div>
  <div class="quickview-panel" id="quickviewPanel">
    <div class="p-4" id="quickviewContent">
      <div class="text-center py-4"><div class="spinner-border text-primary"></div><p class="text-muted mt-2">Loading lead...</p></div>
    </div>
  </div>
</div>

<script>
(function() {
  const BASE = '<?= $base ?>';
  const CSRF = '<?= $csrfToken ?>';
  let allCollapsed = false;

  // —€—€ Drag and Drop —€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€
  let draggedCard = null;

  document.addEventListener('dragstart', function(e) {
    const card = e.target.closest('.lead-card');
    if (!card) return;
    draggedCard = card;
    card.classList.add('dragging');
    e.dataTransfer.effectAllowed = 'move';
    e.dataTransfer.setData('text/plain', card.dataset.id);
  });

  document.addEventListener('dragend', function(e) {
    const card = e.target.closest('.lead-card');
    if (card) card.classList.remove('dragging');
    document.querySelectorAll('.col-body.drag-over').forEach(el => el.classList.remove('drag-over'));
    draggedCard = null;
  });

  document.querySelectorAll('.col-body').forEach(col => {
    col.addEventListener('dragover', function(e) {
      e.preventDefault();
      e.dataTransfer.dropEffect = 'move';
      col.classList.add('drag-over');
    });
    col.addEventListener('dragleave', function(e) {
      if (!col.contains(e.relatedTarget)) col.classList.remove('drag-over');
    });
    col.addEventListener('drop', function(e) {
      e.preventDefault();
      col.classList.remove('drag-over');
      if (!draggedCard) return;

      const newStage = col.dataset.stage;
      const leadId = draggedCard.dataset.id;
      const oldStage = draggedCard.dataset.stage;

      if (newStage === oldStage) return;

      // Optimistic move
      col.appendChild(draggedCard);
      draggedCard.dataset.stage = newStage;
      updateColumnCounts();

      // AJAX update
      const fd = new FormData();
      fd.append('lead_id', leadId);
      fd.append('status', newStage);
      fd.append('csrf_token', CSRF);

      fetch(BASE + '/admin/lead-kanban/update-stage', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(d => {
          if (d.success) {
            showToast('Lead moved to ' + formatStage(newStage), 'success');
            .catch(err => console.error('Request failed:', err));
            refreshStats();
          } else {
            showToast('Failed: ' + (d.error || 'unknown'), 'error');
            location.reload();
          }
        })
        .catch(() => { showToast('Network error', 'error'); location.reload(); });
    });
  });

  // —€—€ Column Counts After Move —€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€
  function updateColumnCounts() {
    document.querySelectorAll('.pipeline-col').forEach(col => {
      const cards = col.querySelectorAll('.lead-card');
      const badge = col.querySelector('.col-count-badge');
      if (badge) badge.textContent = cards.length;
    });
  }

  // —€—€ Format Stage Name —€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€
  function formatStage(slug) {
    return slug.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
  }

  // —€—€ Live Stats Refresh —€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€
  function refreshStats() {
    const params = new URLSearchParams();
    const assignee = document.getElementById('filterAssignee').value;
    const source = document.getElementById('filterSource').value;
    if (assignee) params.set('assigned_to', assignee);
    if (source) params.set('source', source);

    fetch(BASE + '/admin/lead-kanban/pipeline-stats?' + params.toString())
      .then(r => r.json())
      .then(s => {
        .catch(err => console.error('Request failed:', err));
        document.getElementById('stat-total').textContent = s.total.toLocaleString();
        document.getElementById('stat-active').textContent = s.active.toLocaleString();
        document.getElementById('stat-value').textContent = '₹' + (s.value / 100000).toFixed(1) + 'L';
        document.getElementById('stat-won').textContent = '₹' + (s.won_value / 100000).toFixed(1) + 'L';
        document.getElementById('stat-conv').textContent = s.conversion + '%';
      }).catch(() => {});
  }

  // —€—€ Filter Change —€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€
  document.getElementById('filterAssignee').addEventListener('change', applyFilters);
  document.getElementById('filterSource').addEventListener('change', applyFilters);

  function applyFilters() {
    const params = new URLSearchParams();
    const assignee = document.getElementById('filterAssignee').value;
    const source = document.getElementById('filterSource').value;
    if (assignee) params.set('assigned_to', assignee);
    if (source) params.set('source', source);
    window.location.href = BASE + '/admin/lead-kanban' + (params.toString() ? '?' + params.toString() : '');
  }

  // —€—€ Collapse/Expand —€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€
  document.getElementById('collapseAllBtn').addEventListener('click', function() {
    allCollapsed = !allCollapsed;
    document.querySelectorAll('.pipeline-col').forEach(col => {
      col.classList.toggle('collapsed', allCollapsed);
    });
    this.querySelector('i').className = allCollapsed ? 'fas fa-expand-alt' : 'fas fa-compress-alt';
  });

  document.querySelectorAll('.collapse-icon').forEach(icon => {
    icon.addEventListener('click', function(e) {
      e.stopPropagation();
      this.closest('.pipeline-col').classList.toggle('collapsed');
    });
  });

  // —€—€ Quick View —€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€
  window.quickViewLead = function(id) {
    const modal = document.getElementById('quickviewModal');
    const content = document.getElementById('quickviewContent');
    modal.classList.add('show');
    content.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary"></div><p class="text-muted mt-2">Loading lead...</p></div>';

    fetch(BASE + '/admin/lead-kanban/lead-quickview?id=' + id)
      .then(r => r.json())
      .then(d => {
        if (d.error) { content.innerHTML = '<p class="text-danger p-4">' + d.error + '</p>'; return; }
        const lead = d.data || d;
        const name = lead.name || 'Unknown';
        const phone = lead.phone || '—';
        const email = lead.email || '—';
        const budget = lead.budget ? '₹' + Number(lead.budget).toLocaleString() : '—';
        .catch(err => console.error('Request failed:', err));
        const score = lead.lead_score || lead.score || 0;
        const status = (lead.status || 'new').replace(/_/g, ' ');
        const source = lead.source || '—';
        const assigned = lead.assigned_to_name || 'Unassigned';
        const priority = lead.priority || 'medium';
        const notes = lead.notes || '';
        const company = lead.company || '';
        const city = lead.city || '';
        const lastActivity = lead.last_activity_date || lead.last_contacted_at || '';

        let interactions = '';
        if (lead.interactions && lead.interactions.length > 0) {
          interactions = lead.interactions.slice(0, 5).map(i =>
            '<div class="d-flex gap-2 mb-2" class="style-20427">' +
            '<span class="badge bg-secondary">' + (i.interaction_type || 'note') + '</span>' +
            '<span class="text-muted">' + (i.subject || i.body || '') + '</span>' +
            '</div>'
          ).join('');
        }

        content.innerHTML = `
          <div class="d-flex justify-content-between align-items-start mb-3">
            <div>
              <h5 class="mb-1" class="style-96443"><?= $base ?>` + name + `</h5>
              <small class="text-muted">${company ? company + ' Â· ' : ''}${city || ''}</small>
            </div>
            <button class="btn btn-sm btn-outline-secondary" onclick="closeQuickView()"><i class="fas fa-times"></i></button>
          </div>
          <div class="row g-3 mb-3">
            <div class="col-6">
              <small class="text-muted d-block">Phone</small>
              <span class="style-96443">${phone}</span>
            </div>
            <div class="col-6">
              <small class="text-muted d-block">Email</small>
              <span class="style-96443">${email}</span>
            </div>
            <div class="col-4">
              <small class="text-muted d-block">Budget</small>
              <span class="style-49662">${budget}</span>
            </div>
            <div class="col-4">
              <small class="text-muted d-block">Score</small>
              <span class="badge ${score >= 70 ? 'bg-danger' : score >= 40 ? 'bg-warning' : 'bg-secondary'}">${score}</span>
            </div>
            <div class="col-4">
              <small class="text-muted d-block">Priority</small>
              <span class="badge bg-${priority === 'urgent' ? 'danger' : priority === 'high' ? 'warning' : 'secondary'}">${priority}</span>
            </div>
            <div class="col-4">
              <small class="text-muted d-block">Stage</small>
              <span class="badge" class="style-98566">${status}</span>
            </div>
            <div class="col-4">
              <small class="text-muted d-block">Source</small>
              <span class="style-96443">${source}</span>
            </div>
            <div class="col-4">
              <small class="text-muted d-block">Assigned</small>
              <span class="style-96443">${assigned}</span>
            </div>
          </div>
          ${notes ? '<div class="mb-3"><small class="text-muted d-block mb-1">Notes</small><p class="style-65804">' + notes + '</p></div>' : ''}
          ${interactions ? '<div class="mb-3"><small class="text-muted d-block mb-2">Recent Activity</small>' + interactions + '</div>' : ''}
          <div class="d-flex gap-2 mt-3">
            <a href="${BASE}/admin/leads/${lead.id}" class="btn btn-sm btn-primary"><i class="fas fa-external-link-alt me-1"></i>Full Detail</a>
            <a href="${BASE}/admin/leads/${lead.id}/edit" class="btn btn-sm btn-outline-secondary"><i class="fas fa-edit me-1"></i>Edit</a>
            <a href="tel:${phone.replace(/\D/g, '')}" class="btn btn-sm btn-outline-success"><i class="fas fa-phone me-1"></i>Call</a>
          </div>
        `;
      })
      .catch(err => {
        content.innerHTML = '<p class="text-danger p-4">Error loading lead: ' + err.message + '</p>';
      });
  };

  window.closeQuickView = function() {
    document.getElementById('quickviewModal').classList.remove('show');
  };

  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeQuickView();
  });

  // —€—€ Toast —€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€
  function showToast(msg, type) {
    const toast = document.createElement('div');
    toast.className = 'toast-move ' + type;
    toast.innerHTML = '<i class="fas fa-' + (type === 'success' ? 'check-circle' : 'exclamation-circle') + '"></i>' + msg;
    document.body.appendChild(toast);
    setTimeout(() => { toast.style.opacity = '0'; toast.style.transition = 'opacity .3s'; }, 2500);
    setTimeout(() => toast.remove(), 2800);
  }

  // —€—€ Keyboard shortcut: 'n' for new lead —€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€
  document.addEventListener('keydown', function(e) {
    if (e.target.tagName === 'INPUT' || e.target.tagName === 'SELECT' || e.target.tagName === 'TEXTAREA') return;
    if (e.key === 'n' || e.key === 'N') window.location.href = BASE + '/admin/leads/create';
  });

})();
</script>

<style>
  .stat-pill { display:inline-flex; align-items:center; gap:6px; background:#1a1d29; border:1px solid rgba(255,255,255,0.06); border-radius:8px; padding:6px 14px; }
  .stat-pill .stat-label { font-size:11px; color:#94a3b8; text-transform:uppercase; letter-spacing:0.3px; }
  .stat-pill .stat-value { font-size:16px; font-weight:700; color:#e2e8f0; }
</style>
