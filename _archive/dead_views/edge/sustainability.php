<?php $pageTitle = $page_title ?? 'Edge Computing Sustainability'; ?>
<div class="container-fluid py-4">
    <h4 class="mb-4"><i class="fas fa-leaf me-2"></i><?= htmlspecialchars($pageTitle) ?></h4>
    <?php $sd = $sustainability_data ?? []; ?>
    <div class="row g-3">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent"><h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Energy Efficiency</h5></div>
                <div class="card-body aps-cp-card-body">
                    <div class="table-responsive"><table class="table table-sm table-responsive">
                        <?php foreach (($sd['energy_efficiency'] ?? []) as $k => $v): ?>
                            <tr><th class="w-50 text-capitalize"><?= htmlspecialchars(str_replace('_', ' ', $k)) ?></th><td><?= htmlspecialchars(is_array($v) ? ($v['savings'] ?? $v['reduction'] ?? '-') : $v) ?></td></tr>
                        <?php endforeach; ?>
                    </table></div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent"><h5 class="mb-0"><i class="fas fa-recycle me-2"></i>Resource Optimization</h5></div>
                <div class="card-body aps-cp-card-body">
                    <ul class="list-unstyled mb-0">
                        <?php foreach (($sd['resource_optimization'] ?? []) as $k => $v): ?>
                            <li class="mb-2"><i class="fas fa-check-circle text-success me-1"></i><strong class="text-capitalize"><?= htmlspecialchars(str_replace('_', ' ', $k)) ?>:</strong> <?= htmlspecialchars($v) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent"><h5 class="mb-0"><i class="fas fa-flag me-2"></i>Sustainability Initiatives</h5></div>
                <div class="card-body aps-cp-card-body">
                    <ul class="list-unstyled mb-0">
                        <?php foreach (($sd['sustainability_initiatives'] ?? []) as $k => $v): ?>
                            <li class="mb-2"><i class="fas fa-check-circle text-success me-1"></i><strong class="text-capitalize"><?= htmlspecialchars(str_replace('_', ' ', $k)) ?>:</strong> <?= htmlspecialchars($v) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
