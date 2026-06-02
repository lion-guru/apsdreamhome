<div class="container mt-4">
    <nav aria-label="breadcrumb"><ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>">Home</a></li>
        <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/faq-list">FAQs</a></li>
        <li class="breadcrumb-item active">FAQ Detail</li>
    </ol></nav>
    <h1><?php echo htmlspecialchars($faq['question'] ?? ''); ?></h1>
    <div class="mt-4"><?php echo nl2br(htmlspecialchars($faq['answer'] ?? '')); ?></div>
    <a href="<?php echo BASE_URL; ?>/faq-list" class="btn btn-secondary mt-3">&larr; Back to FAQs</a>
</div>
