<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <h4><?= htmlspecialchars($title ?? 'Error') ?></h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-danger">
                        <h5 class="alert-heading">An error occurred</h5>
                        <p><?= nl2br(htmlspecialchars($message ?? 'An unknown error occurred.')) ?></p>
                    </div>
                    <p class="mb-0">
                        <a href="/" class="btn btn-primary">Return to Home</a>
                        <a href="/admin/login" class="btn btn-secondary">Try Again</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
