<?php
$title = 'Create Ticket';
require_once APP_ROOT . '/views/admin/inc/header.php';
require_once APP_ROOT . '/views/admin/inc/sidebar.php';
?>

<div class="main-content">
    <div class="page-header">
        <div class="container-fluid">
            <h2 class="page-title">Create New Ticket</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?php echo url('admin/dashboard'); ?>">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?php echo url('admin/tickets'); ?>">Tickets</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Create</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="container-fluid">
        <div class="card">
            <div class="card-body">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <form action="<?php echo url('admin/tickets/store'); ?>" method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="subject">Subject <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="subject" name="subject" required>
                    </div>

                    <div class="form-group">
                        <label for="priority">Priority</label>
                        <select class="form-control" id="priority" name="priority">
                            <option value="low">Low</option>
                            <option value="medium" selected>Medium</option>
                            <option value="high">High</option>
                            <option value="urgent">Urgent</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="message">Message <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                    </div>

                    <div class="form-group">
                        <label for="attachment">Attachment</label>
                        <input type="file" class="form-control-file" id="attachment" name="attachment">
                    </div>

                    <div class="form-group mt-4">
                        <button type="submit" class="btn btn-primary">Submit Ticket</button>
                        <a href="<?php echo url('admin/tickets'); ?>" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once APP_ROOT . '/views/admin/inc/footer.php'; ?>