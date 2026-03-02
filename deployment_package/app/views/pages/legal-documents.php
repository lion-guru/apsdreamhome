<?php
/**
 * Legal Documents Page - APS Dream Homes
 * Migrated from resources/views/Views/legal.php
 */

require_once __DIR__ . '/init.php';

$page_title = 'Legal Documents | APS Dream Homes';
$layout = 'modern';

// Dynamic fetch for legal documents if table exists
$documents = [];
try {
    $db = \App\Core\App::database();
    $result = $db->fetchAll("SELECT * FROM legal_documents ORDER BY id ASC");
    if ($result) {
        $documents = $result;
    }
} catch (Exception $e) {}

// Fallback static documents
if (empty($documents)) {
    $documents = [
        ['title' => 'Registration Certificate', 'image' => 'legal/one.jpg', 'description' => 'Official company registration certificate.'],
        ['title' => 'ISO Certification', 'image' => 'legal/two.jpg', 'description' => 'Quality management system certification.'],
        ['title' => 'RERA Approval', 'image' => 'legal/three.jpg', 'description' => 'Real Estate Regulatory Authority approval documents.'],
        ['title' => 'PAN Card', 'image' => 'legal/four.jpg', 'description' => 'Company Permanent Account Number card.'],
    ];
}

ob_start();
?>

<div class="row">
    <div class="col-lg-12">
        <!-- Header Section -->
        <div class="page-banner mb-5" style="background: linear-gradient(rgba(30,60,114,0.8), rgba(30,60,114,0.8)), url('<?= get_asset_url('breadcromb.jpg', 'images') ?>') center/cover; padding: 80px 0; color: #fff; border-radius: 0 0 40px 40px;">
            <div class="container text-center">
                <h1 class="display-4 fw-bold mb-2 animate-fade-up">Legal Documents</h1>
                <p class="lead animate-fade-up">Transparency and Trust in Every Step</p>
            </div>
        </div>

        <div class="container pb-5">
            <div class="row mb-5 text-center">
                <div class="col-lg-8 mx-auto">
                    <h2 class="fw-bold mb-3">Our Credentials</h2>
                    <p class="text-muted">At APS Dream Homes, we maintain complete transparency with our clients. Here are our official legal documents and certifications that validate our commitment to quality and legality.</p>
                </div>
            </div>

            <div class="row g-4">
                <?php foreach ($documents as $doc): ?>
                    <div class="col-lg-3 col-md-6">
                        <div class="card border-0 shadow-sm rounded-4 h-100 hover-lift overflow-hidden">
                            <div class="position-relative">
                                <img src="<?= get_asset_url($doc['image'], 'images') ?>" class="card-img-top p-3" alt="<?= h($doc['title']) ?>" style="height: 300px; object-fit: contain; background: #f8f9fa;">
                                <div class="position-absolute top-0 end-0 p-3">
                                    <span class="badge bg-primary rounded-pill"><i class="fas fa-check-circle me-1"></i> Verified</span>
                                </div>
                            </div>
                            <div class="card-body text-center p-4">
                                <h5 class="fw-bold mb-2"><?= h($doc['title']) ?></h5>
                                <p class="small text-muted mb-3"><?= h($doc['description'] ?? '') ?></p>
                                <a href="<?= get_asset_url($doc['image'], 'images') ?>" class="btn btn-outline-primary btn-sm rounded-pill px-4" target="_blank">
                                    <i class="fas fa-search-plus me-1"></i> View Large
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Notice Section -->
            <div class="mt-5 p-4 bg-light rounded-4 border-start border-primary border-4">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3">
                        <i class="fas fa-info-circle text-primary fa-2x"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1">Important Notice</h6>
                        <p class="small text-muted mb-0">These documents are for informational purposes. For any specific legal verification, please contact our corporate office or visit in person with a prior appointment.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();

// Include the layout
require_once __DIR__ . '/../layouts/' . $layout . '.php';
