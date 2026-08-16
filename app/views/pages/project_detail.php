<?php if (!isset($sc)) { $sc = function($k, $d='') { return $GLOBALS['_site_settings_cache'][$k] ?? $d; }; }$phoneRaw = preg_replace('/[^0-9]/', '', $sc('contact_whatsapp', '919277121112')); $phoneDisplay = $sc('contact_phone', '<?= $phoneDisplay ?>'); ?>
<?php
/**
 * Project Detail Page
 * Display individual project/site details
 */
$project = $project ?? null;
$baseUrl = rtrim(BASE_URL, '/');

if ($project) {
    $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $project->site_name));
    $district = strtolower($project->district ?? 'gorakhpur');
    
    // Dynamic image priority
    $heroImg = '/assets/images/projects/placeholder/property.svg';
    if (!empty($project->image)) {
        $heroImg = '/' . ltrim($project->image, '/');
    }
}
?>

<?php if ($project): ?>
<!-- Project Hero -->
<section class="hero-section text-white py-5 position-relative" class="style-598">
    <div class="container">
        <div class="row">
            <div class="col-lg-8">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo $baseUrl; ?>/" class="text-white"><?= __('breadcrumb_home') ?></a></li>
                        <li class="breadcrumb-item"><a href="<?php echo $baseUrl; ?>/company/projects" class="text-white"><?= __('breadcrumb_projects') ?></a></li>
                        <li class="breadcrumb-item text-white active"><?php echo htmlspecialchars($project->site_name); ?></li>
                    </ol>
                </nav>
                <h1 class="display-4 fw-bold mb-3"><?php echo htmlspecialchars($project->site_name); ?></h1>
                <p class="lead mb-3">
                    <i class="fas fa-map-marker-alt me-2"></i>
                    <?php echo htmlspecialchars(($project->location ?? '') . ', ' . ($project->city ?? '')); ?>
                </p>
                <div class="d-flex gap-2 flex-wrap">
                    <span class="badge bg-<?php echo $project->site_type === 'residential' ? 'success' : ($project->site_type === 'commercial' ? 'primary' : 'warning'); ?> fs-6">
                        <?php echo ucfirst($project->site_type ?? 'Residential'); ?>
                    </span>
                    <span class="badge bg-<?php echo $project->status === 'active' ? 'success' : 'secondary'; ?> fs-6">
                        <?php echo $project->status === 'active' ? __('colony_available') : ucfirst($project->status ?? 'Active'); ?>
                    </span>
                    <?php if (!empty($project->total_area)): ?>
                    <span class="badge bg-info fs-6">
                        <i class="fas fa-expand me-1"></i><?php echo htmlspecialchars($project->total_area); ?> Acres
                    </span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Quick Contact Bar -->
<section class="bg-primary text-white py-3">
    <div class="container">
        <div class="row align-items-center text-center">
            <div class="col-md-4 mb-2 mb-md-0">
                <i class="fas fa-phone-alt me-2"></i>
                <a href="tel:<?= $phoneRaw ?>" class="text-white text-decoration-none"><?= $phoneDisplay ?></a>
            </div>
            <div class="col-md-4 mb-2 mb-md-0">
                <i class="fas fa-envelope me-2"></i>
                <a href="mailto:<?= $sc('contact_email', 'info@apsdreamhome.com') ?>" class="text-white text-decoration-none"><?= $sc('contact_email', 'info@apsdreamhome.com') ?></a>
            </div>
            <div class="col-md-4">
                <a href="https://wa.me/<?= $phoneRaw ?>?text=Hi, I'm interested in <?php echo urlencode($project->site_name); ?>" target="_blank" class="btn btn-success">
                    <i class="fab fa-whatsapp me-2"></i><?= __('project_whatsapp_now') ?>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Project Overview -->
<section class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-8">
                <h2 class="mb-4"><i class="fas fa-info-circle text-primary me-2"></i><?= __('project_overview') ?></h2>
                <p class="lead">
                    <?php echo nl2br(htmlspecialchars($project->description ?? 'Premium residential plots with modern infrastructure and excellent amenities located in the heart of ' . ($project->district ?? 'Uttar Pradesh') . '.')); ?>
                </p>
                
                <!-- Key Highlights -->
                <?php
                $highlights = [];
                if (!empty($project->key_highlights)) {
                    $decoded = json_decode($project->key_highlights, true);
                    if (is_array($decoded)) {
                        $highlights = $decoded;
                    }
                }
                ?>
                <?php if (!empty($highlights)): ?>
                <h3 class="mt-5 mb-4"><i class="fas fa-star text-warning me-2"></i><?= __('project_highlights') ?></h3>
                <div class="row g-3 mb-4">
                    <?php
                    $highlightIcons = [
                        'road' => ['icon' => 'fa-road', 'color' => 'primary'],
                        'gated' => ['icon' => 'fa-shield-alt', 'color' => 'success'],
                        'electric' => ['icon' => 'fa-bolt', 'color' => 'warning'],
                        'park' => ['icon' => 'fa-tree', 'color' => 'info'],
                        'water' => ['icon' => 'fa-tint', 'color' => 'info'],
                        'security' => ['icon' => 'fa-video', 'color' => 'danger'],
                        'drainage' => ['icon' => 'fa-water', 'color' => 'primary'],
                        'light' => ['icon' => 'fa-lightbulb', 'color' => 'warning'],
                    ];
                    foreach ($highlights as $highlight):
                        $hlKey = strtolower(preg_replace('/[^a-z]+/', '', $highlight));
                        $hlIcon = 'fa-star';
                        $hlColor = 'primary';
                        foreach ($highlightIcons as $key => $cfg) {
                            if (strpos($hlKey, $key) !== false) {
                                $hlIcon = $cfg['icon'];
                                $hlColor = $cfg['color'];
                                break;
                            }
                        }
                    ?>
                    <div class="col-md-6">
                        <div class="d-flex align-items-center p-3 bg-light rounded">
                            <i class="fas <?= $hlIcon ?> fa-2x text-<?= $hlColor ?> me-3"></i>
                            <div>
                                <h6 class="mb-0"><?php echo htmlspecialchars($highlight); ?></h6>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                
                <!-- Amenities -->
                <?php 
                $amenities = [];
                if (!empty($project->amenities)) {
                    $decoded = json_decode($project->amenities, true);
                    if (is_array($decoded)) {
                        $amenities = $decoded;
                    }
                }
                ?>
                <h3 class="mt-5 mb-4"><i class="fas fa-concierge-bell text-primary me-2"></i><?= __('project_amenities') ?></h3>
                <div class="row">
                    <?php if (!empty($amenities)): ?>
                        <?php foreach ($amenities as $amenity): ?>
                            <div class="col-md-6 mb-3">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-check-circle text-success me-3"></i>
                                    <span><?php echo htmlspecialchars($amenity); ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <?php 
                        $amenitiesList = [];
                        if (!empty($project->amenities)) {
                            $amenitiesList = preg_split('/[\n,]+/', $project->amenities);
                            $amenitiesList = array_map('trim', $amenitiesList);
                            $amenitiesList = array_filter($amenitiesList);
                        }
                        ?>
                        <?php if (!empty($amenitiesList)): ?>
                            <?php foreach ($amenitiesList as $amenity): ?>
                                <div class="col-md-6 mb-3"><i class="fas fa-check-circle text-success me-3"></i><?= htmlspecialchars($amenity) ?></div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="col-md-6 mb-3"><i class="fas fa-check-circle text-success me-3"></i>Wide Roads (30-40 ft)</div>
                            <div class="col-md-6 mb-3"><i class="fas fa-check-circle text-success me-3"></i>24/7 Water Supply</div>
                            <div class="col-md-6 mb-3"><i class="fas fa-check-circle text-success me-3"></i>Underground Electricity</div>
                            <div class="col-md-6 mb-3"><i class="fas fa-check-circle text-success me-3"></i>Green Parks & Gardens</div>
                            <div class="col-md-6 mb-3"><i class="fas fa-check-circle text-success me-3"></i>Gated Community</div>
                            <div class="col-md-6 mb-3"><i class="fas fa-check-circle text-success me-3"></i>CCTV Security</div>
                            <div class="col-md-6 mb-3"><i class="fas fa-check-circle text-success me-3"></i>Rain Water Drainage</div>
                            <div class="col-md-6 mb-3"><i class="fas fa-check-circle text-success me-3"></i>Street Lights</div>
                            <div class="col-md-6 mb-3"><i class="fas fa-check-circle text-success me-3"></i>Park & Playground</div>
                            <div class="col-md-6 mb-3"><i class="fas fa-check-circle text-success me-3"></i>Near Main Road</div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Contact Card -->
                <div class="card shadow-lg mb-4 sticky-top" class="style-36655">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0"><i class="fas fa-headset me-2"></i><?= __('project_get_in_touch') ?></h4>
                    </div>
                    <div class="card-body aps-cp-card-body">
                        <h5 class="text-primary mb-3"><?= __('project_interested') ?></h5>
                        <div class="d-grid gap-2">
                            <a href="tel:<?= $phoneRaw ?>" class="btn btn-success btn-lg">
                                <i class="fas fa-phone me-2"></i><?= __('project_call_now') ?>
                            </a>
                            <a href="https://wa.me/<?= $phoneRaw ?>?text=Hi, I'm interested in <?php echo urlencode($project->site_name); ?>" target="_blank" class="btn btn-outline-success btn-lg">
                                <i class="fab fa-whatsapp me-2"></i><?= __('contact_whatsapp') ?>
                            </a>
                            <a href="<?php echo $baseUrl; ?>/contact" class="btn btn-primary btn-lg">
                                <i class="fas fa-envelope me-2"></i><?= __('contact_inquiry') ?>
                            </a>
                            <a href="<?php echo $baseUrl; ?>/register" class="btn btn-outline-primary btn-lg">
                                <i class="fas fa-user-plus me-2"></i><?= __('contact_register') ?>
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Project Details Card -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0"><i class="fas fa-building me-2"></i><?= __('project_details') ?></h5>
                    </div>
                    <div class="card-body aps-cp-card-body">
                        <div class="table-responsive"><table class="table table-borderless table-sm">
                            <tr>
                                <td><strong>Project Name:</strong></td>
                                <td><?php echo htmlspecialchars($project->site_name); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Location:</strong></td>
                                <td><?php echo htmlspecialchars($project->city ?? ''); ?></td>
                            </tr>
                            <tr>
                                <td><strong>District:</strong></td>
                                <td><?php echo htmlspecialchars($project->district ?? ''); ?></td>
                            </tr>
                            <tr>
                                <td><strong>State:</strong></td>
                                <td><?php echo htmlspecialchars($project->state ?? 'Uttar Pradesh'); ?></td>
                            </tr>
                            <?php if (!empty($project->pincode)): ?>
                            <tr>
                                <td><strong>Pincode:</strong></td>
                                <td><?php echo htmlspecialchars($project->pincode); ?></td>
                            </tr>
                            <?php endif; ?>
                            <?php if (!empty($project->total_area)): ?>
                            <tr>
                                <td><strong>Total Area:</strong></td>
                                <td><?php echo htmlspecialchars($project->total_area); ?> Acres</td>
                            </tr>
                            <?php endif; ?>
                            <tr>
                                <td><strong>Type:</strong></td>
                                <td><span class="badge bg-secondary"><?php echo ucfirst($project->site_type ?? 'Residential'); ?></span></td>
                            </tr>
                            <tr>
                                <td><strong>Status:</strong></td>
                                <td><span class="badge bg-<?php echo $project->status === 'active' ? 'success' : 'info'; ?>"><?php echo $project->status === 'active' ? 'Available' : ucfirst($project->status ?? 'Active'); ?></span></td>
                            </tr>
                        </table></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Location Section -->
<section class="py-5 bg-light">
    <div class="container">
                <h3 class="mb-4"><i class="fas fa-map-marked-alt text-primary me-2"></i><?= __('project_location_access') ?></h3>
        <div class="row g-4">
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-map-marker-alt me-2"></i><?= __('project_address') ?></h5>
                    </div>
                    <div class="card-body aps-cp-card-body">
                        <p class="mb-1"><strong><?php echo htmlspecialchars($project->site_name); ?></strong></p>
                        <p class="mb-1"><?php echo htmlspecialchars($project->location ?? ''); ?></p>
                        <p class="mb-1">
                            <?php echo htmlspecialchars(($project->city ?? '') . ', ' . ($project->district ?? '')); ?>
                        </p>
                        <?php if (!empty($project->pincode)): ?>
                        <p class="mb-0"><?php echo htmlspecialchars($project->pincode); ?></p>
                        <?php endif; ?>
                        <p class="mb-0"><?php echo htmlspecialchars($project->state ?? 'Uttar Pradesh'); ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-directions me-2"></i><?= __('project_nearby') ?></h5>
                    </div>
                    <div class="card-body aps-cp-card-body">
                        <?php
                        $nearbyPlaces = [];
                        if (!empty($project->nearby_places)) {
                            $decoded = json_decode($project->nearby_places, true);
                            if (is_array($decoded)) {
                                $nearbyPlaces = $decoded;
                            }
                        }
                        $nearbyIcons = [
                            'railway' => ['icon' => 'fa-train', 'color' => '#3b82f6'],
                            'bus' => ['icon' => 'fa-bus', 'color' => '#8b5cf6'],
                            'school' => ['icon' => 'fa-school', 'color' => '#f59e0b'],
                            'hospital' => ['icon' => 'fa-hospital', 'color' => '#ef4444'],
                            'market' => ['icon' => 'fa-shopping-cart', 'color' => '#10b981'],
                            'temple' => ['icon' => 'fa-place-of-worship', 'color' => '#6366f1'],
                            'park' => ['icon' => 'fa-tree', 'color' => '#22c55e'],
                            'bank' => ['icon' => 'fa-university', 'color' => '#0ea5e9'],
                            'airport' => ['icon' => 'fa-plane', 'color' => '#f97316'],
                            'mall' => ['icon' => 'fa-store', 'color' => '#ec4899'],
                            'college' => ['icon' => 'fa-graduation-cap', 'color' => '#8b5cf6'],
                            'gas' => ['icon' => 'fa-gas-pump', 'color' => '#64748b'],
                        ];
                        ?>
                        <div class="row">
                            <?php if (!empty($nearbyPlaces)): ?>
                                <?php foreach ($nearbyPlaces as $place):
                                    if (is_string($place)) {
                                        $place = ['name' => $place, 'distance' => '', 'type' => ''];
                                    }
                                    $placeName = $place['name'] ?? $place['label'] ?? '';
                                    $placeDistance = $place['distance'] ?? '';
                                    $placeType = strtolower($place['type'] ?? $placeName);
                                    $placeIcon = 'fa-map-marker-alt';
                                    $placeColor = '#64748b';
                                    foreach ($nearbyIcons as $key => $cfg) {
                                        if (strpos($placeType, $key) !== false || strpos(strtolower($placeName), $key) !== false) {
                                            $placeIcon = $cfg['icon'];
                                            $placeColor = $cfg['color'];
                                            break;
                                        }
                                    }
                                ?>
                                <div class="col-6 mb-2">
                                    <div class="nearby-item">
                                        <i class="fas <?= $placeIcon ?>" class="style-32235"></i>
                                        <div>
                                            <div class="nearby-name"><?php echo htmlspecialchars($placeName); ?></div>
                                            <?php if ($placeDistance): ?>
                                            <div class="nearby-distance"><?php echo htmlspecialchars($placeDistance); ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <?php
                                $fallbackNearby = [
                                    ['icon' => 'fa-train', 'label' => __('contact_railway'), 'distance' => '3-5 km', 'color' => '#3b82f6'],
                                    ['icon' => 'fa-bus', 'label' => __('contact_bus'), 'distance' => '1-2 km', 'color' => '#8b5cf6'],
                                    ['icon' => 'fa-school', 'label' => __('contact_school'), 'distance' => '0.5-2 km', 'color' => '#f59e0b'],
                                    ['icon' => 'fa-hospital', 'label' => __('contact_hospital'), 'distance' => '2-4 km', 'color' => '#ef4444'],
                                    ['icon' => 'fa-shopping-cart', 'label' => __('contact_market'), 'distance' => '0.5-1 km', 'color' => '#10b981'],
                                    ['icon' => 'fa-place-of-worship', 'label' => __('contact_temple'), 'distance' => '0.3-1 km', 'color' => '#6366f1'],
                                ];
                                ?>
                                <?php foreach ($fallbackNearby as $item): ?>
                                <div class="col-6 mb-2">
                                    <div class="nearby-item">
                                        <i class="fas <?= $item['icon'] ?>" class="style-63967"></i>
                                        <div>
                                            <div class="nearby-name"><?= $item['label'] ?></div>
                                            <div class="nearby-distance"><?= $item['distance'] ?></div>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Google Map -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card aps-cp-card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-map-marked me-2"></i><?= __('project_map') ?></h5>
                    </div>
                    <div class="card-body p-0">
                        <?php
                        $mapLat = $project->latitude ?? '26.76';
                        $mapLng = $project->longitude ?? '83.37';
                        if (empty($project->latitude) && !empty($project->district)) {
                            $coords = [
                                'gorakhpur' => ['lat' => '26.7605', 'lng' => '83.3732'],
                                'lucknow' => ['lat' => '26.8467', 'lng' => '80.9462'],
                                'kushinagar' => ['lat' => '26.7399', 'lng' => '83.8890'],
                                'prayagraj' => ['lat' => '25.4358', 'lng' => '81.8463'],
                                'varanasi' => ['lat' => '25.3176', 'lng' => '82.9739'],
                            ];
                            $d = strtolower($project->district);
                            if (isset($coords[$d])) {
                                $mapLat = $coords[$d]['lat'];
                                $mapLng = $coords[$d]['lng'];
                            }
                        }
                        ?>
                        <iframe 
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d14417.5!2d<?= $mapLng ?>!3d<?= $mapLat ?>!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2z<?= $mapLat ?>N+<?= $mapLng ?>E!5e0!3m2!1sen!2sin"
                            width="100%" 
                            height="350" 
                            class="style-49961" 
                            allowfullscreen="" 
                            loading="lazy" 
                            referrerpolicy="no-referrer-when-downgrade">
                        </iframe>
                    </div>
                    <div class="card-footer text-center">
                        <a href="https://www.google.com/maps/search/?api=1&query=<?php echo urlencode(($project->location ?? $project->city ?? '') . ', ' . ($project->district ?? 'Uttar Pradesh')); ?>" target="_blank" class="btn btn-outline-primary">
                            <i class="fas fa-external-link-alt me-2"></i><?= __('project_open_maps') ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Other Projects -->
<section class="py-5">
    <div class="container">
        <h3 class="mb-4"><i class="fas fa-th-large text-primary me-2"></i><?= __('project_other_projects') ?></h3>
        <div class="row">
            <?php if (!empty($related_projects)): ?>
                <?php foreach (array_slice($related_projects, 0, 3) as $related): 
                    $relSlug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $related->site_name));
                    $relImg = '/assets/images/projects/placeholder/property.svg';
                    $relIsExternal = false;
                    if (!empty($related->image)) {
                        if (strpos($related->image, 'http://') === 0 || strpos($related->image, 'https://') === 0) {
                            $relImg = $related->image;
                            $relIsExternal = true;
                        } else {
                            $relImg = '/' . ltrim($related->image, '/');
                        }
                    }
                ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm project-card">
                        <img src="<?= $relIsExternal ? $relImg : BASE_URL . $relImg ?>" class="card-img-top img-fluid" alt="<?php echo htmlspecialchars($related->site_name); ?>" class="style-85571" onerror="this.src='<?= $baseUrl ?>/assets/images/projects/placeholder/property.svg'">
                        <div class="card-body aps-cp-card-body">
                            <h6 class="card-title"><?php echo htmlspecialchars($related->site_name); ?></h6>
                            <p class="text-muted small mb-2"><i class="fas fa-map-marker-alt me-1"></i><?php echo htmlspecialchars($related->district ?? ''); ?></p>
                            <a href="<?php echo $baseUrl; ?>/projects/<?php echo $relSlug; ?>" class="btn btn-sm btn-primary"><?= __('featured_view_details') ?></a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm project-card">
                        <img src="<?= BASE_URL ?>/assets/images/projects/gorakhpur/suryoday.jpg" class="card-img-top" alt="Suryoday Colony" class="style-85571" />
                        <div class="card-body aps-cp-card-body">
                            <h6 class="card-title">Suryoday Colony</h6>
                            <p class="text-muted small mb-2"><i class="fas fa-map-marker-alt me-1"></i>Gorakhpur</p>
                            <a href="<?php echo $baseUrl; ?>/projects/suryoday-colony" class="btn btn-sm btn-primary">View Details</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm project-card">
                        <img src="<?= BASE_URL ?>/assets/images/projects/gorakhpur/suryoday.jpg" class="card-img-top" alt="Raghunath Nagri" class="style-85571" loading="lazy">
                        <div class="card-body aps-cp-card-body">
                            <h6 class="card-title">Raghunath Nagri</h6>
                            <p class="text-muted small mb-2"><i class="fas fa-map-marker-alt me-1"></i>Gorakhpur</p>
                            <a href="<?php echo $baseUrl; ?>/projects/raghunath-nagri" class="btn btn-sm btn-primary">View Details</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm project-card">
                        <img src="<?= BASE_URL ?>/assets/images/projects/gorakhpur/suryoday.jpg" class="card-img-top" alt="Braj Radha Nagri" class="style-85571" />
                        <div class="card-body aps-cp-card-body">
                            <h6 class="card-title">Braj Radha Nagri</h6>
                            <p class="text-muted small mb-2"><i class="fas fa-map-marker-alt me-1"></i>Gorakhpur</p>
                            <a href="<?php echo $baseUrl; ?>/projects/braj-radha-nagri" class="btn btn-sm btn-primary">View Details</a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <div class="text-center mt-3">
            <a href="<?php echo $baseUrl; ?>/company/projects" class="btn btn-outline-primary btn-lg">
                <i class="fas fa-building me-2"></i><?= __('project_view_all') ?>
            </a>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-5 text-white" class="style-68644">
    <div class="container text-center">
        <h2 class="mb-3">Interested in <?php echo htmlspecialchars($project->site_name); ?>?</h2>
        <p class="lead mb-4"><?= __('project_cta_desc') ?></p>
        <div class="d-flex justify-content-center gap-3 flex-wrap">
            <a href="tel:<?= $phoneRaw ?>" class="btn btn-warning btn-lg">
                <i class="fas fa-phone me-2"></i><?= __('project_call_now') ?>
            </a>
            <a href="https://wa.me/<?= $phoneRaw ?>?text=Hi, I'm interested in <?php echo urlencode($project->site_name); ?>" target="_blank" class="btn btn-success btn-lg">
                <i class="fab fa-whatsapp me-2"></i><?= __('contact_whatsapp') ?>
            </a>
            <a href="<?php echo $baseUrl; ?>/register" class="btn btn-light btn-lg">
                <i class="fas fa-user-plus me-2"></i><?= __('contact_register') ?>
            </a>
        </div>
    </div>
</section>

<?php else: ?>
<!-- Project Not Found -->
<section class="py-5 text-center">
    <div class="container">
        <div class="alert alert-info">
            <h2><i class="fas fa-info-circle me-2"></i><?= __('project_not_found') ?></h2>
            <p class="lead"><?= __('project_not_found_desc') ?></p>
            <a href="<?php echo $baseUrl; ?>/company/projects" class="btn btn-primary">
                <i class="fas fa-arrow-left me-2"></i><?= __('project_back_to_projects') ?>
            </a>
        </div>
    </div>
</section>
<?php endif; ?>
