<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/flatpickr.min.css" integrity="sha512-Dxr7n0ANKPO/tUMGAfJOyrUo9qeycGQ21MCH2RKDWEUtNdz/BPZt6r9Ga6IpiObOqYkbKx2+Y8Oob+ST3VkOSA==" crossorigin="anonymous" referrerpolicy="no-referrer" />

<div class="container-fluid py-4" id="engagementDashboard">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">MLM Engagement Dashboard</h1>
            <p class="text-muted mb-0">Track associate performance, leaderboards, goal progress, and engagement signals.</p>
        </div>
        <div>
            <button class="btn btn-outline-secondary" id="refreshBtn"><i class="fas fa-rotate me-2"></i>Refresh</button>
        </div>
    </div>

    <!-- Goal Create/Edit Modal -->
    <div class="modal fade" id="goalModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="goalModalTitle">Create Goal</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="goalForm">
                    <div class="modal-body">
                        <input type="hidden" name="goal_id" id="goalIdField">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label" for="goalType">Goal Type</label>
                                <select class="form-select" name="goal_type" id="goalType" required>
                                    <option value="sales">Sales</option>
                                    <option value="recruits">Recruits</option>
                                    <option value="commission">Commission</option>
                                    <option value="custom">Custom</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="goalScope">Scope</label>
                                <select class="form-select" name="scope" id="goalScope" required>
                                    <option value="individual">Individual</option>
                                    <option value="team">Team</option>
                                </select>
                            </div>
                            <div class="col-md-4" id="goalUserGroup">
                                <label class="form-label" for="goalUserId">Associate ID</label>
                                <input type="number" min="1" class="form-control" name="user_id" id="goalUserId" placeholder="User ID">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="goalTargetValue">Target Value</label>
                                <input type="number" step="0.01" min="0" class="form-control" name="target_value" id="goalTargetValue" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="goalTargetUnits">Units</label>
                                <input type="text" class="form-control" name="target_units" id="goalTargetUnits" placeholder="e.g. INR, recruits">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="goalStatus">Status</label>
                                <select class="form-select" name="status" id="goalStatus">
                                    <option value="active">Active</option>
                                    <option value="draft">Draft</option>
                                    <option value="completed">Completed</option>
                                    <option value="expired">Expired</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="goalStartDate">Start Date</label>
                                <input type="text" class="form-control" name="start_date" id="goalStartDate" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="goalEndDate">End Date</label>
                                <input type="text" class="form-control" name="end_date" id="goalEndDate" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="goalSubmitBtn">Save Goal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Goal Progress Modal -->
    <div class="modal fade" id="goalProgressModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Log Goal Progress</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="goalProgressForm">
                    <div class="modal-body">
                        <input type="hidden" name="goal_id" id="progressGoalId">
                        <div class="mb-3">
                            <label class="form-label" for="progressDate">Checkpoint Date</label>
                            <input type="date" class="form-control" id="progressDate" name="checkpoint_date" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="progressActual">Actual Value</label>
                            <input type="number" step="0.01" min="0" class="form-control" id="progressActual" name="actual_value" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="progressPercentage">Completion % (optional)</label>
                            <input type="number" step="0.01" min="0" max="100" class="form-control" id="progressPercentage" name="percentage_complete" placeholder="Automatically calculated if empty">
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="progressNotes">Notes</label>
                            <textarea class="form-control" id="progressNotes" name="notes" rows="3" placeholder="What changed?"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success" id="progressSubmitBtn">Save Progress</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form id="filtersForm" class="row g-3 align-items-end">
                <div class="col-md-2">
                    <label class="form-label" for="metricsFrom">Metrics From</label>
                    <input type="text" class="form-control" id="metricsFrom" name="metrics_from" value="<?php echo date('Y-m-01'); ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label" for="metricsTo">Metrics To</label>
                    <input type="text" class="form-control" id="metricsTo" name="metrics_to" value="<?php echo date('Y-m-d'); ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label" for="rankLabel">Rank Label</label>
                    <input type="text" class="form-control" id="rankLabel" placeholder="e.g. Sr. Associate">
                </div>
                <div class="col-md-2">
                    <label class="form-label" for="leaderboardMetric">Leaderboard Metric</label>
                    <select class="form-select" id="leaderboardMetric">
                        <option value="sales_monthly" selected>Monthly Sales</option>
                        <option value="recruits_weekly">Weekly Recruits</option>
                        <option value="commission_monthly">Monthly Commission</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label" for="notificationsUser">Notifications User ID</label>
                    <input type="number" class="form-control" id="notificationsUser" min="1" placeholder="Associate ID">
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-fill"><i class="fas fa-filter me-1"></i>Apply</button>
                    <button type="button" class="btn btn-outline-secondary" id="resetFilters">Reset</button>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-4" id="metricCards">
        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="text-muted text-uppercase small">Total Sales</div>
                    <div class="display-6 fw-semibold" id="cardSales">₹0</div>
                    <div class="text-muted small">Across filtered period</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="text-muted text-uppercase small">Total Commission</div>
                    <div class="display-6 fw-semibold" id="cardCommission">₹0</div>
                    <div class="text-muted small">Approved + pending</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="text-muted text-uppercase small">New Recruits</div>
                    <div class="display-6 fw-semibold" id="cardRecruits">0</div>
                    <div class="text-muted small">Associates added</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="text-muted text-uppercase small">Active Team Members</div>
                    <div class="display-6 fw-semibold" id="cardTeam">0</div>
                    <div class="text-muted small">Median per associate</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mt-1">
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Leaderboard</h5>
                    <small class="text-muted" id="leaderboardMeta">No snapshot</small>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="leaderboardTable">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Associate</th>
                                    <th class="text-end">Metric</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Active Goals</h5>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-primary" id="goalsCount">0 goals</span>
                        <div class="btn-group">
                            <button class="btn btn-sm btn-outline-primary" id="openCreateGoal">
                                <i class="fas fa-plus"></i> New Goal
                            </button>
                            <button class="btn btn-sm btn-outline-secondary" id="openEditGoal" disabled>
                                <i class="fas fa-edit"></i> Edit Goal
                            </button>
                            <button class="btn btn-sm btn-outline-success" id="openProgressModal" disabled>
                                <i class="fas fa-tasks"></i> Log Progress
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped mb-0" id="goalsTable">
                            <thead class="table-light">
                                <tr>
                                    <th>Goal</th>
                                    <th>Owner</th>
                                    <th>Progress</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mt-1">
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Goal Timeline</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info mb-0" id="goalTimelineEmpty">Select a goal to view progress.</div>
                    <ul class="list-group list-group-flush d-none" id="goalTimelineList"></ul>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Notification Feed</h5>
                    <div class="d-flex align-items-center gap-2">
                        <small class="text-muted" id="notifMeta">Awaiting user ID</small>
                        <button class="btn btn-sm btn-outline-primary" id="markAllNotifications" disabled>
                            <i class="fas fa-check-double"></i> Mark All Read
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="alert alert-secondary" id="notifEmpty">Enter an associate ID to view recent notifications.</div>
                    <ul class="list-group list-group-flush d-none" id="notifList"></ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/flatpickr.min.js" integrity="sha512-OMoNlsLwDyZaG0/1q/sEem2sr7WzMwP2KVd8UQ0BXpDE2NZkJqcMl3DB3diEFyPZ8s9tfwGBrnrZ0H/Tyuod3g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>
    (function() {
        const form = document.getElementById('filtersForm');
        const refreshBtn = document.getElementById('refreshBtn');
        const resetBtn = document.getElementById('resetFilters');
        const metricsFrom = document.getElementById('metricsFrom');
        const metricsTo = document.getElementById('metricsTo');
        const rankLabel = document.getElementById('rankLabel');
        const leaderboardMetric = document.getElementById('leaderboardMetric');
        const notificationsUser = document.getElementById('notificationsUser');
        const goalsTable = document.getElementById('goalsTable').querySelector('tbody');
        const goalTimelineList = document.getElementById('goalTimelineList');
        const goalTimelineEmpty = document.getElementById('goalTimelineEmpty');
        const leaderboardTable = document.getElementById('leaderboardTable').querySelector('tbody');
        const notifList = document.getElementById('notifList');
        const notifEmpty = document.getElementById('notifEmpty');
        const notifMeta = document.getElementById('notifMeta');
        const markAllNotificationsBtn = document.getElementById('markAllNotifications');
        const leaderboardMeta = document.getElementById('leaderboardMeta');
        const goalsCount = document.getElementById('goalsCount');
        const openCreateGoalBtn = document.getElementById('openCreateGoal');
        const goalModalEl = document.getElementById('goalModal');
        const goalModal = goalModalEl ? new bootstrap.Modal(goalModalEl) : null;
        const goalForm = document.getElementById('goalForm');
        const goalSubmitBtn = document.getElementById('goalSubmitBtn');
        const goalIdField = document.getElementById('goalIdField');
        const goalTypeField = document.getElementById('goalType');
        const goalUserIdField = document.getElementById('goalUserId');
        const goalTargetValueField = document.getElementById('goalTargetValue');
        const goalTargetUnitsField = document.getElementById('goalTargetUnits');
        const goalStatusField = document.getElementById('goalStatus');
        const goalStartDateField = document.getElementById('goalStartDate');
        const goalEndDateField = document.getElementById('goalEndDate');
        const openEditGoalBtn = document.getElementById('openEditGoal');
        const openProgressModalBtn = document.getElementById('openProgressModal');
        const goalProgressModalEl = document.getElementById('goalProgressModal');
        const goalProgressModal = goalProgressModalEl ? new bootstrap.Modal(goalProgressModalEl) : null;
        const goalProgressForm = document.getElementById('goalProgressForm');
        const progressGoalIdField = document.getElementById('progressGoalId');
        const progressSubmitBtn = document.getElementById('progressSubmitBtn');
        const progressDateField = document.getElementById('progressDate');
        const progressActualField = document.getElementById('progressActual');
        const progressPercentField = document.getElementById('progressPercentage');
        const progressNotesField = document.getElementById('progressNotes');
        const goalModalTitle = document.getElementById('goalModalTitle');
        const goalTableSelectedClass = 'table-primary';
        let selectedGoal = null;
        let cachedGoals = [];
        const goalScopedUserGroup = document.getElementById('goalUserGroup');
        const goalScopeField = document.getElementById('goalScope');

        flatpickr([metricsFrom, metricsTo], {
            dateFormat: 'Y-m-d'
        });
        if (goalForm) {
            flatpickr('#goalStartDate', {
                dateFormat: 'Y-m-d'
            });
            flatpickr('#goalEndDate', {
                dateFormat: 'Y-m-d'
            });
        }

        form.addEventListener('submit', function(e) {
            e.preventDefault();
            fetchAll();
        });

        refreshBtn.addEventListener('click', fetchAll);

        if (markAllNotificationsBtn) {
            markAllNotificationsBtn.addEventListener('click', function() {
                const userId = parseInt(notificationsUser.value, 10);
                if (!userId || markAllNotificationsBtn.disabled) {
                    return;
                }
                markAllNotificationsBtn.disabled = true;
                markAllNotificationsBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Marking...';
                markAllNotifications(userId)
                    .then(() => loadNotifications())
                    .catch(err => {
                        console.error(err);
                        alert(err.message || 'Failed to mark all notifications read');
                    })
                    .finally(() => {
                        markAllNotificationsBtn.innerHTML = '<i class="fas fa-check-double"></i> Mark All Read';
                    });
            });
        }

        resetBtn.addEventListener('click', () => {
            metricsFrom.value = '<?php echo date('Y-m-01'); ?>';
            metricsTo.value = '<?php echo date('Y-m-d'); ?>';
            rankLabel.value = '';
            leaderboardMetric.value = 'sales_monthly';
            notificationsUser.value = '';
            fetchAll();
        });

        goalsTable.addEventListener('click', function(e) {
            const button = e.target.closest('[data-goal-id]');
            if (!button) return;
            const goalId = parseInt(button.dataset.goalId, 10);
            const action = button.dataset.action;

            if (action === 'details') {
                selectGoalRow(button.closest('tr'), goalId);
                loadGoalDetails(goalId);
            } else if (action === 'complete' || action === 'cancel') {
                const status = action === 'complete' ? 'completed' : 'cancelled';
                if (confirm(`Mark goal #${goalId} as ${status}?`)) {
                    updateGoalStatus(goalId, status);
                }
            }
        });

        if (openCreateGoalBtn && goalModal) {
            openCreateGoalBtn.addEventListener('click', () => {
                selectedGoal = null;
                goalForm.reset();
                goalIdField.value = '';
                goalModalTitle.innerText = 'Create Goal';
                goalScopeField.value = 'individual';
                handleScopeChange();
                goalStatusField.value = 'active';
                goalModal.show();
            });
        }

        if (openEditGoalBtn && goalModal) {
            openEditGoalBtn.addEventListener('click', () => {
                if (!selectedGoal) return;
                populateGoalForm(selectedGoal);
                goalModalTitle.innerText = `Edit Goal #${selectedGoal.id}`;
                goalModal.show();
            });
        }

        if (openProgressModalBtn && goalProgressModal) {
            openProgressModalBtn.addEventListener('click', () => {
                if (!selectedGoal) return;
                goalProgressForm.reset();
                progressGoalIdField.value = selectedGoal.id;
                progressDateField.value = new Date().toISOString().slice(0, 10);
                goalProgressModal.show();
            });
        }

        if (goalModal) {
            goalScopeField.addEventListener('change', handleScopeChange);

            goalForm.addEventListener('submit', function(e) {
                e.preventDefault();
                saveGoal();
            });
        }

        if (goalProgressModal) {
            goalProgressForm.addEventListener('submit', function(e) {
                e.preventDefault();
                recordProgress();
            });
        }

        function fetchAll() {
            loadMetrics();
            loadLeaderboard();
            loadGoals();
            loadNotifications();
        }

        function handleScopeChange() {
            if (goalScopeField.value === 'individual') {
                goalScopedUserGroup.classList.remove('d-none');
            } else {
                goalScopedUserGroup.classList.add('d-none');
            }
        }

        function loadMetrics() {
            const params = new URLSearchParams();
            if (metricsFrom.value) params.set('from', metricsFrom.value);
            if (metricsTo.value) params.set('to', metricsTo.value);
            if (rankLabel.value) params.set('rank_label', rankLabel.value);
            params.set('limit', '200');

            fetch('<?php echo BASE_URL; ?>admin/mlm-engagement/metrics?' + params.toString())
                .then(r => r.json())
                .then(({
                    success,
                    records
                }) => {
                    if (!success) throw new Error('Failed to load metrics');
                    if (!records || records.length === 0) {
                        updateMetricCards({
                            sales: 0,
                            commission: 0,
                            recruits: 0,
                            team: 0
                        });
                        return;
                    }

                    let sales = 0,
                        commission = 0,
                        recruits = 0,
                        teamSum = 0;
                    records.forEach(row => {
                        sales += parseFloat(row.sales_amount || 0);
                        commission += parseFloat(row.commissions_amount || 0);
                        recruits += parseInt(row.recruits_count || 0, 10);
                        teamSum += parseInt(row.active_team_count || 0, 10);
                    });
                    const team = teamSum / records.length;
                    updateMetricCards({
                        sales,
                        commission,
                        recruits,
                        team
                    });
                })
                .catch(err => {
                    console.error(err);
                    updateMetricCards({
                        sales: 0,
                        commission: 0,
                        recruits: 0,
                        team: 0
                    });
                });
        }

        function updateMetricCards({
            sales,
            commission,
            recruits,
            team
        }) {
            document.getElementById('cardSales').innerText = formatCurrency(sales);
            document.getElementById('cardCommission').innerText = formatCurrency(commission);
            document.getElementById('cardRecruits').innerText = recruits.toLocaleString('en-IN');
            document.getElementById('cardTeam').innerText = team.toFixed(1);
        }

        function loadLeaderboard() {
            const params = new URLSearchParams();
            params.set('metric_type', leaderboardMetric.value);
            params.set('limit', '20');

            fetch('<?php echo BASE_URL; ?>admin/mlm-engagement/leaderboard?' + params.toString())
                .then(r => r.json())
                .then(({
                    success,
                    data
                }) => {
                    if (!success) throw new Error('Failed to load leaderboard');
                    leaderboardTable.innerHTML = '';
                    const records = data.records || [];
                    if (records.length === 0) {
                        leaderboardMeta.innerText = 'No snapshot available';
                        leaderboardTable.innerHTML = '<tr><td colspan="3" class="text-center text-muted py-3">No leaderboard data</td></tr>';
                        return;
                    }
                    leaderboardMeta.innerText = 'Snapshot: ' + data.snapshot_date;
                    records.forEach(row => {
                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                        <td>${row.rank_position}</td>
                        <td>
                            <div class="fw-semibold">${escapeHtml(row.user_name || 'Associate')}</div>
                            <div class="text-muted small">${escapeHtml(row.user_email || '')}</div>
                        </td>
                        <td class="text-end">${Number(row.metric_value || 0).toLocaleString('en-IN')}</td>
                    `;
                        leaderboardTable.appendChild(tr);
                    });
                })
                .catch(err => {
                    console.error(err);
                    leaderboardMeta.innerText = 'Error loading leaderboard';
                    leaderboardTable.innerHTML = '<tr><td colspan="3" class="text-center text-danger py-3">Failed to load leaderboard</td></tr>';
                });
        }

        function loadGoals() {
            const params = new URLSearchParams();
            params.set('status', 'active');
            params.set('limit', '100');

            const previouslySelectedId = selectedGoal ? selectedGoal.id : null;
            toggleGoalActions(false);

            fetch('<?php echo BASE_URL; ?>admin/mlm-engagement/goals?' + params.toString())
                .then(r => r.json())
                .then(({
                    success,
                    records
                }) => {
                    if (!success) throw new Error('Failed to load goals');
                    goalsTable.innerHTML = '';
                    cachedGoals = (records || []).map(goal => {
                        const id = Number(goal.id);
                        return {
                            ...goal,
                            id,
                            target_value: goal.target_value !== null ? Number(goal.target_value) : 0,
                            percentage_complete: goal.percentage_complete !== undefined && goal.percentage_complete !== null ?
                                Number(goal.percentage_complete) : null,
                        };
                    });

                    if (cachedGoals.length === 0) {
                        selectedGoal = null;
                        goalsCount.innerText = '0 goals';
                        goalsTable.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-3">No active goals</td></tr>';
                        return;
                    }

                    goalsCount.innerText = cachedGoals.length + ' goals';
                    cachedGoals.forEach(goal => {
                        const progress = calculateGoalProgress(goal);
                        const tr = document.createElement('tr');
                        tr.dataset.goalRow = goal.id;
                        tr.innerHTML = `
                        <td>
                            <div class="fw-semibold text-capitalize">${escapeHtml(goal.goal_type)}</div>
                            <div class="text-muted small">${goal.start_date} → ${goal.end_date}</div>
                        </td>
                        <td>
                            <div class="fw-semibold">${escapeHtml(goal.owner_name || 'Team')}</div>
                            <div class="text-muted small">${goal.scope}</div>
                        </td>
                        <td>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar" role="progressbar" style="width: ${progress}%"></div>
                            </div>
                            <div class="text-muted small">${progress.toFixed(1)}% of ${formatUnits(goal.target_value, goal.target_units)}</div>
                        </td>
                        <td>
                            <span class="badge bg-${statusColor(goal.status)} text-uppercase">${goal.status}</span>
                            <div class="btn-group btn-group-sm" role="group">
                                <button class="btn btn-outline-secondary" data-goal-id="${goal.id}" data-action="details">Details</button>
                                <button class="btn btn-outline-success" data-goal-id="${goal.id}" data-action="complete">Complete</button>
                                <button class="btn btn-outline-danger" data-goal-id="${goal.id}" data-action="cancel">Cancel</button>
                            </div>
                        </td>
                    `;
                        goalsTable.appendChild(tr);
                    });

                    if (previouslySelectedId) {
                        const restoredGoal = cachedGoals.find(goal => goal.id === previouslySelectedId);
                        if (restoredGoal) {
                            const row = goalsTable.querySelector(`[data-goal-row="${restoredGoal.id}"]`);
                            selectGoalRow(row, restoredGoal.id);
                            loadGoalDetails(restoredGoal.id);
                        } else {
                            selectedGoal = null;
                            toggleGoalActions(false);
                            goalTimelineEmpty.classList.remove('d-none');
                            goalTimelineList.classList.add('d-none');
                            goalTimelineEmpty.innerText = 'Select a goal to view progress.';
                        }
                    }
                })
                .catch(err => {
                    console.error(err);
                    goalsCount.innerText = '0 goals';
                    goalsTable.innerHTML = '<tr><td colspan="4" class="text-center text-danger py-3">Failed to load goals</td></tr>';
                });
        }

        function loadGoalDetails(goalId) {
            const params = new URLSearchParams();
            params.set('goal_id', goalId);

            fetch('<?php echo BASE_URL; ?>admin/mlm-engagement/goal-details?' + params.toString())
                .then(r => r.json())
                .then(({
                    success,
                    data
                }) => {
                    if (!success) throw new Error('Failed to load goal details');

                    const progress = data.progress || [];
                    const events = data.events || [];

                    goalTimelineList.innerHTML = '';
                    if (progress.length === 0 && events.length === 0) {
                        goalTimelineEmpty.classList.remove('d-none');
                        goalTimelineList.classList.add('d-none');
                        goalTimelineEmpty.innerText = 'No progress or events recorded yet.';
                        return;
                    }

                    goalTimelineEmpty.classList.add('d-none');
                    goalTimelineList.classList.remove('d-none');

                    progress.forEach(item => {
                        const li = document.createElement('li');
                        li.className = 'list-group-item';
                        li.innerHTML = `<strong>${item.checkpoint_date}</strong>: ${item.percentage_complete.toFixed(1)}% (${formatCurrency(item.actual_value)})`;
                        goalTimelineList.appendChild(li);
                    });

                    events.forEach(item => {
                        const li = document.createElement('li');
                        li.className = 'list-group-item';
                        li.innerHTML = `<span class="badge bg-info me-2 text-uppercase">${escapeHtml(item.event_type)}</span>${escapeHtml(item.event_message)}<br><small class="text-muted">${item.created_at}</small>`;
                        goalTimelineList.appendChild(li);
                    });
                })
                .catch(err => {
                    console.error(err);
                    goalTimelineEmpty.classList.remove('d-none');
                    goalTimelineList.classList.add('d-none');
                    goalTimelineEmpty.innerText = 'Failed to load goal timeline.';
                });
        }

        function saveGoal() {
            goalSubmitBtn.disabled = true;
            const formData = new FormData(goalForm);

            const endpoint = formData.get('goal_id') ? 'update' : 'create';

            fetch(`<?php echo BASE_URL; ?>admin/mlm-engagement/goals/${endpoint}`, {
                    method: 'POST',
                    body: formData
                })
                .then(r => r.json())
                .then(response => {
                    if (!response.success) {
                        throw new Error(response.message || 'Failed to create goal');
                    }
                    goalModal.hide();
                    fetchAll();
                    alert(messageForGoalSave(response, endpoint));
                })
                .catch(err => {
                    console.error(err);
                    alert(err.message);
                })
                .finally(() => {
                    goalSubmitBtn.disabled = false;
                });
        }

        function messageForGoalSave(response, endpoint) {
            if (endpoint === 'create') {
                return 'Goal created successfully (ID ' + response.goal_id + ').';
            }
            return 'Goal updated successfully.';
        }

        function recordProgress() {
            progressSubmitBtn.disabled = true;
            const formData = new FormData(goalProgressForm);

            fetch('<?php echo BASE_URL; ?>admin/mlm-engagement/goals/progress', {
                    method: 'POST',
                    body: formData
                })
                .then(r => r.json())
                .then(response => {
                    if (!response.success) {
                        throw new Error(response.message || 'Failed to record progress');
                    }
                    goalProgressModal.hide();
                    if (selectedGoal) {
                        loadGoalDetails(selectedGoal.id);
                    }
                    fetchAll();
                })
                .catch(err => {
                    console.error(err);
                    alert(err.message);
                })
                .finally(() => {
                    progressSubmitBtn.disabled = false;
                });
        }

        function updateGoalStatus(goalId, status) {
            const formData = new FormData();
            formData.append('goal_id', goalId);
            formData.append('status', status);

            fetch('<?php echo BASE_URL; ?>admin/mlm-engagement/goals/status', {
                    method: 'POST',
                    body: formData
                })
                .then(r => r.json())
                .then(response => {
                    if (!response.success) {
                        throw new Error(response.message || 'Failed to update goal');
                    }
                    fetchAll();
                })
                .catch(err => {
                    console.error(err);
                    alert(err.message);
                });
        }

        notifList.addEventListener('click', function(e) {
            const button = e.target.closest('[data-action="mark-notification-read"]');
            if (!button) return;
            const notificationId = parseInt(button.dataset.notificationId, 10);
            const userId = parseInt(notificationsUser.value, 10);
            if (!notificationId || !userId) return;

            button.disabled = true;
            button.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';

            markNotificationRead(notificationId, userId)
                .then(() => loadNotifications())
                .catch(err => {
                    console.error(err);
                    alert(err.message || 'Failed to mark notification read');
                });
        });

        function loadNotifications() {
            const userId = parseInt(notificationsUser.value, 10);
            if (!userId) {
                notifMeta.innerText = 'Awaiting user ID';
                notifEmpty.classList.remove('d-none');
                notifList.classList.add('d-none');
                if (markAllNotificationsBtn) {
                    markAllNotificationsBtn.disabled = true;
                }
                return;
            }

            const params = new URLSearchParams();
            params.set('user_id', userId);
            params.set('limit', '20');

            fetch('<?php echo BASE_URL; ?>admin/mlm-engagement/notifications?' + params.toString())
                .then(r => r.json())
                .then(({
                    success,
                    records
                }) => {
                    if (!success) throw new Error('Failed to load notifications');

                    notifMeta.innerText = `Showing latest ${records.length} events for #${userId}`;
                    notifList.innerHTML = '';

                    if (!records || records.length === 0) {
                        notifEmpty.classList.remove('d-none');
                        notifList.classList.add('d-none');
                        notifEmpty.innerText = 'No recent notifications for this associate.';
                        if (markAllNotificationsBtn) {
                            markAllNotificationsBtn.disabled = true;
                        }
                        return;
                    }

                    notifEmpty.classList.add('d-none');
                    notifList.classList.remove('d-none');

                    const unreadCount = records.reduce((count, item) => item.read_at ? count : count + 1, 0);
                    if (markAllNotificationsBtn) {
                        markAllNotificationsBtn.disabled = unreadCount === 0;
                    }

                    records.forEach(item => {
                        const li = document.createElement('li');
                        const isRead = !!item.read_at;
                        li.className = 'list-group-item';
                        li.innerHTML = `
                        <div class="d-flex justify-content-between align-items-start gap-3">
                            <div class="flex-grow-1">
                                <div class="fw-semibold">${escapeHtml(item.title)}</div>
                                <div class="text-muted small text-uppercase">${escapeHtml(item.category || 'general')}</div>
                                <p class="mb-0">${escapeHtml(item.message)}</p>
                            </div>
                            <div class="text-end" style="min-width: 160px;">
                                <div class="small text-muted">${formatDateTime(item.created_at)}</div>
                                ${isRead
                                    ? '<span class="badge bg-success">Read</span>'
                                    : '<span class="badge bg-warning text-dark">Unread</span>'}
                                <div class="mt-2">
                                    <button class="btn btn-sm btn-outline-secondary" data-action="mark-notification-read" data-notification-id="${item.id}" ${isRead ? 'disabled' : ''}>
                                        <i class="fas fa-check"></i> Mark Read
                                    </button>
                                </div>
                            </div>
                        </div>
                    `;
                        notifList.appendChild(li);
                    });
                })
                .catch(err => {
                    console.error(err);
                    notifMeta.innerText = 'Error loading notifications';
                    notifEmpty.classList.remove('d-none');
                    notifList.classList.add('d-none');
                    notifEmpty.innerText = 'Unable to load notifications for this associate.';
                    if (markAllNotificationsBtn) {
                        markAllNotificationsBtn.disabled = true;
                    }
                });
        }

        function markNotificationRead(notificationId, userId) {
            const formData = new FormData();
            formData.append('id', notificationId);
            formData.append('user_id', userId);

            return fetch('<?php echo BASE_URL; ?>admin/mlm-engagement/notifications/mark-read', {
                    method: 'POST',
                    body: formData
                })
                .then(r => r.json())
                .then(response => {
                    if (!response.success) {
                        throw new Error(response.message || 'Failed to mark notification read');
                    }
                    return response;
                });
        }

        function markAllNotifications(userId) {
            const formData = new FormData();
            formData.append('user_id', userId);

            return fetch('<?php echo BASE_URL; ?>admin/mlm-engagement/notifications/mark-all-read', {
                    method: 'POST',
                    body: formData
                })
                .then(r => r.json())
                .then(response => {
                    if (!response.success) {
                        throw new Error(response.message || 'Failed to mark notifications read');
                    }
                    return response;
                });
        }

        function calculateGoalProgress(goal) {
            if (goal.status === 'completed') return 100;
            if (typeof goal.percentage_complete === 'number') {
                return Math.max(0, Math.min(100, Number(goal.percentage_complete)));
            }
            if (!goal.target_value || Number(goal.target_value) === 0) return 0;
            if (goal.progress_summary && typeof goal.progress_summary.percentage_complete === 'number') {
                return Math.max(0, Math.min(100, Number(goal.progress_summary.percentage_complete)));
            }
            return 0;
        }

        function formatUnits(value, units) {
            const formatted = Number(value || 0).toLocaleString('en-IN');
            if (!units) return formatted;
            return `${formatted} ${units}`;
        }

        function populateGoalForm(goal) {
            goalForm.reset();
            goalIdField.value = goal.id;
            goalTypeField.value = goal.goal_type;
            goalScopeField.value = goal.scope;
            handleScopeChange();
            goalUserIdField.value = goal.user_id || '';
            goalTargetValueField.value = goal.target_value;
            goalTargetUnitsField.value = goal.target_units || '';
            goalStatusField.value = goal.status;
            goalStartDateField.value = goal.start_date;
            goalEndDateField.value = goal.end_date;
        }

        function selectGoalRow(row, goalId) {
            goalsTable.querySelectorAll('tr').forEach(tr => tr.classList.remove(goalTableSelectedClass));
            if (row) {
                row.classList.add(goalTableSelectedClass);
            }
            selectedGoal = cachedGoals.find(goal => goal.id === goalId) || null;
            toggleGoalActions(!!selectedGoal);
        }

        function toggleGoalActions(enabled) {
            if (!openEditGoalBtn || !openProgressModalBtn) return;
            openEditGoalBtn.disabled = !enabled;
            openProgressModalBtn.disabled = !enabled;
        }

        function formatCurrency(value) {
            return '₹' + Number(value || 0).toLocaleString('en-IN', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        function formatDateTime(value) {
            if (!value) return '—';
            return new Date(value).toLocaleString();
        }

        function escapeHtml(str) {
            if (str === null || str === undefined) return '';
            return String(str).replace(/[&<>"']/g, char => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;'
            } [char]));
        }

        function statusColor(status) {
            switch (status) {
                case 'active':
                    return 'primary';
                case 'completed':
                    return 'success';
                case 'expired':
                    return 'secondary';
                case 'cancelled':
                    return 'dark';
                default:
                    return 'warning';
            }
        }

        fetchAll();
    })();
</script>
