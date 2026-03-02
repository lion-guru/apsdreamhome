<?php
$amenities = $highlights['amenities'] ?? [];
$highlightItems = $highlights['highlights'] ?? [];
$awards = $highlights['awards'] ?? [];
$usp = $highlights['usp'] ?? [];
?>
<section class="microsite-section py-5" id="microsite-highlights">
    <div class="container">
        <div class="row mb-4">
            <div class="col-lg-8">
                <h2 class="section-title">Project Highlights</h2>
                <?php if (!empty($project['description'])): ?>
                <p class="section-subtitle"><?php echo nl2br(h($project['description'])); ?></p>
                <?php endif; ?>
            </div>
        </div>
        <div class="row g-4">
            <?php if (!empty($amenities)): ?>
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-body">
                        <h3 class="h5">Amenities</h3>
                        <ul class="list-unstyled microsite-list">
                            <?php foreach ($amenities as $amenity): ?>
                            <li>
                                <i class="fa-solid fa-circle-check text-success me-2"></i>
                                <?php echo h(is_array($amenity) ? ($amenity['label'] ?? $amenity['name'] ?? '') : $amenity); ?>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($highlightItems)): ?>
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-body">
                        <h3 class="h5">Key Highlights</h3>
                        <ul class="list-unstyled microsite-list">
                            <?php foreach ($highlightItems as $item): ?>
                            <li>
                                <i class="fa-solid fa-star text-warning me-2"></i>
                                <?php echo h(is_array($item) ? ($item['label'] ?? $item['title'] ?? '') : $item); ?>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <?php if (!empty($usp) || !empty($awards)): ?>
        <div class="row g-4 mt-1">
            <?php if (!empty($usp)): ?>
            <div class="col-lg-8">
                <div class="card h-100">
                    <div class="card-body">
                        <h3 class="h5">Why Choose This Project</h3>
                        <div class="row g-3">
                            <?php foreach ($usp as $point): ?>
                            <div class="col-md-6">
                                <div class="microsite-feature">
                                    <i class="fa-solid fa-check-double text-primary me-2"></i>
                                    <span><?php echo h(is_array($point) ? ($point['label'] ?? $point['title'] ?? '') : $point); ?></span>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($awards)): ?>
            <div class="col-lg-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h3 class="h5">Recognitions</h3>
                        <ul class="list-unstyled microsite-list">
                            <?php foreach ($awards as $award): ?>
                            <li>
                                <i class="fa-solid fa-trophy text-warning me-2"></i>
                                <?php echo h(is_array($award) ? ($award['title'] ?? $award['name'] ?? '') : $award); ?>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</section>
