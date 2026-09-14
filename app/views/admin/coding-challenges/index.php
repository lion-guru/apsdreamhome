<?php
$page_title = $page_title ?? 'Coding Challenges';
$active_page = 'coding-challenges';

$difficultyLabels = [
    'easy' => ['label' => 'Easy', 'class' => 'success'],
    'medium' => ['label' => 'Medium', 'class' => 'warning'],
    'hard' => ['label' => 'Hard', 'class' => 'danger'],
];
?>
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="fas fa-code"></i> Coding Challenges</h1>
    <a href="<?= BASE_URL ?>/admin/coding-challenges/create" class="btn btn-primary"><i class="fas fa-plus"></i> Create Challenge</a>
</div>

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show"><?= htmlspecialchars($_SESSION['success']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>
<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show"><?= htmlspecialchars($_SESSION['error']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<!-- Stats -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white h-100">
            <div class="card-body text-center py-3">
                <h3><?= $stats['total'] ?? 0 ?></h3>
                <p class="mb-0 small">Total Challenges</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white h-100">
            <div class="card-body text-center py-3">
                <h3><?= $stats['easy'] ?? 0 ?></h3>
                <p class="mb-0 small">Easy</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-dark h-100">
            <div class="card-body text-center py-3">
                <h3><?= $stats['medium'] ?? 0 ?></h3>
                <p class="mb-0 small">Medium</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-danger text-white h-100">
            <div class="card-body text-center py-3">
                <h3><?= $stats['hard'] ?? 0 ?></h3>
                <p class="mb-0 small">Hard</p>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="<?= BASE_URL ?>/admin/coding-challenges">
            <div class="row g-2">
                <div class="col-md-4">
                    <input type="text" class="form-control" name="search" placeholder="Search title or slug..." value="<?= htmlspecialchars($search ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="difficulty">
                        <option value="">All Difficulties</option>
                        <?php foreach ($difficultyLabels as $key => $info): ?>
                            <option value="<?= $key ?>" <?= ($difficulty ?? '') === $key ? 'selected' : '' ?>><?= $info['label'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Filter</button>
                    <a href="<?= BASE_URL ?>/admin/coding-challenges" class="btn btn-secondary">Clear</a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Challenges Table -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Slug</th>
                        <th>Category</th>
                        <th>Difficulty</th>
                        <th>Points</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($challenges)): ?>
                        <tr><td colspan="9" class="text-center py-4 text-muted"><i class="fas fa-code fa-2x mb-2"></i><br>No challenges found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($challenges as $ch): ?>
                            <?php $dl = $difficultyLabels[$ch['difficulty']] ?? ['label' => $ch['difficulty'], 'class' => 'secondary']; ?>
                            <tr>
                                <td><?= $ch['id'] ?></td>
                                <td><strong><?= htmlspecialchars($ch['title'] ?? '') ?></strong></td>
                                <td><code><?= htmlspecialchars($ch['slug'] ?? '') ?></code></td>
                                <td><span class="badge bg-light text-dark"><?= htmlspecialchars(ucfirst($ch['category'] ?? '')) ?></span></td>
                                <td><span class="badge bg-<?= $dl['class'] ?>"><?= $dl['label'] ?></span></td>
                                <td><?= $ch['points'] ?? 0 ?></td>
                                <td><?= $ch['is_active'] ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>' ?></td>
                                <td><small><?= date('d M Y', strtotime($ch['created_at'])) ?></small></td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= BASE_URL ?>/admin/coding-challenges/edit/<?= $ch['id'] ?>" class="btn btn-outline-primary" title="Edit"><i class="fas fa-edit"></i></a>
                                        <a href="<?= BASE_URL ?>/admin/coding-challenges/delete/<?= $ch['id'] ?>" class="btn btn-outline-danger" title="Delete" onclick="return confirm('Delete this challenge?')"><i class="fas fa-trash"></i></a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if (($totalPages ?? 1) > 1): ?>
            <nav class="mt-3">
                <ul class="pagination justify-content-center">
                    <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=<?= $page - 1 ?>&difficulty=<?= $difficulty ?? '' ?>&search=<?= $search ?? '' ?>">Previous</a>
                    </li>
                    <?php for ($i = max(1, $page - 3); $i <= min($totalPages, $page + 3); $i++): ?>
                        <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>&difficulty=<?= $difficulty ?? '' ?>&search=<?= $search ?? '' ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=<?= $page + 1 ?>&difficulty=<?= $difficulty ?? '' ?>&search=<?= $search ?? '' ?>">Next</a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</div>