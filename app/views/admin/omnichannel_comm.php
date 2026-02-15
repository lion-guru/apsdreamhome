<?php
require_once __DIR__ . '/core/init.php';
require_once __DIR__ . '/../includes/ai/AIManager.php';
require_once __DIR__ . '/../includes/ai/UnifiedCommManager.php';

$page_title = "Omnichannel Comm Center";
$db = \App\Core\App::database();
$aiManager = new AIManager($db);
$commManager = new UnifiedCommManager($db, $aiManager);

// Stats
$totalInteractions = $db->fetchOne("SELECT COUNT(*) as count FROM communication_interactions")['count'] ?? 0;
$pendingRouting = $db->fetchOne("SELECT COUNT(*) as count FROM communication_interactions WHERE status = 'pending'")['count'] ?? 0;
$deptWise = $db->fetchAll("SELECT d.name, COUNT(r.id) as count FROM departments d LEFT JOIN interaction_routing r ON d.id = r.department_id GROUP BY d.id");

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
                            <li class="breadcrumb-item"><a href="index.php"><?= h($mlSupport->translate('Dashboard')) ?></a></li>
                            <li class="breadcrumb-item active" aria-current="page"><?= h($mlSupport->translate('Omnichannel Comm Center')) ?></li>
                        </ol>
                    </nav>
                    <h3 class="page-title"><?= h($mlSupport->translate('Omnichannel Comm Center')) ?></h3>
                    <p class="text-muted"><?= h($mlSupport->translate('WhatsApp, Telegram & Phone Integrated Routing')) ?></p>
                </div>
                <div class="col-auto">
                    <button class="btn btn-success shadow-sm px-4">
                        <i class="fas fa-plus me-2"></i> <?= h($mlSupport->translate('Manual Log')) ?>
                    </button>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-3 mb-4">
                <div class="card bg-primary text-white shadow-sm border-0 h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-comments fa-2x me-3 opacity-50"></i>
                            <h5 class="mb-0"><?= h($mlSupport->translate('Total Interactions')) ?></h5>
                        </div>
                        <h3 class="fw-bold mb-0"><?= h($totalInteractions) ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="card bg-warning text-dark shadow-sm border-0 h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-route fa-2x me-3 opacity-50"></i>
                            <h5 class="mb-0"><?= h($mlSupport->translate('Pending Routing')) ?></h5>
                        </div>
                        <h3 class="fw-bold mb-0"><?= h($pendingRouting) ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="card bg-success text-white shadow-sm border-0 h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-chart-line fa-2x me-3 opacity-50"></i>
                            <h5 class="mb-0"><?= h($mlSupport->translate('Finance/Investments')) ?></h5>
                        </div>
                        <h3 class="fw-bold mb-0"><?= h($db->fetchOne("SELECT COUNT(*) as count FROM communication_interactions WHERE tag = 'investment'")['count'] ?? 0) ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="card bg-info text-white shadow-sm border-0 h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-building fa-2x me-3 opacity-50"></i>
                            <h5 class="mb-0"><?= h($mlSupport->translate('Real Estate Enquiries')) ?></h5>
                        </div>
                        <h3 class="fw-bold mb-0"><?= h($db->fetchOne("SELECT COUNT(*) as count FROM communication_interactions WHERE tag = 'enquiry'")['count'] ?? 0) ?></h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <!-- Recent Interactions -->
            <div class="col-md-8 mb-4">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white border-bottom-0 py-3">
                        <h4 class="card-title mb-0 fw-bold text-primary">
                            <i class="fas fa-stream me-2"></i><?= h($mlSupport->translate('Live Interaction Stream')) ?>
                        </h4>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="border-0"><?= h($mlSupport->translate('Channel')) ?></th>
                                        <th class="border-0"><?= h($mlSupport->translate('Lead/Contact')) ?></th>
                                        <th class="border-0"><?= h($mlSupport->translate('Type')) ?></th>
                                        <th class="border-0"><?= h($mlSupport->translate('AI Tag')) ?></th>
                                        <th class="border-0"><?= h($mlSupport->translate('Assigned Dept')) ?></th>
                                        <th class="border-0"><?= h($mlSupport->translate('Time')) ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $interactions = $db->fetchAll("
                                        SELECT ci.*, d.name as dept_name, ir.status as routing_status
                                        FROM communication_interactions ci
                                        LEFT JOIN interaction_routing ir ON ci.id = ir.interaction_id
                                        LEFT JOIN departments d ON ir.department_id = d.id
                                        ORDER BY ci.created_at DESC LIMIT 10
                                    ");
                                    foreach ($interactions as $row):
                                    ?>
                                    <tr>
                                        <td>
                                            <span class="badge rounded-pill bg-light text-dark px-3">
                                                <i class="fab fa-<?= h($row['channel']) ?> me-1"></i> <?= h(strtoupper($row['channel'])) ?>
                                            </span>
                                        </td>
                                        <td><?= h($mlSupport->translate('Lead')) ?> #<?= h($row['lead_id'] ?: $mlSupport->translate('Unknown')) ?></td>
                                        <td><span class="text-uppercase small fw-bold"><?= h($row['interaction_type']) ?></span></td>
                                        <td><span class="badge bg-<?= ($row['tag'] == 'investment') ? 'success' : 'info' ?> rounded-pill px-3"><?= h(strtoupper($row['tag'])) ?></span></td>
                                        <td><?= $row['dept_name'] ? h($row['dept_name']) : '<span class="text-muted small italic">' . h($mlSupport->translate('Routing...')) . '</span>' ?></td>
                                        <td><?= h(date('H:i', strtotime($row['created_at']))) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Departmental Performance -->
            <div class="col-md-4">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white border-bottom-0 py-3">
                        <h4 class="card-title mb-0 fw-bold text-primary">
                            <i class="fas fa-users-cog me-2"></i><?= h($mlSupport->translate('Department Load')) ?>
                        </h4>
                    </div>
                    <div class="card-body">
                        <?php foreach ($deptWise as $dept): ?>
                        <div class="mb-4">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="fw-medium text-dark"><?= h($dept['name']) ?></span>
                                <span class="badge bg-light text-primary"><?= h($dept['count']) ?> <?= h($mlSupport->translate('cases')) ?></span>
                            </div>
                            <div class="progress rounded-pill" style="height: 8px;">
                                <div class="progress-bar bg-primary" role="progressbar" style="width: <?= min(100, $dept['count'] * 10) ?>%" aria-valuenow="<?= min(100, $dept['count'] * 10) ?>" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Closure Documentation -->
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white border-bottom-0 py-3">
                        <h4 class="card-title mb-0 fw-bold text-primary">
                            <i class="fas fa-file-contract me-2"></i><?= h($mlSupport->translate('Business Closure Docs')) ?>
                        </h4>
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">
                            <?php
                            $docs = $db->fetchAll("SELECT * FROM business_documents ORDER BY generated_at DESC LIMIT 5");
                            foreach ($docs as $doc):
                            ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-sm bg-light text-danger rounded me-3 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                        <i class="fas fa-file-pdf"></i>
                                    </div>
                                    <span class="fw-medium text-uppercase small"><?= h($doc['doc_type']) ?></span>
                                </div>
                                <a href="<?= h($doc['file_path']) ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                    <?= h($mlSupport->translate('View')) ?>
                                </a>
                            </li>
                            <?php endforeach; ?>
                            <?php if (empty($docs)): ?>
                            <li class="list-group-item text-center text-muted py-4">
                                <i class="fas fa-folder-open fa-2x mb-2 d-block opacity-25"></i>
                                <?= h($mlSupport->translate('No documents generated yet.')) ?>
                            </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/admin_footer.php'; ?>
