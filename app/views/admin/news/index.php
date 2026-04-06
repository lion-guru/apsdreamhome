<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'News Management'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="/admin/dashboard">APS Dream Home Admin</a>
            <a class="btn btn-outline-light btn-sm" href="/admin/logout">Logout</a>
        </div>
    </nav>
    
    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-md-2">
                <div class="list-group">
                    <a href="/admin/dashboard" class="list-group-item list-group-item-action">
                        <i class="fas fa-home"></i> Dashboard
                    </a>
                    <a href="/admin/properties" class="list-group-item list-group-item-action">
                        <i class="fas fa-building"></i> Properties
                    </a>
                    <a href="/admin/sites" class="list-group-item list-group-item-action">
                        <i class="fas fa-map-marker"></i> Sites
                    </a>
                    <a href="/admin/news" class="list-group-item list-group-item-action active">
                        <i class="fas fa-newspaper"></i> News
                    </a>
                    <a href="/admin/testimonials" class="list-group-item list-group-item-action">
                        <i class="fas fa-quote-left"></i> Testimonials
                    </a>
                    <a href="/admin/settings" class="list-group-item list-group-item-action">
                        <i class="fas fa-cog"></i> Settings
                    </a>
                </div>
            </div>
            
            <div class="col-md-10">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1><i class="fas fa-newspaper me-2"></i>News & Blog Management</h1>
                    <a href="/admin/news/create" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i> Add New Article
                    </a>
                </div>

                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-4">
                                <input type="text" name="search" class="form-control" placeholder="Search articles..." value="<?php echo htmlspecialchars($filters['search'] ?? ''); ?>">
                            </div>
                            <div class="col-md-3">
                                <select name="status" class="form-select">
                                    <option value="">All Status</option>
                                    <option value="published" <?php echo ($filters['status'] ?? '') === 'published' ? 'selected' : ''; ?>>Published</option>
                                    <option value="draft" <?php echo ($filters['status'] ?? '') === 'draft' ? 'selected' : ''; ?>>Draft</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> Search
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($news)): ?>
                                        <tr>
                                            <td colspan="3" class="text-center py-4">
                                                <i class="fas fa-newspaper fa-3x text-muted mb-3"></i>
                                                <p>No news articles found. <a href="/admin/news/create">Create one</a></p>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($news as $article): ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($article['title']); ?></strong>
                                                    <br><small class="text-muted"><?php echo htmlspecialchars(substr($article['summary'] ?? '', 0, 80)); ?>...</small>
                                                </td>
                                                <td><?php echo date('M d, Y', strtotime($article['created_at'] ?? $article['date'])); ?></td>
                                                <td>
                                                    <a href="/admin/news/<?php echo $article['id']; ?>/edit" class="btn btn-sm btn-primary">
                                                        <i class="fas fa-edit"></i> Edit
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
