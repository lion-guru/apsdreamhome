<?php
$layout = 'layouts/customer';
$page_title = $page_title ?? 'Insurance - APS Dream Home';
$current_page = 'insurance';

ob_start();
?>
<div class="aps-cp-page-header">
    <h2><i class="fas fa-shield-alt"></i> Insurance Plans</h2>
    <p>Protect your property and family with our insurance partners.</p>
</div>

<div class="aps-cp-stat-grid">
    <div class="aps-cp-stat aps-cp-stat-blue">
        <div class="aps-cp-stat-icon"><i class="fas fa-home"></i></div>
        <div class="aps-cp-stat-value">3</div>
        <div class="aps-cp-stat-label">Home Insurance</div>
    </div>
    <div class="aps-cp-stat aps-cp-stat-green">
        <div class="aps-cp-stat-icon"><i class="fas fa-heartbeat"></i></div>
        <div class="aps-cp-stat-value">2</div>
        <div class="aps-cp-stat-label">Health Insurance</div>
    </div>
    <div class="aps-cp-stat aps-cp-stat-orange">
        <div class="aps-cp-stat-icon"><i class="fas fa-umbrella"></i></div>
        <div class="aps-cp-stat-value">1</div>
        <div class="aps-cp-stat-label">Term Life</div>
    </div>
    <div class="aps-cp-stat aps-cp-stat-purple">
        <div class="aps-cp-stat-icon"><i class="fas fa-shield-alt"></i></div>
        <div class="aps-cp-stat-value">6</div>
        <div class="aps-cp-stat-label">Total Plans</div>
    </div>
</div>

<div class="aps-cp-card mt-3">
    <div class="aps-cp-card-header">
        <h3>Featured Plans</h3>
    </div>
    <div class="aps-cp-card-body">
        <div class="aps-cp-info-grid">
            <div class="aps-cp-info-card">
                <div class="aps-cp-info-card-head">
                    <h4><i class="fas fa-home"></i> Home Shield Plan</h4>
                    <span class="aps-cp-badge aps-cp-badge-success">Recommended</span>
                </div>
                <p class="aps-cp-info-card-meta">Property + Contents coverage up to ₹1 Cr</p>
                <ul class="aps-cp-list">
                    <li>Fire, theft, natural disasters</li>
                    <li>Personal accident cover</li>
                    <li>Free annual inspection</li>
                </ul>
                <div class="aps-cp-info-card-foot">
                    <strong>From ₹499/month</strong>
                    <button class="aps-cp-btn aps-cp-btn-sm">Enquire</button>
                </div>
            </div>

            <div class="aps-cp-info-card">
                <div class="aps-cp-info-card-head">
                    <h4><i class="fas fa-heartbeat"></i> Family Health Plan</h4>
                    <span class="aps-cp-badge aps-cp-badge-primary">Top Pick</span>
                </div>
                <p class="aps-cp-info-card-meta">Cashless treatment up to ₹25 Lakh</p>
                <ul class="aps-cp-list">
                    <li>10,000+ network hospitals</li>
                    <li>Pre & post hospitalization cover</li>
                    <li>Free annual health check-up</li>
                </ul>
                <div class="aps-cp-info-card-foot">
                    <strong>From ₹1,299/month</strong>
                    <button class="aps-cp-btn aps-cp-btn-sm">Enquire</button>
                </div>
            </div>

            <div class="aps-cp-info-card">
                <div class="aps-cp-info-card-head">
                    <h4><i class="fas fa-umbrella"></i> Life Secure Term Plan</h4>
                </div>
                <p class="aps-cp-info-card-meta">Pure protection up to ₹1 Cr cover</p>
                <ul class="aps-cp-list">
                    <li>Affordable premium</li>
                    <li>Tax benefits under 80C</li>
                    <li>Critical illness rider</li>
                </ul>
                <div class="aps-cp-info-card-foot">
                    <strong>From ₹649/month</strong>
                    <button class="aps-cp-btn aps-cp-btn-sm">Enquire</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="aps-cp-alert aps-cp-alert-info mt-3">
    <i class="fas fa-info-circle"></i> Insurance plans are powered by our partner network. Premiums and terms shown are indicative; final policy subject to insurer underwriting.
</div>

<?php
$content = ob_get_clean();
include APP_ROOT . '/app/views/layouts/customer.php';
