
<!-- Hero Section -->
<section class="hero-section bg-light py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1 class="display-4 fw-bold">Frequently Asked Questions</h1>
                <p class="lead text-muted">Find answers to common questions about real estate and our services.</p>
                <div class="mt-4">
                    <form class="search-form" id="faqSearch" onsubmit="return false;">
                        <div class="input-group">
                            <input type="text" class="form-control" placeholder="Search FAQs..." id="searchInput">
                            <button class="btn btn-primary" type="button">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="col-lg-6">
                <!-- Fallback image or use generic asset -->
                <div class="bg-white p-5 rounded shadow-sm text-center">
                    <i class="fas fa-question-circle fa-5x text-primary mb-3"></i>
                    <h4>Have more questions?</h4>
                    <p>Contact our support team for personalized assistance.</p>
                    <a href="<?= BASE_URL ?>contact" class="btn btn-outline-primary">Contact Us</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FAQ Content -->
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
</section>

<script>
document.getElementById('searchInput').addEventListener('keyup', function() {
    let searchText = this.value.toLowerCase();
    let accordionItems = document.querySelectorAll('.accordion-item');
    
    accordionItems.forEach(item => {
        let question = item.querySelector('.accordion-button').textContent.toLowerCase();
        let answer = item.querySelector('.accordion-body').textContent.toLowerCase();
        
        if (question.includes(searchText) || answer.includes(searchText)) {
            item.style.display = 'block';
        } else {
            item.style.display = 'none';
        }
    });
});
</script>
