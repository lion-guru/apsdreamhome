<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title ?? 'FAQs - APS Dream Home') ?></title>
    <meta name="description" content="Frequently asked questions about APS Dream Home — Tripartite Master Deed, booking, EMI, cancellation, and legal policies.">
    <link href="<?= BASE_URL ?>/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/fonts/fontawesome/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --navy: #2c3e50;
            --trust-blue: #2980b9;
            --gold: #d4af37;
            --bg: #f4f6f9;
        }
        *, *::before, *::after { box-sizing: border-box; }
        body { margin: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: var(--bg); color: #1a1a2e; line-height: 1.7; }

        /* ── Hero ── */
        .faq-hero {
            background: linear-gradient(135deg, var(--navy) 0%, var(--trust-blue) 100%);
            color: #fff;
            padding: 56px 20px 48px;
            text-align: center;
        }
        .faq-hero h1 { font-size: 2.4rem; font-weight: 700; margin-bottom: 12px; }
        .faq-hero p { font-size: 1.05rem; color: #cbd5e1; max-width: 600px; margin: 0 auto 20px; }
        .faq-hero .search-box {
            max-width: 480px;
            margin: 0 auto;
            position: relative;
        }
        .faq-hero .search-box input {
            width: 100%;
            padding: 14px 20px 14px 48px;
            border-radius: 10px;
            border: 2px solid rgba(255,255,255,0.2);
            background: rgba(255,255,255,0.1);
            color: #fff;
            font-size: 1rem;
            outline: none;
            backdrop-filter: blur(8px);
        }
        .faq-hero .search-box input::placeholder { color: #94a3b8; }
        .faq-hero .search-box input:focus { border-color: var(--gold); }
        .faq-hero .search-box i { position: absolute; left: 18px; top: 50%; transform: translateY(-50%); color: #94a3b8; }

        /* ── Section ── */
        .faq-section { padding: 52px 20px; }
        .faq-section .section-head { text-align: center; margin-bottom: 36px; }
        .faq-section .section-head h2 { font-size: 1.7rem; color: var(--navy); font-weight: 700; margin-bottom: 6px; }
        .faq-section .section-head p { color: #64748b; max-width: 540px; margin: 0 auto; }

        /* ── Category Label ── */
        .cat-label {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 1.05rem;
            font-weight: 700;
            color: var(--navy);
            margin-bottom: 16px;
            padding-bottom: 8px;
            border-bottom: 2px solid var(--gold);
        }
        .cat-label i { color: var(--trust-blue); }

        /* ── Accordion ── */
        .faq-wrap { max-width: 860px; margin: 0 auto; }
        .faq-wrap .accordion-item { border: 1px solid #e2e8f0; border-radius: 10px !important; margin-bottom: 12px; overflow: hidden; }
        .faq-wrap .accordion-button {
            font-size: 1rem;
            font-weight: 600;
            color: var(--navy);
            padding: 18px 22px;
            background: #fff;
            box-shadow: none;
        }
        .faq-wrap .accordion-button:not(.collapsed) { background: #f1f5f9; color: var(--trust-blue); }
        .faq-wrap .accordion-button::after { filter: none; }
        .faq-wrap .accordion-body { padding: 0 22px 20px; color: #475569; font-size: 0.95rem; line-height: 1.8; }
        .faq-wrap .accordion-body strong { color: var(--navy); }

        /* ── Tripartite Highlight ── */
        .trip-card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-left: 4px solid var(--gold);
            border-radius: 10px;
            padding: 24px 28px;
            margin-bottom: 16px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.04);
        }
        .trip-card h4 { font-size: 1.05rem; color: var(--navy); font-weight: 700; margin: 0 0 8px; display: flex; align-items: flex-start; gap: 10px; }
        .trip-card h4 .num { flex-shrink: 0; width: 28px; height: 28px; border-radius: 50%; background: var(--trust-blue); color: #fff; display: inline-flex; align-items: center; justify-content: center; font-size: 0.8rem; font-weight: 700; }
        .trip-card p { margin: 0; color: #475569; font-size: 0.95rem; }
        .trip-card .warn-tag { display: inline-block; background: #fef3c7; color: #92400e; font-size: 0.78rem; font-weight: 600; padding: 3px 10px; border-radius: 20px; margin-top: 10px; }

        /* ── CTA ── */
        .faq-cta {
            background: linear-gradient(135deg, var(--navy) 0%, var(--trust-blue) 100%);
            color: #fff;
            text-align: center;
            padding: 52px 20px;
        }
        .faq-cta h2 { font-size: 1.6rem; margin-bottom: 8px; }
        .faq-cta p { color: #cbd5e1; margin-bottom: 24px; }
        .faq-cta .btn-cta { display: inline-block; padding: 13px 32px; border-radius: 8px; font-weight: 700; font-size: 1rem; text-decoration: none; transition: all 0.3s; margin: 0 6px 10px; }
        .faq-cta .btn-gold { background: var(--gold); color: var(--navy); }
        .faq-cta .btn-gold:hover { background: #e6c24a; transform: translateY(-2px); }
        .faq-cta .btn-outline { border: 2px solid rgba(255,255,255,0.4); color: #fff; }
        .faq-cta .btn-outline:hover { border-color: #fff; background: rgba(255,255,255,0.1); }

        .hidden { display: none !important; }

        @media (max-width: 768px) {
            .faq-hero h1 { font-size: 1.8rem; }
        }
    </style>
</head>
<body>

<!-- ═══════════════════ HERO ═══════════════════ -->
<section class="faq-hero">
    <div class="container">
        <h1><i class="fas fa-question-circle me-2"></i> Frequently Asked Questions</h1>
        <p>Find answers about booking, legal policies, EMI plans, cancellation, and the Tripartite Master Deed.</p>
        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" id="faqSearch" placeholder="Search your question..." autocomplete="off">
        </div>
    </div>
</section>

<!-- ═══════════════════ TRIPARTITE MASTER DEED ═══════════════════ -->
<section class="faq-section" style="background:#fff;">
    <div class="container">
        <div class="section-head">
            <h2><i class="fas fa-file-contract me-2" style="color:var(--gold);"></i> Tripartite Master Deed</h2>
            <p>Critical questions every buyer must understand before signing the Tripartite Agreement.</p>
        </div>

        <div class="faq-wrap" id="tripSection">
            <div class="trip-card">
                <h4><span class="num">1</span> Is the booking amount refundable under the Tripartite Master Deed?</h4>
                <p>No. Once the Tripartite Agreement is signed and the booking amount is deposited, it becomes <strong>non-refundable</strong> under Clause 7 of the Deed. The booking amount is adjusted against the total plot value at the time of possession. If the buyer defaults on subsequent EMI payments or fails to complete documentation within 90 days, the entire deposited amount is forfeited and the developer reserves the right to re-allot the plot.</p>
                <span class="warn-tag"><i class="fas fa-exclamation-triangle me-1"></i> Non-refundable once signed</span>
            </div>

            <div class="trip-card">
                <h4><span class="num">2</span> What happens if my EMI bounces or I miss a payment?</h4>
                <p>Under the Tripartite Deed (Clause 11), an EMI bounce attracts a <strong>penalty of ₹500 per instance</strong> plus applicable bank charges. If two consecutive EMIs are missed, the developer issues a formal 15-day cure notice. Failure to regularize within the cure period triggers <strong>acceleration of the entire outstanding balance</strong>, making the full remaining amount immediately due. After 3 consecutive missed payments, the agreement stands terminated and Clause 7 (forfeiture) applies.</p>
                <span class="warn-tag"><i class="fas fa-exclamation-triangle me-1"></i> ₹500 penalty per bounce + acceleration clause</span>
            </div>

            <div class="trip-card">
                <h4><span class="num">3</span> If I cancel, when do I get my refund (minus deductions)?</h4>
                <p>Cancellation refund is processed within <strong>60 working days</strong> from the date of receipt of a written cancellation request (Clause 14). The refund is calculated as: <strong>Total deposited amount − 10% administrative charges − stamp duty &amp; registration fees already paid to government</strong>. Any interest earned on the deposited amount is retained by the developer. Refund is made via NEFT/RTGS to the original payment source. No cash refunds are entertained.</p>
                <span class="warn-tag"><i class="fas fa-clock me-1"></i> 60 working days | 10% admin deduction</span>
            </div>

            <div class="trip-card">
                <h4><span class="num">4</span> Are GDA charges, RERA fees, and stamp duty included in the quoted price?</h4>
                <p>No. The quoted plot price is <strong>exclusive of all government levies</strong>. As per the Tripartite Deed (Schedule B), the buyer is additionally liable for: <strong>GDA conversion/development charges</strong> (as notified by GDA, currently ₹612/sq m for residential), <strong>RERA registration fee</strong> (0.5% of plot value, capped at ₹50,000), <strong>stamp duty</strong> (7% in Uttar Pradesh) and <strong>registration charges</strong> (2% in UP). These are payable at the time of registry and are not part of the EMI plan.</p>
                <span class="warn-tag"><i class="fas fa-receipt me-1"></i> Government levies excluded from quoted price</span>
            </div>
        </div>
    </div>
</section>

<!-- ═══════════════════ GENERAL FAQs (DB-driven) ═══════════════════ -->
<?php if (!empty($faqs_grouped)): ?>
<section class="faq-section">
    <div class="container">
        <div class="section-head">
            <h2><i class="fas fa-list-ul me-2" style="color:var(--trust-blue);"></i> More Questions</h2>
            <p>Browse all frequently asked questions or use the search bar above.</p>
        </div>

        <div class="faq-wrap" id="dbFaqSection">
            <?php
            $catIcons = [
                'General'   => 'fas fa-info-circle',
                'Booking'   => 'fas fa-calendar-check',
                'Financial' => 'fas fa-university',
                'Legal'     => 'fas fa-gavel',
                'Payment'   => 'fas fa-credit-card',
            ];
            foreach ($faqs_grouped as $category => $faqs):
                $icon = $catIcons[$category] ?? 'fas fa-question';
            ?>
            <div class="mb-4 faq-category-group">
                <div class="cat-label"><i class="<?= $icon ?>"></i> <?= htmlspecialchars($category) ?></div>
                <?php foreach ($faqs as $i => $faq): ?>
                <div class="accordion-item faq-item" data-search="<?= htmlspecialchars(strtolower($faq['question'] . ' ' . strip_tags($faq['answer'] ?? ''))) ?>">
                    <h2 class="accordion-header" id="dbQ<?= $faq['id'] ?>">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#dbA<?= $faq['id'] ?>" aria-expanded="false">
                            <?= htmlspecialchars($faq['question']) ?>
                        </button>
                    </h2>
                    <div id="dbA<?= $faq['id'] ?>" class="accordion-collapse collapse" data-bs-parent="#dbAccordion">
                        <div class="accordion-body"><?= $faq['answer'] ?? '' ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ═══════════════════ CTA ═══════════════════ -->
<section class="faq-cta">
    <h2>Still Have Questions?</h2>
    <p>Our team is available to help you understand every clause of the agreement.</p>
    <a href="<?= BASE_URL ?>/contact" class="btn-cta btn-gold"><i class="fas fa-envelope me-2"></i>Email Us</a>
    <a href="tel:919277121112" class="btn-cta btn-outline"><i class="fas fa-phone-alt me-2"></i>Call: 92771 21112</a>
</section>

<!-- Bootstrap JS (for accordion) -->
<script src="<?= BASE_URL ?>/assets/js/bootstrap.bundle.min.js"></script>

<script>
// ── FAQ Search ──
document.getElementById('faqSearch')?.addEventListener('input', function() {
    const q = this.value.toLowerCase().trim();

    // Search tripartite cards
    document.querySelectorAll('.trip-card').forEach(card => {
        const text = card.textContent.toLowerCase();
        card.style.display = (!q || text.includes(q)) ? '' : 'none';
    });
    // Show/hide tripartite section heading if all hidden
    const tripCards = document.querySelectorAll('.trip-card');
    const visibleTrip = Array.from(tripCards).some(c => c.style.display !== 'none');
    const tripSection = document.getElementById('tripSection');
    if (tripSection) tripSection.closest('.faq-section').style.display = visibleTrip ? '' : 'none';

    // Search DB accordion items
    document.querySelectorAll('.faq-item').forEach(item => {
        const text = item.getAttribute('data-search') || '';
        item.style.display = (!q || text.includes(q)) ? '' : 'none';
    });
    // Show/hide category groups with no visible items
    document.querySelectorAll('.faq-category-group').forEach(grp => {
        const vis = grp.querySelectorAll('.faq-item:not([style*="display: none"])');
        grp.style.display = vis.length ? '' : 'none';
    });
});
</script>

</body>
</html>
