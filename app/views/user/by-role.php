<?php
$page_title = $page_title ?? 'Users by Role';
$users = $users ?? [];
$role = $role ?? '';
$total_count = $total_count ?? 0;
?>
<div class="container-fluid py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-users me-2"></i>Users by Role: <?= htmlspecialchars(ucfirst($role)) ?></h2>
    <span class="badge bg-primary fs-6"><?= $total_count ?> users</span>
  </div>

  <?php if (empty($users)): ?>
    <div class="alert alert-info">
      <i class="fas fa-info-circle me-2"></i>No users found with role "<?= htmlspecialchars($role) ?>".
    </div>
  <?php else: ?>
    <div class="table-responsive">
      <table class="table table-striped table-hover">
        <thead class="table-dark">
          <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Status</th>
            <th>Joined</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($users as $u): ?>
          <tr>
            <td><?= (int)$u['id'] ?></td>
            <td><?= htmlspecialchars($u['name'] ?? '') ?></td>
            <td><?= htmlspecialchars($u['email'] ?? '') ?></td>
            <td><?= htmlspecialchars($u['phone'] ?? '') ?></td>
            <td>
              <span class="badge bg-<?= ($u['is_active'] ?? 0) ? 'success' : 'secondary' ?>">
                <?= ($u['is_active'] ?? 0) ? 'Active' : 'Inactive' ?>
              </span>
            </td>
            <td><?= date('d M Y', strtotime($u['created_at'] ?? 'now')) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>

  <a href="<?= BASE_URL ?>/admin/users" class="btn btn-outline-secondary mt-3">
    <i class="fas fa-arrow-left me-2"></i>Back to Users
  </a>
</div>
