<?php
$page_title = $page_title ?? 'Frequently Asked Questions - APS Dream Home';
$page_description = $page_description ?? 'Find answers to common questions about booking, legal policies, EMI plans, cancellation, and legal policies.';
$current_page = 'faqs';
?>
<style>
    .faq-hero { background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 100%); color: #fff; padding: 64px 0; }
    .faq-hero h1 { font-size: 2rem; font-weight: 800; margin-bottom: 12px; }
    .faq-hero p { opacity: .85; font-size: 1.05rem; max-width: 720px; margin-bottom: 20px; }
    .search-box { position: relative; max-width: 560px; }
    .search-box i { position: absolute; left: 18px; top: 50%; transform: translateY(-50%); color: #64748b; }
    .search-box input { width: 100%; min-height: 48px; padding: 0 18px 0 48px; border-radius: 50px; border: 2px solid transparent; font-size: 1rem; color: #0f172a; }
    .search-box input:focus { outline: none; border-color: #f4c542; box-shadow: 0 0 0 4px rgba(244,197,66,.25); }
    .faq-section { padding: 48px 0; }
    .section-head { margin-bottom: 24px; }
    .section-head h2 { font-size: 1.6rem; font-weight: 800; color: #0f172a; }
    .section-head p { color: #475569; margin: 6px 0 0; }
    .faq-wrap { display: flex; flex-direction: column; gap: 14px; }
    .trip-card { background: #fff; border: 1px solid #e2e8f0; border-left: 5px solid #f4c542; border-radius: 12px; padding: 18px 20px; box-shadow: 0 1px 3px rgba(15,23,42,.06); }
    .trip-card h3 { font-size: 1.05rem; font-weight: 700; margin-bottom: 8px; color: #0f172a; }
    .trip-card .num { background: #0f172a; color: #f4c542; border-radius: 6px; padding: 2px 8px; font-size: .8rem; margin-right: 8px; vertical-align: middle; }
    .trip-card p { color: #334155; font-size: .92rem; line-height: 1.6; margin-bottom: 10px; }
    .warn-tag { display: inline-flex; align-items: center; background: #fef3c7; color: #92400e; border-radius: 6px; padding: 4px 10px; font-size: .78rem; font-weight: 600; }
    .cat-label { font-size: .8rem; letter-spacing: 1px; text-transform: uppercase; font-weight: 700; color: #475569; margin-bottom: 10px; }
    .accordion-item { border: 1px solid #e2e8f0; border-radius: 10px !important; margin-bottom: 10px; overflow: hidden; }
    .accordion-button { min-height: 48px; padding: 14px 18px; font-weight: 600; color: #0f172a !important; background: #fff !important; font-size: .95rem; }
    .accordion-button:not(.collapsed) { background: #f8fafc !important; color: #0f766e !important; }
    .accordion-button:focus { box-shadow: 0 0 0 3px rgba(15,118,110,.3); }
    .accordion-header h2 { margin: 0; }
    .accordion-body { color: #334155; line-height: 1.6; border-top: 1px solid #e2e8f0; }
    .faq-cta { background: #1e3a5f; color: #fff; text-align: center; padding: 56px 20px; }
    .faq-cta h2 { font-size: 1.8rem; font-weight: 800; margin-bottom: 8px; color: #fff; }
    .faq-cta p { opacity: .85; margin-bottom: 20px; }
    .btn-cta { display: inline-flex; align-items: center; min-height: 48px; padding: 12px 22px; border-radius: 8px; font-weight: 700; text-decoration: none; margin: 4px; }
    .btn-gold { background: #f4c542; color: #1e293b !important; }
    .btn-gold:hover { background: #eab308; color: #1e293b !important; }
    .btn-outline { background: transparent; color: #fff !important; border: 2px solid rgba(255,255,255,.5); }
    .btn-outline:hover { background: rgba(255,255,255,.15); color: #fff !important; }
    @media (max-width: 575px) { .faq-hero h1 { font-size: 1.5rem; } .faq-cta h2 { font-size: 1.4rem; } }
</style>
<!-- ═══════════════════ HERO ═══════════════════ -->
<section class="faq-hero">
    <div class="container">
        <h1><i class="fas fa-question-circle me-2"></i> Frequently Asked Questions</h1>
        <p>Find answers about booking, legal policies, EMI plans, cancellation, and the Tripartite Master Deed.</p>
        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" id="faqSearch" placeholder="Search your question..." autocomplete="off" aria-label="Search frequently asked questions">
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
                <h3><span class="num">1</span> Is the booking amount refundable under the Tripartite Master Deed?</h3>
                <p>No. Once the Tripartite Agreement is signed and the booking amount is deposited, it becomes <strong>non-refundable</strong> under Clause 7 of the Deed. The booking amount is adjusted against the total plot value at the time of possession. If the buyer defaults on subsequent EMI payments or fails to complete documentation within 90 days, the entire deposited amount is forfeited and the developer reserves the right to re-allot the plot.</p>
                <span class="warn-tag"><i class="fas fa-exclamation-triangle me-1"></i> Non-refundable once signed</span>
            </div>

            <div class="trip-card">
                <h3><span class="num">2</span> What happens if my EMI bounces or I miss a payment?</h3>
                <p>Under the Tripartite Deed (Clause 11), an EMI bounce attracts a <strong>penalty of ₹500 per instance</strong> plus applicable bank charges. If two consecutive EMIs are missed, the developer issues a formal 15-day cure notice. Failure to regularize within the cure period triggers <strong>acceleration of the entire outstanding balance</strong>, making the full remaining amount immediately due. After 3 consecutive missed payments, the agreement stands terminated and Clause 7 (forfeiture) applies.</p>
                <span class="warn-tag"><i class="fas fa-exclamation-triangle me-1"></i> ₹500 penalty per bounce + acceleration clause</span>
            </div>

            <div class="trip-card">
                <h3><span class="num">3</span> If I cancel, when do I get my refund (minus deductions)?</h3>
                <p>Cancellation refund is processed within <strong>60 working days</strong> from the date of receipt of a written cancellation request (Clause 14). The refund is calculated as: <strong>Total deposited amount − 10% administrative charges − stamp duty & registration fees already paid to government</strong>. Any interest earned on the deposited amount is retained by the developer. Refund is made via NEFT/RTGS to the original payment source. No cash refunds are entertained.</p>
                <span class="warn-tag"><i class="fas fa-clock me-1"></i> 60 working days | 10% admin deduction</span>
            </div>

            <div class="trip-card">
                <h3><span class="num">4</span> Are GDA charges, RERA fees, and stamp duty included in the quoted price?</h3>
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
            <p>Browse all frequently asked questions or use the search bar above.</        </div>

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
        document.queryAll('.trip-card').forEach(card => {
            const text = card.textContent.toLowerCase();
            card.style.display = (!q || text.includes(q)) ? '' : 'none';
        });
        // Show/hide tripartite section heading if all hidden
        const tripCards = document.queryAll('.trip-card');
        const visibleTrip = Array.from(tripCards).some(c => c.style.display !== 'none');
        const tripSection = document.getElementById('tripSection');
        if (tripSection) tripSection.closest('.faq-section').style.display = visibleTrip ? '' : 'none';

        // Search DB accordion items
        document.queryAll('.faq-item').forEach(item => {
            const text = item.getAttribute('data-search') || '';
            item.style.display = (!q || text.includes(q)) ? '' : 'none';
        });
        // Show/hide category groups with no visible items
        document.queryAll('.faq-category-group').forEach(grp => {
            const vis = grp.querySelectorAll('.faq-item:not([style*="display: none"])');
            grp.style.display = vis.length ? '' : 'none';
        });
    });
</script>