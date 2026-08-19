<?php
$page_title = $page_title ?? 'Import Detail';
$page_heading = $page_heading ?? 'Import Detail';
$import = $import ?? [];
$summary = $summary ?? [];
$matched_txns = $matched_txns ?? [];
$unmatched_txns = $unmatched_txns ?? [];
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-file-csv me-2 text-primary"></i>
            Import #<?= (int)($import['id'] ?? 0) ?>
            <small class="text-muted fs-6">— <?= htmlspecialchars($import['original_filename'] ?? '') ?></small>
        </h2>
        <div class="d-flex gap-2">
            <a href="<?= BASE_URL ?>/admin/bank-import" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
            <?php if ((int)($summary['unmatched_count'] ?? 0) > 0): ?>
                <a href="<?= BASE_URL ?>/admin/bank-import/<?= (int)$import['id'] ?>/export" class="btn btn-outline-success"><i class="fas fa-download me-1"></i>Export Unmatched</a>
            <?php endif; ?>
            <?php if ((int)($summary['unmatched_count'] ?? 0) > 0): ?>
                <form method="post" action="<?= BASE_URL ?>/admin/bank-import/<?= (int)$import['id'] ?>/match" class="d-inline">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                    <button type="submit" class="btn btn-primary" onclick="return confirm('Auto-match all eligible transactions?')"><i class="fas fa-magic me-1"></i>Auto-Match</button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <!-- Flash messages -->
    <?php if (!empty($_SESSION['flash_success'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle me-1"></i><?= htmlspecialchars($_SESSION['flash_success'] ?? '') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['flash_success']); ?>
    <?php endif; ?>
    <?php if (!empty($_SESSION['flash_error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle me-1"></i><?= htmlspecialchars($_SESSION['flash_error'] ?? '') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['flash_error']); ?>
    <?php endif; ?>

    <!-- Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="aps-cp-card h-100">
                <div class="aps-cp-card-body">
                    <div class="text-muted small mb-1">Total Transactions</div>
                    <div class="fs-3 fw-bold"><?= number_format((int)($summary['total_transactions'] ?? 0)) ?></div>
                    <div class="small text-muted">Credits: ₹<?= number_format((float)($summary['total_credits'] ?? 0), 2) ?></div>
                    <div class="small text-muted">Debits: ₹<?= number_format((float)($summary['total_debits'] ?? 0), 2) ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="aps-cp-card h-100">
                <div class="aps-cp-card-body">
                    <div class="text-muted small mb-1">Matched</div>
                    <div class="fs-3 fw-bold text-success"><?= number_format((int)($summary['matched_count'] ?? 0)) ?></div>
                    <div class="small text-muted">Amount: ₹<?= number_format((float)($summary['matched_amount'] ?? 0), 2) ?></div>
                    <div class="progress mt-1" class="style-51910">
                        <div class="progress-bar bg-success" class="style-70324"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="aps-cp-card h-100">
                <div class="aps-cp-card-body">
                    <div class="text-muted small mb-1">Unmatched</div>
                    <div class="fs-3 fw-bold text-warning"><?= number_format((int)($summary['unmatched_count'] ?? 0)) ?></div>
                    <div class="small text-muted">Amount: ₹<?= number_format((float)($summary['unmatched_amount'] ?? 0), 2) ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="aps-cp-card h-100">
                <div class="aps-cp-card-body">
                    <div class="text-muted small mb-1">Match Rate</div>
                    <div class="fs-3 fw-bold text-primary"><?= (float)($summary['match_rate'] ?? 0) ?>%</div>
                    <div class="progress mt-1" class="style-51910">
                        <div class="progress-bar bg-primary" class="style-70324"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <ul class="nav nav-tabs mb-3" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" data-bs-toggle="tab" href="#unmatchedTab">
                <i class="fas fa-exclamation-circle text-warning me-1"></i>Unmatched
                <span class="badge bg-warning text-dark ms-1"><?= count($unmatched_txns) ?></span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#matchedTab">
                <i class="fas fa-check-circle text-success me-1"></i>Matched
                <span class="badge bg-success ms-1"><?= count($matched_txns) ?></span>
            </a>
        </li>
    </ul>

    <div class="tab-content">
        <!-- Unmatched Tab -->
        <div class="tab-pane fade show active" id="unmatchedTab">
            <?php if (empty($unmatched_txns)): ?>
                <div class="aps-cp-card">
                    <div class="aps-cp-card-body text-center py-5">
                        <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                        <h5>All transactions matched!</h5>
                        <p class="text-muted">No unmatched transactions remain in this import.</p>
                    </div>
                </div>
            <?php else: ?>
                <div class="aps-cp-card">
                    <div class="aps-cp-card-header">
                        <span><i class="fas fa-list me-1"></i> Unmatched Transactions</span>
                    </div>
                    <div class="aps-cp-card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Date</th>
                                        <th>Description</th>
                                        <th class="text-end">Debit (₹)</th>
                                        <th class="text-end">Credit (₹)</th>
                                        <th class="text-end">Balance (₹)</th>
                                        <th>Cheque/Ref</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($unmatched_txns as $txn): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($txn['transaction_date'] ?? '') ?></td>
                                        <td><?= htmlspecialchars(mb_substr($txn['description'] ?? '', 0, 60)) ?></td>
                                        <td class="text-end"><?= (float)($txn['debit'] ?? 0) > 0 ? '₹' . number_format((float)$txn['debit'], 2) : '—' ?></td>
                                        <td class="text-end"><?= (float)($txn['credit'] ?? 0) > 0 ? '₹' . number_format((float)$txn['credit'], 2) : '—' ?></td>
                                        <td class="text-end">₹<?= number_format((float)($txn['balance'] ?? 0), 2) ?></td>
                                        <td class="small text-muted"><?= htmlspecialchars($txn['cheque_number'] ?? $txn['reference_number'] ?? '') ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#matchModal"
                                                data-txn-id="<?= (int)$txn['id'] ?>"
                                                data-txn-date="<?= htmlspecialchars($txn['transaction_date'] ?? '') ?>"
                                                data-txn-desc="<?= htmlspecialchars($txn['description'] ?? '') ?>"
                                                data-txn-amount="<?= number_format(max((float)$txn['debit'], (float)$txn['credit']), 2, '.', '') ?>"
                                                data-txn-amount-display="<?= number_format(max((float)$txn['debit'], (float)$txn['credit']), 2) ?>">
                                                <i class="fas fa-link me-1"></i>Match
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Matched Tab -->
        <div class="tab-pane fade" id="matchedTab">
            <?php if (empty($matched_txns)): ?>
                <div class="aps-cp-card">
                    <div class="aps-cp-card-body text-center py-5">
                        <i class="fas fa-info-circle fa-3x text-muted mb-3"></i>
                        <h5>No matched transactions yet</h5>
                        <p class="text-muted">Run auto-match or manually match transactions from the Unmatched tab.</p>
                    </div>
                </div>
            <?php else: ?>
                <div class="aps-cp-card">
                    <div class="aps-cp-card-header">
                        <span><i class="fas fa-check-double me-1"></i> Matched Transactions</span>
                    </div>
                    <div class="aps-cp-card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Date</th>
                                        <th>Description</th>
                                        <th class="text-end">Debit (₹)</th>
                                        <th class="text-end">Credit (₹)</th>
                                        <th class="text-end">Balance (₹)</th>
                                        <th>Cheque/Ref</th>
                                        <th>Matched At</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($matched_txns as $txn): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($txn['transaction_date'] ?? '') ?></td>
                                        <td><?= htmlspecialchars(mb_substr($txn['description'] ?? '', 0, 60)) ?></td>
                                        <td class="text-end"><?= (float)($txn['debit'] ?? 0) > 0 ? '₹' . number_format((float)$txn['debit'], 2) : '—' ?></td>
                                        <td class="text-end"><?= (float)($txn['credit'] ?? 0) > 0 ? '₹' . number_format((float)$txn['credit'], 2) : '—' ?></td>
                                        <td class="text-end">₹<?= number_format((float)($txn['balance'] ?? 0), 2) ?></td>
                                        <td class="small text-muted"><?= htmlspecialchars($txn['cheque_number'] ?? $txn['reference_number'] ?? '') ?></td>
                                        <td class="small text-muted"><?= htmlspecialchars($txn['matched_at'] ?? '') ?></td>
                                        <td>
                                            <form method="post" action="<?= BASE_URL ?>/admin/bank-import/unmatch/<?= (int)$txn['id'] ?>" class="d-inline">
                                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-warning" title="Unmatch" onclick="return confirm('Remove this match?')" aria-label="Unlink"><i class="fas fa-unlink"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Manual Match Modal -->
<div class="modal fade" id="matchModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="post" action="<?= BASE_URL ?>/admin/bank-import/manual-match">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                <input type="hidden" name="bank_txn_id" id="matchBankTxnId">

                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-link me-1"></i>Match Transaction</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <!-- Bank transaction info -->
                    <div class="alert alert-info mb-3">
                        <div class="row">
                            <div class="col-md-4"><strong>Date:</strong> <span id="matchDate"></span></div>
                            <div class="col-md-5"><strong>Description:</strong> <span id="matchDesc"></span></div>
                            <div class="col-md-3"><strong>Amount:</strong> ₹<span id="matchAmount"></span></div>
                        </div>
                    </div>

                    <!-- Search internal transactions -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Search Internal Transactions</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                            <input type="text" class="form-control" id="internalSearch" placeholder="Search by description or type..." autocomplete="off">
                            <button type="button" class="btn btn-outline-primary" id="searchBtn">Search</button>
                        </div>
                        <div class="form-text">Searches payment_transactions and daily_cash_book for matching amounts.</div>
                    </div>

                    <div id="searchResults" class="d-none">
                        <div class="table-responsive" class="style-52319">
                            <table class="table table-sm table-hover">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th></th>
                                        <th>Date</th>
                                        <th>Type</th>
                                        <th>Description</th>
                                        <th class="text-end">Amount (₹)</th>
                                    </tr>
                                </thead>
                                <tbody id="internalResultsBody">
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div id="noResults" class="text-center text-muted py-3 d-none">
                        <i class="fas fa-search fa-2x mb-2"></i>
                        <p>No matching internal transactions found. Try adjusting your search.</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="matchSubmitBtn" disabled>
                        <i class="fas fa-link me-1"></i>Match Selected
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
(function() {
    var searchTimeout;
    var selectedInternalId = null;

    // Match modal - set data
    var matchModal = document.getElementById('matchModal');
    matchModal.addEventListener('show.bs.modal', function(event) {
        var btn = event.relatedTarget;
        document.getElementById('matchBankTxnId').value = btn.getAttribute('data-txn-id');
        document.getElementById('matchDate').textContent = btn.getAttribute('data-txn-date');
        document.getElementById('matchDesc').textContent = btn.getAttribute('data-txn-desc');
        document.getElementById('matchAmount').textContent = btn.getAttribute('data-txn-amount-display');
        document.getElementById('internalSearch').value = '';
        document.getElementById('searchResults').classList.add('d-none');
        document.getElementById('noResults').classList.add('d-none');
        document.getElementById('matchSubmitBtn').disabled = true;
        selectedInternalId = null;

        // Auto-search with the amount
        var amount = btn.getAttribute('data-txn-amount');
        if (amount > 0) {
            searchInternal(amount);
        }
    });

    document.getElementById('searchBtn').addEventListener('click', function() {
        searchInternal(0);
    });

    document.getElementById('internalSearch').addEventListener('keyup', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() { searchInternal(0); }, 400);
    });

    function searchInternal(amount) {
        var q = document.getElementById('internalSearch').value;
        var url = '<?= BASE_URL ?>/admin/bank-import/search-internal?q=' + encodeURIComponent(q);
        if (amount > 0) url += '&amount=' + amount;

        fetch(url, { headers: { 'Accept': 'application/json' } })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                var results = data.data || [];
                var tbody = document.getElementById('internalResultsBody');
                .catch(err => console.error('Request failed:', err));
                tbody.innerHTML = '';

                if (results.length === 0) {
                    document.getElementById('searchResults').classList.add('d-none');
                    document.getElementById('noResults').classList.remove('d-none');
                    return;
                }

                document.getElementById('searchResults').classList.remove('d-none');
                document.getElementById('noResults').classList.add('d-none');

                results.forEach(function(row) {
                    var tr = document.createElement('tr');
                    tr.style.cursor = 'pointer';
                    tr.innerHTML = '<td><input type="radio" name="internal_txn_id" value="' + row.id + '" class="form-check-input"></td>' +
                        '<td>' + (row.transaction_date || '') + '</td>' +
                        '<td>' + (row.type || '') + '</td>' +
                        '<td>' + (row.description || '').substring(0, 50) + '</td>' +
                        '<td class="text-end">₹' + parseFloat(row.amount || 0).toLocaleString('en-IN', {minimumFractionDigits: 2}) + '</td>';

                    tr.addEventListener('click', function() {
                        tr.querySelector('input[type="radio"]').checked = true;
                        selectedInternalId = row.id;
                        document.getElementById('matchSubmitBtn').disabled = false;
                    });

                    tbody.appendChild(tr);
                });
            })
            .catch(function() {
                document.getElementById('searchResults').classList.add('d-none');
                document.getElementById('noResults').classList.remove('d-none');
            });
    }
})();
</script>
