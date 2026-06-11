<?php
/**
 * APS Dream Home - Production Checklist view
 *
 * @var array $checks       Each check has 'key','label','detail','command','howto','status','done'
 * @var array $completed    Session-persisted "I did this" map
 * @var int   $totalCount
 * @var int   $passedCount
 * @var int   $failedCount
 * @var int   $unknownCount
 * @var string $csrf_token
 */
$baseUrl = defined('BASE_URL') ? BASE_URL : '';
$page_title   = $page_title   ?? 'Production Checklist';
$page_heading = $page_heading ?? 'Production Launch Checklist';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-rocket me-2"></i><?= htmlspecialchars($page_heading) ?>
        </h1>
        <div>
            <a href="<?= $baseUrl ?>/admin/dashboard" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>Back to dashboard
            </a>
        </div>
    </div>

    <?php
    // Flash messages
    foreach (['success', 'error', 'warning', 'info'] as $fk) {
        if (!empty($_SESSION[$fk]) || !empty($_SESSION['flash_' . $fk])) {
            $msg = $_SESSION[$fk] ?? $_SESSION['flash_' . $fk];
            $cls = $fk === 'success' ? 'success' : ($fk === 'error' ? 'danger' : $fk);
            echo '<div class="alert alert-' . $cls . ' alert-dismissible fade show" role="alert">'
               . htmlspecialchars((string)$msg)
               . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>'
               . '</div>';
            unset($_SESSION[$fk], $_SESSION['flash_' . $fk]);
        }
    }
    ?>

    <!-- ============== SUMMARY CARDS ============== -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body aps-cp-card-body">
                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total checks</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= (int)$totalCount ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body aps-cp-card-body">
                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Auto-passing</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= (int)$passedCount ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body aps-cp-card-body">
                    <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Auto-failing</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= (int)$failedCount ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-secondary shadow h-100 py-2">
                <div class="card-body aps-cp-card-body">
                    <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">Manual check</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= (int)$unknownCount ?></div>
                </div>
            </div>
        </div>
    </div>

    <p class="text-muted small mb-3">
        <i class="fas fa-info-circle me-1"></i>
        Each card below is one pre-launch check. The <strong>status badge</strong> is a best-effort auto-detect
        (read from <code>.env</code>, PHP modules, filesystem). The "Mark done" button is a sticky session note
        for the operator. For checks marked <em>Manual</em>, run the listed command in your shell and confirm
        the output before flipping DNS.
    </p>

    <!-- ============== CHECKLIST ============== -->
    <div class="row g-3">
        <?php foreach ($checks as $check):
            $status = $check['status'];
            $badgeCls = $status === 'pass' ? 'bg-success' : ($status === 'fail' ? 'bg-danger' : 'bg-secondary');
            $badgeTxt = $status === 'pass' ? 'Auto: pass' : ($status === 'fail' ? 'Auto: fail' : 'Manual');
            $done = !empty($check['done']);
        ?>
            <div class="col-12">
                <div class="card shadow-sm h-100 <?= $done ? 'border-left-success' : '' ?>">
                    <div class="card-body py-3">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h6 class="mb-0 font-weight-bold text-gray-800">
                                <i class="fas fa-<?= $done ? 'check-circle text-success' : 'circle-notch text-muted' ?> me-2"></i>
                                <?= htmlspecialchars($check['label']) ?>
                            </h6>
                            <div class="d-flex gap-2 align-items-center">
                                <span class="badge <?= $badgeCls ?>"><?= htmlspecialchars($badgeTxt) ?></span>
                                <form method="post" action="<?= $baseUrl ?>/admin/production-checklist/mark/<?= urlencode($check['key']) ?>" class="d-inline">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string)$csrf_token) ?>"/>
                                    <button type="submit" class="btn btn-sm <?= $done ? 'btn-success' : 'btn-outline-secondary' ?>">
                                        <i class="fas fa-<?= $done ? 'check' : 'thumbtack' ?> me-1"></i>
                                        <?= $done ? 'Marked done' : 'Mark done' ?>
                                    </button>
                                </form>
                            </div>
                        </div>

                        <p class="small text-muted mb-2"><?= htmlspecialchars($check['detail']) ?></p>

                        <?php if (!empty($check['command'])): ?>
                            <p class="small text-muted mb-1">Run on the server:</p>
                            <pre class="bg-dark text-light p-2 small mb-2 rounded"><code><?= htmlspecialchars($check['command']) ?></code></pre>
                        <?php endif; ?>

                        <?php if (!empty($check['howto'])): ?>
                            <p class="small text-muted mb-0">
                                <i class="fas fa-wrench me-1"></i><strong>How to fix:</strong> <?= htmlspecialchars($check['howto']) ?>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="card bg-light mt-4">
        <div class="card-body small text-muted">
            <i class="fas fa-lightbulb me-1"></i>
            <strong>Tip:</strong> Bookmark this page and run through it again after every deploy. Sticky "Mark done"
            state lives in your session only, so each operator sees their own list.
        </div>
    </div>
</div>
