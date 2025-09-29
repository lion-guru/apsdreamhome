<?php include '../app/views/includes/header.php'; ?>

<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Home</a></li>
                    <li class="breadcrumb-item"><a href="/properties">Properties</a></li>
                    <li class="breadcrumb-item active"><?php echo htmlspecialchars($property['title']); ?></li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <h1 class="card-title mb-3"><?php echo htmlspecialchars($property['title']); ?></h1>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <p class="mb-2">
                                <i class="fas fa-map-marker-alt text-primary"></i>
                                <strong>Location:</strong> <?php echo htmlspecialchars($property['location']); ?>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-2">
                                <i class="fas fa-tag text-success"></i>
                                <strong>Price:</strong> ₹<?php echo number_format($property['price']); ?>
                            </p>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <p class="mb-2">
                                <i class="fas fa-home"></i>
                                <strong>Type:</strong> <?php echo ucfirst(htmlspecialchars($property['type'])); ?>
                            </p>
                        </div>
                        <?php if (!empty($property['bedrooms'])): ?>
                            <div class="col-md-6">
                                <p class="mb-2">
                                    <i class="fas fa-bed"></i>
                                    <strong>Bedrooms:</strong> <?php echo htmlspecialchars($property['bedrooms']); ?>
                                </p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="row mb-4">
                        <?php if (!empty($property['bathrooms'])): ?>
                            <div class="col-md-6">
                                <p class="mb-2">
                                    <i class="fas fa-bath"></i>
                                    <strong>Bathrooms:</strong> <?php echo htmlspecialchars($property['bathrooms']); ?>
                                </p>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($property['area'])): ?>
                            <div class="col-md-6">
                                <p class="mb-2">
                                    <i class="fas fa-ruler-combined"></i>
                                    <strong>Area:</strong> <?php echo htmlspecialchars($property['area']); ?> sqft
                                </p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <h4 class="mb-3">Description</h4>
                    <p class="card-text"><?php echo nl2br(htmlspecialchars($property['description'])); ?></p>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Contact Agent</h5>
                </div>
                <div class="card-body">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <form action="/properties/<?php echo $property['id']; ?>/contact" method="POST">
                            <div class="mb-3">
                                <label for="name" class="form-label">Your Name</label>
                                <input type="text" class="form-control" id="name" name="name" required
                                       value="<?php echo htmlspecialchars($_SESSION['user_name'] ?? ''); ?>">
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Your Email</label>
                                <input type="email" class="form-control" id="email" name="email" required
                                       value="<?php echo htmlspecialchars($_SESSION['user_email'] ?? ''); ?>">
                            </div>
                            <div class="mb-3">
                                <label for="phone" class="form-label">Your Phone</label>
                                <input type="tel" class="form-control" id="phone" name="phone" required
                                       value="<?php echo htmlspecialchars($_SESSION['user_phone'] ?? ''); ?>">
                            </div>
                            <div class="mb-3">
                                <label for="message" class="form-label">Message</label>
                                <textarea class="form-control" id="message" name="message" rows="4"
                                          placeholder="I'm interested in this property..."></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Send Inquiry</button>
                        </form>
                    <?php else: ?>
                        <div class="text-center">
                            <p class="mb-3">Please <a href="/login">login</a> to contact the agent.</p>
                            <a href="/login" class="btn btn-primary">Login</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../app/views/includes/footer.php'; ?>
