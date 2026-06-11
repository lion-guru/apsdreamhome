<?php $pageTitle = $pageTitle ?? $page_title ?? "Share Property"; $property = $property ?? []; $base = $base ?? BASE_URL; ?>
<div class="container py-4">
    <h4><i class="fas fa-share-alt me-2"></i>Share Property</h4>
    <div class="card mt-3"><div class="card-body aps-cp-card-body">
        <h5><?= h($property["title"] ?? "Property") ?></h5>
        <p class="text-muted"><?= h($property["location"] ?? "") ?> - ?<?= number_format($property["price"] ?? 0) ?></p>
        <hr>
        <p class="mb-2">Share via:</p>
        <div class="d-flex gap-2">
            <a href="https://wa.me/?text=<?= urlencode($property["title"] ?? "") ?>" target="_blank" class="btn btn-success"><i class="fab fa-whatsapp me-1"></i>WhatsApp</a>
            <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode($base . "property/" . ($property["id"] ?? "")) ?>" target="_blank" class="btn btn-primary"><i class="fab fa-facebook me-1"></i>Facebook</a>
            <a href="https://twitter.com/intent/tweet?text=<?= urlencode($property["title"] ?? "") ?>" target="_blank" class="btn btn-info text-white"><i class="fab fa-twitter me-1"></i>Twitter</a>
        </div>
        <hr>
        <p class="small text-muted">Or copy link: <input type="text" class="form-control form-control-sm mt-1" value="<?= htmlspecialchars($base, ENT_QUOTES, 'UTF-8') ?>property/<?= $property["id"] ?? "" ?>" readonly onclick="this.select()"></p>
    </div></div>
</div>