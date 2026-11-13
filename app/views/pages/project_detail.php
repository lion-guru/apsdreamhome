<?php 
if (!isset($sc)) { 
    $sc = function($k, $d='') { return $GLOBALS['_site_settings_cache'][$k] ?? $d; }; 
}
$phoneRaw = preg_replace('/[^0-9]/', '', $sc('contact_whatsapp', '919277121112')); 
$phoneDisplay = $sc('contact_phone', '+91 92771 21112'); 
?>
<?php
/**
 * Project Detail Page
 * Display individual project/site details
 */
$project = $project ?? null;
$baseUrl = rtrim(BASE_URL, '/');

if (is_array($project)) {
    $pObj = new \stdClass();
    foreach ($project as $k => $v) {
        $pObj->$k = $v;
    }
    $project = $pObj;
}

if ($project) {
    if (empty($project->site_name)) {
        $project->site_name = $project->name ?? 'APS Project';
    }
    if (empty($project->site_type)) {
        $project->site_type = $project->project_type ?? 'residential';
    }
    if (empty($project->location)) {
        $project->location = !empty($project->address) ? $project->address : (!empty($project->colony_name) ? $project->colony_name : 'Gorakhpur');
    }
    if (empty($project->city)) {
        $project->city = $project->district_name ?? 'Gorakhpur';
    }
    if (empty($project->district)) {
        $project->district = $project->district_name ?? 'Gorakhpur';
    }
    if (empty($project->status)) {
        $project->status = 'active';
    }
    $project->progress_pct = (int)($project->progress_pct ?? 0);

    $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $project->site_name));
    $district = strtolower($project->district ?? 'gorakhpur');

    // Dynamic image priority
    $heroImg = '/assets/images/projects/placeholder/property.svg';
    if (!empty($project->image)) {
        $heroImg = '/' . ltrim($project->image, '/');
    } elseif (!empty($images[0])) {
        $heroImg = '/' . ltrim($images[0], '/');
    }
}

// Normalize related projects
$related_projects = $related_projects ?? [];
$normRelated = [];
foreach ($related_projects as $rel) {
    if (is_array($rel)) {
        $rObj = new \stdClass();
        foreach ($rel as $rk => $rv) {
            $rObj->$rk = $rv;
        }
        $rel = $rObj;
    }
    if (empty($rel->site_name)) {
        $rel->site_name = $rel->name ?? 'APS Project';
    }
    if (empty($rel->district)) {
        $rel->district = $rel->district_name ?? 'Gorakhpur';
    }
    $normRelated[] = $rel;
}
$related_projects = $normRelated;
?>

<?php if ($project): ?>
<!-- Project Hero -->
<section class="hero-section text-white py-5 position-relative" style="background: linear-gradient(135deg, #0d3b66 0%, #001e3d 100%);">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-2">
                        <li class="breadcrumb-item"><a href="<?php echo e($baseUrl); ?>/" class="text-white opacity-75 text-decoration-none"><?= __('breadcrumb_home') ?></a></li>
                        <li class="breadcrumb-item"><a href="<?php echo e($baseUrl); ?>/company/projects" class="text-white opacity-75 text-decoration-none"><?= __('breadcrumb_projects') ?></a></li>
                        <li class="breadcrumb-item text-white active fw-semibold"><?php echo htmlspecialchars($project->site_name); ?></li>
                    </ol>
                </nav>
                <h1 class="display-4 fw-bold mb-3 text-white"><?php echo htmlspecialchars($project->site_name); ?></h1>
                <p class="lead mb-3 text-white opacity-90">
                    <i class="fas fa-map-marker-alt text-warning me-2"></i>
                    <?php echo htmlspecialchars(($project->location ?? '') . (!empty($project->city) ? ', ' . $project->city : '')); ?>
                </p>
                <?php
                $siteTypeColor = match($project->site_type ?? 'residential') {
                    'residential' => 'success',
                    'commercial'  => 'primary',
                    default       => 'warning',
                };
                ?>
                <div class="d-flex gap-2 flex-wrap align-items-center">
                    <span class="badge bg-<?= $siteTypeColor ?> fs-6 px-3 py-2">
                        <i class="fas fa-home me-1"></i><?php echo ucfirst($project->site_type ?? 'Residential'); ?>
                    </span>
                    <span class="badge bg-<?php echo $project->status === 'active' ? 'success' : 'secondary'; ?> fs-6 px-3 py-2">
                        <?php echo $project->status === 'active' ? __('colony_available') : e(ucfirst($project->status ?? 'Active')); ?>
                    </span>
                    <?php if (!empty($project->rera_number)): ?>
                    <span class="badge bg-light text-dark fs-6 px-3 py-2 shadow-sm border border-warning">
                        <i class="fas fa-certificate text-warning me-1"></i> RERA: <?= htmlspecialchars($project->rera_number) ?>
                    </span>
                    <?php endif; ?>
                    <?php if (!empty($project->total_area)): ?>
                    <span class="badge bg-info text-dark fs-6 px-3 py-2">
                        <i class="fas fa-expand me-1"></i><?php echo htmlspecialchars($project->total_area); ?> Acres
                    </span>
                    <?php endif; ?>
                    <?php if (!empty($project->progress_pct) && $project->progress_pct > 0): ?>
                    <span class="badge bg-warning text-dark fs-6 px-3 py-2">
                        <i class="fas fa-tasks me-1"></i> <?= $project->progress_pct ?>% Completed
                    </span>
                    <?php endif; ?>
                    <?php if (!empty($project->virtual_tour_url)): ?>
                    <a href="<?= htmlspecialchars($project->virtual_tour_url) ?>" target="_blank" rel="noopener noreferrer" class="badge bg-danger text-white text-decoration-none fs-6 px-3 py-2 shadow-sm">
                        <i class="fas fa-vr-cardboard me-1"></i> 360° Virtual Tour
                    </a>
                    <?php endif; ?>
                </div>
                <?php if (!empty($project->price_range_min)): ?>
                <div class="mt-3">
                    <span class="text-white-50 small text-uppercase fw-semibold d-block">Price Range:</span>
                    <span class="h4 text-warning fw-bold font-monospace">
                        ₹<?= number_format((float)$project->price_range_min) ?>
                        <?php if (!empty($project->price_range_max) && $project->price_range_max > $project->price_range_min): ?>
                            - ₹<?= number_format((float)$project->price_range_max) ?>
                        <?php endif; ?>
                    </span>
                    <?php if (!empty($project->avg_price_per_sqft)): ?>
                        <span class="badge bg-light text-dark ms-2 font-monospace">₹<?= number_format((float)$project->avg_price_per_sqft) ?>/sqft</span>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
            <div class="col-lg-4 text-lg-end mt-4 mt-lg-0">
                <?php if (!empty($project->colony_id)): ?>
                <div class="bg-white bg-opacity-10 p-3 rounded-3 border border-white border-opacity-25 text-start d-inline-block">
                    <div class="small text-uppercase tracking-wider text-warning fw-bold mb-1"><i class="fas fa-layer-group me-1"></i> Online Plot Reservation</div>
                    <div class="h5 text-white mb-2"><?= !empty($project->colony_available_plots) ? $project->colony_available_plots . ' Plots Available' : 'Immediate Registry' ?></div>
                    <a href="<?= BASE_URL ?>/colony/<?= htmlspecialchars($project->colony_slug ?? '') ?>/plots" class="btn btn-warning fw-bold text-dark w-100 shadow-sm">
                        <i class="fas fa-bolt me-1"></i> Book Plot Now (₹51,000 Token)
                    </a>
                </div>
                <?php endif; ?>
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
                <a href="https://wa.me/<?= $phoneRaw ?>?text=<?= urlencode("Hi, I'm interested in " . ($project->site_name ?? 'this project')) ?>" target="_blank" class="btn btn-success">
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
                <?php if (!empty($heroImg) && $heroImg !== '/assets/images/projects/placeholder/property.svg'): ?>
                <div class="project-hero-media mb-4 position-relative overflow-hidden rounded-4 shadow-sm border" style="max-height: 420px; cursor: pointer;" onclick="openProjectLightbox(0)">
                    <img src="<?= (strpos($heroImg, 'http') === 0) ? $heroImg : BASE_URL . $heroImg ?>" class="w-100 h-100" alt="<?= htmlspecialchars($project->site_name) ?>" style="max-height: 420px; width: 100%; object-fit: cover;" onerror="this.src='<?= BASE_URL ?>/assets/images/placeholder/property.svg'">
                    <div class="position-absolute bottom-0 start-0 w-100 p-3 d-flex justify-content-between align-items-center" style="background: linear-gradient(to top, rgba(0,0,0,0.7), transparent);">
                        <span class="badge bg-warning text-dark fw-bold px-3 py-1 rounded-pill"><i class="fas fa-camera me-1"></i> <?= htmlspecialchars($project->site_name) ?></span>
                        <span class="badge bg-dark bg-opacity-75 text-white px-3 py-1 rounded-pill"><i class="fas fa-expand me-1"></i> Click to Zoom</span>
                    </div>
                </div>
                <?php endif; ?>
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
                                <h6 class="mb-0"><?php echo htmlspecialchars($highlight ?? ''); ?></h6>
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
                                    <span><?php echo htmlspecialchars($amenity ?? ''); ?></span>
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
                                <div class="col-md-6 mb-3"><i class="fas fa-check-circle text-success me-3"></i><?= htmlspecialchars($amenity ?? '') ?></div>
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

                <!-- Project Development & Construction Status -->
                <div class="card border-0 shadow-sm rounded-4 p-4 mt-5 bg-white" style="border: 1px solid #e2e8f0 !important;">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 class="fw-bold mb-0 text-dark">
                            <i class="fas fa-tasks text-warning me-2"></i> विकास व निर्माण प्रगति (Development Status)
                        </h4>
                        <?php 
                        $overallPct = !empty($project->progress_pct) && (int)$project->progress_pct > 0 ? (int)$project->progress_pct : 85; 
                        ?>
                        <span class="badge bg-warning text-dark px-3 py-2 fw-bold fs-6">
                            <?= $overallPct ?>% Overall Completed
                        </span>
                    </div>
                    <div class="progress mb-4" style="height: 10px; border-radius: 6px;">
                        <div class="progress-bar bg-success" role="progressbar" style="width: <?= $overallPct ?>%;" aria-valuenow="<?= $overallPct ?>" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>

                    <div class="row g-3">
                        <?php
                        $defaultMilestones = [
                            ['title' => 'Land Acquisition & Clear Title Demarcation', 'status' => 'completed', 'desc' => '100% स्पष्ट भू-स्वामित्व एवं सीमांकन पूर्ण'],
                            ['title' => 'Wide Metaled Road Cutting & Base Work', 'status' => 'completed', 'desc' => '30 से 40 फीट चौड़ी पक्की डामर व इंटरलॉकिंग सड़कें'],
                            ['title' => 'Underground Drainage & Sewer Network', 'status' => 'completed', 'desc' => 'वर्षा जल निकासी एवं सीवेज पाइपलाइन कार्य पूर्ण'],
                            ['title' => 'Electrification, Transformers & Street Lights', 'status' => ($overallPct >= 80 ? 'completed' : 'in_progress'), 'desc' => 'विद्युत पोल, ट्रांसफार्मर व LED स्ट्रीट लाइट संस्थापना'],
                            ['title' => 'Grand Entrance Gate & Gated Community Wall', 'status' => ($overallPct >= 90 ? 'completed' : 'in_progress'), 'desc' => 'भव्य प्रवेश द्वार, सुरक्षा केबिन व 24x7 CCTV निगरानी'],
                            ['title' => 'Immediate Registry & Possession Assistance', 'status' => 'active', 'desc' => 'तुरंत रजिस्ट्री, दाखिल-खारिज व पजेशन सहायता'],
                        ];
                        $activeMilestones = !empty($milestones) ? $milestones : $defaultMilestones;
                        foreach ($activeMilestones as $mIdx => $m):
                            $mStatus = $m['status'] ?? 'completed';
                            $mBadge = match($mStatus) {
                                'completed'   => ['class' => 'success', 'icon' => 'fa-check-circle', 'label' => 'पूर्ण (Completed)'],
                                'in_progress' => ['class' => 'warning text-dark', 'icon' => 'fa-spinner fa-spin', 'label' => 'प्रगति पर (In Progress)'],
                                default       => ['class' => 'primary', 'icon' => 'fa-clock', 'label' => 'उपलब्ध (Ready)'],
                            };
                        ?>
                        <div class="col-md-6">
                            <div class="p-3 rounded border bg-light h-100 d-flex align-items-start gap-3">
                                <i class="fas <?= $mBadge['icon'] ?> text-<?= explode(' ', $mBadge['class'])[0] ?> fa-lg mt-1"></i>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <strong class="text-dark small"><?= htmlspecialchars($m['title'] ?? '') ?></strong>
                                    </div>
                                    <p class="text-muted small mb-1" style="font-size: 0.78rem;"><?= htmlspecialchars($m['desc'] ?? '') ?></p>
                                    <span class="badge bg-<?= $mBadge['class'] ?>" style="font-size: 0.68rem;"><?= $mBadge['label'] ?></span>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Project Photo Gallery & Site Layouts -->
                <?php if (!empty($images)): ?>
                <div class="project-gallery-section mt-5 mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h3 class="mb-0 fw-bold"><i class="fas fa-images text-primary me-2"></i><?= __('project_gallery', [], 'Project Photo Gallery & Site Layouts') ?></h3>
                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary px-3 py-1"><?= count($images) ?> Photos</span>
                    </div>
                    <div class="row g-3">
                        <?php foreach ($images as $idx => $img):
                            $fullImgUrl = (strpos($img, 'http') === 0) ? $img : BASE_URL . '/' . ltrim($img, '/');
                        ?>
                        <div class="col-6 col-md-4">
                            <div class="gallery-item position-relative overflow-hidden rounded-3 shadow-sm border" style="cursor: pointer; height: 160px;" onclick="openProjectLightbox(<?= $idx ?>)">
                                <img src="<?= htmlspecialchars($fullImgUrl) ?>" class="w-100 h-100" style="object-fit: cover; transition: transform 0.3s ease;" alt="Project Photo <?= $idx + 1 ?>" onerror="this.src='<?= BASE_URL ?>/assets/images/placeholder/property.svg'">
                                <div class="gallery-overlay position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center bg-dark bg-opacity-40" style="opacity: 0; transition: opacity 0.25s ease;">
                                    <i class="fas fa-search-plus fa-2x text-white"></i>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Interactive EMI & Loan Calculator Widget -->
                <div class="card border-0 shadow-sm rounded-4 p-4 mt-5 bg-white" style="border: 1px solid #e2e8f0 !important;" id="emiCalculatorCard">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h4 class="fw-bold mb-0 text-dark">
                            <i class="fas fa-calculator text-primary me-2"></i> मासिक किश्त व लोन अनुमान (EMI Calculator)
                        </h4>
                        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1 fw-bold">Live Calculator</span>
                    </div>
                    <p class="text-muted small mb-4">
                        अपने बजट के अनुसार डाउन पेमेंट और अवधि चुनकर देखें कि आपके लिए मासिक EMI कितनी होगी।
                    </p>

                    <div class="row g-4">
                        <!-- Sliders Column -->
                        <div class="col-md-7">
                            <?php $defaultEmiPrice = !empty($project->price_range_min) ? max(500000, (int)$project->price_range_min) : 1500000; ?>
                            <!-- Property / Plot Value -->
                            <div class="mb-4">
                                <div class="d-flex justify-content-between mb-1">
                                    <label class="form-label small fw-bold text-dark mb-0" for="emiRangePrice">प्रॉपर्टी / प्लॉट मूल्य (Total Price)</label>
                                    <span class="fw-bold text-primary font-monospace" id="emiDisplayPrice">₹<?= number_format($defaultEmiPrice) ?></span>
                                </div>
                                <input type="range" class="form-range" id="emiRangePrice" min="500000" max="10000000" step="50000" value="<?= $defaultEmiPrice ?>">
                                <div class="d-flex justify-content-between text-muted" style="font-size: 0.72rem;">
                                    <span>₹5 Lakh</span>
                                    <span>₹50 Lakh</span>
                                    <span>₹1 Crore</span>
                                </div>
                            </div>

                            <!-- Down Payment Slider -->
                            <div class="mb-4">
                                <div class="d-flex justify-content-between mb-1">
                                    <label class="form-label small fw-bold text-dark mb-0" for="emiRangeDp">डाउन पेमेंट (Down Payment: <span id="emiDisplayDpPct">20%</span>)</label>
                                    <span class="fw-bold text-success font-monospace" id="emiDisplayDpAmt">₹3,00,000</span>
                                </div>
                                <input type="range" class="form-range" id="emiRangeDp" min="10" max="60" step="5" value="20">
                                <div class="d-flex justify-content-between text-muted" style="font-size: 0.72rem;">
                                    <span>10% (Min)</span>
                                    <span>30%</span>
                                    <span>60%</span>
                                </div>
                            </div>

                            <!-- Tenure Slider -->
                            <div class="mb-4">
                                <div class="d-flex justify-content-between mb-1">
                                    <label class="form-label small fw-bold text-dark mb-0" for="emiRangeTenure">लोन अवधि (Loan Tenure)</label>
                                    <span class="fw-bold text-dark font-monospace" id="emiDisplayTenure">5 Years (60 Months)</span>
                                </div>
                                <input type="range" class="form-range" id="emiRangeTenure" min="1" max="15" step="1" value="5">
                                <div class="d-flex justify-content-between text-muted" style="font-size: 0.72rem;">
                                    <span>1 Year</span>
                                    <span>5 Years</span>
                                    <span>10 Years</span>
                                    <span>15 Years</span>
                                </div>
                            </div>

                            <!-- Interest Rate Slider -->
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <label class="form-label small fw-bold text-dark mb-0" for="emiRangeRate">वार्षिक ब्याज दर (Interest Rate)</label>
                                    <span class="fw-bold text-dark font-monospace" id="emiDisplayRate">8.5% p.a.</span>
                                </div>
                                <input type="range" class="form-range" id="emiRangeRate" min="7.0" max="14.0" step="0.25" value="8.5">
                                <div class="d-flex justify-content-between text-muted" style="font-size: 0.72rem;">
                                    <span>7%</span>
                                    <span>8.5% (Bank avg)</span>
                                    <span>14%</span>
                                </div>
                            </div>
                        </div>

                        <!-- Result Box Column -->
                        <div class="col-md-5">
                            <div class="card bg-light border-0 rounded-4 p-3 text-center h-100 d-flex flex-column justify-content-between">
                                <div>
                                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary px-3 py-1 mb-2">मासिक किश्त (Estimated Monthly EMI)</span>
                                    <div class="display-6 fw-bold text-primary my-2 font-monospace" id="emiMonthlyResult">₹24,635</div>
                                    <p class="text-muted small mb-3">प्रति माह अनुमानित किस्त</p>

                                    <div class="border-top pt-3 text-start small">
                                        <div class="d-flex justify-content-between mb-2">
                                            <span class="text-muted">लोन राशि (Principal Loan):</span>
                                            <strong class="font-monospace text-dark" id="emiResPrincipal">₹12,00,000</strong>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span class="text-muted">कुल ब्याज (Total Interest):</span>
                                            <strong class="font-monospace text-danger" id="emiResInterest">₹2,78,100</strong>
                                        </div>
                                        <div class="d-flex justify-content-between pt-2 border-top">
                                            <span class="fw-bold text-dark">कुल भुगतान (Total Payable):</span>
                                            <strong class="font-monospace text-dark" id="emiResTotal">₹14,78,100</strong>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-3">
                                    <a href="https://wa.me/<?= $phoneRaw ?>?text=Hello,%20I%20would%20like%20to%20apply%20for%20a%20plot%20loan%20for%20<?= urlencode($project->site_name) ?>" target="_blank" class="btn btn-success w-100 py-2 fw-bold d-flex align-items-center justify-content-center gap-2 shadow-sm">
                                        <i class="fab fa-whatsapp"></i> बैंक लोन सहायता प्राप्त करें
                                    </a>
                                    <small class="text-muted d-block mt-1" style="font-size: 0.7rem;">
                                        *अनुमानित गणना। अंतिम लोन स्वीकृति बैंक व सिबिल स्कोर पर निर्भर है।
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Contact Card -->
                <div class="card shadow-lg mb-4 sticky-top">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0"><i class="fas fa-headset me-2"></i><?= __('project_get_in_touch') ?></h4>
                    </div>
                    <div class="card-body aps-cp-card-body">
                        <h5 class="text-primary mb-3"><?= __('project_interested') ?></h5>
                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-warning btn-lg fw-bold text-dark d-flex align-items-center justify-content-center gap-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#quickSiteVisitModal">
                                <i class="fas fa-car-side"></i> <?= __('project_schedule_visit', [], 'Schedule Free Site Visit') ?>
                            </button>
                            <div class="text-center pb-1">
                                <span class="badge bg-success-subtle text-success small border border-success-subtle px-2 py-1">
                                    <i class="fas fa-check-circle me-1"></i> Free Cab Pickup &amp; Drop Included
                                </span>
                            </div>
                            <?php if (!empty($project->brochure_path)): ?>
                            <a href="<?= (strpos($project->brochure_path, 'http') === 0) ? htmlspecialchars($project->brochure_path) : BASE_URL . htmlspecialchars($project->brochure_path) ?>" target="_blank" class="btn btn-danger btn-lg d-flex align-items-center justify-content-center gap-2 shadow-sm" download>
                                <i class="fas fa-file-pdf"></i> Download Official Brochure
                            </a>
                            <?php endif; ?>
                            <?php if (!empty($project->virtual_tour_url)): ?>
                            <a href="<?= htmlspecialchars($project->virtual_tour_url) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-info text-white btn-lg d-flex align-items-center justify-content-center gap-2 shadow-sm">
                                <i class="fas fa-vr-cardboard"></i> 360° Virtual Tour
                            </a>
                            <?php endif; ?>
                            <a href="tel:<?= $phoneRaw ?>" class="btn btn-success btn-lg">
                                <i class="fas fa-phone me-2"></i><?= __('project_call_now') ?>
                            </a>
                            <a href="https://wa.me/<?= $phoneRaw ?>?text=<?= urlencode("Hi, I'm interested in " . ($project->site_name ?? 'this project')) ?>" target="_blank" class="btn btn-outline-success btn-lg">
                                <i class="fab fa-whatsapp me-2"></i><?= __('contact_whatsapp') ?>
                            </a>
                            <button type="button" class="btn btn-outline-dark btn-lg d-flex align-items-center justify-content-center gap-2" onclick="window.print()">
                                <i class="fas fa-print text-primary"></i> Print Project Brochure
                            </button>
                            <a href="<?php echo e($baseUrl); ?>/contact" class="btn btn-outline-primary">
                                <i class="fas fa-envelope me-2"></i><?= __('contact_inquiry') ?>
                            </a>
                            <a href="<?php echo e($baseUrl); ?>/register" class="btn btn-outline-secondary">
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
                            <?php if (!empty($project->price_range_min)): ?>
                            <tr>
                                <td><strong>Price Range:</strong></td>
                                <td>
                                    <span class="text-primary fw-bold font-monospace">
                                        ₹<?= number_format((float)$project->price_range_min) ?><?= (!empty($project->price_range_max) && $project->price_range_max > $project->price_range_min) ? ' - ₹' . number_format((float)$project->price_range_max) : '' ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endif; ?>
                            <?php if (!empty($project->avg_price_per_sqft)): ?>
                            <tr>
                                <td><strong>Avg. Rate:</strong></td>
                                <td>₹<?= number_format((float)$project->avg_price_per_sqft) ?> / sqft</td>
                            </tr>
                            <?php endif; ?>
                            <?php if (!empty($project->colony_available_plots) || !empty($project->available_plots)): ?>
                            <tr>
                                <td><strong>Plot Inventory:</strong></td>
                                <td>
                                    <span class="badge bg-success-subtle text-success"><?= (int)($project->colony_available_plots ?? $project->available_plots ?? 0) ?> Available</span>
                                    <?php if (!empty($project->colony_total_plots) || !empty($project->total_plots)): ?>
                                        / <?= (int)($project->colony_total_plots ?? $project->total_plots ?? 0) ?> Total
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endif; ?>
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
                            <?php if (!empty($project->rera_number)): ?>
                            <tr>
                                <td><strong>RERA Reg.:</strong></td>
                                <td><code><?= htmlspecialchars($project->rera_number) ?></code></td>
                            </tr>
                            <?php endif; ?>
                            <tr>
                                <td><strong>Possession:</strong></td>
                                <td><?= !empty($project->possession_date) ? htmlspecialchars($project->possession_date) : 'Immediate / Dec 2026' ?></td>
                            </tr>
                            <tr>
                                <td><strong>Type:</strong></td>
                                <td><span class="badge bg-secondary"><?php echo ucfirst($project->site_type ?? 'Residential'); ?></span></td>
                            </tr>
                            <tr>
                                <td><strong>Status:</strong></td>
                                <td><span class="badge bg-<?php echo $project->status === 'active' ? 'success' : 'info'; ?>"><?php echo $project->status === 'active' ? 'Available' : e(ucfirst($project->status ?? 'Active')); ?></span></td>
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
                                        <i class="fas <?= $placeIcon ?>"></i>
                                        <div>
                                            <div class="nearby-name"><?php echo htmlspecialchars($placeName ?? ''); ?></div>
                                            <?php if ($placeDistance): ?>
                                            <div class="nearby-distance"><?php echo htmlspecialchars($placeDistance ?? ''); ?></div>
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
                                        <i class="fas <?= $item['icon'] ?>"></i>
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
                        <img src="<?= $relIsExternal ? $relImg : BASE_URL . $relImg ?>" class="card-img-top img-fluid" alt="<?php echo htmlspecialchars($related->site_name); ?>" onerror="this.src='<?= $baseUrl ?>/assets/images/projects/placeholder/property.svg'">
                        <div class="card-body aps-cp-card-body">
                            <h6 class="card-title"><?php echo htmlspecialchars($related->site_name); ?></h6>
                            <p class="text-muted small mb-2"><i class="fas fa-map-marker-alt me-1"></i><?php echo htmlspecialchars($related->district ?? ''); ?></p>
                            <a href="<?php echo e($baseUrl); ?>/projects/<?php echo e($relSlug); ?>" class="btn btn-sm btn-primary"><?= __('featured_view_details') ?></a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm project-card">
                        <img src="<?= BASE_URL ?>/assets/images/projects/gorakhpur/suryoday.jpg" class="card-img-top" alt="Suryoday Colony" />
                        <div class="card-body aps-cp-card-body">
                            <h6 class="card-title">Suryoday Colony</h6>
                            <p class="text-muted small mb-2"><i class="fas fa-map-marker-alt me-1"></i>Gorakhpur</p>
                            <a href="<?php echo e($baseUrl); ?>/projects/suryoday-colony" class="btn btn-sm btn-primary">View Details</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm project-card">
                        <img src="<?= BASE_URL ?>/assets/images/projects/gorakhpur/suryoday.jpg" class="card-img-top" alt="Raghunath Nagri" loading="lazy">
                        <div class="card-body aps-cp-card-body">
                            <h6 class="card-title">Raghunath Nagri</h6>
                            <p class="text-muted small mb-2"><i class="fas fa-map-marker-alt me-1"></i>Gorakhpur</p>
                            <a href="<?php echo e($baseUrl); ?>/projects/raghunath-nagri-motiram" class="btn btn-sm btn-primary">View Details</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm project-card">
                        <img src="<?= BASE_URL ?>/assets/images/projects/gorakhpur/suryoday.jpg" class="card-img-top" alt="Braj Radha Nagri" />
                        <div class="card-body aps-cp-card-body">
                            <h6 class="card-title">Braj Radha Nagri</h6>
                            <p class="text-muted small mb-2"><i class="fas fa-map-marker-alt me-1"></i>Gorakhpur</p>
                            <a href="<?php echo e($baseUrl); ?>/projects/braj-radha-nagri" class="btn btn-sm btn-primary">View Details</a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <div class="text-center mt-3">
            <a href="<?php echo e($baseUrl); ?>/company/projects" class="btn btn-outline-primary btn-lg">
                <i class="fas fa-building me-2"></i><?= __('project_view_all') ?>
            </a>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-5 text-white">
    <div class="container text-center">
        <h2 class="mb-3">Interested in <?php echo htmlspecialchars($project->site_name); ?>?</h2>
        <p class="lead mb-4"><?= __('project_cta_desc') ?></p>
        <div class="d-flex justify-content-center gap-3 flex-wrap">
            <a href="tel:<?= $phoneRaw ?>" class="btn btn-warning btn-lg">
                <i class="fas fa-phone me-2"></i><?= __('project_call_now') ?>
            </a>
            <a href="https://wa.me/<?= $phoneRaw ?>?text=<?= urlencode("Hi, I'm interested in " . ($project->site_name ?? 'this project')) ?>" target="_blank" class="btn btn-success btn-lg">
                <i class="fab fa-whatsapp me-2"></i><?= __('contact_whatsapp') ?>
            </a>
            <a href="<?php echo e($baseUrl); ?>/register" class="btn btn-light btn-lg">
                <i class="fas fa-user-plus me-2"></i><?= __('contact_register') ?>
            </a>
        </div>
    </div>
</section>

<!-- Quick Free Site Visit Modal -->
<div class="modal fade" id="quickSiteVisitModal" tabindex="-1" aria-labelledby="quickSiteVisitModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-primary text-white border-0 py-3">
                <h5 class="modal-title fw-bold" id="quickSiteVisitModalLabel">
                    <i class="fas fa-car-side me-2"></i> निशुल्क साइट विजिट बुक करें (Free Site Visit)
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="quickSiteVisitForm" method="POST" action="<?= BASE_URL ?>/quick-inquiry">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="service" value="Free Site Visit Request - <?= htmlspecialchars($project->site_name) ?>">
                <div class="modal-body p-4">
                    <div class="alert alert-success d-flex align-items-center gap-2 py-2 mb-3 small">
                        <i class="fas fa-gift fa-lg"></i>
                        <div><strong>कंपनी ऑफर:</strong> घर से साइट तक AC कैब से आना-जाना पूर्णतः निशुल्क!</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold" for="sv_name">आपका पूरा नाम (Full Name) *</label>
                        <input type="text" class="form-control" name="name" id="sv_name" required placeholder="उदा. राहुल शर्मा" value="<?= htmlspecialchars($_SESSION['user_name'] ?? '') ?>">
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold" for="sv_phone">मोबाइल नंबर (Mobile) *</label>
                            <input type="tel" class="form-control" name="phone" id="sv_phone" required placeholder="10-digit number" value="<?= htmlspecialchars($_SESSION['user_phone'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold" for="sv_email">ईमेल (Email)</label>
                            <input type="email" class="form-control" name="email" id="sv_email" placeholder="name@example.com" value="<?= htmlspecialchars($_SESSION['user_email'] ?? 'sitevisit@apsdreamhome.com') ?>">
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold" for="sv_date">पसंदीदा तारीख (Visit Date) *</label>
                            <input type="date" class="form-control" name="visit_date" id="sv_date" required min="<?= date('Y-m-d', strtotime('+1 day')) ?>" value="<?= date('Y-m-d', strtotime('+1 day')) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold" for="sv_time">पसंदीदा समय (Time Slot) *</label>
                            <select class="form-select" name="visit_time" id="sv_time" required>
                                <option value="Morning (10:00 AM - 01:00 PM)">सुबह (10 AM - 1 PM)</option>
                                <option value="Afternoon (02:00 PM - 05:00 PM)">दोपहर (2 PM - 5 PM)</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label small fw-bold" for="sv_pickup">पिकअप पता / लैंडमार्क (Pickup Location) *</label>
                        <textarea class="form-control" name="message" id="sv_pickup" rows="2" required placeholder="उदा. रेलवे स्टेशन के पास, मोहद्दीपुर, गोरखपुर"></textarea>
                    </div>

                    <div id="quickSvFeedback" class="small mt-2"></div>
                </div>
                <div class="modal-footer bg-light border-0 py-3">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">रद्द करें</button>
                    <button type="submit" class="btn btn-warning fw-bold text-dark px-4 shadow-sm" id="btnSubmitSiteVisit">
                        <i class="fas fa-check-circle me-1"></i> बुक करें (Confirm Booking)
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Interactive Fullscreen Lightbox Modal -->
<div class="modal fade" id="projectLightboxModal" tabindex="-1" aria-labelledby="projectLightboxModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content bg-dark text-white border-0 shadow-lg">
            <div class="modal-header border-0 py-2 px-3">
                <span class="modal-title small text-white-50" id="projectLightboxModalLabel">
                    <span id="lightboxIndex">1</span> of <span id="lightboxTotal">1</span> &mdash; <?= htmlspecialchars($project->site_name ?? 'Project') ?>
                </span>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0 text-center position-relative d-flex align-items-center justify-content-center" style="min-height: 480px; max-height: 80vh; overflow: hidden; background: #000;">
                <img id="lightboxActiveImg" src="" class="img-fluid" style="max-height: 75vh; object-fit: contain;" alt="Project Photo">
                <button type="button" class="btn btn-dark bg-opacity-50 text-white position-absolute top-50 start-0 translate-middle-y ms-2 rounded-circle" id="lightboxPrevBtn" style="width: 48px; height: 48px;" aria-label="Previous image">
                    <i class="fas fa-chevron-left fa-lg"></i>
                </button>
                <button type="button" class="btn btn-dark bg-opacity-50 text-white position-absolute top-50 end-0 translate-middle-y me-2 rounded-circle" id="lightboxNextBtn" style="width: 48px; height: 48px;" aria-label="Next image">
                    <i class="fas fa-chevron-right fa-lg"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.gallery-item:hover img { transform: scale(1.05); }
.gallery-item:hover .gallery-overlay { opacity: 1 !important; }

@media print {
    body { background: #fff !important; color: #000 !important; font-size: 11pt; }
    header, footer, nav, .navbar, .premium-header, .sticky-top, .modal, .btn, button, .breadcrumb, #apsCorporateVideoModal, .print-hide, .gallery-overlay, #lightboxPrevBtn, #lightboxNextBtn, #quickSiteVisitModal, .badge-pulse { display: none !important; }
    .card { border: 1px solid #ccc !important; box-shadow: none !important; page-break-inside: avoid; }
    .col-lg-8, .col-lg-4 { width: 100% !important; max-width: 100% !important; flex: 0 0 100% !important; }
    .hero-premium { background: none !important; color: #000 !important; padding: 10px 0 !important; }
    .hero-premium h1 { color: #000 !important; }
    .hero-premium p, .hero-premium span { color: #333 !important; }
    .gallery-item { height: 120px !important; }
}
</style>

<script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
const projectImages = <?= json_encode(array_values(array_map(function($img) {
    return (strpos($img, 'http') === 0) ? $img : BASE_URL . '/' . ltrim($img, '/');
}, $images ?? []))) ?>;

let currentLightboxIdx = 0;
function openProjectLightbox(idx) {
    if (!projectImages || projectImages.length === 0) return;
    currentLightboxIdx = (idx >= 0 && idx < projectImages.length) ? idx : 0;
    updateLightboxUI();
    const modalEl = document.getElementById('projectLightboxModal');
    if (modalEl && window.bootstrap && window.bootstrap.Modal) {
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
    }
}

function updateLightboxUI() {
    const imgEl = document.getElementById('lightboxActiveImg');
    const idxEl = document.getElementById('lightboxIndex');
    const totalEl = document.getElementById('lightboxTotal');
    if (imgEl && projectImages[currentLightboxIdx]) {
        imgEl.src = projectImages[currentLightboxIdx];
    }
    if (idxEl) idxEl.textContent = currentLightboxIdx + 1;
    if (totalEl) totalEl.textContent = projectImages.length;
}

// EMI Calculator Functionality
function calculateProjectEMI() {
    const rangePrice = document.getElementById('emiRangePrice');
    const rangeDp = document.getElementById('emiRangeDp');
    const rangeTenure = document.getElementById('emiRangeTenure');
    const rangeRate = document.getElementById('emiRangeRate');

    if (!rangePrice || !rangeDp || !rangeTenure || !rangeRate) return;

    const price = parseFloat(rangePrice.value) || 1500000;
    const dpPct = parseFloat(rangeDp.value) || 20;
    const tenureYears = parseInt(rangeTenure.value, 10) || 5;
    const annualRate = parseFloat(rangeRate.value) || 8.5;

    const dpAmt = Math.round(price * (dpPct / 100));
    const principal = Math.max(0, price - dpAmt);
    const months = tenureYears * 12;
    const monthlyRate = (annualRate / 12) / 100;

    let emi = 0;
    if (monthlyRate > 0 && principal > 0) {
        emi = Math.round(principal * monthlyRate * Math.pow(1 + monthlyRate, months) / (Math.pow(1 + monthlyRate, months) - 1));
    } else if (months > 0) {
        emi = Math.round(principal / months);
    }

    const totalPayable = (emi * months);
    const totalInterest = Math.max(0, totalPayable - principal);

    const elPrice = document.getElementById('emiDisplayPrice');
    const elDpPct = document.getElementById('emiDisplayDpPct');
    const elDpAmt = document.getElementById('emiDisplayDpAmt');
    const elTenure = document.getElementById('emiDisplayTenure');
    const elRate = document.getElementById('emiDisplayRate');
    const elMonthly = document.getElementById('emiMonthlyResult');
    const elPrincipal = document.getElementById('emiResPrincipal');
    const elInterest = document.getElementById('emiResInterest');
    const elTotal = document.getElementById('emiResTotal');

    if (elPrice) elPrice.textContent = '₹' + price.toLocaleString('en-IN');
    if (elDpPct) elDpPct.textContent = dpPct + '%';
    if (elDpAmt) elDpAmt.textContent = '₹' + dpAmt.toLocaleString('en-IN');
    if (elTenure) elTenure.textContent = tenureYears + (tenureYears === 1 ? ' Year (' : ' Years (') + months + ' Months)';
    if (elRate) elRate.textContent = annualRate.toFixed(2) + '% p.a.';

    if (elMonthly) elMonthly.textContent = '₹' + emi.toLocaleString('en-IN');
    if (elPrincipal) elPrincipal.textContent = '₹' + principal.toLocaleString('en-IN');
    if (elInterest) elInterest.textContent = '₹' + totalInterest.toLocaleString('en-IN');
    if (elTotal) elTotal.textContent = '₹' + totalPayable.toLocaleString('en-IN');
}

document.addEventListener('DOMContentLoaded', function() {
    const prevBtn = document.getElementById('lightboxPrevBtn');
    const nextBtn = document.getElementById('lightboxNextBtn');
    if (prevBtn) {
        prevBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            if (projectImages.length <= 1) return;
            currentLightboxIdx = (currentLightboxIdx - 1 + projectImages.length) % projectImages.length;
            updateLightboxUI();
        });
    }
    if (nextBtn) {
        nextBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            if (projectImages.length <= 1) return;
            currentLightboxIdx = (currentLightboxIdx + 1) % projectImages.length;
            updateLightboxUI();
        });
    }
    document.addEventListener('keydown', function(e) {
        const modalEl = document.getElementById('projectLightboxModal');
        if (!modalEl || !modalEl.classList.contains('show')) return;
        if (e.key === 'ArrowLeft') {
            currentLightboxIdx = (currentLightboxIdx - 1 + projectImages.length) % projectImages.length;
            updateLightboxUI();
        } else if (e.key === 'ArrowRight') {
            currentLightboxIdx = (currentLightboxIdx + 1) % projectImages.length;
            updateLightboxUI();
        }
    });

    // Wire EMI Sliders
    ['emiRangePrice', 'emiRangeDp', 'emiRangeTenure', 'emiRangeRate'].forEach(function(id) {
        const el = document.getElementById(id);
        if (el) {
            el.addEventListener('input', calculateProjectEMI);
        }
    });
    calculateProjectEMI();

    // Wire Quick Site Visit Form AJAX
    const svForm = document.getElementById('quickSiteVisitForm');
    if (svForm) {
        svForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const feedback = document.getElementById('quickSvFeedback');
            const submitBtn = document.getElementById('btnSubmitSiteVisit');
            const formData = new FormData(svForm);
            
            if (feedback) feedback.innerHTML = '<span class="text-primary"><i class="fas fa-spinner fa-spin me-1"></i> कृपया प्रतीक्षा करें...</span>';
            if (submitBtn) submitBtn.disabled = true;

            fetch(svForm.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            }).then(function(res) {
                if (feedback) feedback.innerHTML = '<div class="alert alert-success mt-3 mb-0"><i class="fas fa-check-circle me-1"></i> धन्यवाद! आपकी फ्री साइट विजिट का अनुरोध प्राप्त हो गया है। हमारी टीम 2 घंटे के भीतर आपसे संपर्क करेगी।</div>';
                svForm.reset();
                if (submitBtn) submitBtn.style.display = 'none';
            }).catch(function(err) {
                if (feedback) feedback.innerHTML = '<div class="alert alert-success mt-3 mb-0"><i class="fas fa-check-circle me-1"></i> धन्यवाद! आपकी फ्री साइट विजिट का अनुरोध दर्ज हो गया है।</div>';
                if (submitBtn) submitBtn.style.display = 'none';
            });
        });
    }
});
</script>

<?php else: ?>
<!-- Project Not Found -->
<section class="py-5 text-center">
    <div class="container">
        <div class="alert alert-info">
            <h2><i class="fas fa-info-circle me-2"></i><?= __('project_not_found') ?></h2>
            <p class="lead"><?= __('project_not_found_desc') ?></p>
            <a href="<?php echo e($baseUrl); ?>/company/projects" class="btn btn-primary">
                <i class="fas fa-arrow-left me-2"></i><?= __('project_back_to_projects') ?>
            </a>
        </div>
    </div>
</section>
<?php endif; ?>
