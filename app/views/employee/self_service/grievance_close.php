<?php
/**
 * Employee Self-Service - Close Grievance with Satisfaction Rating
 */

$page_title = $page_title ?? 'Close Grievance';
$grievance = $grievance ?? [];
?>

<style nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
.star-rating {
    display: inline-flex;
    flex-direction: row-reverse;
    font-size: 2rem;
    gap: 0.5rem;
}
.star-rating input {
    display: none;
}
.star-rating label {
    color: #cbd5e1;
    cursor: pointer;
    transition: color 0.15s ease, transform 0.15s ease;
}
.star-rating label:hover,
.star-rating label:hover ~ label,
.star-rating input:checked ~ label {
    color: #f59e0b;
}
.star-rating label:hover {
    transform: scale(1.15);
}
</style>

<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-7 col-xl-6">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="mb-1 fw-bold text-dark">
                        <i class="fas fa-check-circle me-2 text-success"></i>Close Grievance & Provide Feedback
                    </h4>
                    <p class="text-muted mb-0 small">Grievance #<strong><?= htmlspecialchars($grievance['grievance_number'] ?? 'N/A') ?></strong></p>
                </div>
                <a href="<?= BASE_URL ?>/employee/self-service/grievances/<?= $grievance['id'] ?>" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left me-1"></i> Back to Detail
                </a>
            </div>

            <!-- Flash Messages -->
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show rounded-3 mb-4" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!-- Resolution Summary -->
            <?php if (!empty($grievance['resolution'])): ?>
                <div class="card border-0 shadow-sm rounded-3 mb-4 bg-light">
                    <div class="card-body p-3">
                        <span class="text-muted small fw-bold text-uppercase d-block mb-1">Provided Resolution</span>
                        <div class="small text-dark">
                            <?= nl2br(htmlspecialchars($grievance['resolution'])) ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Rating & Closure Form -->
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-star text-warning me-2"></i>How satisfied are you with the resolution?
                    </h6>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="<?= BASE_URL ?>/employee/self-service/grievances/<?= $grievance['id'] ?>/close">
                        <div class="text-center py-3 mb-3">
                            <label class="form-label d-block text-muted small fw-semibold text-uppercase mb-2">Select Your Rating</label>
                            <div class="star-rating justify-content-center">
                                <input type="radio" id="star5" name="rating" value="5" required>
                                <label for="star5" title="5 stars - Outstanding"><i class="fas fa-star"></i></label>
                                
                                <input type="radio" id="star4" name="rating" value="4">
                                <label for="star4" title="4 stars - Good"><i class="fas fa-star"></i></label>
                                
                                <input type="radio" id="star3" name="rating" value="3" checked>
                                <label for="star3" title="3 stars - Satisfactory"><i class="fas fa-star"></i></label>
                                
                                <input type="radio" id="star2" name="rating" value="2">
                                <label for="star2" title="2 stars - Poor"><i class="fas fa-star"></i></label>
                                
                                <input type="radio" id="star1" name="rating" value="1">
                                <label for="star1" title="1 star - Very Dissatisfied"><i class="fas fa-star"></i></label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="feedback" class="form-label fw-semibold small text-dark">Feedback / Closing Remarks (Optional)</label>
                            <textarea name="feedback" id="feedback" rows="4" class="form-control rounded-2" placeholder="Let us know how the resolution process went or if any ongoing actions are needed..."></textarea>
                        </div>

                        <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                            <a href="<?= BASE_URL ?>/employee/self-service/grievances/<?= $grievance['id'] ?>" class="btn btn-light px-4">Cancel</a>
                            <button type="submit" class="btn btn-success px-4">
                                <i class="fas fa-check-circle me-1"></i> Confirm & Close Grievance
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
