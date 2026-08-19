<?php
// Ensure variables are available (extract() may not work in all contexts)
$_conn = $connected ?? false;
$_cfg = $config ?? [];
$_pageTitle = $page_title ?? 'SIM Calling Settings';
?>
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0"><i class="fas fa-cog text-teal"></i> <?= htmlspecialchars($_pageTitle) ?></h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/sim-calling">SIM Calling</a></li>
                        <li class="breadcrumb-item active">Settings</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-8">
                    <div class="card card-outline card-teal">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-server"></i> Asterisk AMI Configuration</h3>
                        </div>
                        <form method="POST">
    <?php echo CSRFProtection::csrfField(); ?>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="form-group">
                                            <label>AMI Host</label>
                                            <input type="text" name="host" class="form-control" value="<?= htmlspecialchars($config['host'] ?? '127.0.0.1') ?>" placeholder="127.0.0.1">
                                            <small class="text-muted">IP address of your Asterisk server</small>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>AMI Port</label>
                                            <input type="number" name="port" class="form-control" value="<?= htmlspecialchars($config['port'] ?? '5038') ?>" placeholder="5038">
                                            <small class="text-muted">Default: 5038</small>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Username</label>
                                            <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($config['username'] ?? 'admin') ?>" placeholder="admin">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Secret / Password</label>
                                            <input type="password" name="secret" class="form-control" value="<?= htmlspecialchars($config['secret'] ?? '') ?>" placeholder="AMI password">
                                        </div>
                                    </div>
                                </div>

                                <hr>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Dialplan Context</label>
                                            <input type="text" name="context" class="form-control" value="<?= htmlspecialchars($config['context'] ?? 'outbound-calls') ?>" placeholder="outbound-calls">
                                            <small class="text-muted">Context in extensions.conf</small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>SIP Trunk Name</label>
                                            <input type="text" name="trunk" class="form-control" value="<?= htmlspecialchars($config['trunk'] ?? 'gsm-gateway') ?>" placeholder="gsm-gateway">
                                            <small class="text-muted">PJSIP endpoint name for GSM gateway</small>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Caller ID (SIM Number)</label>
                                    <input type="text" name="caller_id" class="form-control" value="<?= htmlspecialchars($config['caller_id'] ?? '') ?>" placeholder="919277121112">
                                    <small class="text-muted">This number will appear on customer's phone as caller</small>
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="submit" class="btn btn-teal"><i class="fas fa-save"></i> Save Settings</button>
                                <a href="<?= BASE_URL ?>/admin/sim-calling" class="btn btn-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="col-md-4">
                    <!-- Connection Test -->
                    <div class="card card-outline <?= $_conn ? 'card-success' : 'card-danger' ?>">
                        <div class="card-body text-center">
                            <i class="fas <?= $_conn ? 'fa-check-circle text-success' : 'fa-times-circle text-danger' ?> fa-3x mb-3"></i>
                            <h4><?= $_conn ? 'Connected!' : 'Not Connected' ?></h4>
                            <p class="text-muted"><?= $_conn ? 'Asterisk AMI is reachable' : 'Cannot reach Asterisk AMI' ?></p>
                            <button onclick="testConnection()" class="btn btn-sm btn-outline-<?= $_conn ? 'success' : 'danger' ?>">
                                <i class="fas fa-plug"></i> Test Connection
                            </button>
                        </div>
                    </div>

                    <!-- Quick Links -->
                    <div class="card card-outline card-secondary">
                        <div class="card-header"><h3 class="card-title">Quick Links</h3></div>
                        <div class="card-body p-0">
                            <div class="list-group list-group-flush">
                                <a href="<?= BASE_URL ?>/admin/sim-calling" class="list-group-item list-group-item-action">
                                    <i class="fas fa-tachometer-alt text-teal"></i> Dashboard
                                </a>
                                <a href="<?= BASE_URL ?>/admin/sim-calling/generate-dialplan" class="list-group-item list-group-item-action">
                                    <i class="fas fa-download text-info"></i> Download Dialplan
                                </a>
                                <a href="<?= BASE_URL ?>/admin/voice-agents" class="list-group-item list-group-item-action">
                                    <i class="fas fa-robot text-purple"></i> Voice Agents
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Hardware Guide -->
                    <div class="card card-outline card-secondary">
                        <div class="card-header"><h3 class="card-title"><i class="fas fa-info-circle"></i> Hardware Needed</h3></div>
                        <div class="card-body">
                            <ul class="list-unstyled small">
                                <li><i class="fas fa-check text-success"></i> <strong>Asterisk Server</strong> — Ubuntu 20.04+</li>
                                <li><i class="fas fa-check text-success"></i> <strong>GSM Gateway</strong> — GoIP-1/4/8</li>
                                <li><i class="fas fa-check text-success"></i> <strong>SIM Card</strong> — Any Indian operator</li>
                                <li><i class="fas fa-check text-success"></i> <strong>Network</strong> — SIP trunk config</li>
                            </ul>
                            <hr>
                            <p class="small text-muted mb-0">
                                <strong>Budget:</strong> GoIP-1 (~₹5,000) + SIM (~₹500/mo) = Total ₹5,500 setup
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<style>
.text-teal { color: #0d9488 !important; }
.btn-teal { background-color: #0d9488; color: #fff; border-color: #0d9488; }
.btn-teal:hover { background-color: #0f766e; color: #fff; }
.text-purple { color: #6f42c1 !important; }
</style>

<script>
async function testConnection() {
    try {
        showLoader();
        const res = await fetch('<?= BASE_URL ?>/admin/sim-calling/api/status');
        const data = await res.json();
        if (data.connected) {
            showToast('Connected to Asterisk AMI!\nActive channels: ' + data.active_channels, 'info');
        } else {
            showToast('Cannot connect to Asterisk AMI.\nCheck host/port and Asterisk service.', 'info');
        }
    } catch (e) {
        showToast('Connection failed: ' + e.message, 'danger');
    }
}
</script>
