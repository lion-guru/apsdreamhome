<!-- Page Header -->
<section class="legal-hero-section section-padding bg-primary text-white text-center rounded-bottom-4 py-5" data-aos="fade-down">
    <div class="container py-4">
        <h1 class="display-5 fw-bold mb-2">Legal Documents</h1>
        <p class="lead mb-0">Transparency and trust are our foundations. View our official certifications and legal papers.</p>
    </div>
</section>

<!-- Breadcrumb -->
<nav class="bg-light border-bottom py-2">
    <div class="container">
        <ol class="breadcrumb mb-0">
            <?php foreach ($breadcrumbs as $crumb): ?>
                <?php if (isset($crumb['url'])): ?>
                    <li class="breadcrumb-item"><a href="<?= $crumb['url'] ?>"><?= $crumb['title'] ?></a></li>
                <?php else: ?>
                    <li class="breadcrumb-item active"><?= $crumb['title'] ?></li>
                <?php endif; ?>
            <?php endforeach; ?>
        </ol>
    </div>
</nav>

<div class="full-row bg-white py-5">
    <div class="container">
        <div class="row g-4">
            <?php
            $featured_docs = array_slice($legal_docs, 0, 3);
            $table_docs = array_slice($legal_docs, 3);

            $colors = ['primary', 'success', 'info', 'warning', 'danger', 'secondary'];
            $icons = ['fa-certificate', 'fa-check-double', 'fa-file-contract', 'fa-shield-alt', 'fa-gavel', 'fa-landmark'];

            foreach ($featured_docs as $index => $doc):
                $color = $colors[$index % count($colors)];
                $icon = $icons[$index % count($icons)];
            ?>
            <!-- Legal Card -->
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="<?= ($index + 1) * 100 ?>">
                <div class="legal-card p-4 rounded-4 shadow-sm h-100 border text-center transition-hover">
                    <div class="icon-box mb-4 mx-auto bg-<?= $color ?>-soft">
                        <i class="fas <?= $icon ?> text-<?= $color ?> fa-2x"></i>
                    </div>
                    <h4 class="fw-bold mb-3"><?= h($doc->title) ?></h4>
                    <p class="text-muted mb-4"><?= h($doc->description) ?></p>
                    <a href="<?= get_asset_url('legal/' . $doc->file_path, 'docs') ?>" class="btn btn-outline-<?= $color ?> rounded-pill px-4" target="_blank">
                        <i class="fas fa-download me-2"></i>Download PDF
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Document List Section -->
        <div class="row mt-5 pt-4">
            <div class="col-lg-12">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="card-header bg-light border-0 py-3 px-4">
                        <h5 class="mb-0 fw-bold">Additional Legal Papers</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">Document Name</th>
                                        <th>Category</th>
                                        <th>Date Published</th>
                                        <th class="text-end pe-4">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($table_docs)): ?>
                                        <?php foreach ($table_docs as $doc): ?>
                                        <tr>
                                            <td class="ps-4 fw-medium"><?= h($doc->title) ?></td>
                                            <td><?= h($doc->category) ?></td>
                                            <td><?= date('Y-m-d', strtotime($doc->published_date)) ?></td>
                                            <td class="text-end pe-4">
                                                <a href="<?= get_asset_url('legal/' . $doc->file_path, 'docs') ?>" class="text-primary" target="_blank">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4" class="text-center py-4 text-muted">No additional documents available.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .legal-hero-section {
        background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%) !important;
    }
    .header-desc {
        max-width: 700px;
    }
    .legal-card {
        transition: all 0.3s ease;
        background-color: #fff;
    }
    .legal-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 1rem 3rem rgba(0,0,0,.175) !important;
    }
    .bg-primary-soft { background-color: rgba(13, 110, 253, 0.1); }
    .bg-success-soft { background-color: rgba(25, 135, 84, 0.1); }
    .bg-info-soft { background-color: rgba(13, 202, 240, 0.1); }
    .icon-box {
        width: 70px;
        height: 70px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }
</style>
