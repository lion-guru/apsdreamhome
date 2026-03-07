<!-- FAQ Section -->
<section id="faq" class="faq-section bg-light">
    <div class="container">
        <div class="section-header text-center" data-aos="fade-up">
            <h2>Frequently Asked Questions</h2>
            <p>Common questions about our interior design services</p>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="accordion" id="faqAccordion">
                    <?php foreach ($faqs as $index => $faq): ?>
                    <div class="accordion-item" data-aos="fade-up" data-aos-delay="100">
                        <h2 class="accordion-header" id="heading<?php echo $index; ?>">
                            <button class="accordion-button <?php echo $index === 0 ? '' : 'collapsed'; ?>" 
                                    type="button" 
                                    data-bs-toggle="collapse" 
                                    data-bs-target="#collapse<?php echo $index; ?>" 
                                    aria-expanded="<?php echo $index === 0 ? 'true' : 'false'; ?>" 
                                    aria-controls="collapse<?php echo $index; ?>">
                                <?php echo h($faq['question']); ?>
                            </button>
                        </h2>
                        <div id="collapse<?php echo $index; ?>" 
                             class="accordion-collapse collapse <?php echo $index === 0 ? 'show' : ''; ?>" 
                             aria-labelledby="heading<?php echo $index; ?>" 
                             data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                <?php echo h($faq['answer']); ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="text-center mt-4">
                    <a href="faq" class="btn btn-outline-primary">View All FAQs</a>
                </div>
            </div>
        </div>
    </div>
</section>
<section id="main-content" class="py-5">
    <div class="container">
        <!-- Category Filter -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="faq-filter text-center">
                    <a href="<?= BASE_URL ?>faq" class="btn <?= $current_category === 'all' ? 'btn-primary' : 'btn-outline-primary' ?> me-2 mb-2">
                        All Categories
                    </a>
                    <?php foreach ($categories as $cat): ?>
                        <?php $catName = is_object($cat) ? $cat->category : $cat['category']; ?>
                        <a href="<?= BASE_URL ?>faq?category=<?= urlencode($catName) ?>"
                            class="btn <?= $current_category === $catName ? 'btn-primary' : 'btn-outline-primary' ?> me-2 mb-2">
                            <?= htmlspecialchars(ucwords($catName)) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- FAQ Accordion -->
        <div class="row">
            <div class="col-lg-10 mx-auto">
                <div class="accordion faq-accordion" id="faqAccordion">
                    <?php if (!empty($grouped_faqs)): ?>
                        <?php foreach ($grouped_faqs as $category => $faqs): ?>
                            <div class="faq-category mb-4">
                                <h3 class="category-title mb-3 border-bottom pb-2 text-primary"><?= htmlspecialchars(ucwords($category)) ?></h3>
                                <?php foreach ($faqs as $index => $faq): ?>
                                    <?php
                                    $faqId = is_object($faq) ? $faq->id : $faq['id'];
                                    $question = is_object($faq) ? $faq->question : $faq['question'];
                                    $answer = is_object($faq) ? $faq->answer : $faq['answer'];
                                    $relatedLinks = is_object($faq) ? ($faq->related_links ?? null) : ($faq['related_links'] ?? null);
                                    ?>
                                    <div class="accordion-item mb-3 border rounded shadow-sm">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button collapsed" type="button"
                                                data-bs-toggle="collapse"
                                                data-bs-target="#faq-<?= $faqId ?>">
                                                <?= htmlspecialchars($question) ?>
                                            </button>
                                        </h2>
                                        <div id="faq-<?= $faqId ?>" class="accordion-collapse collapse"
                                            data-bs-parent="#faqAccordion">
                                            <div class="accordion-body">
                                                <?= nl2br(htmlspecialchars($answer)) ?>
                                                <?php if (!empty($relatedLinks)): ?>
                                                    <div class="related-links mt-3">
                                                        <h6>Related Resources:</h6>
                                                        <ul class="list-unstyled">
                                                            <?php
                                                            $links = json_decode($relatedLinks, true);
                                                            if (is_array($links)):
                                                                foreach ($links as $link):
                                                            ?>
                                                                    <li>
                                                                        <a href="<?= htmlspecialchars($link['url']) ?>" class="text-primary">
                                                                            <i class="fas fa-external-link-alt me-2"></i>
                                                                            <?= htmlspecialchars($link['title']) ?>
                                                                        </a>
                                                                    </li>
                                                            <?php
                                                                endforeach;
                                                            endif;
                                                            ?>
                                                        </ul>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <h3>No FAQs found</h3>
                            <p class="text-muted">Please try selecting a different category or check back later.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section