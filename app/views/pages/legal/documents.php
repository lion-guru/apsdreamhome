<?php
// Legal Documents Page - APS Dream Homes
$page_title = 'Legal Documents | APS Dream Homes';

$documents = [
    ['title' => 'Registration Certificate', 'description' => 'Official company registration certificate.'],
    ['title' => 'ISO Certification', 'description' => 'Quality management system certification.'],
    ['title' => 'RERA Approval', 'description' => 'Real Estate Regulatory Authority approval documents.'],
    ['title' => 'PAN Card', 'description' => 'Company Permanent Account Number card.'],
    ['title' => 'GST Certificate', 'description' => 'Goods and Services Tax registration certificate.'],
    ['title' => 'Trade License', 'description' => 'Municipal trade license for real estate business.'],
];
?>

<section class="py-5 bg-primary text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
    <div class="container text-center">
        <h1 class="display-4 fw-bold mb-3">Legal Documents</h1>
        <p class="lead">Transparency and Trust in Every Step</p>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="row justify-content-center mb-5">
            <div class="col-lg-8 text-center">
                <h2 class="fw-bold mb-3">Our Credentials</h2>
                <p class="text-muted">At APS Dream Homes, we maintain complete transparency. Here are our official legal documents and certifications.</p>
            </div>
        </div>
        <div class="row">
            <?php foreach ($documents as $doc): ?>
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-body text-center p-4">
                        <div class="mb-3">
                            <i class="fas fa-file-alt fa-3x text-primary"></i>
                        </div>
                        <h5 class="fw-bold"><?= htmlspecialchars($doc['title']) ?></h5>
                        <p class="text-muted small"><?= htmlspecialchars($doc['description']) ?></p>
                        <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>Verified</span>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="mt-4 p-4 bg-light rounded border-start border-primary border-4">
            <i class="fas fa-info-circle text-primary me-2"></i>
            <strong>Important:</strong> For legal verification, please contact our corporate office.
        </div>
    </div>
</section>
