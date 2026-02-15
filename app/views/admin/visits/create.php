<?php
require_once __DIR__ . '/../../includes/admin_header.php';
?>

<div class="admin-header py-4 bg-primary text-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1 class="mb-0 text-white">
                    <i class="fas fa-calendar-plus me-2"></i>
                    Schedule New Visit
                </h1>
                <p class="mb-0 opacity-75">Schedule a property site visit for a customer.</p>
            </div>
            <div class="col-lg-6 text-lg-end">
                <a href="<?php echo BASE_URL; ?>admin/visits" class="btn btn-light">
                    <i class="fas fa-arrow-left me-2"></i>Back to List
                </a>
            </div>
        </div>
    </div>
</div>

<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        <form action="<?php echo BASE_URL; ?>admin/visits" method="POST">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="customer_id" class="form-label">Select Customer</label>
                                    <select class="form-select" id="customer_id" name="customer_id" required>
                                        <option value="">Choose customer...</option>
                                        <?php if (isset($customers)): ?>
                                            <?php foreach ($customers as $customer): ?>
                                                <option value="<?php echo $customer['id']; ?>"><?php echo htmlspecialchars($customer['name']); ?></option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="property_id" class="form-label">Select Property</label>
                                    <select class="form-select" id="property_id" name="property_id" required>
                                        <option value="">Choose property...</option>
                                        <?php if (isset($properties)): ?>
                                            <?php foreach ($properties as $property): ?>
                                                <option value="<?php echo $property['id']; ?>"><?php echo htmlspecialchars($property['title']); ?></option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="visit_date" class="form-label">Visit Date & Time</label>
                                    <input type="datetime-local" class="form-control" id="visit_date" name="visit_date" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="visit_type" class="form-label">Visit Type</label>
                                    <select class="form-select" id="visit_type" name="visit_type" required>
                                        <option value="site_visit">Site Visit</option>
                                        <option value="virtual_tour">Virtual Tour</option>
                                        <option value="office_meeting">Office Meeting</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label for="notes" class="form-label">Additional Notes</label>
                                    <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Any special instructions or notes..."></textarea>
                                </div>
                                <div class="col-12 mt-4">
                                    <hr>
                                    <div class="d-flex justify-content-end gap-2">
                                        <button type="reset" class="btn btn-outline-secondary">Reset Form</button>
                                        <button type="submit" class="btn btn-primary px-4">Schedule Visit</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
require_once __DIR__ . '/../../includes/admin_footer.php';
?>
