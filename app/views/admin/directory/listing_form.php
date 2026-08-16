<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="fas fa-list me-2"></i><?= $listing ? 'Edit' : 'New' ?> Listing</h1>
        <a href="<?= BASE_URL ?>/admin/directory/listings" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body aps-cp-card-body">
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Business Name *</label>
                        <input type="text" name="business_name" class="form-control" value="<?= htmlspecialchars($listing['business_name'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Owner Name</label>
                        <input type="text" name="owner_name" class="form-control" value="<?= htmlspecialchars($listing['owner_name'] ?? '') ?>">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Category *</label>
                        <select name="category_id" class="form-control" required>
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $c): ?>
                                <option value="<?= $c['id'] ?>" <?= ($listing['category_id'] ?? '') == $c['id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['name'] ?? '') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Experience (years)</label>
                        <input type="number" name="experience_years" class="form-control" value="<?= (int)($listing['experience_years'] ?? 0) ?>">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Price Range</label>
                        <select name="price_range" class="form-control">
                            <option value="">Select</option>
                            <option value="budget" <?= ($listing['price_range'] ?? '') === 'budget' ? 'selected' : '' ?>>Budget</option>
                            <option value="mid-range" <?= ($listing['price_range'] ?? '') === 'mid-range' ? 'selected' : '' ?>>Mid-Range</option>
                            <option value="premium" <?= ($listing['price_range'] ?? '') === 'premium' ? 'selected' : '' ?>>Premium</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="4"><?= htmlspecialchars($listing['description'] ?? '') ?></textarea>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($listing['phone'] ?? '') ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">WhatsApp</label>
                        <input type="text" name="whatsapp" class="form-control" value="<?= htmlspecialchars($listing['whatsapp'] ?? '') ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($listing['email'] ?? '') ?>">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Website</label>
                        <input type="url" name="website" class="form-control" value="<?= htmlspecialchars($listing['website'] ?? '') ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Photo URL</label>
                        <input type="text" name="photo" class="form-control" value="<?= htmlspecialchars($listing['photo'] ?? '') ?>" placeholder="https://example.com/photo.jpg">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">City</label>
                        <input type="text" name="city" class="form-control" value="<?= htmlspecialchars($listing['city'] ?? '') ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Pincode</label>
                        <input type="text" name="pincode" class="form-control" value="<?= htmlspecialchars($listing['pincode'] ?? '') ?>">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Address</label>
                    <textarea name="address" class="form-control" rows="2"><?= htmlspecialchars($listing['address'] ?? '') ?></textarea>
                </div>

                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-control">
                            <option value="pending" <?= ($listing['status'] ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="approved" <?= ($listing['status'] ?? '') === 'approved' ? 'selected' : '' ?>>Approved</option>
                            <option value="rejected" <?= ($listing['status'] ?? '') === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3 d-flex align-items-end">
                        <div class="form-check me-3">
                            <input type="checkbox" name="is_verified" value="1" class="form-check-input" id="iv" <?= ($listing['is_verified'] ?? 0) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="iv">Verified</label>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" name="is_featured" value="1" class="form-check-input" id="ifeat" <?= ($listing['is_featured'] ?? 0) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="ifeat">Featured</label>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Save Listing</button>
            </form>
        </div>
    </div>
</div>
