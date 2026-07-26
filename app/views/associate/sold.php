<?php
$page_title = $page_title ?? __('assoc_sold_title');
$properties = $properties ?? [];
?>
<div class="container-fluid px-4">
    <h4 class="mb-4"><i class="fas fa-check-circle text-success me-2"></i><?= __('assoc_sold_title') ?></h4>
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <?php if (empty($properties)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-check-circle fa-4x text-muted mb-3"></i>
                    <p class="text-muted"><?= __('assoc_sold_empty') ?></p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th><?= __('assoc_sold_th_title') ?></th>
                                <th><?= __('assoc_sold_th_type') ?></th>
                                <th><?= __('assoc_sold_th_price') ?></th>
                                <th><?= __('assoc_sold_th_date') ?></th>
                                <th><?= __('assoc_sold_th_action') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($properties as $p): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($p['title'] ?? __('assoc_sold_na')); ?></td>
                                    <td><?php echo htmlspecialchars($p['property_type'] ?? __('assoc_sold_na')); ?></td>
                                    <td>₹<?php echo number_format($p['price'] ?? 0); ?></td>
                                    <td><?php echo htmlspecialchars($p['date'] ?? ''); ?></td>
                                    <td>
                                        <a href="<?= BASE_URL ?>/associate/properties/edit/<?= (int)($p['id'] ?? 0) ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye me-1"></i><?= __('assoc_view') ?>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
