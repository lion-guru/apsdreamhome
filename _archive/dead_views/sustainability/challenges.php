<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= $base ?? BASE_URL ?>"><i class="fas fa-home"></i></a></li>
                    <li class="breadcrumb-item"><a href="<?= $base ?? BASE_URL ?>sustainability">Sustainability</a></li>
                    <li class="breadcrumb-item active">Challenges</li>
                </ol>
            </nav>
            <h1 class="display-5 fw-bold"><i class="fas fa-exclamation-triangle me-3 text-warning"></i><?= ($page_title ?? 'Challenges') ?></h1>
        </div>
    </div>

    <?php $cd = $challenges_data ?? []; $impl_challenges = $cd['implementation_challenges'] ?? []; $market_challenges = $cd['market_challenges'] ?? []; $sc = $cd['supply_chain_issues'] ?? []; ?>

    <?php foreach (['implementation_challenges' => 'Implementation Challenges', 'market_challenges' => 'Market Challenges'] as $section => $title): $items = ${$section} ?? []; ?>
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white border-0"><h5 class="mb-0"><i class="fas fa-<?= $section === 'implementation_challenges' ? 'cogs' : 'store' ?> me-2 text-warning"></i><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></h5></div>
        <div class="card-body aps-cp-card-body">
            <div class="row g-4">
                <?php if (is_array($items)): ?>
                <?php foreach ($items as $key => $item): if (!is_array($item)) continue; ?>
                <div class="col-md-4">
                    <div class="border rounded p-3 h-100">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h6><?= ($item['challenge'] ?? ucfirst(str_replace('_', ' ', $key))) ?></h6>
                            <span class="badge bg-<?= (int)($item['success_rate'] ?? 0) >= 80 ? 'success' : ((int)($item['success_rate'] ?? 0) >= 60 ? 'warning' : 'danger') ?>"><?= ($item['success_rate'] ?? '0%') ?></span>
                        </div>
                        <p class="small text-muted mb-0"><strong>Solution:</strong> <?= ($item['solution'] ?? '') ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
                <?php if (empty($items)): ?><div class="col-12"><p class="text-muted text-center py-3">No data available.</p></div><?php endif; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
