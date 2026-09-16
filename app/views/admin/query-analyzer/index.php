<?php
/**
 * Query Analyzer Dashboard
 */
$base = defined('BASE_URL') ? BASE_URL : '/apsdreamhome';
?>
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Query Analyzer</h1>
        <div>
            <button class="btn btn-outline-primary me-2" onclick="refreshAll()">
                <i class="fas fa-sync me-1"></i> Refresh All
            </button>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalExplain">
                <i class="fas fa-search me-1"></i> Explain Query
            </button>
        </div>
    </div>

    <!-- Tab Navigation -->
    <ul class="nav nav-tabs mb-4" id="analyzerTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="tab-slow" data-bs-toggle="tab" data-bs-target="#slow-queries" type="button">
                <i class="fas fa-turtle me-1"></i> Slow Queries
            </button>
            <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-tables" data-bs-toggle="tab" data-bs-target="#table-stats" type="button">
                <i class="fas fa-table me-1"></i> Table Stats
            </button>
            <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-indexes" data-bs-toggle="tab" data-bs-target="#index-stats" type="button">
                <i class="fas fa-key me-1"></i> Index Analysis
            </button>
            <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-missing" data-bs-toggle="tab" data-bs-target="#missing-indexes" type="button">
                <i class="fas fa-exclamation-triangle me-1"></i> Missing Indexes
            </button>
            <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-processes" data-bs-toggle="tab" data-bs-target="#processes" type="button">
                <i class="fas fa-cogs me-1"></i> Live Processes
            </button>
        </ul>

    <div class="tab-content">
        <!-- Slow Queries -->
        <div class="tab-pane fade show active" id="slow-queries" role="tabpanel">
            <div class="card">
                <div class="card-header d-flex justify-content-between">
                    <h5 class="mb-0">Slow Queries (from Performance Schema)</h5>
                    <button class="btn btn-sm btn-outline-primary" onclick="loadSlowQueries()">
                        <i class="fas fa-sync me-1"></i> Refresh
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="tbl-slow-queries">
                            <thead>
                                <tr>
                                    <th>Query (truncated)</th>
                                    <th class="text-center">Avg Time (ms)</th>
                                    <th class="text-center">Max Time (ms)</th>
                                    <th class="text-center">Exec Count</th>
                                    <th class="text-center">Rows Sent</th>
                                    <th class="text-center">Rows Examined</th>
                                    <th class="text-center">DB</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id="slow-queries-body"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table Stats -->
        <div class="tab-pane fade" id="table-stats" role="tabpanel">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Table Statistics</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="tbl-table-stats">
                            <thead>
                                <tr>
                                    <th>Table</th>
                                    <th class="text-center">Rows</th>
                                    <th class="text-center">Data Size</th>
                                    <th class="text-center">Index Size</th>
                                    <th class="text-center">Total Size</th>
                                    <th class="text-center">Engine</th>
                                    <th class="text-center">Updated</th>
                                </tr>
                            </thead>
                            <tbody id="table-stats-body"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Index Stats -->
        <div class="tab-pane fade" id="index-stats" role="tabpanel">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Index Statistics</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="tbl-index-stats">
                            <thead>
                                <tr>
                                    <th>Table</th>
                                    <th>Index</th>
                                    <th class="text-center">Column</th>
                                    <th class="text-center">Seq</th>
                                    <th class="text-center">Unique</th>
                                    <th class="text-center">Type</th>
                                    <th class="text-center">Cardinality</th>
                                </tr>
                            </thead>
                            <tbody id="index-stats-body"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Missing Indexes -->
        <div class="tab-pane fade" id="missing-indexes" role="tabpanel">
            <div class="card">
                <div class="card-header d-flex justify-content-between">
                    <h5 class="mb-0">Missing Index Suggestions</h5>
                    <button class="btn btn-sm btn-primary" onclick="loadMissingIndexes()">
                        <i class="fas fa-search me-1"></i> Scan
                    </button>
                </div>
                <div class="card-body">
                    <div id="missing-indexes-content"></div>
                </div>
            </div>
        </div>

        <!-- Live Processes -->
        <div class="tab-pane fade" id="processes" role="tabpanel">
            <div class="card">
                <div class="card-header d-flex justify-content-between">
                    <h5 class="mb-0">Live Processes</h5>
                    <button class="btn btn-sm btn-outline-primary" onclick="loadProcesses()">
                        <i class="fas fa-sync me-1"></i> Refresh
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="tbl-processes">
                            <thead>
                                <tr>
                                    <th class="text-center">ID</th>
                                    <th>User</th>
                                    <th>Host</th>
                                    <th>DB</th>
                                    <th>Command</th>
                                    <th class="text-center">Time (s)</th>
                                    <th>State</th>
                                    <th>Info (truncated)</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id="processes-body"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Explain Query Modal -->
<div class="modal fade" id="modalExplain" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Explain Query</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">SQL Query</label>
                    <textarea class="form-control font-monospace" id="explainQuery" rows="6" placeholder="SELECT * FROM users WHERE email = 'test@example.com'"></textarea>
                </div>
                <button class="btn btn-primary" onclick="runExplain()">
                    <i class="fas fa-search me-1"></i> Explain
                </button>
            </div>
            <div class="modal-body" id="explainResult" style="display: none;">
                <h6>Explain Plan</h6>
                <div class="table-responsive">
                    <table class="table table-sm" id="explainTable">
                        <thead>
                            <tr>
                                <th>id</th><th>select_type</th><th>table</th><th>partitions</th>
                                <th>type</th><th>possible_keys</th><th>key</th><th>key_len</th>
                                <th>ref</th><th>rows</th><th>filtered</th><th>Extra</th>
                            </tr>
                        </thead>
                        <tbody id="explainBody"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const base = '<?php echo $base; ?>';
const csrf = '<?php echo $_SESSION['csrf_token'] ?? ''; ?>';

const headers = {
    'Content-Type': 'application/json',
    'X-CSRF-Token': csrf
};

async function fetchJson(url, options = {}) {
    const res = await fetch(base + url, {
        headers: { ...headers, ...options.headers },
        ...options
    });
    return res.json();
}

function formatBytes(bytes) {
    if (bytes >= 1073741824) return (bytes / 1073741824).toFixed(2) + ' GB';
    if (bytes >= 1048576) return (bytes / 1048576).toFixed(2) + ' MB';
    if (bytes >= 1024) return (bytes / 1024).toFixed(2) + ' KB';
    return bytes + ' B';
}

function truncate(str, len = 80) {
    if (!str) return '';
    return str.length > len ? str.substring(0, len) + '...' : str;
}

async function loadSlowQueries() {
    const data = await fetchJson('/api/query-analyzer/slow-queries');
    const tbody = document.getElementById('slow-queries-body');
    if (!data.success) { tbody.innerHTML = '<tr><td colspan="8" class="text-center text-danger">Failed to load</td></tr>'; return; }
    tbody.innerHTML = data.data.map(q => `
        <tr>
            <td class="font-monospace small">${escapeHtml(truncate(q.SQL_TEXT || q.sql_text || '', 100))}</td>
            <td class="text-center">${(parseFloat(q.AVG_TIMER_WAIT || q.avg_timer_wait || 0) / 1e6).toFixed(2)}</td>
            <td class="text-center">${(parseFloat(q.MAX_TIMER_WAIT || q.max_timer_wait || 0) / 1e6).toFixed(2)}</td>
            <td class="text-center">${q.EXEC_COUNT || q.exec_count || 0}</td>
            <td class="text-center">${q.ROWS_SENT || q.rows_sent || 0}</td>
            <td class="text-center">${q.ROWS_EXAMINED || q.rows_examined || 0}</td>
            <td>${q.DB || q.db || ''}</td>
            <td><button class="btn btn-sm btn-outline-primary" onclick="explainQuery('${escapeJs(q.SQL_TEXT || q.sql_text || '')}')"><i class="fas fa-search"></i></button></td>
        </tr>
    `).join('') || '<tr><td colspan="8" class="text-center text-muted">No slow queries found</td></tr>';
}

async function loadTableStats() {
    const data = await fetchJson('/api/query-analyzer/table-stats');
    const tbody = document.getElementById('table-stats-body');
    if (!data.success) { tbody.innerHTML = '<tr><td colspan="7" class="text-center text-danger">Failed to load</td></tr>'; return; }
    tbody.innerHTML = data.data.map(t => `
        <tr>
            <td><strong>${t.TABLE_NAME || t.table_name}</strong></td>
            <td class="text-center">${Number(t.TABLE_ROWS || t.table_rows || 0).toLocaleString()}</td>
            <td class="text-center">${formatBytes(t.DATA_LENGTH || t.data_length || 0)}</td>
            <td class="text-center">${formatBytes(t.INDEX_LENGTH || t.index_length || 0)}</td>
            <td class="text-center">${formatBytes((t.DATA_LENGTH || 0) + (t.INDEX_LENGTH || 0))}</td>
            <td class="text-center">${t.ENGINE || t.engine || ''}</td>
            <td class="text-center">${t.UPDATE_TIME || t.update_time || 'N/A'}</td>
        </tr>
    `).join('') || '<tr><td colspan="7" class="text-center text-muted">No tables</td></tr>';
}

async function loadIndexStats() {
    const data = await fetchJson('/api/query-analyzer/index-stats');
    const tbody = document.getElementById('index-stats-body');
    if (!data.success) { tbody.innerHTML = '<tr><td colspan="7" class="text-center text-danger">Failed to load</td></tr>'; return; }
    tbody.innerHTML = data.data.map(i => `
        <tr>
            <td>${i.TABLE_NAME || i.table_name}</td>
            <td>${i.INDEX_NAME || i.index_name}</td>
            <td class="text-center">${i.COLUMN_NAME || i.column_name}</td>
            <td class="text-center">${i.SEQ_IN_INDEX || i.seq_in_index}</td>
            <td class="text-center">${(i.NON_UNIQUE || i.non_unique) == 0 ? 'Yes' : 'No'}</td>
            <td class="text-center">${i.INDEX_TYPE || i.index_type || ''}</td>
            <td class="text-center">${Number(i.CARDINALITY || i.cardinality || 0).toLocaleString()}</td>
        </tr>
    `).join('') || '<tr><td colspan="7" class="text-center text-muted">No indexes</td></tr>';
}

async function loadMissingIndexes() {
    const data = await fetchJson('/api/query-analyzer/missing-indexes');
    const container = document.getElementById('missing-indexes-content');
    if (!data.success) { container.innerHTML = '<div class="alert alert-danger">Failed to load</div>'; return; }
    if (!data.data.length) {
        container.innerHTML = '<div class="alert alert-success"><i class="fas fa-check-circle me-2"></i>No missing indexes found!</div>';
        return;
    }
    container.innerHTML = data.data.map(s => `
        <div class="card mb-2 border-warning">
            <div class="card-body">
                <h6 class="mb-1"><i class="fas fa-exclamation-triangle text-warning me-2"></i>${s.message}</div>
                <code class="d-block bg-light p-2 small">${s.sql}</code>
                <button class="btn btn-sm btn-outline-primary mt-2" onclick="explainQuery('${escapeJs(s.sql)}')"><i class="fas fa-search me-1"></i> Explain</button>
            </div>
        </div>
    `).join('');
}

async function loadProcesses() {
    const data = await fetchJson('/api/query-analyzer/processes');
    const tbody = document.getElementById('processes-body');
    if (!data.success) { tbody.innerHTML = '<tr><td colspan="9" class="text-center text-danger">Failed to load</td></tr>'; return; }
    tbody.innerHTML = data.data.map(p => `
        <tr>
            <td class="text-center">${p.ID || p.id}</td>
            <td>${p.USER || p.user}</td>
            <td>${p.HOST || p.host}</td>
            <td>${p.DB || p.db || '-'}</td>
            <td><span class="badge bg-secondary">${p.COMMAND || p.command}</span></td>
            <td class="text-center">${p.TIME || p.time}</td>
            <td>${p.STATE || p.state || ''}</td>
            <td class="font-monospace small">${truncate(p.INFO || p.info || '', 80)}</td>
            <td>
                <button class="btn btn-sm btn-danger" onclick="killProcess(${p.ID || p.id})" title="Kill"><i class="fas fa-times"></i></button>
            </td>
        </tr>
    `).join('') || '<tr><td colspan="9" class="text-center text-muted">No active processes</td></tr>';
}

async function explainQuery(query) {
    if (!query) {
        const modalQuery = document.getElementById('explainQuery');
        query = modalQuery?.value;
        if (!query) return;
    }
    const data = await fetchJson('/api/query-analyzer/explain', {
        method: 'POST',
        body: JSON.stringify({ query })
    });
    if (!data.success) { alert('Explain failed: ' + data.error); return; }
    const tbody = document.getElementById('explainBody');
    tbody.innerHTML = data.data.map(r => `
        <tr>
            <td>${r.id}</td><td>${r.select_type}</td><td>${r.table}</td>
            <td>${r.partitions || ''}</td><td>${r.type}</td>
            <td>${r.possible_keys || ''}</td><td>${r.key || ''}</td>
            <td>${r.key_len || ''}</td><td>${r.ref || ''}</td>
            <td>${r.rows}</td><td>${r.filtered}</td><td>${r.Extra || ''}</td>
        </tr>
    `).join('');
    document.getElementById('explainResult').style.display = 'block';
    if (!document.getElementById('modalExplain').classList.contains('show')) {
        new bootstrap.Modal(document.getElementById('modalExplain')).show();
    }
}

async function runExplain() {
    const query = document.getElementById('explainQuery').value.trim();
    if (!query) return;
    explainQuery(query);
}

function refreshAll() {
    loadSlowQueries();
    loadTableStats();
    loadIndexStats();
    loadMissingIndexes();
    loadProcesses();
}

function escapeHtml(str) {
    return String(str).replace(/&/g, '&').replace(/</g, '<').replace(/>/g, '>').replace(/"/g, '"').replace(/'/g, '&#039;');
}

function escapeJs(str) {
    return String(str).replace(/\\/g, '\\\\').replace(/'/g, "\\'").replace(/"/g, '\\"').replace(/\n/g, '\\n').replace(/\r/g, '\\r');
}

function truncate(str, len = 80) {
    if (!str) return '';
    return str.length > len ? str.substring(0, len) + '...' : str;
}

// Initialize
document.addEventListener('DOMContentLoaded', () => {
    refreshAll();
});
</script>