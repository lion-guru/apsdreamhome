<?php
$page_title = $page_title ?? 'त्योहार व विशेष ऑफर्स — Special Festival Booking Offers | APS Dream Home';
$page_description = $page_description ?? 'Explore current festival discounts, plot booking concessions, promo codes, and special gifts on your dream property.';
$offers = $offers ?? [];
?>

<div class="offers-page-wrapper py-4" style="background: linear-gradient(180deg, #f8fafc 0%, #edf2f7 100%); min-height: 80vh;">
    <div class="container py-4">

        <!-- HERO HEADER -->
        <div class="row justify-content-center text-center mb-5">
            <div class="col-lg-9">
                <span class="badge bg-warning text-dark px-3 py-2 rounded-pill fw-bold text-uppercase mb-3 shadow-sm" style="font-size: 0.85rem; letter-spacing: 1px;">
                    <i class="fas fa-gift me-2 text-danger"></i> त्योहार व विशेष बुकिंग ऑफर्स (Festive Deals & Promos)
                </span>
                <h1 class="display-5 fw-bold text-dark mb-3">
                    अपने सपनों का घर व प्लॉट पाएँ <span class="text-primary">विशेष फेस्टिव छूट</span> के साथ
                </h1>
                <p class="lead text-muted mb-4" style="font-size: 1.15rem;">
                    त्योहारों और विशेष अवसरों पर APS Dream Home की ओर से ग्राहकों के लिए विशेष प्लॉट डिस्काउंट, फ्लैट छूट और प्रोमो कोड। प्लॉट बुक करते समय प्रोमो कोड लागू करें और तुरंत छूट प्राप्त करें।
                </p>
                <div class="d-flex flex-wrap justify-content-center gap-3">
                    <a href="<?= BASE_URL ?>/plots" class="btn btn-primary btn-lg rounded-pill px-4 shadow-sm">
                        <i class="fas fa-map-marked-alt me-2"></i> एक्सप्लोर करें सभी प्लॉट्स (Browse Plots)
                    </a>
                    <a href="<?= BASE_URL ?>/become-associate" class="btn btn-outline-dark btn-lg rounded-pill px-4">
                        <i class="fas fa-handshake me-2 text-warning"></i> एसोसिएट रिवॉर्ड्स देखें (Partner Offers)
                    </a>
                </div>
            </div>
        </div>

        <!-- FILTER PILLS -->
        <?php if (!empty($offers)): ?>
        <div class="d-flex flex-wrap justify-content-center gap-2 mb-4">
            <button type="button" class="btn btn-sm btn-dark rounded-pill px-3 active offer-filter-btn" data-filter="all">
                <i class="fas fa-th-large me-1"></i> सभी ऑफर्स (All Offers)
            </button>
            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 offer-filter-btn" data-filter="flat_amount">
                <i class="fas fa-rupee-sign me-1"></i> फ्लैट छूट (Flat Cash Off)
            </button>
            <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-3 offer-filter-btn" data-filter="percentage">
                <i class="fas fa-percent me-1"></i> प्रतिशत छूट (% Off)
            </button>
            <button type="button" class="btn btn-sm btn-outline-info rounded-pill px-3 offer-filter-btn" data-filter="per_sqft">
                <i class="fas fa-ruler-combined me-1"></i> प्रति वर्गफुट छूट (Per Sq.Ft.)
            </button>
        </div>
        <?php endif; ?>

        <!-- OFFERS GRID -->
        <div class="row g-4 mb-5" id="offersGridContainer">
            <?php if (empty($offers)): ?>
                <div class="col-12 text-center py-5">
                    <div class="card border-0 shadow-sm p-5 rounded-4 mx-auto" style="max-width: 600px; background: #fff;">
                        <div class="mb-3">
                            <i class="fas fa-gift fa-3x text-warning"></i>
                        </div>
                        <h4 class="fw-bold text-dark mb-2">वर्तमान में नए फेस्टिव ऑफर्स तैयार हो रहे हैं</h4>
                        <p class="text-muted mb-4">
                            आगामी त्योहार व विशेष अभियानों पर नए बुकिंग ऑफर्स जल्द ही लाइव किए जाएंगे। आप हमारे सभी उपलब्ध टाउनशिप प्लॉट्स देख सकते हैं।
                        </p>
                        <a href="<?= BASE_URL ?>/plots" class="btn btn-primary rounded-pill px-4">
                            उपलब्ध प्लॉट्स देखें (View Plots)
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($offers as $o):
                    $discountType = $o['discount_type'] ?? 'percentage';
                    $discountText = '';
                    if ($discountType === 'flat_amount') {
                        $discountText = '₹' . number_format((float)($o['discount_value'] ?? 0)) . ' FLAT OFF';
                    } elseif ($discountType === 'percentage') {
                        $pct = (float)($o['discount_percentage'] ?? 0);
                        $discountText = ($pct > 0 ? $pct : (float)($o['discount_value'] ?? 0)) . '% OFF';
                    } elseif ($discountType === 'per_sqft') {
                        $discountText = '₹' . number_format((float)($o['discount_value'] ?? 0)) . '/sqft छूट';
                    } else {
                        $discountText = 'विशेष उपहार / कैशबैक';
                    }

                    $festivalName = !empty($o['festival_name']) ? $o['festival_name'] : 'Festive Special';
                    $promoCode = $o['promo_code'] ?? '';
                    $validUntil = !empty($o['valid_until']) ? date('d M Y', strtotime($o['valid_until'])) : 'Limited Time';
                    $minBooking = (float)($o['min_booking_amount'] ?? 0);
                ?>
                <div class="col-md-6 col-lg-4 offer-card-item" data-type="<?= htmlspecialchars($discountType) ?>">
                    <div class="card border-0 shadow-sm h-100 rounded-4 overflow-hidden position-relative" style="background: #ffffff; transition: transform 0.2s, box-shadow 0.2s;">
                        <!-- Festive Header Ribbon -->
                        <div class="p-3 text-white" style="background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="badge bg-warning text-dark px-2 py-1 rounded-pill fw-bold" style="font-size: 0.75rem;">
                                    <i class="fas fa-fire me-1 text-danger"></i> <?= htmlspecialchars($festivalName) ?>
                                </span>
                                <span class="small text-white-50">
                                    <i class="far fa-clock me-1"></i> वैध: <?= htmlspecialchars($validUntil) ?>
                                </span>
                            </div>
                            <h3 class="h5 fw-bold mb-0 text-white"><?= htmlspecialchars($o['title'] ?? 'Special Offer') ?></h3>
                        </div>

                        <!-- Card Body -->
                        <div class="card-body p-4 d-flex flex-column justify-content-between">
                            <div>
                                <!-- Discount Highlight -->
                                <div class="p-3 rounded-3 mb-3 text-center" style="background: #eff6ff; border: 1px dashed #3b82f6;">
                                    <div class="text-primary fw-bold display-6" style="font-size: 1.85rem;">
                                        <?= htmlspecialchars($discountText) ?>
                                    </div>
                                    <div class="text-muted small mt-1">
                                        <?= htmlspecialchars($o['description'] ?? 'त्योहार पर प्लॉट बुकिंग पर विशेष छूट') ?>
                                    </div>
                                </div>

                                <!-- Eligibility Badges -->
                                <ul class="list-unstyled mb-4 small text-muted">
                                    <?php if ($minBooking > 0): ?>
                                    <li class="mb-2">
                                        <i class="fas fa-check-circle text-success me-2"></i>
                                        न्यूनतम बुकिंग मूल्य: <strong>₹<?= number_format($minBooking) ?></strong>
                                    </li>
                                    <?php else: ?>
                                    <li class="mb-2">
                                        <i class="fas fa-check-circle text-success me-2"></i>
                                        सभी बुकिंग्स पर लागू (No Minimum Value)
                                    </li>
                                    <?php endif; ?>

                                    <li class="mb-2">
                                        <i class="fas fa-map-marker-alt text-primary me-2"></i>
                                        टाउनशिप: <strong><?= !empty($o['colony_name']) ? htmlspecialchars($o['colony_name']) : 'सभी अधिकृत टाउनशिप (All Colonies)' ?></strong>
                                    </li>
                                </ul>
                            </div>

                            <!-- Promo Code Box & CTA -->
                            <div>
                                <?php if (!empty($promoCode)): ?>
                                <div class="d-flex align-items-center justify-content-between p-2 rounded-3 bg-light border mb-3">
                                    <div class="ms-2">
                                        <span class="text-muted small d-block" style="font-size: 11px;">PROMO CODE:</span>
                                        <strong class="text-dark font-monospace fs-6" id="code-<?= (int)$o['id'] ?>"><?= htmlspecialchars($promoCode) ?></strong>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 copy-code-btn" data-code="<?= htmlspecialchars($promoCode) ?>">
                                        <i class="far fa-copy me-1"></i> Copy
                                    </button>
                                </div>
                                <?php endif; ?>

                                <a href="<?= BASE_URL ?>/plots<?= !empty($promoCode) ? '?promo=' . urlencode($promoCode) : '' ?>" class="btn btn-primary w-100 rounded-pill fw-bold py-2 shadow-sm">
                                    <i class="fas fa-shopping-cart me-2"></i> अभी बुक करें (Book Now)
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- ASSOCIATE PARTNER REWARDS BANNER -->
        <div class="card border-0 shadow-lg rounded-4 overflow-hidden mb-5 text-white" style="background: linear-gradient(135deg, #064e3b 0%, #047857 100%);">
            <div class="card-body p-4 p-md-5">
                <div class="row align-items-center g-4">
                    <div class="col-lg-8">
                        <span class="badge bg-warning text-dark px-3 py-1 rounded-pill fw-bold text-uppercase mb-2">
                            <i class="fas fa-award me-1"></i> एसोसिएट पार्टनर्स के लिए विशेष फेस्टिव रिवॉर्ड्स
                        </span>
                        <h2 class="h3 fw-bold mb-2">क्या आप APS एसोसिएट पार्टनर हैं?</h2>
                        <p class="text-white-50 mb-3" style="font-size: 1.05rem;">
                            हर त्योहार और विशेष अभियान पर एसोसिएट पार्टनर्स को मिलती है सामान्य प्लॉट कमीशन के अलावा अतिरिक्त नकद बोनस (सीधे वॉलेट में), सोने-चांदी के सिक्के, एलईडी टीवी, बाइक और टूर पैकेज रिवॉर्ड्स!
                        </p>
                        <div class="d-flex flex-wrap gap-2">
                            <a href="<?= BASE_URL ?>/associate/offers" class="btn btn-warning text-dark fw-bold rounded-pill px-4">
                                <i class="fas fa-gift me-2"></i> एसोसिएट ऑफर्स व टारगेट क्लेम करें
                            </a>
                            <a href="<?= BASE_URL ?>/associate/salary" class="btn btn-outline-light rounded-pill px-4">
                                नियमित मासिक लीडरशिप सैलरी प्लान
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-4 text-center">
                        <div class="p-4 rounded-4 bg-white bg-opacity-10 d-inline-block text-center" style="border: 1px solid rgba(255,255,255,0.2);">
                            <i class="fas fa-coins fa-3x text-warning mb-2"></i>
                            <div class="fs-5 fw-bold">₹5,000 से ₹1,00,000+</div>
                            <div class="small text-white-50">अतिरिक्त फेस्टिव कैश व गिफ्ट रिवॉर्ड्स</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- HOW TO REDEEM & FAQ -->
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white">
                    <h3 class="h4 fw-bold text-dark mb-4 text-center">
                        <i class="fas fa-question-circle text-primary me-2"></i> फेस्टिव ऑफर कैसे लागू करें? (Frequently Asked Questions)
                    </h3>
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="d-flex align-items-start gap-3">
                                <div class="rounded-circle bg-primary bg-opacity-10 p-3 text-primary flex-shrink-0">
                                    <i class="fas fa-tags fa-lg"></i>
                                </div>
                                <div>
                                    <h5 class="fw-bold mb-1">प्रोमो कोड कैसे लागू करें?</h5>
                                    <p class="text-muted small mb-0">
                                        प्लॉट चयन के बाद डिजिटल बुकिंग या चेकआउट स्क्रीन पर प्रोमो कोड दर्ज करें। सिस्टम वास्तविक समय में कुल प्लॉट मूल्य में से छूट काटकर शुद्ध देय राशि अपडेट कर देता है।
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="d-flex align-items-start gap-3">
                                <div class="rounded-circle bg-success bg-opacity-10 p-3 text-success flex-shrink-0">
                                    <i class="fas fa-university fa-lg"></i>
                                </div>
                                <div>
                                    <h5 class="fw-bold mb-1">क्या बैंक लोन या ईएमआई पर भी छूट मान्य है?</h5>
                                    <p class="text-muted small mb-0">
                                        हाँ, सभी फेस्टिव डिस्काउंट्स डाउन पेमेंट या कुल प्लॉट लागत में समायोजित किए जाते हैं, जिससे बैंक लोन और ईएमआई की मासिक किस्तें भी घट जाती हैं।
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="d-flex align-items-start gap-3">
                                <div class="rounded-circle bg-warning bg-opacity-10 p-3 text-dark flex-shrink-0">
                                    <i class="fas fa-calendar-alt fa-lg"></i>
                                </div>
                                <div>
                                    <h5 class="fw-bold mb-1">ऑफर की वैधता व शर्तें</h5>
                                    <p class="text-muted small mb-0">
                                        प्रत्येक ऑफर कार्ड पर वैधता तिथि अंकित है। अधिकतम रिडेम्पशन सीमा पूरी होने या तिथि समाप्त होने के बाद प्रोमो कोड स्वतः बंद हो जाते हैं।
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="d-flex align-items-start gap-3">
                                <div class="rounded-circle bg-info bg-opacity-10 p-3 text-info flex-shrink-0">
                                    <i class="fas fa-headset fa-lg"></i>
                                </div>
                                <div>
                                    <h5 class="fw-bold mb-1">मदद या स्पॉट बुकिंग सहायता</h5>
                                    <p class="text-muted small mb-0">
                                        किसी भी सहायता या स्पॉट बुकिंग छूट के लिए हमारे कस्टमर सपोर्ट अथवा निकटतम अधिकृत एसोसिएट से संपर्क करें: <strong>support@apsdreamhome.com</strong>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
document.addEventListener('DOMContentLoaded', function () {
    // 1. Promo Code Clipboard Copy
    const copyBtns = document.querySelectorAll('.copy-code-btn');
    copyBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            const code = this.getAttribute('data-code');
            if (!code) return;
            navigator.clipboard.writeText(code).then(() => {
                const originalHtml = this.innerHTML;
                this.innerHTML = '<i class="fas fa-check text-success me-1"></i> Copied!';
                this.classList.remove('btn-outline-primary');
                this.classList.add('btn-success', 'text-white');
                setTimeout(() => {
                    this.innerHTML = originalHtml;
                    this.classList.remove('btn-success', 'text-white');
                    this.classList.add('btn-outline-primary');
                }, 2000);
            }).catch(() => {
                alert('Code: ' + code);
            });
        });
    });

    // 2. Offer Category Filtering
    const filterBtns = document.querySelectorAll('.offer-filter-btn');
    const offerItems = document.querySelectorAll('.offer-card-item');

    filterBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            const filter = this.getAttribute('data-filter');

            filterBtns.forEach(b => {
                b.classList.remove('btn-dark', 'active');
                if (!b.classList.contains('btn-outline-primary') &&
                    !b.classList.contains('btn-outline-success') &&
                    !b.classList.contains('btn-outline-info')) {
                    b.classList.add('btn-outline-dark');
                }
            });

            this.classList.remove('btn-outline-dark', 'btn-outline-primary', 'btn-outline-success', 'btn-outline-info');
            this.classList.add('btn-dark', 'active');

            offerItems.forEach(item => {
                const itemType = item.getAttribute('data-type');
                if (filter === 'all' || itemType === filter) {
                    item.style.display = '';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    });
});
</script>

