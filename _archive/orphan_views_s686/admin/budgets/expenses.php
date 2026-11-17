<?php
// Budget Expense Tracking / Add Expense View (inline in show.php, this is a helper view for standalone expense management if needed)
// This is kept minimal — the main expense tracking is handled within the budget show page.
$budget = $budget ?? ['id' => 0, 'budget_name' => 'Unknown'];
?>
<div class="card">
    <div class="card-header"><strong>Expense Tracking for Budget: <?= htmlspecialchars($budget['budget_name']) ?></strong></div>
    <div class="card-body">
        <p class="text-muted">Expenses are tracked directly within the budget detail page. This is a reference view.</p>
        <a href="<?= BASE_URL ?>/admin/budgets/<?= $budget['id'] ?? '' ?>" class="btn btn-primary">Back to Budget</a>
    </div>
</div>