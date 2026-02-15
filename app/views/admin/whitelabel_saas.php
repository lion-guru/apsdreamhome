<?php
/**
 * White-Label SaaS Management
 * Allows administrators to manage white-label SaaS instances and configurations
 */
require_once __DIR__ . '/core/init.php';
require_once __DIR__ . '/../includes/notification/webhook_manager.php';

// Ensure user has correct permissions
requireRole('Admin');

$page_title = "White-Label SaaS Management";
$db = \App\Core\App::database();
$userId = $_SESSION['user_id'] ?? 0;

$success_msg = '';
$error_msg = '';

// Ensure SaaS tables exist
$db->execute("
    CREATE TABLE IF NOT EXISTS saas_instances (
        id VARCHAR(36) PRIMARY KEY,
        client_name VARCHAR(100) NOT NULL,
        domain VARCHAR(255) NOT NULL,
        status ENUM('active', 'suspended', 'pending') DEFAULT 'pending',
        config JSON,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )
");

// Handle Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        if ($_POST['action'] === 'add_instance') {
            try {
                $name = $_POST['client_name'] ?? '';
                $domain = $_POST['domain'] ?? '';
                $id = \vsprintf('%s%s-%s-%s-%s-%s%s%s', \str_split(\bin2hex(\App\Helpers\SecurityHelper::secureRandomBytes(16)), 4));
                
                $sql = "INSERT INTO saas_instances (id, client_name, domain, status) VALUES (:id, :name, :domain, 'active')";
                if ($db->execute($sql, [
                    'id' => $id,
                    'name' => $name,
                    'domain' => $domain
                ])) {
                    logAdminActivity($userId, 'add_saas_instance', 'Added new SaaS instance: ' . $name);
                    $success_msg = $mlSupport->translate('SaaS instance added successfully!');
                }
            } catch (Exception $e) {
                $error_msg = $mlSupport->translate('Error adding instance: ') . $e->getMessage();
            }
        } elseif ($_POST['action'] === 'delete_instance') {
            $id = $_POST['id'] ?? '';
            if ($db->execute("DELETE FROM saas_instances WHERE id = :id", ['id' => $id])) {
                logAdminActivity($userId, 'delete_saas_instance', 'Deleted SaaS instance ID: ' . $id);
                $success_msg = $mlSupport->translate('SaaS instance deleted successfully!');
            }
        }
    }
}

// Fetch Instances
$instances = $db->fetchAll("SELECT * FROM saas_instances ORDER BY created_at DESC LIMIT 20");

require_once __DIR__ . '/admin_header.php';
require_once __DIR__ . '/admin_sidebar.php';
?>

<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-2">
                            <li class="breadcrumb-item"><a href="dashboard.php"><?= h($mlSupport->translate('Dashboard')) ?></a></li>
                            <li class="breadcrumb-item active" aria-current="page"><?= h($mlSupport->translate('White-Label SaaS')) ?></li>
                        </ol>
                    </nav>
                    <h3 class="page-title fw-bold text-primary"><?= h($mlSupport->translate('White-Label SaaS Management')) ?></h3>
                    <p class="text-muted small mb-0"><?= h($mlSupport->translate('Manage client instances and white-label configurations')) ?></p>
                </div>
                <div class="col-auto">
                    <button type="button" class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#addInstanceModal">
                        <i class="fas fa-plus me-2"></i> <?= h($mlSupport->translate('New Instance')) ?>
                    </button>
                </div>
            </div>
        </div>

        <?php if ($success_msg): ?>
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                <i class="fas fa-check-circle me-2"></i> <?= h($success_msg) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if ($error_msg): ?>
            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i> <?= h($error_msg) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-12">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-4"><?= h($mlSupport->translate('Client Name')) ?></th>
                                        <th><?= h($mlSupport->translate('Domain')) ?></th>
                                        <th><?= h($mlSupport->translate('Status')) ?></th>
                                        <th><?= h($mlSupport->translate('Created At')) ?></th>
                                        <th class="text-end pe-4"><?= h($mlSupport->translate('Actions')) ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($instances as $instance): ?>
                                    <tr>
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm me-3 bg-soft-primary rounded text-primary d-flex align-items-center justify-content-center">
                                                    <i class="fas fa-building"></i>
                                                </div>
                                                <span class="fw-bold"><?= h($instance['client_name']) ?></span>
                                            </div>
                                        </td>
                                        <td><code><?= h($instance['domain']) ?></code></td>
                                        <td>
                                            <?php if ($instance['status'] === 'active'): ?>
                                                <span class="badge bg-success-soft text-success px-3"><?= h($mlSupport->translate('Active')) ?></span>
                                            <?php elseif ($instance['status'] === 'suspended'): ?>
                                                <span class="badge bg-danger-soft text-danger px-3"><?= h($mlSupport->translate('Suspended')) ?></span>
                                            <?php else: ?>
                                                <span class="badge bg-warning-soft text-warning px-3"><?= h($mlSupport->translate('Pending')) ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= h(date('M d, Y', strtotime($instance['created_at']))) ?></td>
                                        <td class="text-end pe-4">
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-light border-0 shadow-sm" type="button" data-bs-toggle="dropdown">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                                                    <li><a class="dropdown-item" href="#"><i class="fas fa-edit me-2 text-primary"></i> <?= h($mlSupport->translate('Edit Config')) ?></a></li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <form method="POST" onsubmit="return confirm('<?= h($mlSupport->translate('Are you sure you want to delete this instance?')) ?>');">
                                                            <?= getCsrfField() ?>
                                                            <input type="hidden" name="action" value="delete_instance">
                                                            <input type="hidden" name="id" value="<?= h($instance['id']) ?>">
                                                            <button type="submit" class="dropdown-item text-danger">
                                                                <i class="fas fa-trash-alt me-2"></i> <?= h($mlSupport->translate('Delete')) ?>
                                                            </button>
                                                        </form>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php if (empty($instances)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-5 text-muted">
                                            <i class="fas fa-server fa-3x mb-3 d-block opacity-25"></i>
                                            <?= h($mlSupport->translate('No SaaS instances found.')) ?>
                                        </td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Instance Modal -->
<div class="modal fade" id="addInstanceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold"><?= h($mlSupport->translate('Add New SaaS Instance')) ?></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body p-4">
                    <?= getCsrfField() ?>
                    <input type="hidden" name="action" value="add_instance">
                    <div class="mb-3">
                        <label class="form-label fw-medium"><?= h($mlSupport->translate('Client Name')) ?></label>
                        <input type="text" name="client_name" class="form-control border-0 bg-light shadow-sm" required placeholder="e.g. Dream Home Realty">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium"><?= h($mlSupport->translate('Domain / Subdomain')) ?></label>
                        <input type="text" name="domain" class="form-control border-0 bg-light shadow-sm" required placeholder="e.g. dreamhome.example.com">
                    </div>
                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="button" class="btn btn-link text-muted" data-bs-dismiss="modal"><?= h($mlSupport->translate('Cancel')) ?></button>
                    <button type="submit" class="btn btn-primary px-4 shadow-sm"><?= h($mlSupport->translate('Create Instance')) ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.bg-success-soft { background-color: rgba(25, 135, 84, 0.1); }
.bg-danger-soft { background-color: rgba(220, 53, 69, 0.1); }
.bg-warning-soft { background-color: rgba(255, 193, 7, 0.1); }
.bg-soft-primary { background-color: rgba(13, 110, 253, 0.1); }
.avatar-sm { width: 40px; height: 40px; }
</style>

<?php require_once __DIR__ . '/admin_footer.php'; ?>

