<div class="container py-4">
    <div class="text-center mb-5">
        <h4 class="fw-bold"><?= ($page_title ?? 'FAQ') ?></h4>
        <p class="text-muted"><?= ($page_description ?? 'Frequently asked questions') ?></p>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <?php if (!empty($faqs ?? [])): ?>
            <?php $categories = array_unique(array_column($faqs ?? [], 'category')); ?>
            <?php foreach ($categories as $cat): ?>
            <div class="mb-4">
                <h6 class="text-primary mb-3"><i class="fas fa-tag me-2"></i><?= htmlspecialchars($cat ?? '') ?></h6>
                <div class="accordion" id="faqAccordion-<?= md5($cat) ?>">
                    <?php $idx = 0; foreach (($faqs ?? []) as $faq): if (($faq['category'] ?? '') !== $cat) continue; $idx++; ?>
                    <div class="accordion-item border-0 mb-2 shadow-sm rounded">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed rounded" type="button" data-bs-toggle="collapse" data-bs-target="#faq<?= ($faq['id'] ?? $idx) ?>">
                                <?= htmlspecialchars($faq['question'] ?? '') ?>
                            </button>
                        </h2>
                        <div id="faq<?= ($faq['id'] ?? $idx) ?>" class="accordion-collapse collapse" data-bs-parent="#faqAccordion-<?= md5($cat) ?>">
                            <div class="accordion-body text-muted"><?= nl2br(htmlspecialchars($faq['answer'] ?? '')) ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>
            <?php else: ?>
            <div class="text-center py-5 text-muted">
                <i class="fas fa-question-circle fa-3x mb-3"></i>
                <p>No FAQs available.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
