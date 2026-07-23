<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="<?= BASE_URL ?>/admin/messages" class="btn btn-outline-secondary btn-sm me-2">
                <i class="fas fa-arrow-left"></i> Back to Inbox
            </a>
            <h1 class="h3 mb-0 d-inline">Compose Message</h1>
        </div>
    </div>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Select Recipient</h5>
                </div>
                <div class="card-body">
                    <!-- Search -->
                    <div class="mb-4">
                        <input type="text" class="form-control" id="searchInput"
                               placeholder="Search users by name, email, or phone..."
                               value="<?= htmlspecialchars($search) ?>"
                               onkeyup="searchUsers(this.value)">
                        <div class="mt-2">
                            <small class="text-muted">Type at least 2 characters to search</small>
                        </div>
                    </div>

                    <!-- Quick filters -->
                    <div class="mb-3">
                        <button class="btn btn-sm btn-outline-primary me-1" onclick="filterRole('all')">All</button>
                        <button class="btn btn-sm btn-outline-danger me-1" onclick="filterRole('admin')">Admin</button>
                        <button class="btn btn-sm btn-outline-success me-1" onclick="filterRole('associate')">Associate</button>
                        <button class="btn btn-sm btn-outline-info me-1" onclick="filterRole('agent')">Agent</button>
                        <button class="btn btn-sm btn-outline-warning me-1" onclick="filterRole('employee')">Employee</button>
                        <button class="btn btn-sm btn-outline-secondary me-1" onclick="filterRole('customer')">Customer</button>
                    </div>

                    <!-- Users List -->
                    <div id="usersList" style="max-height: 400px; overflow-y: auto;">
                        <?php if (empty($users)): ?>
                            <div class="text-center py-4">
                                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No users found. Try a different search.</p>
                            </div>
                        <?php else: ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($users as $user): ?>
                                    <a href="#" class="list-group-item list-group-item-action user-item"
                                       data-user-id="<?= $user['id'] ?>"
                                       data-user-name="<?= htmlspecialchars($user['name'] ?? '') ?>"
                                       data-user-role="<?= $user['role'] ?? 'user' ?>"
                                       onclick="selectUser(this)">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0 me-3">
                                                <div class="avatar-circle d-flex align-items-center justify-content-center text-white fw-bold"
                                                     style="width: 40px; height: 40px; border-radius: 50%; background-color: <?php
                                                        $role = $user['role'] ?? 'user';
                                                        $colors = ['admin'=>'#dc3545','associate'=>'#198754','agent'=>'#0dcaf0','employee'=>'#ffc107','customer'=>'#6c757d'];
                                                        echo $colors[$role] ?? '#6c757d';
                                                     ?>;">
                                                    <?= strtoupper(substr($user['name'] ?? '?', 0, 1)) ?>
                                                </div>
                                            </div>
                                            <div>
                                                <strong><?= htmlspecialchars($user['name'] ?? '') ?></strong>
                                                <br>
                                                <small class="text-muted">
                                                    <span class="badge bg-<?php
                                                        echo match($role) {'admin'=>'danger','associate'=>'success','agent'=>'info','employee'=>'warning',default=>'secondary'};
                                                    ?>"><?= ucfirst($role) ?></span>
                                                    <?= htmlspecialchars($user['email'] ?? '') ?>
                                                    <?php if (!empty($user['phone'])): ?>
                                                        &middot; <?= htmlspecialchars($user['phone']) ?>
                                                    <?php endif; ?>
                                                </small>
                                            </div>
                                        </div>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">New Message</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?= BASE_URL ?>/admin/messages/send">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                        <input type="hidden" name="receiver_id" id="receiverId" value="">

                        <div class="mb-3">
                            <label class="form-label">To:</label>
                            <div id="selectedUser" class="text-muted" style="min-height: 24px;">
                                Select a user from the list
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Message:</label>
                            <textarea name="message" class="form-control" rows="6"
                                      placeholder="Type your message..." required
                                      style="resize: none;"></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary w-100" id="sendBtn" disabled>
                            <i class="fas fa-paper-plane"></i> Send Message
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let selectedUserId = null;

function selectUser(el) {
    selectedUserId = el.dataset.userId;
    const name = el.dataset.userName;
    const role = el.dataset.userRole;

    document.getElementById('receiverId').value = selectedUserId;
    document.getElementById('selectedUser').innerHTML =
        '<strong>' + name + '</strong> <span class="badge bg-'
        + (role === 'admin' ? 'danger' : role === 'associate' ? 'success' : role === 'agent' ? 'info' : role === 'employee' ? 'warning' : 'secondary')
        + '">' + role.charAt(0).toUpperCase() + role.slice(1) + '</span>';
    document.getElementById('sendBtn').disabled = false;

    document.querySelectorAll('.user-item').forEach(function(item) {
        item.classList.remove('active');
    });
    el.classList.add('active');
}

let searchTimer = null;
function searchUsers(query) {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(function() {
        if (query.length < 2 && query.length > 0) return;
        const url = '<?= BASE_URL ?>/admin/messages/ajax-search?q=' + encodeURIComponent(query);
        fetch(url)
            .then(function(r) { return r.json(); })
            .then(function(users) {
                var list = document.getElementById('usersList');
                if (!users || users.length === 0) {
                    list.innerHTML = '<div class="text-center py-4"><i class="fas fa-users fa-3x text-muted mb-3"></i><p class="text-muted">No users found.</p></div>';
                    return;
                }
                var html = '<div class="list-group list-group-flush">';
                var colors = {'admin':'#dc3545','associate':'#198754','agent':'#0dcaf0','employee':'#ffc107','customer':'#6c757d'};
                var badges = {'admin':'danger','associate':'success','agent':'info','employee':'warning','customer':'secondary'};
                users.forEach(function(u) {
                    var color = colors[u.role] || '#6c757d';
                    var badge = badges[u.role] || 'secondary';
                    var initial = (u.name || '?').charAt(0).toUpperCase();
                    html += '<a href="#" class="list-group-item list-group-item-action user-item"'
                        + ' data-user-id="' + u.id + '" data-user-name="' + escapeHtml(u.name || '') + '" data-user-role="' + (u.role || 'user') + '"'
                        + ' onclick="selectUser(this)">'
                        + '<div class="d-flex align-items-center">'
                        + '<div class="flex-shrink-0 me-3"><div class="d-flex align-items-center justify-content-center text-white fw-bold" style="width:40px;height:40px;border-radius:50%;background-color:' + color + ';">' + initial + '</div></div>'
                        + '<div><strong>' + escapeHtml(u.name || '') + '</strong><br><small class="text-muted"><span class="badge bg-' + badge + '">' + (u.role || 'user').charAt(0).toUpperCase() + (u.role || 'user').slice(1) + '</span> ' + escapeHtml(u.email || '') + (u.phone ? ' &middot; ' + escapeHtml(u.phone) : '') + '</small></div>'
                        + '</div></a>';
                });
                html += '</div>';
                list.innerHTML = html;
            });
    }, 300);
}

function filterRole(role) {
    var items = document.querySelectorAll('.user-item');
    items.forEach(function(item) {
        if (role === 'all') {
            item.style.display = '';
        } else {
            var userRole = item.dataset.userRole;
            item.style.display = userRole === role ? '' : 'none';
        }
    });
}

function escapeHtml(str) {
    var div = document.createElement('div');
    div.appendChild(document.createTextNode(str));
    return div.innerHTML;
}
</script>

<style>
.user-item.active {
    background-color: #e8f0fe;
    border-left: 3px solid #0d6efd;
}
.user-item:hover {
    background-color: #f8f9fa;
}
</style>

