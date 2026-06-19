<div class="container mt-4">
    <h1 class="mb-4"><?php echo $page_title ?? __('faq_page_title'); ?></h1>
    <?php if (!empty($faqs)): ?>
        <div class="accordion" id="faqAccordion">
            <?php foreach ($faqs as $index => $faq): ?>
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button <?php echo $index > 0 ? 'collapsed' : ''; ?>" type="button" data-bs-toggle="collapse" data-bs-target="#faq<?php echo $faq['id']; ?>">
                            <?php echo htmlspecialchars($faq['question'] ?? ''); ?>
                        </button>
                    </h2>
                    <div id="faq<?php echo $faq['id']; ?>" class="accordion-collapse collapse <?php echo $index === 0 ? 'show' : ''; ?>" data-bs-parent="#faqAccordion">
                        <div class="accordion-body"><?php echo nl2br(htmlspecialchars($faq['answer'] ?? '')); ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-info"><?= __('faq_no_available') ?></div>
    <?php endif; ?>
</div>
