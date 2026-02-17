<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Manage Careers</h1>
        <a href="/admin/careers/create" class="btn btn-primary">
            <i class="fas fa-plus"></i> Post New Job
        </a>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= $_SESSION['success']; unset($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= $_SESSION['error']; unset($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Job Listings</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="careersTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Type</th>
                            <th>Location</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($careers)): ?>
                            <?php foreach ($careers as $item): ?>
                                <tr>
                                    <td><?= htmlspecialchars($item->title) ?></td>
                                    <td><?= htmlspecialchars($item->type) ?></td>
                                    <td><?= htmlspecialchars($item->location) ?></td>
                                    <td>
                                        <span class="badge bg-<?= $item->status == 'active' ? 'success' : 'secondary' ?>">
                                            <?= ucfirst($item->status) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="/admin/careers/edit/<?= $item->id ?>" class="btn btn-sm btn-info" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="/admin/careers/applications/<?= $item->id ?>" class="btn btn-sm btn-warning" title="View Applications">
                                            <i class="fas fa-users"></i>
                                        </a>
                                        <form action="/admin/careers/delete/<?= $item->id ?>" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this job?');">
                                            <input type="hidden" name="csrf_token" value="<?= $this->getCsrfToken() ?>">
                                            <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center">No jobs found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#careersTable').DataTable();
    });
</script>
