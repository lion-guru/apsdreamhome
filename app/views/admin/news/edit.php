<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title ?? 'Edit News Article') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <nav class="col-md-3 col-lg-2 d-md-block bg-light sidebar">
                <div class="position-sticky pt-3">
                    <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
                        <span>Admin Panel</span>
                    </h6>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="/admin/dashboard">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/admin/news">
                                <i class="fas fa-newspaper"></i> News
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Edit News Article</h1>
                    <a href="/admin/news" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                </div>

                <?php if (empty($news)): ?>
                    <div class="alert alert-danger">Article not found.</div>
                <?php else: ?>
                    <div class="card">
                        <div class="card-body">
                            <form id="newsForm">
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="mb-3">
                                            <label for="title" class="form-label">Title *</label>
                                            <input type="text" class="form-control" id="title" name="title" value="<?= htmlspecialchars($news['title']) ?>" required>
                                        </div>

                                        <div class="mb-3">
                                            <label for="summary" class="form-label">Summary *</label>
                                            <textarea class="form-control" id="summary" name="summary" rows="2" required><?= htmlspecialchars($news['summary']) ?></textarea>
                                        </div>

                                        <div class="mb-3">
                                            <label for="content" class="form-label">Content *</label>
                                            <textarea class="form-control summernote" id="content" name="content" rows="10" required><?= htmlspecialchars($news['content']) ?></textarea>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="card bg-light mb-3">
                                            <div class="card-body">
                                                <h6>Publish Settings</h6>
                                                
                                                <div class="mb-3">
                                                    <label for="status" class="form-label">Status</label>
                                                    <select class="form-select" id="status" name="status">
                                                        <option value="draft" <?= $news['status'] === 'draft' ? 'selected' : '' ?>>Draft</option>
                                                        <option value="published" <?= $news['status'] === 'published' ? 'selected' : '' ?>>Published</option>
                                                        <option value="archived" <?= $news['status'] === 'archived' ? 'selected' : '' ?>>Archived</option>
                                                    </select>
                                                </div>

                                                <div class="mb-3">
                                                    <label for="published_at" class="form-label">Publish Date</label>
                                                    <input type="datetime-local" class="form-control" id="published_at" name="published_at" value="<?= $news['published_at'] ? date('Y-m-d\TH:i', strtotime($news['published_at'])) : '' ?>">
                                                </div>

                                                <div class="form-check">
                                                    <input type="checkbox" class="form-check-input" id="featured" name="featured" value="1" <?= $news['featured'] ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="featured">Featured</label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="card bg-light mb-3">
                                            <div class="card-body">
                                                <h6>Featured Image</h6>
                                                <?php if (!empty($news['image'])): ?>
                                                    <div class="mb-2 text-center">
                                                        <img src="/<?= $news['image'] ?>" class="img-fluid rounded" style="max-height: 150px;">
                                                    </div>
                                                <?php endif; ?>
                                                <input type="file" class="form-control" id="image" name="image" accept="image/*">
                                                <small class="text-muted">Leave empty to keep current</small>
                                                <div id="imagePreview" class="mt-2 text-center"></div>
                                            </div>
                                        </div>

                                        <button type="submit" class="btn btn-primary w-100">
                                            <i class="fas fa-save"></i> Update Article
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.summernote').summernote({
                height: 300,
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'italic', 'underline', 'clear']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['insert', ['link', 'picture']],
                    ['view', ['fullscreen', 'codeview']]
                ]
            });
        });

        document.getElementById('newsForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
            
            fetch('/admin/news/<?= $news['id'] ?>/update', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = '/admin/news';
                } else {
                    alert(data.error || 'Failed to update article');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="fas fa-save"></i> Update Article';
                }
            })
            .catch(error => {
                alert('An error occurred');
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-save"></i> Update Article';
            });
        });

        document.getElementById('image').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('imagePreview').innerHTML = '<img src="' + e.target.result + '" class="img-fluid rounded" style="max-height: 150px;">';
                };
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>
