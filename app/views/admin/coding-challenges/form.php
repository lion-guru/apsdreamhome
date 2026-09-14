<?php
$page_title = $page_title ?? 'Coding Challenge Form';
$active_page = 'coding-challenges';
$isEdit = $challenge !== null;

$difficultyOptions = ['easy' => 'Easy', 'medium' => 'Medium', 'hard' => 'Hard'];
?>
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="fas fa-<?= $isEdit ? 'edit' : 'plus' ?>"></i> <?= $isEdit ? 'Edit' : 'Create' ?> Challenge</h1>
    <a href="<?= BASE_URL ?>/admin/coding-challenges" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
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

<form method="POST" action="<?= $isEdit ? BASE_URL . '/admin/coding-challenges/update/' . $challenge['id'] : BASE_URL . '/admin/coding-challenges/store' ?>">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

    <div class="card mb-4">
        <div class="card-header aps-cp-card-header">
            <h5 class="mb-0"><i class="fas fa-info-circle"></i> Basic Information</h5>
        </div>
        <div class="card-body aps-cp-card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Title *</label>
                    <input type="text" class="form-control" name="title" value="<?= htmlspecialchars($challenge['title'] ?? '') ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Slug *</label>
                    <input type="text" class="form-control" name="slug" value="<?= htmlspecialchars($challenge['slug'] ?? '') ?>" required>
                    <div class="form-text">URL-friendly identifier (e.g., two-sum)</div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Difficulty *</label>
                    <select class="form-select" name="difficulty" required>
                        <?php foreach ($difficultyOptions as $key => $label): ?>
                            <option value="<?= $key ?>" <?= ($challenge['difficulty'] ?? 'medium') === $key ? 'selected' : '' ?>><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Category *</label>
                    <select class="form-select" name="category" required>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat ?>" <?= ($challenge['category'] ?? '') === $cat ? 'selected' : '' ?>><?= ucfirst($cat) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Points</label>
                    <input type="number" class="form-control" name="points" value="<?= htmlspecialchars($challenge['points'] ?? 10) ?>" min="1" max="100">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Time Limit (seconds)</label>
                    <input type="number" class="form-control" name="time_limit_seconds" value="<?= htmlspecialchars($challenge['time_limit_seconds'] ?? 2) ?>" min="1" max="300">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Memory Limit (MB)</label>
                    <input type="number" class="form-control" name="memory_limit_mb" value="<?= htmlspecialchars($challenge['memory_limit_mb'] ?? 256) ?>" min="64" max="1024">
                </div>
                <div class="col-md-4">
                    <div class="form-check mt-4">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" <?= ($challenge['is_active'] ?? 1) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="is_active">Active</label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header aps-cp-card-header">
            <h5 class="mb-0"><i class="fas fa-file-alt"></i> Challenge Content</h5>
        </div>
        <div class="card-body aps-cp-card-body">
            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea class="form-control" name="description" rows="3"><?= htmlspecialchars($challenge['description'] ?? '') ?></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">Problem Statement *</label>
                <textarea class="form-control" name="problem_statement" rows="5" required><?= htmlspecialchars($challenge['problem_statement'] ?? '') ?></textarea>
            </div>
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Input Format</label>
                        <textarea class="form-control" name="input_format" rows="4"><?= htmlspecialchars($challenge['input_format'] ?? '') ?></textarea>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Output Format</label>
                        <textarea class="form-control" name="output_format" rows="4"><?= htmlspecialchars($challenge['output_format'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Constraints</label>
                <textarea class="form-control" name="constraints" rows="4"><?= htmlspecialchars($challenge['constraints'] ?? '') ?></textarea>
            </div>
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Sample Input</label>
                        <textarea class="form-control font-monospace" name="sample_input" rows="4"><?= htmlspecialchars($challenge['sample_input'] ?? '') ?></textarea>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Sample Output</label>
                        <textarea class="form-control font-monospace" name="sample_output" rows="4"><?= htmlspecialchars($challenge['sample_output'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Explanation</label>
                <textarea class="form-control" name="explanation" rows="4"><?= htmlspecialchars($challenge['explanation'] ?? '') ?></textarea>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header aps-cp-card-header">
            <h5 class="mb-0"><i class="fas fa-code"></i> Code Templates</h5>
        </div>
        <div class="card-body aps-cp-card-body">
            <div class="mb-3">
                <label class="form-label">Starter Code (visible to users)</label>
                <textarea class="form-control font-monospace" name="starter_code" rows="8"><?= htmlspecialchars($challenge['starter_code'] ?? '') ?></textarea>
                <div class="form-text">Include function signature only. Users will complete the implementation.</div>
            </div>
            <div class="mb-3">
                <label class="form-label">Solution Code (hidden, for validation)</label>
                <textarea class="form-control font-monospace" name="solution_code" rows="10"><?= htmlspecialchars($challenge['solution_code'] ?? '') ?></textarea>
                <div class="form-text">Complete working solution for automated testing.</div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2">
        <a href="<?= BASE_URL ?>/admin/coding-challenges" class="btn btn-secondary"><i class="fas fa-times"></i> Cancel</a>
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> <?= $isEdit ? 'Update' : 'Create' ?> Challenge</button>
    </div>
</form>