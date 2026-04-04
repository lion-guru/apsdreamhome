<?php
$property = $data['property'] ?? null;
$property_images = $data['property_images'] ?? [];
$related = $data['related_properties'] ?? [];
?>
<div class="container-fluid py-4">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/">Home</a></li>
            <li class="breadcrumb-item"><a href="/properties">Properties</a></li>
            <li class="breadcrumb-item active"><?php echo htmlspecialchars($property['title'] ?? 'Property'); ?></li>
        </ol>
    </nav>

    <?php if ($property): ?>
        <div class="row">
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div id="propertyCarousel" class="carousel slide" data-bs-ride="carousel">
                        <div class="carousel-inner">
                            <?php
                            $images = !empty($property_images) ? $property_images : [['image_path' => 'https://via.placeholder.com/800x400?text=No+Image']];
                            foreach ($images as $i => $img):
                            ?>
                                <div class="carousel-item <?php echo $i === 0 ? 'active' : ''; ?>">
                                    <img src="<?php echo htmlspecialchars($img['image_path'] ?? $property['image_url'] ?? 'https://via.placeholder.com/800x400'); ?>"
                                        class="d-block w-100" style="height: 400px; object-fit: cover;"
                                        alt="<?php echo htmlspecialchars($property['title'] ?? ''); ?>">
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <?php if (count($images) > 1): ?>
                            <button class="carousel-control-prev" type="button" data-bs-target="#propertyCarousel" data-bs-slide="prev">
                                <span class="carousel-control-prev-icon"></span>
                            </button>
                            <button class="carousel-control-next" type="button" data-bs-target="#propertyCarousel" data-bs-slide="next">
                                <span class="carousel-control-next-icon"></span>
                            </button>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h3 class="card-title mb-1"><?php echo htmlspecialchars($property['title'] ?? $property['name'] ?? 'Property'); ?></h3>
                                <p class="text-muted mb-0">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <?php echo htmlspecialchars($property['location'] ?? $property['address'] ?? 'Location not specified'); ?>
                                </p>
                            </div>
                            <span class="badge bg-success fs-6"><?php echo ucfirst($property['status'] ?? 'available'); ?></span>
                        </div>

                        <div class="row mb-4">
                            <?php if (!empty($property['price'])): ?>
                                <div class="col-md-4 text-center">
                                    <h4 class="text-primary mb-0">₹<?php echo number_format($property['price']); ?></h4>
                                    <small class="text-muted"><?php echo $property['price_type'] ?? 'Total Price'; ?></small>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($property['area']) || !empty($property['size'])): ?>
                                <div class="col-md-4 text-center border-start">
                                    <h5 class="mb-0"><?php echo htmlspecialchars($property['area'] ?? $property['size'] ?? 'N/A'); ?></h5>
                                    <small class="text-muted"><?php echo $property['area_unit'] ?? 'Area'; ?></small>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($property['property_type'])): ?>
                                <div class="col-md-4 text-center border-start">
                                    <h5 class="mb-0"><?php echo ucfirst($property['property_type']); ?></h5>
                                    <small class="text-muted">Property Type</small>
                                </div>
                            <?php endif; ?>
                        </div>

                        <?php if (!empty($property['description'])): ?>
                            <h5>Description</h5>
                            <p><?php echo nl2br(htmlspecialchars($property['description'])); ?></p>
                        <?php endif; ?>

                        <h5 class="mt-4">Property Details</h5>
                        <div class="row">
                            <?php if (!empty($property['bedrooms'])): ?>
                                <div class="col-md-4 mb-2">
                                    <i class="fas fa-bed text-primary me-2"></i> <?php echo $property['bedrooms']; ?> Bedrooms
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($property['bathrooms'])): ?>
                                <div class="col-md-4 mb-2">
                                    <i class="fas fa-bath text-primary me-2"></i> <?php echo $property['bathrooms']; ?> Bathrooms
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($property['parking'])): ?>
                                <div class="col-md-4 mb-2">
                                    <i class="fas fa-car text-primary me-2"></i> <?php echo $property['parking']; ?> Parking
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($property['furnished'])): ?>
                                <div class="col-md-4 mb-2">
                                    <i class="fas fa-couch text-primary me-2"></i> <?php echo ucfirst($property['furnished']); ?> Furnished
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($property['facing'])): ?>
                                <div class="col-md-4 mb-2">
                                    <i class="fas fa-compass text-primary me-2"></i> <?php echo ucfirst($property['facing']); ?> Facing
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($property['floor'])): ?>
                                <div class="col-md-4 mb-2">
                                    <i class="fas fa-building text-primary me-2"></i> Floor: <?php echo $property['floor']; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <?php
                        $amenities = [];
                        if (!empty($property['amenities'])) {
                            if (is_string($property['amenities'])) {
                                $amenities = json_decode($property['amenities'], true) ?? [$property['amenities']];
                            } else {
                                $amenities = (array)$property['amenities'];
                            }
                        }
                        ?>
                        <?php if (!empty($amenities)): ?>
                            <h5 class="mt-4">Amenities</h5>
                            <div class="row">
                                <?php foreach ($amenities as $amenity): ?>
                                    <div class="col-md-4 mb-2">
                                        <i class="fas fa-check-circle text-success me-2"></i> <?php echo htmlspecialchars($amenity); ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($property['rera_number'])): ?>
                            <div class="alert alert-info mt-4">
                                <i class="fas fa-certificate me-2"></i>
                                RERA Registered: <?php echo htmlspecialchars($property['rera_number']); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-phone me-2"></i>Enquire About This Property</h5>
                    </div>
                    <div class="card-body">
                        <form action="/contact" method="POST">
                            <input type="hidden" name="property_id" value="<?php echo $property['id']; ?>">
                            <div class="mb-3">
                                <label class="form-label">Your Name</label>
                                <input type="text" name="name" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Mobile Number</label>
                                <input type="tel" name="mobile" class="form-control" pattern="[0-9]{10}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Message</label>
                                <textarea name="message" class="form-control" rows="3">I'm interested in this property. Please contact me.</textarea>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-paper-plane me-2"></i>Send Enquiry
                            </button>
                        </form>
                        <hr>
                        <div class="d-grid gap-2">
                            <a href="tel:+919277121112" class="btn btn-success">
                                <i class="fas fa-phone me-2"></i>Call Now
                            </a>
                        </div>
                    </div>
                </div>

                <?php if (!empty($related)): ?>
                    <div class="card mt-4">
                        <div class="card-header">
                            <h6 class="mb-0">Related Properties</h6>
                        </div>
                        <ul class="list-group list-group-flush">
                            <?php foreach (array_slice($related, 0, 3) as $rel): ?>
                                <li class="list-group-item">
                                    <a href="/properties/<?php echo $rel['id']; ?>" class="text-decoration-none">
                                        <div class="d-flex">
                                            <img src="<?php echo htmlspecialchars($rel['image_url'] ?? 'https://via.placeholder.com/60'); ?>"
                                                class="rounded me-2" style="width: 60px; height: 45px; object-fit: cover;">
                                            <div>
                                                <small class="fw-bold"><?php echo htmlspecialchars($rel['title'] ?? $rel['name'] ?? 'Property'); ?></small>
                                                <br><small class="text-primary">₹<?php echo number_format($rel['price'] ?? 0); ?></small>
                                            </div>
                                        </div>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-warning">
            <h4>Property Not Found</h4>
            <p>The property you're looking for doesn't exist or has been removed.</p>
            <a href="/properties" class="btn btn-primary">Browse Properties</a>
        </div>
    <?php endif; ?>
</div>