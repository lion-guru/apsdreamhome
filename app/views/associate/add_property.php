<?php
// Add Property Page for Associates
$page_title = $page_title ?? 'Add Property - APS Dream Home';
$page_description = $page_description ?? 'Add a new property listing';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <h1><?php echo htmlspecialchars($page_title); ?></h1>
            <p class="text-muted"><?php echo htmlspecialchars($page_description); ?></p>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="/associate/add-property">
                        <div class="mb-3">
                            <label class="form-label">Property Title</label>
                            <input type="text" class="form-control" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Property Type</label>
                            <select class="form-select" name="property_type" required>
                                <option value="">Select Type</option>
                                <option value="residential">Residential</option>
                                <option value="commercial">Commercial</option>
                                <option value="plot">Plot</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Location</label>
                            <input type="text" class="form-control" name="location" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Price</label>
                            <input type="number" class="form-control" name="price" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="4"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Add Property</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
