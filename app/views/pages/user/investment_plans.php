<?php
$layout = 'layouts/customer';
$page_title = $page_title ?? 'Investment Plans - APS Dream Home';
$current_page = 'investment';

ob_start();
?>
<div class="aps-cp-page-header">
    <h2><i class="fas fa-chart-line"></i> Investment Plans</h2>
    <p>Grow your wealth with property-backed and traditional investment options.</p>
</div>

<div class="aps-cp-stat-grid">
    <div class="aps-cp-stat aps-cp-stat-green">
        <div class="aps-cp-stat-icon"><i class="fas fa-coins"></i></div>
        <div class="aps-cp-stat-value" data-countup="125000">0</div>
        <div class="aps-cp-stat-label">Total Invested (₹)</div>
    </div>
    <div class="aps-cp-stat aps-cp-stat-blue">
        <div class="aps-cp-stat-icon"><i class="fas fa-trending-up"></i></div>
        <div class="aps-cp-stat-value" data-countup="18750">0</div>
        <div class="aps-cp-stat-label">Total Returns (₹)</div>
    </div>
    <div class="aps-cp-stat aps-cp-stat-purple">
        <div class="aps-cp-stat-icon"><i class="fas fa-percentage"></i></div>
        <div class="aps-cp-stat-value">15</div>
        <div class="aps-cp-stat-label">Avg. Return (%)</div>
    </div>
    <div class="aps-cp-stat aps-cp-stat-orange">
        <div class="aps-cp-stat-icon"><i class="fas fa-star"></i></div>
        <div class="aps-cp-stat-value">Gold</div>
        <div class="aps-cp-stat-label">Investor Level</div>
    </div>
</div>

<div class="aps-cp-card mt-3">
    <div class="aps-cp-card-header">
        <h3>Active Plans</h3>
    </div>
    <div class="aps-cp-card-body">
        <div class="aps-cp-info-grid">
            <div class="aps-cp-info-card">
                <div class="aps-cp-info-card-head">
                    <h4><i class="fas fa-building"></i> Property SIP</h4>
                    <span class="aps-cp-badge aps-cp-badge-success">Active</span>
                </div>
                <p class="aps-cp-info-card-meta">₹5,000/month for 24 months</p>
                <ul class="aps-cp-list">
                    <li>Builds corpus for property purchase</li>
                    <li>12% projected annual return</li>
                    <li>Auto-invests in property fund</li>
                </ul>
                <div class="aps-cp-info-card-foot">
                    <strong>Months active: 8</strong>
                    <button class="aps-cp-btn aps-cp-btn-sm">View Details</button>
                </div>
            </div>

            <div class="aps-cp-info-card">
                <div class="aps-cp-info-card-head">
                    <h4><i class="fas fa-landmark"></i> Real Estate Fund</h4>
                    <span class="aps-cp-badge aps-cp-badge-success">Active</span>
                </div>
                <p class="aps-cp-info-card-meta">Lump sum ₹50,000</p>
                <ul class="aps-cp-list">
                    <li>REIT-backed investment</li>
                    <li>Quarterly dividend payouts</li>
                    <li>Liquidity after 1 year</li>
                </ul>
                <div class="aps-cp-info-card-foot">
                    <strong>Returns: ₹7,500 (15%)</strong>
                    <button class="aps-cp-btn aps-cp-btn-sm">View Details</button>
                </div>
            </div>

            <div class="aps-cp-info-card">
                <div class="aps-cp-info-card-head">
                    <h4><i class="fas fa-piggy-bank"></i> Recurring Deposit</h4>
                </div>
                <p class="aps-cp-info-card-meta">₹10,000/month for 36 months</p>
                <ul class="aps-cp-list">
                    <li>Fixed return: 7.5% p.a.</li>
                    <li>Tax-saving under 80C</li>
                    <li>Premature withdrawal allowed</li>
                </ul>
                <div class="aps-cp-info-card-foot">
                    <strong>Maturity: ₹4,05,000</strong>
                    <button class="aps-cp-btn aps-cp-btn-sm aps-cp-btn-primary">Enroll</button>
                </div>
            </div>

            <div class="aps-cp-info-card">
                <div class="aps-cp-info-card-head">
                    <h4><i class="fas fa-gem"></i> Gold Saver</h4>
                </div>
                <p class="aps-cp-info-card-meta">Digital gold from ₹100</p>
                <ul class="aps-cp-list">
                    <li>24K pure gold, 999 purity</li>
                    <li>Insured vault storage</li>
                    <li>Redeemable as coins/bars</li>
                </ul>
                <div class="aps-cp-info-card-foot">
                    <strong>Holding: 12.5g</strong>
                    <button class="aps-cp-btn aps-cp-btn-sm aps-cp-btn-primary">Add More</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="aps-cp-card mt-3">
    <div class="aps-cp-card-header">
        <h3><i class="fas fa-trophy"></i> Investor Level &amp; Rewards</h3>
    </div>
    <div class="aps-cp-card-body">
        <div class="aps-cp-info-grid">
            <div class="aps-cp-info-card">
                <h4>Current: Gold Investor</h4>
                <p>Reach <strong>Platinum</strong> by investing ₹5,00,000+ total</p>
                <div class="aps-cp-progress" style="background:#e2e8f0;border-radius:8px;height:12px;overflow:hidden;margin-top:10px;">
                    <div style="width:25%;height:100%;background:linear-gradient(90deg,#fbbf24,#f59e0b);"></div>
                </div>
                <small>25% to Platinum (₹1,25,000 / ₹5,00,000)</small>
            </div>
            <div class="aps-cp-info-card">
                <h4>Available Rewards</h4>
                <ul class="aps-cp-list">
                    <li>Priority property allocation</li>
                    <li>Free legal consultation</li>
                    <li>Reduced brokerage (0.5%)</li>
                    <li>Exclusive new-launch previews</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include APP_ROOT . '/app/views/layouts/customer.php';
