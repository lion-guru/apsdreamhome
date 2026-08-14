<?php
$page_title = 'WebSocket Test - APS Dream Home';
require __DIR__ . '/../layouts/header.php';
?>

<main class="container py-5">
    <h1 class="mb-4"><i class="fas fa-bolt text-warning"></i> WebSocket Real-Time Notification Test</h1>

    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Connection Status</h5>
                </div>
                <div class="card-body">
                    <p><strong>State:</strong> <span id="connState" class="badge bg-secondary">Initializing...</span></p>
                    <p><strong>URL:</strong> <code id="connUrl">-</code></p>
                    <p><strong>User ID:</strong> <code id="connUserId">-</code></p>
                    <p><strong>Role:</strong> <code id="connUserRole">-</code></p>
                    <p><strong>Reconnect attempts:</strong> <span id="connAttempts">0</span></p>
                    <p><strong>Last message:</strong> <code id="connLastMsg">-</code></p>
                    <p><strong>Notifications received:</strong> <span id="connCount" class="badge bg-info">0</span></p>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Trigger Test Notification</h5>
                </div>
                <div class="card-body">
                    <p class="small text-muted">Publish a notification via the backend. If you're logged in, the WebSocket should receive it in real-time.</p>
                    <div class="mb-2">
                        <label class="form-label small">Channel</label>
                        <input type="text" id="testChannel" class="form-control form-control-sm" value="global">
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">Event Type</label>
                        <input type="text" id="testEvent" class="form-control form-control-sm" value="browser_test">
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">User ID (blank = global)</label>
                        <input type="text" id="testUserId" class="form-control form-control-sm" value="">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">Message</label>
                        <input type="text" id="testMessage" class="form-control form-control-sm" value="Hello from browser test!">
                    </div>
                    <button class="btn btn-primary btn-sm" id="triggerBtn">
                        <i class="fas fa-paper-plane"></i> Publish Notification
                    </button>
                    <button class="btn btn-warning btn-sm" id="clearBtn">
                        <i class="fas fa-trash"></i> Clear Log
                    </button>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">Live Event Log</h5>
                </div>
                <div class="card-body p-0">
                    <pre id="logBox" class="style-44669"></pre>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
(function() {
    var stateEl = document.getElementById('connState');
    var urlEl = document.getElementById('connUrl');
    var userIdEl = document.getElementById('connUserId');
    var roleEl = document.getElementById('connUserRole');
    var attemptsEl = document.getElementById('connAttempts');
    var lastMsgEl = document.getElementById('connLastMsg');
    var countEl = document.getElementById('connCount');
    var logEl = document.getElementById('logBox');

    var userId = (window.NOTIFY_USER && window.NOTIFY_USER.id) || null;
    var role = (window.NOTIFY_USER && window.NOTIFY_USER.role) || 'guest';
    userIdEl.textContent = userId === null ? 'null (guest)' : userId;
    roleEl.textContent = role;

    var notifCount = 0;
    var attempts = 0;
    var protocol = window.location.protocol === 'https:' ? 'wss:' : 'ws:';
    var wsUrl = protocol + '//' + window.location.hostname + ':8080';
    urlEl.textContent = wsUrl;

    function log(level, msg) {
        var d = new Date();
        var ts = d.toTimeString().split(' ')[0] + '.' + String(d.getMilliseconds()).padStart(3, '0');
        var colors = { INFO: '#4ec9b0', OK: '#b5cea8', WARN: '#dcdcaa', ERR: '#f48771', RECV: '#9cdcfe' };
        var c = colors[level] || '#d4d4d4';
        logEl.innerHTML += '<span class="style-66501">' + ts + '</span> <span class="style-93734">[' + level + ']</span> ' + escapeHtml(msg) + '\n';
        logEl.scrollTop = logEl.scrollHeight;
    }

    function escapeHtml(s) {
        return String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    }

    function setState(s, color) {
        stateEl.textContent = s;
        stateEl.className = 'badge bg-' + color;
    }

    log('INFO', 'Connecting to ' + wsUrl + '...');
    var ws = new WebSocket(wsUrl);

    ws.onopen = function() {
        setState('CONNECTED', 'success');
        log('OK', 'WebSocket connection established');
        lastMsgEl.textContent = 'Connected at ' + new Date().toLocaleTimeString();
    };

    ws.onmessage = function(event) {
        notifCount++;
        countEl.textContent = notifCount;
        lastMsgEl.textContent = event.data.length > 60 ? event.data.substring(0, 60) + '...' : event.data;
        log('RECV', event.data);
    };

    ws.onerror = function(err) {
        log('ERR', 'WebSocket error (see console)');
        console.error('WebSocket error:', err);
    };

    ws.onclose = function(e) {
        setState('CLOSED', 'danger');
        log('WARN', 'Connection closed (code=' + e.code + ')');
    };

    setInterval(function() {
        if (ws.readyState === WebSocket.OPEN) {
            ws.send(JSON.stringify({ type: 'ping', timestamp: Date.now() }));
            log('INFO', 'Sent heartbeat ping');
        }
    }, 30000);

    document.getElementById('triggerBtn').addEventListener('click', function() {
        var body = {
            channel: document.getElementById('testChannel').value,
            event_type: document.getElementById('testEvent').value,
            payload: {
                title: 'Browser Test',
                message: document.getElementById('testMessage').value,
                test: true
            }
        };
        var uid = document.getElementById('testUserId').value;
        if (uid) body.user_id = parseInt(uid, 10);

        log('INFO', 'Publishing via /api/notification...');
        fetch('/api/notification', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(body)
        }).then(r => r.json()).then(d => {
            log('OK', 'API response: ' + JSON.stringify(d));
        }).catch(e => {
            log('ERR', 'API error: ' + e.message);
        });
    });

    document.getElementById('clearBtn').addEventListener('click', function() {
        logEl.innerHTML = '';
    });

    if (typeof window.notificationSystem !== 'undefined') {
        log('INFO', 'window.notificationSystem active - bells in header should appear');
    } else {
        log('WARN', 'window.notificationSystem NOT defined');
    }
})();
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
