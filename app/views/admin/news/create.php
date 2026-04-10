<?php
$page_title = 'Create News Article';
$active_page = 'news';
include APP_PATH . '/views/admin/layouts/header.php';
?>

<div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Create News Article</h1>
    <a href="/admin/news" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left"></i> Back
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form id="newsForm">
            <div class="row">
                <div class="col-md-8">
                    <div class="mb-3">
                        <label for="title" class="form-label">Title *</label>
                        <input type="text" class="form-control" id="title" name="title" required>
                    </div>

                    <div class="mb-3">
                        <label for="summary" class="form-label">Summary *</label>
                        <textarea class="form-control" id="summary" name="summary" rows="2" required></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="content" class="form-label">Content *</label>
                        <textarea class="form-control summernote" id="content" name="content" rows="10" required></textarea>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card bg-light mb-3">
                        <div class="card-body">
                            <h6>Publish Settings</h6>

                            <div class="mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="draft">Draft</option>
                                    <option value="published">Published</option>
                                    <option value="archived">Archived</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="published_at" class="form-label">Publish Date</label>
                                <input type="datetime-local" class="form-control" id="published_at" name="published_at">
                            </div>

                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="featured" name="featured" value="1">
                                <label class="form-check-label" for="featured">Featured</label>
                            </div>
                        </div>
                    </div>

                    <div class="card bg-light mb-3">
                        <div class="card-body">
                            <h6>Featured Image</h6>
                            <input type="file" class="form-control" id="image" name="image" accept="image/*">
                            <div id="imagePreview" class="mt-2 text-center"></div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-save"></i> Create Article
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

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
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating...';

        fetch('/admin/news', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = '/admin/news';
                } else {
                    alert(data.error || 'Failed to create article');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="fas fa-save"></i> Create Article';
                }
            })
            .catch(error => {
                alert('An error occurred');
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-save"></i> Create Article';
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

<?php include APP_PATH . '/views/admin/layouts/footer.php'; ?>