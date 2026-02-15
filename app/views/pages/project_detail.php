<?php
/**
 * Project Detail Page - APS Dream Homes
 * Modern Layout Integrated
 */

require_once __DIR__ . '/init.php';

// Fetch project details from DB
$project = null;
$city = $_GET['city'] ?? '';
$project_slug = $_GET['project'] ?? '';

if ($city && $project_slug) {
    try {
        $db = \App\Core\App::database();
        // Try to match by slug (lowercase, hyphenated name)
        $sql = "SELECT * FROM project_master WHERE LOWER(city) = LOWER(?) AND LOWER(REPLACE(name, ' ', '-')) = ? LIMIT 1";
        $project = $db->fetch($sql, [$city, $project_slug]);
    } catch (Exception $e) {
        error_log('Project detail database error: ' . $e->getMessage());
    }
}

// Fallback for demo if not found in DB
if (!$project) {
    // Check for specific hardcoded projects or show 404
    if ($project_slug === 'raghunath-nagri') {
        $project = [
            'name' => 'Raghunath Nagri',
            'city' => 'Gorakhpur',
            'location' => 'Near Medical College, Gorakhpur',
            'description' => 'A premium residential township with modern amenities, wide roads, and lush green parks.',
            'amenities' => 'Parks, Security, 24/7 Water, Street Lights, Community Center',
            'images' => 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab,https://images.unsplash.com/photo-1449156001533-cb3941a246ee',
            'video' => 'https://www.youtube.com/embed/dQw4w9WgXcQ',
            'layout_map' => 'assets/images/mapsuryoday.pdf'
        ];
    } else {
        // Redirect or show error
        $page_title = 'Project Not Found | APS Dream Homes';
        $layout = 'modern';
        ob_start();
        echo '<div class="container py-5 mt-5 text-center"><div class="alert alert-warning py-5 rounded-4 shadow-sm"><i class="fas fa-exclamation-triangle fa-3x mb-3"></i><h3>Project Not Found</h3><p class="mb-4">The project you are looking for does not exist or has been moved.</p><a href="projects.php" class="btn btn-primary rounded-pill px-4">View All Projects</a></div></div>';
        $content = ob_get_clean();
        require_once __DIR__ . '/../layouts/' . $layout . '.php';
        exit;
    }
}

$page_title = $project['name'] . ' | APS Dream Homes';
$layout = 'modern';

// Prepare amenities and images
$amenities = isset($project['amenities']) ? array_map('trim', explode(',', $project['amenities'])) : [];
$images = isset($project['images']) ? array_map('trim', explode(',', $project['images'])) : [];

ob_start();
?>

<!-- Page Header -->
<div class="page-header py-5 bg-dark text-white text-center mb-0 position-relative overflow-hidden project-detail-header">
    <div class="container py-5 mt-4" data-aos="fade-up">
        <h1 class="display-3 fw-bold mb-3"><?= h($project['name']) ?></h1>
        <p class="lead opacity-75 mb-0 mx-auto header-desc"><i class="fas fa-map-marker-alt me-2"></i> <?= h($project['location']) ?></p>
        <div class="d-flex justify-content-center gap-3 mt-3">
            <span class="badge bg-primary rounded-pill px-3 py-2 text-uppercase letter-spacing-1"><?= h($project['city']) ?></span>
            <?php if (isset($project['id'])): ?>
                <a href="<?= BASE_URL ?>book-property?project_id=<?= $project['id'] ?>" class="btn btn-warning rounded-pill px-4 fw-bold">
                    <i class="fas fa-calendar-check me-2"></i>Book Site Visit
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="container py-5 mt-5">
    <div class="row g-5">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Image Gallery -->
            <div class="row g-3 mb-5" data-aos="fade-up">
                <?php if (!empty($images)): ?>
                    <div class="col-12">
                        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                            <img src="<?= h($images[0]) ?>" class="img-fluid main-project-img" alt="<?= h($project['name']) ?>">
                        </div>
                    </div>
                    <?php if (count($images) > 1): ?>
                        <?php for($i=1; $i<count($images); $i++): ?>
                            <div class="col-md-4">
                                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                                    <img src="<?= h($images[$i]) ?>" class="img-fluid thumb-project-img" alt="<?= h($project['name']) ?>">
                                </div>
                            </div>
                        <?php endfor; ?>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

            <!-- About Project -->
            <div class="mb-5" data-aos="fade-up">
                <h3 class="fw-bold text-dark mb-4 border-start border-primary border-4 ps-3">About the Project</h3>
                <p class="text-secondary lead-sm"><?= nl2br(h($project['description'])) ?></p>
            </div>

            <!-- Amenities -->
            <?php if (!empty($amenities)): ?>
            <div class="mb-5" data-aos="fade-up">
                <h3 class="fw-bold text-dark mb-4 border-start border-primary border-4 ps-3">World Class Amenities</h3>
                <div class="row g-4">
                    <?php foreach ($amenities as $a): ?>
                    <div class="col-md-4 col-6">
                        <div class="d-flex align-items-center p-3 bg-light rounded-4">
                            <i class="fas fa-check-circle text-primary me-2 fs-5"></i>
                            <span class="fw-medium text-secondary"><?= h($a) ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Video/Layout -->
            <div class="row g-4 mb-5">
                <?php if (!empty($project['video'])): ?>
                <div class="col-md-6" data-aos="fade-right">
                    <h5 class="fw-bold mb-3">Project Video</h5>
                    <div class="ratio ratio-16x9 rounded-4 overflow-hidden shadow-sm">
                        <iframe src="<?= h($project['video']) ?>" title="Project Video" allowfullscreen></iframe>
                    </div>
                </div>
                <?php endif; ?>

                <?php if (!empty($project['layout_map'])): ?>
                <div class="col-md-6" data-aos="fade-left">
                    <h5 class="fw-bold mb-3">Layout Map</h5>
                    <?php if (strpos($project['layout_map'], '.pdf') !== false): ?>
                        <div class="p-4 bg-light rounded-4 text-center border">
                            <i class="fas fa-file-pdf fa-4x text-danger mb-3"></i>
                            <p class="small text-muted mb-3">Project Layout Map (PDF)</p>
                            <a href="<?= h($project['layout_map']) ?>" class="btn btn-dark rounded-pill px-4" target="_blank">View PDF Map</a>
                        </div>
                    <?php else: ?>
                        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                            <img src="<?= h($project['layout_map']) ?>" class="img-fluid" alt="Layout Map">
                        </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <div class="sticky-top" style="top: 100px;">
                <!-- Inquiry Card -->
                <div class="card border-0 shadow-lg rounded-4 overflow-hidden mb-4" data-aos="fade-left">
                    <div class="card-body p-4 p-md-5">
                        <h4 class="fw-bold text-dark mb-4">Inquire About This Project</h4>
                        <div id="project-inquiry-message"></div>
                        <form id="project-inquiry-form" action="<?= BASE_URL ?>contact" method="post">
                            <?= csrf_field() ?>
                            <input type="hidden" name="project_id" value="<?= $project['id'] ?? '' ?>">
                            <input type="hidden" name="subject" value="Inquiry about Project: <?= h($project['name']) ?>">

                            <div class="mb-3">
                                <label class="form-label small fw-bold text-secondary">Full Name</label>
                                <input type="text" name="name" class="form-control bg-light border-0 py-2" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-secondary">Email Address</label>
                                <input type="email" name="email" class="form-control bg-light border-0 py-2" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-secondary">Phone Number</label>
                                <input type="tel" name="phone" class="form-control bg-light border-0 py-2" required>
                            </div>
                            <div class="mb-4">
                                <label class="form-label small fw-bold text-secondary">Message</label>
                                <textarea name="message" class="form-control bg-light border-0 py-2" rows="3" required
                                          placeholder="I'm interested in this project. Please send more details."></textarea>
                            </div>
                            <button type="submit" id="submitProjectInquiryBtn" class="btn btn-primary w-100 rounded-pill py-3 fw-bold">Send Inquiry</button>
                        </form>
                    </div>
                </div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const inquiryForm = document.getElementById('project-inquiry-form');
    const submitBtn = document.getElementById('submitProjectInquiryBtn');
    const messageDiv = document.getElementById('project-inquiry-message');

    if (inquiryForm) {
        inquiryForm.addEventListener('submit', function(e) {
            e.preventDefault();

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Sending...';

            const formData = new FormData(this);

            fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    messageDiv.innerHTML = `<div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i> ${data.message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>`;
                    inquiryForm.reset();
                } else {
                    messageDiv.innerHTML = `<div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i> ${data.message || 'Failed to send inquiry.'}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>`;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                messageDiv.innerHTML = `<div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i> An error occurred. Please try again later.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>`;
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = 'Send Inquiry';
            });
        });
    }
});
</script>

                <!-- Location Card -->
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden" data-aos="fade-left" data-aos-delay="100">
                    <div class="card-body p-2">
                        <iframe src="https://www.google.com/maps?q=<?= urlencode($project['location'] . ' ' . $project['city']) ?>&output=embed" width="100%" height="250" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
                    </div>
                    <div class="card-footer bg-white border-0 p-3 text-center">
                        <a href="https://www.google.com/maps?q=<?= urlencode($project['location'] . ' ' . $project['city']) ?>" target="_blank" class="text-primary text-decoration-none small fw-bold">Open in Google Maps <i class="fas fa-external-link-alt ms-1"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .project-detail-header {
        background: linear-gradient(rgba(0,0,0,0.8), rgba(0,0,0,0.8)), url('https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80') center/cover no-repeat;
    }
    .header-desc {
        max-width: 700px;
    }
    .main-project-img {
        width: 100%;
        height: 450px;
        object-fit: cover;
    }
    .thumb-project-img {
        width: 100%;
        height: 150px;
        object-fit: cover;
        cursor: pointer;
        transition: transform 0.3s ease;
    }
    .thumb-project-img:hover {
        transform: scale(1.05);
    }
    .letter-spacing-1 { letter-spacing: 1px; }
    .lead-sm { font-size: 1.1rem; line-height: 1.8; }
</style>

<?php
$content = ob_get_clean();

// Include the layout
require_once __DIR__ . '/../layouts/' . $layout . '.php';
?>
