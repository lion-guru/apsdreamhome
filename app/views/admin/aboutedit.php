<?php

/**
 * Edit About Content Page (View)
 */

$page_title = $page_title ?? "Edit About Content";

if (!isset($about_data)) {
    echo "<div class='alert alert-danger'>About section not found.</div>";
    return;
}
?>

<div class="page-header">
    <div class="row">
        <div class="col-sm-12">
            <h3 class="page-title"><?php echo $page_title; ?></h3>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?php echo APP_URL; ?>admin/dashboard">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?php echo APP_URL; ?>admin/about">About</a></li>
                <li class="breadcrumb-item active">Edit About Content</li>
            </ul>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card shadow-sm">
            <div class="card-header">
                <h4 class="card-title">Edit About Us Content</h4>
            </div>
            <div class="card-body">
                <?php if (isset($error) && $error) echo "<div class='alert alert-danger'>$error</div>"; ?>

                <form method="post" enctype="multipart/form-data" action="<?php echo APP_URL; ?>admin/about/update/<?php echo (int)$about_data['id']; ?>" class="needs-validation" novalidate>
                    <?php echo getCsrfField(); ?>
                    <input type="hidden" name="id" value="<?php echo (int)$about_data['id']; ?>">
                    <div class="row">
                        <div class="col-xl-12">
                            <div class="form-group row mb-3">
                                <label class="col-lg-2 col-form-label">Title</label>
                                <div class="col-lg-9">
                                    <input type="text" class="form-control" name="title" required value="<?php echo htmlspecialchars($about_data['title']); ?>">
                                    <div class="invalid-feedback">Please enter a title.</div>
                                </div>
                            </div>
                            <div class="form-group row mb-3">
                                <label class="col-lg-2 col-form-label">Content</label>
                                <div class="col-lg-9">
                                    <textarea class="form-control" name="content" rows="10" required><?php echo htmlspecialchars($about_data['content']); ?></textarea>
                                    <div class="invalid-feedback">Please enter content.</div>
                                </div>
                            </div>
                            <div class="form-group row mb-3">
                                <label class="col-lg-2 col-form-label">Image</label>
                                <div class="col-lg-9">
                                    <input class="form-control" name="image" type="file">
                                    <small class="form-text text-muted">Leave blank to keep current image.</small>
                                    <?php if (!empty($about_data['image'])): ?>
                                        <div class="mt-2">
                                            <img src="<?php echo APP_URL; ?>upload/<?php echo htmlspecialchars($about_data['image']); ?>" alt="Current image" class="img-thumbnail" style="max-width: 150px;">
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="text-start mt-4">
                        <button type="submit" class="btn btn-primary" name="update">
                            <i class="fas fa-save me-2"></i> Update
                        </button>
                        <a href="<?php echo APP_URL; ?>admin/about" class="btn btn-secondary ms-2">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>