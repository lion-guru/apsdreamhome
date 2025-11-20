<?php
// post-property.php: Public property posting form for APS Dream Homes
session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/functions/common-functions.php';
$page_title = "Post Your Property - APS Dream Homes";
$meta_description = "Post your property for sale or rent on APS Dream Homes and reach thousands of buyers and renters instantly.";
$additional_css = '<link rel="stylesheet" href="/assets/css/homepage-modern.css?v=1.0">';
$additional_js = '';
require_once __DIR__ . '/includes/templates/dynamic_header.php';
?>

<section class="hero-section" style="padding:32px 0 18px 0;">
    <h1>Post Your Property</h1>
    <p>Fill in the details below to list your property for sale or rent. Your property will be visible to thousands of users after admin approval.</p>
</section>

<section>
    <div class="container" style="max-width:600px; margin:auto;">
        <form method="post" action="/submit-property.php" enctype="multipart/form-data" class="card shadow p-4 rounded-4 bg-white">
            <div class="mb-3">
                <label for="title" class="form-label fw-bold">Property Title</label>
                <input type="text" class="form-control" id="title" name="title" required maxlength="100">
            </div>
            <div class="mb-3">
                <label for="type" class="form-label fw-bold">Property Type</label>
                <select class="form-select" id="type" name="type" required>
                    <option value="">Select</option>
                    <option value="residential">Residential</option>
                    <option value="commercial">Commercial</option>
                    <option value="plot">Plot</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="mode" class="form-label fw-bold">Listing For</label>
                <select class="form-select" id="mode" name="mode" required>
                    <option value="">Select</option>
                    <option value="sale">Sale</option>
                    <option value="rent">Rent</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="location" class="form-label fw-bold">Location</label>
                <input type="text" class="form-control" id="location" name="location" required maxlength="100">
            </div>
            <div class="mb-3">
                <label for="price" class="form-label fw-bold">Price (₹)</label>
                <input type="number" class="form-control" id="price" name="price" required min="0">
            </div>
            <div class="mb-3">
                <label for="bedrooms" class="form-label fw-bold">Bedrooms</label>
                <input type="number" class="form-control" id="bedrooms" name="bedrooms" min="0">
            </div>
            <div class="mb-3">
                <label for="bathrooms" class="form-label fw-bold">Bathrooms</label>
                <input type="number" class="form-control" id="bathrooms" name="bathrooms" min="0">
            </div>
            <div class="mb-3">
                <label for="area" class="form-label fw-bold">Area (sq.ft)</label>
                <input type="number" class="form-control" id="area" name="area" min="0">
            </div>
            <div class="mb-3">
                <label for="description" class="form-label fw-bold">Description</label>
                <textarea class="form-control" id="description" name="description" rows="4" maxlength="500"></textarea>
            </div>
            <div class="mb-3">
                <label for="images" class="form-label fw-bold">Property Images</label>
                <input type="file" class="form-control" id="images" name="images[]" accept="image/*" multiple required>
            </div>
            <div class="mb-3">
                <label for="contact_name" class="form-label fw-bold">Your Name</label>
                <input type="text" class="form-control" id="contact_name" name="contact_name" required maxlength="80">
            </div>
            <div class="mb-3">
                <label for="contact_phone" class="form-label fw-bold">Contact Number</label>
                <input type="text" class="form-control" id="contact_phone" name="contact_phone" required maxlength="15">
            </div>
            <div class="mb-3">
                <label for="contact_email" class="form-label fw-bold">Email</label>
                <input type="email" class="form-control" id="contact_email" name="contact_email" required maxlength="100">
            </div>
            <div class="mb-3 text-center">
                <button type="submit" class="btn btn-primary btn-lg rounded-pill px-5">Submit Property</button>
            </div>
            <div class="text-muted small text-center">After admin approval, your property will be live on the website.</div>
        </form>
    </div>
</section>

<footer style="margin:40px 0 0 0; padding:24px 0; background:#2a5298; color:#fff; text-align:center;">
    &copy; <?php echo date('Y'); ?> APS Dream Homes. All rights reserved.
</footer>
