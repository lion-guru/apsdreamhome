<?php $pageTitle = 'Edit Property'; ?>
<div class="container-fluid">
    <div class="page-header mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title"><i class="fas fa-edit me-2"></i>Edit Property</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/admin/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="/admin/properties">Properties</a></li>
                    <li class="breadcrumb-item"><a href="/admin/properties/show/<?= $property['id'] ?? 0 ?>"><?= $property['title'] ?? 'Property' ?></a></li>
                    <li class="breadcrumb-item active">Edit</li>
                </ul>
            </div>
        </div>
    </div>
    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <form method="POST" action="/admin/properties/update/<?= $property['id'] ?? 0 ?>" enctype="multipart/form-data">
                <div class="row g-3">
                    <div class="col-md-8"><label class="form-label">Title <span class="text-danger">*</span></label><input type="text" name="title" class="form-control" value="<?= $property['title'] ?? '' ?>" required></div>
                    <div class="col-md-4"><label class="form-label">Type <span class="text-danger">*</span></label><select name="type" class="form-select"><option value="house" <?= ($property['type'] ?? '') === 'house' ? 'selected' : '' ?>>House</option><option value="flat" <?= ($property['type'] ?? '') === 'flat' ? 'selected' : '' ?>>Flat</option><option value="plot" <?= ($property['type'] ?? '') === 'plot' ? 'selected' : '' ?>>Plot</option><option value="shop" <?= ($property['type'] ?? '') === 'shop' ? 'selected' : '' ?>>Shop</option><option value="farmhouse" <?= ($property['type'] ?? '') === 'farmhouse' ? 'selected' : '' ?>>Farmhouse</option></select></div>
                    <div class="col-md-4"><label class="form-label">Price <span class="text-danger">*</span></label><input type="number" name="price" class="form-control" value="<?= $property['price'] ?? '' ?>" required></div>
                    <div class="col-md-4"><label class="form-label">Area (sqft)</label><input type="number" name="area_sqft" class="form-control" value="<?= $property['area_sqft'] ?? '' ?>"></div>
                    <div class="col-md-4"><label class="form-label">Status</label><select name="status" class="form-select"><option value="available" <?= ($property['status'] ?? '') === 'available' ? 'selected' : '' ?>>Available</option><option value="sold" <?= ($property['status'] ?? '') === 'sold' ? 'selected' : '' ?>>Sold</option><option value="pending" <?= ($property['status'] ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option></select></div>
                    <div class="col-md-6"><label class="form-label">Address</label><textarea name="address" class="form-control" rows="2"><?= $property['address'] ?? '' ?></textarea></div>
                    <div class="col-md-3"><label class="form-label">City</label><input type="text" name="city" class="form-control" value="<?= $property['city'] ?? '' ?>"></div>
                    <div class="col-md-3"><label class="form-label">State</label><input type="text" name="state" class="form-control" value="<?= $property['state'] ?? '' ?>"></div>
                    <div class="col-md-6"><label class="form-label">Owner Name</label><input type="text" name="owner_name" class="form-control" value="<?= $property['owner_name'] ?? '' ?>"></div>
                    <div class="col-md-3"><label class="form-label">Owner Phone</label><input type="text" name="owner_phone" class="form-control" value="<?= $property['owner_phone'] ?? '' ?>"></div>
                    <div class="col-md-3"><label class="form-label">Owner Email</label><input type="email" name="owner_email" class="form-control" value="<?= $property['owner_email'] ?? '' ?>"></div>
                    <div class="col-12"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="3"><?= $property['description'] ?? '' ?></textarea></div>
                    <div class="col-12"><button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Update Property</button> <a href="/admin/properties" class="btn btn-secondary">Cancel</a></div>
                </div>
            </form>
        </div>
    </div>
</div>
