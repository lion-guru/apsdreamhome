<div class="container-fluid py-4">
    <?php $virtual_property = $virtual_property ?? []; $customization_options = $customization_options ?? []; ?>
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= $base ?? BASE_URL ?>"><i class="fas fa-home"></i></a></li>
                    <li class="breadcrumb-item"><a href="<?= $base ?? BASE_URL ?>metaverse">Metaverse</a></li>
                    <li class="breadcrumb-item active">Customize Property</li>
                </ol>
            </nav>
            <h1 class="display-5 fw-bold"><i class="fas fa-paint-roller me-3 text-primary"></i><?= ($page_title ?? 'Customize Property') ?></h1>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <form method="POST" action="<?= $base ?? BASE_URL ?>metaverse/customize-property/<?= ($virtual_property['id'] ?? '') ?>">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                <?php foreach (($customization_options['colors'] ?? []) as $category => $options): ?>
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white border-0">
                        <h5 class="mb-0"><i class="fas fa-palette me-2 text-primary"></i><?= ucfirst($category) ?></h5>
                    </div>
                    <div class="card-body aps-cp-card-body">
                        <div class="row g-2">
                            <?php foreach ($options as $option): ?>
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="colors[<?= htmlspecialchars($category, ENT_QUOTES, 'UTF-8') ?>]" value="<?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?>" id="color_<?= htmlspecialchars($category, ENT_QUOTES, 'UTF-8') ?>_<?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?>">
                                    <label class="form-check-label" for="color_<?= htmlspecialchars($category, ENT_QUOTES, 'UTF-8') ?>_<?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?></label>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>

                <?php foreach (($customization_options['furniture'] ?? []) as $room => $items): ?>
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white border-0">
                        <h5 class="mb-0"><i class="fas fa-couch me-2 text-primary"></i><?= ucfirst(str_replace('_', ' ', $room)) ?> Furniture</h5>
                    </div>
                    <div class="card-body aps-cp-card-body">
                        <div class="row g-2">
                            <?php foreach ($items as $item): ?>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="furniture[<?= htmlspecialchars($room, ENT_QUOTES, 'UTF-8') ?>][]" value="<?= htmlspecialchars($item, ENT_QUOTES, 'UTF-8') ?>" id="furn_<?= htmlspecialchars($room, ENT_QUOTES, 'UTF-8') ?>_<?= htmlspecialchars($item, ENT_QUOTES, 'UTF-8') ?>">
                                    <label class="form-check-label" for="furn_<?= htmlspecialchars($room, ENT_QUOTES, 'UTF-8') ?>_<?= htmlspecialchars($item, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($item, ENT_QUOTES, 'UTF-8') ?></label>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>

                <?php foreach (($customization_options['lighting'] ?? []) as $type => $options): ?>
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white border-0">
                        <h5 class="mb-0"><i class="fas fa-lightbulb me-2 text-primary"></i><?= ucfirst($type) ?> Lighting</h5>
                    </div>
                    <div class="card-body aps-cp-card-body">
                        <div class="row g-2">
                            <?php foreach ($options as $option): ?>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="lighting[<?= htmlspecialchars($type, ENT_QUOTES, 'UTF-8') ?>]" value="<?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?>" id="lt_<?= htmlspecialchars($type, ENT_QUOTES, 'UTF-8') ?>_<?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?>">
                                    <label class="form-check-label" for="lt_<?= htmlspecialchars($type, ENT_QUOTES, 'UTF-8') ?>_<?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?></label>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>

                <?php foreach (($customization_options['decor'] ?? []) as $category => $options): ?>
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white border-0">
                        <h5 class="mb-0"><i class="fas fa-paint-brush me-2 text-primary"></i><?= ucfirst($category) ?></h5>
                    </div>
                    <div class="card-body aps-cp-card-body">
                        <div class="row g-2">
                            <?php foreach ($options as $option): ?>
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="decor[<?= htmlspecialchars($category, ENT_QUOTES, 'UTF-8') ?>][]" value="<?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?>" id="decor_<?= htmlspecialchars($category, ENT_QUOTES, 'UTF-8') ?>_<?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?>">
                                    <label class="form-check-label" for="decor_<?= htmlspecialchars($category, ENT_QUOTES, 'UTF-8') ?>_<?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?></label>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>

                <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-save me-2"></i>Apply Customization</button>
            </form>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2 text-primary"></i>Property Info</h5>
                </div>
                <div class="card-body aps-cp-card-body">
                    <h5><?= ($virtual_property['name'] ?? 'Property') ?></h5>
                    <p class="text-muted"><?= ($virtual_property['description'] ?? '') ?></p>
                    <hr>
                    <div class="d-flex justify-content-between mb-2"><span>Type</span><strong><?= ucfirst($virtual_property['property_type'] ?? 'N/A') ?></strong></div>
                    <div class="d-flex justify-content-between mb-2"><span>Price</span><strong><?= number_format($virtual_property['base_price'] ?? 0) ?> VRC</strong></div>
                    <div class="d-flex justify-content-between"><span>Area</span><strong><?= ($virtual_property['area_sqft'] ?? 'N/A') ?> sq.ft</strong></div>
                </div>
            </div>
        </div>
    </div>
</div>
