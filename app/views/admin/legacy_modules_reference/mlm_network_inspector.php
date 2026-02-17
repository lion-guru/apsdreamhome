<?php
require_once __DIR__ . '/../../includes/admin_header.php';
?>

<div class="container-fluid py-4">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center mb-4 gap-3">
        <div>
            <h1 class="h3 mb-1">MLM Network Inspector</h1>
            <p class="text-muted mb-0">Browse associate trees, manage commission agreements, and trigger maintenance actions.</p>
        </div>
        <div class="d-flex gap-2">
            <input type="text" id="userSearch" class="form-control" placeholder="Search associate by name or email" style="max-width: 280px;">
            <button class="btn btn-outline-primary" id="searchBtn"><i class="fas fa-search me-1"></i>Search</button>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card h-100">
                <div class="card-header d-flex flex-wrap gap-2 align-items-center justify-content-between">
                    <div>
                        <h5 class="mb-0">Network Tree <span class="text-muted" id="selectedUserLabel"></span></h5>
                        <small class="text-muted">Depth-limited preview with rank color coding.</small>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <select class="form-select form-select-sm" id="depthSelect" style="width:auto;">
                            <option value="2">Depth 2</option>
                            <option value="3">Depth 3</option>
                            <option value="4">Depth 4</option>
                            <option value="5" selected>Depth 5</option>
                        </select>
                        <select class="form-select form-select-sm" id="rankSelect" style="min-width:160px;">
                            <option value="">All Ranks</option>
                            <?php foreach ($ranks as $rank): ?>
                                <option value="<?= htmlspecialchars($rank['label']) ?>"><?= htmlspecialchars($rank['label']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button class="btn btn-sm btn-outline-secondary" id="exportCsv"><i class="fas fa-file-csv me-1"></i>CSV</button>
                        <button class="btn btn-sm btn-outline-secondary" id="exportPng"><i class="fas fa-image me-1"></i>PNG</button>
                    </div>
                </div>
                <div class="card-body">
                    <div id="treePlaceholder" class="text-center text-muted">Select an associate to load the tree.</div>
                    <div id="treeContainer" class="network-tree" style="display:none;"></div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Commission Agreements</h5>
                    <button class="btn btn-sm btn-primary" id="newAgreementBtn"><i class="fas fa-plus me-1"></i>New</button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped mb-0" id="agreementTable">
                            <thead class="table-light">
                                <tr>
                                    <th>Rate</th>
                                    <th>Flat</th>
                                    <th>Valid</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="text-muted">
                                    <td colspan="4" class="text-center">Select a user to view agreements</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <button class="btn btn-outline-primary w-100 mb-2" id="rebuildBtn" disabled>
                        <i class="fas fa-redo me-1"></i> Rebuild Network
                    </button>
                    <button class="btn btn-outline-secondary w-100 mb-2" id="viewProfileBtn" disabled>
                        <i class="fas fa-user me-1"></i> View Profile
                    </button>
                    <button class="btn btn-outline-success w-100" id="notificationsBtn" disabled>
                        <i class="fas fa-envelope me-1"></i> Notification Log
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Agreement Modal -->
<div class="modal fade" id="agreementModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Commission Agreement</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="agreementForm">
                    <input type="hidden" name="id" id="agreementId">
                    <input type="hidden" name="user_id" id="agreementUserId">
                    <div class="mb-3">
                        <label class="form-label">Property (optional)</label>
                        <input type="number" name="property_id" class="form-control" placeholder="Property ID">
                    </div>
                    <div class="row g-3">
                        <div class="col">
                            <label class="form-label">Commission %</label>
                            <input type="number" step="0.001" name="commission_rate" class="form-control" placeholder="e.g. 0.150">
                        </div>
                        <div class="col">
                            <label class="form-label">Flat Amount (₹)</label>
                            <input type="number" step="0.01" name="flat_amount" class="form-control" placeholder="e.g. 50000">
                        </div>
                    </div>
                    <div class="row g-3 mt-3">
                        <div class="col">
                            <label class="form-label">Valid From</label>
                            <input type="date" name="valid_from" class="form-control">
                        </div>
                        <div class="col">
                            <label class="form-label">Valid To</label>
                            <input type="date" name="valid_to" class="form-control">
                        </div>
                    </div>
                    <div class="mt-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="2"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveAgreementBtn">Save</button>
            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/orgchart@4.1.0/dist/css/jquery.orgchart.min.css">
<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
<script>
    const state = {
        selectedUser: null,
        tree: [],
    };

    async function searchUsers() {
        const query = document.getElementById('userSearch').value.trim();
        if (!query) return;
        const response = await fetch('<?= BASE_URL ?>admin/mlm-network/search?query=' + encodeURIComponent(query));
        const data = await response.json();
        if (data.success && data.data.length) {
            const user = data.data[0];
            state.selectedUser = user;
            document.getElementById('selectedUserLabel').textContent = `for ${user.name} (${user.email})`;
            document.getElementById('rebuildBtn').disabled = false;
            document.getElementById('viewProfileBtn').disabled = false;
            document.getElementById('notificationsBtn').disabled = false;
            document.getElementById('agreementUserId').value = user.id;
            loadTree();
            loadAgreements();
        } else {
            alert('No matching user found');
        }
    }

    async function loadTree() {
        if (!state.selectedUser) return;
        const depth = document.getElementById('depthSelect').value;
        const rank = document.getElementById('rankSelect').value;
        const response = await fetch(`<?= BASE_URL ?>admin/mlm-network/tree?user_id=${state.selectedUser.id}&depth=${depth}&rank=${encodeURIComponent(rank)}`);
        const data = await response.json();
        if (data.success) {
            state.tree = data.data;
            renderTree();
        }
    }

    function renderTree() {
        const container = document.getElementById('treeContainer');
        const placeholder = document.getElementById('treePlaceholder');
        if (!state.tree.length) {
            placeholder.style.display = 'block';
            container.style.display = 'none';
            placeholder.textContent = 'No network data for selected user.';
            return;
        }

        placeholder.style.display = 'none';
        container.style.display = 'block';

        const grouped = {};
        state.tree.forEach(node => {
            const level = node.level || 0;
            if (!grouped[level]) grouped[level] = [];
            grouped[level].push(node);
        });

        container.innerHTML = '';
        Object.keys(grouped).sort((a, b) => a - b).forEach(level => {
            const row = document.createElement('div');
            row.className = 'd-flex flex-wrap justify-content-center gap-3 mb-3';
            grouped[level].forEach(node => {
                const card = document.createElement('div');
                card.className = 'p-3 border rounded text-center';
                card.style.minWidth = '180px';
                card.style.boxShadow = '0 4px 12px rgba(0,0,0,0.08)';
                card.innerHTML = `
                    <strong>${node.name}</strong><br>
                    <small>${node.email ?? ''}</small><br>
                    <span class="badge mt-2" style="background:${node.rank_color ?? '#667eea'}">${node.rank_label ?? 'Associate'}</span>
                    <div class="mt-2">
                        <small>Level ${node.level ?? 0}</small>
                    </div>
                `;
                row.appendChild(card);
            });
            container.appendChild(row);
        });
    }

    async function loadAgreements() {
        if (!state.selectedUser) return;
        const response = await fetch(`<?= BASE_URL ?>admin/mlm-network/agreements?user_id=${state.selectedUser.id}`);
        const data = await response.json();
        const tbody = document.querySelector('#agreementTable tbody');
        tbody.innerHTML = '';

        if (!data.success || !data.data.length) {
            tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted">No agreements</td></tr>';
            return;
        }

        state.agreements = data.data;
        data.data.forEach(row => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${row.commission_rate ?? '-'}</td>
                <td>${row.flat_amount ?? '-'}</td>
                <td>${row.valid_from ?? '-'}<br><small class="text-muted">${row.valid_to ?? ''}</small></td>
                <td class="text-end">
                    <button class="btn btn-sm btn-link edit-agreement" data-id="${row.id}"><i class="fas fa-edit"></i></button>
                    <button class="btn btn-sm btn-link text-danger delete-agreement" data-id="${row.id}"><i class="fas fa-trash"></i></button>
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    document.getElementById('searchBtn').addEventListener('click', searchUsers);
    document.getElementById('depthSelect').addEventListener('change', loadTree);
    document.getElementById('rankSelect').addEventListener('change', loadTree);

    document.getElementById('newAgreementBtn').addEventListener('click', () => {
        if (!state.selectedUser) return;
        document.getElementById('agreementForm').reset();
        document.getElementById('agreementId').value = '';
        document.getElementById('agreementUserId').value = state.selectedUser.id;
        const modal = new bootstrap.Modal(document.getElementById('agreementModal'));
        modal.show();
    });

    document.getElementById('saveAgreementBtn').addEventListener('click', async () => {
        const form = document.getElementById('agreementForm');
        const formData = new FormData(form);
        const id = formData.get('id');
        const url = id ? '<?= BASE_URL ?>admin/mlm-network/agreements/update' : '<?= BASE_URL ?>admin/mlm-network/agreements/create';
        const response = await fetch(url, {
            method: 'POST',
            body: formData
        });
        const data = await response.json();
        if (data.success) {
            new bootstrap.Modal(document.getElementById('agreementModal')).hide();
            loadAgreements();
        } else {
            alert('Failed to save agreement');
        }
    });

    document.getElementById('exportCsv').addEventListener('click', () => exportTree('csv'));
    document.getElementById('exportPng').addEventListener('click', () => exportTree('png'));
    document.getElementById('rebuildBtn').addEventListener('click', () => triggerRebuild());
    document.getElementById('viewProfileBtn').addEventListener('click', () => {
        if (!state.selectedUser) return;
        window.open('<?= BASE_URL ?>admin/users?search=' + encodeURIComponent(state.selectedUser.email), '_blank');
    });
    document.getElementById('notificationsBtn').addEventListener('click', () => {
        if (!state.selectedUser) return;
        window.open('<?= BASE_URL ?>admin/logs?user=' + state.selectedUser.id, '_blank');
    });

    async function triggerRebuild() {
        if (!state.selectedUser) return;
        if (!confirm('Rebuild network tree for this associate?')) return;
        const response = await fetch('<?= BASE_URL ?>admin/mlm-network/rebuild', {
            method: 'POST',
            body: new URLSearchParams({
                user_id: state.selectedUser.id
            })
        });
        if (response.ok) {
            alert('Rebuild requested. Please refresh the tree.');
        } else {
            alert('Failed to request rebuild');
        }
    }

    function exportTree(format) {
        if (!state.tree.length) return;
        if (format === 'csv') {
            const rows = [
                ['Name', 'Email', 'Rank', 'Level']
            ];
            state.tree.forEach(node => {
                rows.push([node.name, node.email ?? '', node.rank_label ?? '', node.level ?? '']);
            });
            const csvContent = rows.map(row => row.map(value => '"' + String(value).replace('"', '""') + '"').join(',')).join('\n');
            const blob = new Blob([csvContent], {
                type: 'text/csv;charset=utf-8;'
            });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = `network_${state.selectedUser?.id ?? 'tree'}.csv`;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        } else if (format === 'png') {
            const container = document.getElementById('treeContainer');
            html2canvas(container).then(canvas => {
                const link = document.createElement('a');
                link.href = canvas.toDataURL('image/png');
                link.download = `network_${state.selectedUser?.id ?? 'tree'}.png`;
                link.click();
            });
        }
    }

    document.addEventListener('keydown', event => {
        if (event.key === 'Enter' && document.activeElement === document.getElementById('userSearch')) {
            event.preventDefault();
            searchUsers();
        }
    });

    document.querySelector('#agreementTable tbody').addEventListener('click', async (event) => {
        const editBtn = event.target.closest('.edit-agreement');
        const deleteBtn = event.target.closest('.delete-agreement');
        if (editBtn) {
            const id = editBtn.dataset.id;
            const agreement = (state.agreements || []).find(a => a.id == id) || null;
            if (!agreement) {
                loadAgreements();
                return;
            }
            document.getElementById('agreementId').value = agreement.id;
            document.querySelector('[name="property_id"]').value = agreement.property_id ?? '';
            document.querySelector('[name="commission_rate"]').value = agreement.commission_rate ?? '';
            document.querySelector('[name="flat_amount"]').value = agreement.flat_amount ?? '';
            document.querySelector('[name="valid_from"]').value = agreement.valid_from ?? '';
            document.querySelector('[name="valid_to"]').value = agreement.valid_to ?? '';
            document.querySelector('[name="notes"]').value = agreement.notes ?? '';
            document.getElementById('agreementUserId').value = agreement.user_id;
            new bootstrap.Modal(document.getElementById('agreementModal')).show();
        }
        if (deleteBtn) {
            const id = deleteBtn.dataset.id;
            if (confirm('Delete this agreement?')) {
                const formData = new FormData();
                formData.append('id', id);
                const response = await fetch('<?= BASE_URL ?>admin/mlm-network/agreements/delete', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();
                if (data.success) {
                    loadAgreements();
                } else {
                    alert('Failed to delete agreement');
                }
            }
        }
    });
</script>

<?php
require_once __DIR__ . '/../../includes/admin_footer.php';
?>
