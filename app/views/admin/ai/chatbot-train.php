<?php
$training_data = $training_data ?? [];
$categories = $categories ?? [];
$base = defined('BASE_URL') ? BASE_URL : '/' . trim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/');
?>
<div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1">Train Chatbot</h2>
                <p class="text-muted mb-0">Manage Q&A pairs and training data</p>
            </div>
            <a href="<?php echo $base; ?>/admin/chatbot" class="btn btn-outline-secondary">Back</a>
        </div>

        <div class="row g-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white"><h5 class="mb-0">Add Training Data</h5></div>
                    <div class="card-body aps-cp-card-body">
                        <form method="post" action="<?php echo $base; ?>/admin/chatbot/train/store">
                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                            <div class="mb-3">
                                <label class="form-label">Intent</label>
                                <input type="text" name="intent" class="form-control" placeholder="e.g., property_inquiry" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Category</label>
                                <select name="category" class="form-select">
                                    <option value="general">General</option>
                                    <option value="properties">Properties</option>
                                    <option value="booking">Booking</option>
                                    <option value="projects">Projects</option>
                                    <option value="services">Services</option>
                                    <option value="contact">Contact</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Question Pattern</label>
                                <textarea name="question" class="form-control" rows="2" placeholder="e.g., What properties are available?" required></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Answer</label>
                                <textarea name="answer" class="form-control" rows="4" placeholder="e.g., We have plots, houses, and shops available..." required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary w-100"><i class="fas fa-save me-2"></i>Save</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Training Data</h5>
                        <span class="badge bg-primary"><?php echo count($training_data); ?> entries</span>
                    </div>
                    <div class="card-body aps-cp-card-body">
                        <?php if (!empty($training_data)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead><tr><th>Intent</th><th>Category</th><th>Question</th><th>Frequency</th><th>Status</th><th>Actions</th></tr></thead>
                                <tbody>
                                    <?php foreach ($training_data as $item): ?>
                                    <tr>
                                        <td><span class="badge bg-info"><?php echo htmlspecialchars($item['intent'] ?? ''); ?></span></td>
                                        <td><?php echo htmlspecialchars($item['category'] ?? 'general'); ?></td>
                                        <td><?php echo htmlspecialchars(substr($item['question'] ?? '', 0, 60)); ?></td>
                                        <td><?php echo $item['frequency'] ?? 0; ?></td>
                                        <td><?php echo !empty($item['is_active']) ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Inactive</span>'; ?></td>
                                        <td>
                                            <a href="<?php echo $base; ?>/admin/chatbot/train/toggle/<?php echo $item['id']; ?>" class="btn btn-sm btn-outline-warning" title="Toggle"><i class="fas fa-toggle-on"></i></a>
                                            <a href="<?php echo $base; ?>/admin/chatbot/train/delete/<?php echo $item['id']; ?>" class="btn btn-sm btn-outline-danger" data-aps-confirm="Delete this entry?" title="Delete"><i class="fas fa-trash"></i></a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-database fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No training data yet. Add your first Q&A pair.</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

