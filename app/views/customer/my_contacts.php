<?php
$contacts = $contacts ?? [];
$counts = $counts ?? ['new' => 0, 'contacted' => 0, 'closed' => 0];
$stages = ['new' => 'New', 'contacted' => 'Contacted', 'closed' => 'Closed'];
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-1">
        <div>
            <h2 class="fw-bold mb-1"><i class="fas fa-address-book me-2"></i><?= __('user_contacts_title', [], 'My Contacts') ?></h2>
            <p class="text-muted mb-0"><?= __('user_contacts_sub', [], 'Your buyers, renters and inquiries — your private pipeline') ?></p>
        </div>
    </div>

    <div class="row g-3 my-3">
        <?php foreach (['new' => 'primary', 'contacted' => 'warning', 'closed' => 'success'] as $k => $color): ?>
        <div class="col-4"><div class="card border-0 shadow-sm"><div class="card-body text-center p-3">
            <div class="fs-3 fw-bold"><?= (int)($counts[$k] ?? 0) ?></div>
            <div class="small text-muted"><?= $stages[$k] ?></div>
        </div></div></div>
        <?php endforeach; ?>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white fw-bold"><i class="fas fa-plus me-2"></i><?= __('user_contacts_add', [], 'Add Contact') ?></div>
        <div class="card-body">
            <form method="POST" action="<?= BASE_URL ?>/user/contacts/store" class="row g-2">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                <div class="col-md-3"><input type="text" name="name" class="form-control" required maxlength="150" placeholder="<?= __('user_contacts_name', [], 'Full name *') ?>"></div>
                <div class="col-md-3"><input type="text" name="phone" class="form-control" required maxlength="20" placeholder="<?= __('user_contacts_phone', [], 'Mobile *') ?>"></div>
                <div class="col-md-3"><input type="email" name="email" class="form-control" maxlength="150" placeholder="<?= __('user_contacts_email', [], 'Email') ?>"></div>
                <div class="col-md-3"><input type="text" name="property_ref" class="form-control" maxlength="150" placeholder="<?= __('user_contacts_prop', [], 'Property ref') ?>"></div>
                <div class="col-12"><input type="text" name="notes" class="form-control" placeholder="<?= __('user_contacts_notes', [], 'Notes') ?>"></div>
                <div class="col-12"><button type="submit" class="btn btn-primary"><i class="fas fa-plus me-1"></i><?= __('user_contacts_add_btn', [], 'Add Contact') ?></button></div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <?php if (empty($contacts)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-address-book fa-3x mb-3"></i>
                    <h6><?= __('user_contacts_empty', [], 'No contacts yet') ?></h6>
                    <p class="small mb-0"><?= __('user_contacts_empty_desc', [], 'Add above everyone who asked about your properties.') ?></p>
                </div>
            <?php else: ?>
            <div class="table-responsive"><table class="table table-hover align-middle mb-0">
                <thead class="bg-light"><tr><th><?= __('user_contacts_th_who', [], 'Who') ?></th><th><?= __('user_contacts_th_stage', [], 'Stage') ?></th><th><?= __('user_contacts_th_next', [], 'Next') ?></th></tr></thead>
                <tbody>
                <?php foreach ($contacts as $c): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($c['name'] ?? '') ?></strong><br><small class="text-muted"><i class="fas fa-phone me-1"></i><?= htmlspecialchars($c['phone'] ?? '') ?><?= !empty($c['email']) ? ' · ' . htmlspecialchars($c['email']) : '' ?></small><?= !empty($c['property_ref']) ? '<br><small class="text-muted">' . htmlspecialchars($c['property_ref']) . '</small>' : '' ?><?= !empty($c['notes']) ? '<br><small>' . htmlspecialchars(mb_substr($c['notes'], 0, 80)) . '</small>' : '' ?></td>
                        <td><span class="badge bg-<?= $c['stage'] === 'closed' ? 'success' : ($c['stage'] === 'contacted' ? 'warning text-dark' : 'primary') ?>"><?= $stages[$c['stage']] ?? $c['stage'] ?></span></td>
                        <td>
                            <form method="POST" action="<?= BASE_URL ?>/user/contacts/<?= (int)$c['id'] ?>/stage" class="d-flex gap-1">
                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                <select name="stage" class="form-select form-select-sm" style="width:auto">
                                    <?php foreach ($stages as $k => $label): ?>
                                    <option value="<?= $k ?>" <?= ($c['stage'] ?? '') === $k ? 'selected' : '' ?>><?= $label ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" class="btn btn-sm btn-outline-primary"><i class="fas fa-check"></i></button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table></div>
            <?php endif; ?>
        </div>
    </div>
</div>
