<!-- Page Header -->
<section class="bank-hero-section section-padding bg-primary text-white text-center rounded-bottom-4 py-5" data-aos="fade-down">
    <div class="container py-4">
        <h1 class="display-5 fw-bold mb-2">Bank Details</h1>
        <p class="lead mb-0">Official banking information for secure and transparent property transactions with APS Dream Homes.</p>
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

<!-- Main Content -->
<main class="py-5 bg-light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <!-- Bank Accounts Display -->
                <?php if (!empty($bank_accounts)): ?>
                    <div class="row g-4 mb-5">
                        <?php foreach ($bank_accounts as $account): ?>
                            <div class="col-md-6" data-aos="fade-up">
                                <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
                                    <div class="card-header bg-primary text-white py-3 border-0">
                                        <h5 class="mb-0 fw-bold"><?= h($account->bank_name) ?></h5>
                                    </div>
                                    <div class="card-body p-4">
                                        <div class="mb-3">
                                            <label class="text-muted small text-uppercase fw-bold">Account Name</label>
                                            <p class="h6 mb-0"><?= h($account->account_name) ?></p>
                                        </div>
                                        <div class="mb-3">
                                            <label class="text-muted small text-uppercase fw-bold">Account Number</label>
                                            <div class="d-flex align-items-center">
                                                <p class="h5 mb-0 fw-bold text-primary"><?= h($account->account_number) ?></p>
                                                <button class="btn btn-sm btn-link text-muted ms-2 p-0" onclick="copyToClipboard('<?= $account->account_number ?>')">
                                                    <i class="far fa-copy"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-6">
                                                <label class="text-muted small text-uppercase fw-bold">IFSC Code</label>
                                                <p class="mb-0 fw-bold"><?= h($account->ifsc_code) ?></p>
                                            </div>
                                            <div class="col-6 text-end">
                                                <label class="text-muted small text-uppercase fw-bold">Branch</label>
                                                <p class="mb-0 small"><?= h($account->branch_name) ?></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <!-- Bank Details Image (Legacy/Backup) -->
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-5 bg-white" data-aos="fade-up">
                    <div class="card-body p-0">
                        <div class="bank-details-wrapper text-center p-4 p-md-5">
                            <h4 class="fw-bold mb-4">Official Bank Account Details</h4>
                            <img src="<?= get_asset_url('bank.PNG', 'images') ?>" class="img-fluid rounded-4 shadow-sm border" alt="APS Dream Homes Bank Details" onerror="this.src='https://placehold.co/800x400?text=Bank+Details+Image'">
                        </div>
                    </div>
                </div>

                <!-- Payment Instructions -->
                <div class="card border-0 shadow-sm rounded-4 bg-white" data-aos="fade-up">
                    <div class="card-body p-4 p-md-5">
                        <h4 class="fw-bold text-dark mb-4 border-bottom pb-3">Payment Instructions</h4>
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="d-flex align-items-start mb-4">
                                    <div class="flex-shrink-0 bg-primary-subtle text-primary rounded-circle p-3 me-3">
                                        <i class="fas fa-shield-alt fa-lg"></i>
                                    </div>
                                    <div>
                                        <h6 class="fw-bold mb-1">Verify Account Details</h6>
                                        <p class="small text-muted mb-0">Always double-check the account number and IFSC code before initiating any transaction.</p>
                                    </div>
                                </div>
                                <div class="d-flex align-items-start">
                                    <div class="flex-shrink-0 bg-primary-subtle text-primary rounded-circle p-3 me-3">
                                        <i class="fas fa-pencil-alt fa-lg"></i>
                                    </div>
                                    <div>
                                        <h6 class="fw-bold mb-1">Add Transaction Remark</h6>
                                        <p class="small text-muted mb-0">Please mention your Name and Property/Plot ID in the payment remarks for faster verification.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-start mb-4">
                                    <div class="flex-shrink-0 bg-primary-subtle text-primary rounded-circle p-3 me-3">
                                        <i class="fab fa-whatsapp fa-lg"></i>
                                    </div>
                                    <div>
                                        <h6 class="fw-bold mb-1">Share Payment Receipt</h6>
                                        <p class="small text-muted mb-0">Send a screenshot of the successful transaction to our official accounts WhatsApp number.</p>
                                    </div>
                                </div>
                                <div class="d-flex align-items-start">
                                    <div class="flex-shrink-0 bg-primary-subtle text-primary rounded-circle p-3 me-3">
                                        <i class="fas fa-headset fa-lg"></i>
                                    </div>
                                    <div>
                                        <h6 class="fw-bold mb-1">Payment Support</h6>
                                        <p class="small text-muted mb-0">Contact our accounts department at +91-XXXXXXXXXX for any payment-related queries or issues.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- QR Code Section (Optional Future Use) -->
                <div class="mt-5 text-center" data-aos="zoom-in">
                    <div class="p-4 rounded-4 bg-white shadow-sm d-inline-block">
                        <p class="fw-bold mb-2">Scan for Easy Payment</p>
                        <img src="https://placehold.co/150x150?text=UPI+QR+Code" class="img-fluid rounded" alt="UPI QR Code">
                        <div class="mt-2">
                            <span class="badge bg-light text-dark border"><i class="fas fa-bolt text-warning me-1"></i> Instant Settlement</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        alert('Account number copied to clipboard!');
    }, function(err) {
        console.error('Could not copy text: ', err);
    });
}
</script>

<style>
    .bank-hero-section {
        background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%) !important;
    }
    .bg-primary-subtle {
        background-color: rgba(var(--bs-primary-rgb), 0.1) !important;
    }
    .bank-details-wrapper img {
        max-width: 100%;
        height: auto;
    }
    .card {
        transition: transform 0.3s ease;
    }
    .card:hover {
        transform: translateY(-5px);
    }
</style>
