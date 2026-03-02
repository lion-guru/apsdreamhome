<!-- Page Header -->
<section class="calculator-hero-section section-padding bg-primary text-white text-center rounded-bottom-4 py-5" data-aos="fade-down">
    <div class="container py-4">
        <h1 class="display-5 fw-bold mb-2">Commission Calculator</h1>
        <p class="lead mb-0">Discover your potential income with APS Dream Homes MLM program.</p>
    </div>
</section>

<!-- Breadcrumb -->
<nav class="bg-light border-bottom py-2">
    <div class="container">
        <ol class="breadcrumb mb-0">
            <?php foreach ($breadcrumbs as $crumb): ?>
                <?php if (isset($crumb['url'])): ?>
                    <li class="breadcrumb-item"><a href="<?= $crumb['url'] ?>"><?= $crumb['title'] ?></a></li>
                <?php else: ?>
                    <li class="breadcrumb-item active"><?= $crumb['title'] ?></li>
                <?php endif; ?>
            <?php endforeach; ?>
        </ol>
    </div>
</nav>

<!-- Stats Section (Moved from old hero) -->
<section class="py-5 bg-white border-bottom">
    <div class="container">
        <div class="row g-4 text-center">
            <div class="col-md-4" data-aos="fade-up">
                <div class="p-4 rounded-4 bg-light">
                    <h2 class="fw-bold text-primary mb-1">7%</h2>
                    <p class="text-muted mb-0">Direct Commission</p>
                </div>
            </div>
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                <div class="p-4 rounded-4 bg-light">
                    <h2 class="fw-bold text-primary mb-1">10</h2>
                    <p class="text-muted mb-0">Commission Levels</p>
                </div>
            </div>
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                <div class="p-4 rounded-4 bg-light">
                    <h2 class="fw-bold text-primary mb-1">28%</h2>
                    <p class="text-muted mb-0">Total Potential</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Main Calculator Section -->
<section class="calculator-section py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-12 text-center mb-5">
                <h2 class="section-title">Interactive Commission Calculator</h2>
                <p class="section-subtitle">Adjust the parameters below to see your earning potential</p>
            </div>
        </div>
        
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="calculator-card">
                    <div class="card-header">
                        <h4>📊 Earnings Parameters</h4>
                    </div>
                    <div class="card-body">
                        <form id="commissionCalculator">
                            <div class="row g-4">
                                <!-- Property Sale Amount -->
                                <div class="col-md-6">
                                    <label class="form-label">
                                        <i class="fas fa-home me-2"></i>Average Property Sale Amount
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text">₹</span>
                                        <input type="number" class="form-control" id="propertyAmount" value="1000000" min="100000" step="50000">
                                        <span class="input-group-text">Lakhs</span>
                                    </div>
                                    <div class="form-text">Typical property value (₹10L - ₹5Cr)</div>
                                </div>
                                
                                <!-- Direct Referrals -->
                                <div class="col-md-6">
                                    <label class="form-label">
                                        <i class="fas fa-users me-2"></i>Direct Referrals
                                    </label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="directReferrals" value="5" min="0" max="100">
                                        <span class="input-group-text">People</span>
                                    </div>
                                    <div class="form-text">People you directly refer</div>
                                </div>
                                
                                <!-- Team Size -->
                                <div class="col-md-6">
                                    <label class="form-label">
                                        <i class="fas fa-network-wired me-2"></i>Total Team Size
                                    </label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="teamSize" value="25" min="0" max="1000">
                                        <span class="input-group-text">People</span>
                                    </div>
                                    <div class="form-text">Your entire network size</div>
                                </div>
                                
                                <!-- Monthly Sales -->
                                <div class="col-md-6">
                                    <label class="form-label">
                                        <i class="fas fa-chart-line me-2"></i>Avg Monthly Sales per Person
                                    </label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="monthlySales" value="2" min="0" max="20" step="0.5">
                                        <span class="input-group-text">Sales</span>
                                    </div>
                                    <div class="form-text">Average sales per team member</div>
                                </div>
                                
                                <!-- Active Rate -->
                                <div class="col-md-6">
                                    <label class="form-label">
                                        <i class="fas fa-percentage me-2"></i>Team Active Rate
                                    </label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="activeRate" value="30" min="10" max="100" step="5">
                                        <span class="input-group-text">%</span>
                                    </div>
                                    <div class="form-text">Percentage of active team members</div>
                                </div>
                                
                                <!-- Work Hours -->
                                <div class="col-md-6">
                                    <label class="form-label">
                                        <i class="fas fa-clock me-2"></i>Hours per Week
                                    </label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="workHours" value="20" min="5" max="80" step="5">
                                        <span class="input-group-text">Hours</span>
                                    </div>
                                    <div class="form-text">Time you invest weekly</div>
                                </div>
                            </div>
                            
                            <div class="text-center mt-4">
                                <button type="button" class="btn btn-primary btn-lg" onclick="calculateCommissions()">
                                    <i class="fas fa-calculator me-2"></i>Calculate My Earnings
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Results Section -->
<section id="resultsSection" class="results-section py-5 bg-light" style="display: none;">
    <div class="container">
        <div class="row">
            <div class="col-lg-12 text-center mb-5">
                <h2 class="section-title">💰 Your Potential Earnings</h2>
                <p class="section-subtitle">Based on your parameters, here's your earning potential</p>
            </div>
        </div>
        
        <div class="row g-4">
            <!-- Monthly Earnings -->
            <div class="col-lg-6">
                <div class="result-card main-result">
                    <div class="result-header">
                        <h4>Monthly Earnings</h4>
                        <div class="result-icon">📅</div>
                    </div>
                    <div class="result-body">
                        <h2 id="monthlyEarnings">₹0</h2>
                        <p class="result-description">Your estimated monthly income</p>
                    </div>
                </div>
            </div>
            
            <!-- Annual Earnings -->
            <div class="col-lg-6">
                <div class="result-card">
                    <div class="result-header">
                        <h4>Annual Earnings</h4>
                        <div class="result-icon">📊</div>
                    </div>
                    <div class="result-body">
                        <h2 id="annualEarnings">₹0</h2>
                        <p class="result-description">Your estimated annual income</p>
                    </div>
                </div>
            </div>
            
            <!-- Hourly Rate -->
            <div class="col-lg-6">
                <div class="result-card">
                    <div class="result-header">
                        <h4>Effective Hourly Rate</h4>
                        <div class="result-icon">⏰</div>
                    </div>
                    <div class="result-body">
                        <h2 id="hourlyRate">₹0</h2>
                        <p class="result-description">Your effective earnings per hour</p>
                    </div>
                </div>
            </div>
            
            <!-- Network Value -->
            <div class="col-lg-6">
                <div class="result-card">
                    <div class="result-header">
                        <h4>Network Value</h4>
                        <div class="result-icon">🌐</div>
                    </div>
                    <div class="result-body">
                        <h2 id="networkValue">₹0</h2>
                        <p class="result-description">Total value of your network</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Detailed Breakdown -->
        <div class="row mt-5">
            <div class="col-lg-12">
                <div class="breakdown-card">
                    <div class="card-header">
                        <h4>📈 Commission Breakdown</h4>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <div class="breakdown-item">
                                    <span class="breakdown-label">Direct Commissions (Level 1)</span>
                                    <span class="breakdown-value" id="directCommission">₹0</span>
                                    <div class="progress">
                                        <div class="progress-bar bg-success" id="directCommissionBar" style="width: 0%"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="breakdown-item">
                                    <span class="breakdown-label">Level 2-3 Commissions</span>
                                    <span class="breakdown-value" id="level23Commission">₹0</span>
                                    <div class="progress">
                                        <div class="progress-bar bg-info" id="level23CommissionBar" style="width: 0%"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="breakdown-item">
                                    <span class="breakdown-label">Level 4-7 Commissions</span>
                                    <span class="breakdown-value" id="level47Commission">₹0</span>
                                    <div class="progress">
                                        <div class="progress-bar bg-warning" id="level47CommissionBar" style="width: 0%"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="breakdown-item">
                                    <span class="breakdown-label">Level 8-10 Commissions</span>
                                    <span class="breakdown-value" id="level810Commission">₹0</span>
                                    <div class="progress">
                                        <div class="progress-bar bg-secondary" id="level810CommissionBar" style="width: 0%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Commission Structure -->
<section class="commission-structure py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-12 text-center mb-5">
                <h2 class="section-title">🎯 Commission Structure</h2>
                <p class="section-subtitle">Multi-level commission rates for maximum earning potential</p>
            </div>
        </div>
        
        <div class="row g-4">
            <div class="col-md-6">
                <div class="structure-card">
                    <div class="structure-header bg-primary text-white">
                        <h4>Direct Commissions</h4>
                    </div>
                    <div class="structure-body">
                        <div class="commission-level">
                            <span class="level">Level 1</span>
                            <span class="rate">7%</span>
                            <span class="description">Your direct referrals</span>
                        </div>
                        <div class="commission-level">
                            <span class="level">Level 2</span>
                            <span class="rate">5%</span>
                            <span class="description">Their referrals</span>
                        </div>
                        <div class="commission-level">
                            <span class="level">Level 3</span>
                            <span class="rate">3%</span>
                            <span class="description">3rd level</span>
                        </div>
                        <div class="commission-level">
                            <span class="level">Level 4</span>
                            <span class="rate">2%</span>
                            <span class="description">4th level</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="structure-card">
                    <div class="structure-header bg-success text-white">
                        <h4>Team Commissions</h4>
                    </div>
                    <div class="structure-body">
                        <div class="commission-level">
                            <span class="level">Level 5</span>
                            <span class="rate">1.5%</span>
                            <span class="description">5th level</span>
                        </div>
                        <div class="commission-level">
                            <span class="level">Level 6</span>
                            <span class="rate">1%</span>
                            <span class="description">6th level</span>
                        </div>
                        <div class="commission-level">
                            <span class="level">Level 7</span>
                            <span class="rate">0.75%</span>
                            <span class="description">7th level</span>
                        </div>
                        <div class="commission-level">
                            <span class="level">Level 8-10</span>
                            <span class="rate">0.5%</span>
                            <span class="description">Deep levels</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Success Examples -->
<section class="success-examples py-5 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-lg-12 text-center mb-5">
                <h2 class="section-title">🏆 Real Success Examples</h2>
                <p class="section-subtitle">See how others are earning with APS Dream Homes MLM</p>
            </div>
        </div>
        
        <div class="row g-4">
            <div class="col-md-4">
                <div class="example-card">
                    <div class="example-header">
                        <h5>Part-Time Agent</h5>
                        <span class="badge bg-success">₹25K/Month</span>
                    </div>
                    <div class="example-body">
                        <div class="example-params">
                            <p><strong>5 direct referrals</strong></p>
                            <p><strong>20 total team</strong></p>
                            <p><strong>15 hours/week</strong></p>
                        </div>
                        <div class="example-result">
                            <h4>₹25,000/month</h4>
                            <p>Working part-time while building network</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="example-card">
                    <div class="example-header">
                        <h5>Full-Time Agent</h5>
                        <span class="badge bg-warning">₹75K/Month</span>
                    </div>
                    <div class="example-body">
                        <div class="example-params">
                            <p><strong>15 direct referrals</strong></p>
                            <p><strong>85 total team</strong></p>
                            <p><strong>40 hours/week</strong></p>
                        </div>
                        <div class="example-result">
                            <h4>₹75,000/month</h4>
                            <p>Dedicated full-time effort</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="example-card">
                    <div class="example-header">
                        <h5>Team Leader</h5>
                        <span class="badge bg-danger">₹1.5L/Month</span>
                    </div>
                    <div class="example-body">
                        <div class="example-params">
                            <p><strong>25 direct referrals</strong></p>
                            <p><strong>150+ total team</strong></p>
                            <p><strong>50 hours/week</strong></p>
                        </div>
                        <div class="example-result">
                            <h4>₹1,50,000/month</h4>
                            <p>Leading large network</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="cta-section py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-12 text-center">
                <h2 class="cta-title">Ready to Start Earning?</h2>
                <p class="cta-subtitle">Join APS Dream Homes MLM program and start building your real estate empire today</p>
                <div class="cta-actions">
                    <a href="<?= BASE_URL ?>register-mlm" class="btn btn-warning btn-lg me-3">
                        <i class="fas fa-rocket me-2"></i>Join Now - Free
                    </a>
                    <a href="<?= BASE_URL ?>mlm-opportunity" class="btn btn-outline-light btn-lg">
                        <i class="fas fa-info-circle me-2"></i>Learn More
                    </a>
                </div>
                <div class="trust-badges">
                    <span>✅ Free to Join</span>
                    <span>✅ No Hidden Charges</span>
                    <span>✅ Instant Commission Tracking</span>
                    <span>✅ 24/7 Support</span>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
function calculateCommissions() {
    // Get input values
    const propertyAmount = parseFloat(document.getElementById('propertyAmount').value);
    const directReferrals = parseInt(document.getElementById('directReferrals').value);
    const teamSize = parseInt(document.getElementById('teamSize').value);
    const monthlySales = parseFloat(document.getElementById('monthlySales').value);
    const activeRate = parseFloat(document.getElementById('activeRate').value) / 100;
    const workHours = parseInt(document.getElementById('workHours').value);
    
    // Commission rates
    const commissionRates = {
        level1: 0.07,  // 7%
        level2: 0.05,  // 5%
        level3: 0.03,  // 3%
        level4: 0.02,  // 2%
        level5: 0.015, // 1.5%
        level6: 0.01,  // 1%
        level7: 0.0075, // 0.75%
        level8_10: 0.005 // 0.5%
    };
    
    // Calculate active team members
    const activeTeam = Math.floor(teamSize * activeRate);
    const activeDirect = Math.floor(directReferrals * activeRate);
    
    // Calculate monthly commissions
    const directCommission = propertyAmount * commissionRates.level1 * activeDirect * monthlySales;
    const level2Commission = propertyAmount * commissionRates.level2 * (activeTeam - activeDirect) * monthlySales * 0.3;
    const level3Commission = propertyAmount * commissionRates.level3 * (activeTeam - activeDirect) * monthlySales * 0.2;
    const level4Commission = propertyAmount * commissionRates.level4 * (activeTeam - activeDirect) * monthlySales * 0.15;
    const level5Commission = propertyAmount * commissionRates.level5 * (activeTeam - activeDirect) * monthlySales * 0.1;
    const level6Commission = propertyAmount * commissionRates.level6 * (activeTeam - activeDirect) * monthlySales * 0.08;
    const level7Commission = propertyAmount * commissionRates.level7 * (activeTeam - activeDirect) * monthlySales * 0.05;
    const level8_10Commission = propertyAmount * commissionRates.level8_10 * (activeTeam - activeDirect) * monthlySales * 0.12;
    
    // Total monthly earnings
    const monthlyEarnings = directCommission + level2Commission + level3Commission + 
                          level4Commission + level5Commission + level6Commission + 
                          level7Commission + level8_10Commission;
    
    const annualEarnings = monthlyEarnings * 12;
    const hourlyRate = workHours > 0 ? monthlyEarnings / (workHours * 4.33) : 0;
    const networkValue = activeTeam * propertyAmount * 0.05; // 5% of network value
    
    // Update display
    document.getElementById('monthlyEarnings').textContent = '₹' + Math.round(monthlyEarnings).toLocaleString('en-IN');
    document.getElementById('annualEarnings').textContent = '₹' + Math.round(annualEarnings).toLocaleString('en-IN');
    document.getElementById('hourlyRate').textContent = '₹' + Math.round(hourlyRate).toLocaleString('en-IN');
    document.getElementById('networkValue').textContent = '₹' + Math.round(networkValue).toLocaleString('en-IN');
    
    // Update breakdown
    const totalCommission = monthlyEarnings;
    document.getElementById('directCommission').textContent = '₹' + Math.round(directCommission).toLocaleString('en-IN');
    document.getElementById('level23Commission').textContent = '₹' + Math.round(level2Commission + level3Commission).toLocaleString('en-IN');
    document.getElementById('level47Commission').textContent = '₹' + Math.round(level4Commission + level5Commission + level6Commission + level7Commission).toLocaleString('en-IN');
    document.getElementById('level810Commission').textContent = '₹' + Math.round(level8_10Commission).toLocaleString('en-IN');
    
    // Update progress bars
    document.getElementById('directCommissionBar').style.width = (directCommission / totalCommission * 100) + '%';
    document.getElementById('level23CommissionBar').style.width = ((level2Commission + level3Commission) / totalCommission * 100) + '%';
    document.getElementById('level47CommissionBar').style.width = ((level4Commission + level5Commission + level6Commission + level7Commission) / totalCommission * 100) + '%';
    document.getElementById('level810CommissionBar').style.width = (level8_10Commission / totalCommission * 100) + '%';
    
    // Show results section
    document.getElementById('resultsSection').style.display = 'block';
    
    // Scroll to results
    document.getElementById('resultsSection').scrollIntoView({ behavior: 'smooth' });
}

// Auto-calculate on input change
document.querySelectorAll('#commissionCalculator input').forEach(input => {
    input.addEventListener('input', function() {
        if (document.getElementById('resultsSection').style.display === 'block') {
            calculateCommissions();
        }
    });
});
</script>

<style>
/* Calculator Page Styles */
.calculator-hero-section {
    background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%) !important;
}

.hero-title {
    font-size: 3rem;
    font-weight: 700;
    margin-bottom: 1.5rem;
}

.hero-subtitle {
    font-size: 1.3rem;
    margin-bottom: 2rem;
    opacity: 0.9;
}

.quick-stats {
    display: flex;
    gap: 2rem;
    margin-bottom: 2rem;
}

.stat {
    text-align: center;
}

.stat h3 {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
}

.calculator-visual {
    text-align: center;
    padding: 2rem;
}

.calc-icon {
    font-size: 8rem;
    opacity: 0.3;
}

/* Calculator Cards */
.calculator-card {
    border: none;
    border-radius: 20px;
    box-shadow: 0 20px 40px rgba(0,0,0,0.1);
    overflow: hidden;
}

.calculator-card .card-header {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    padding: 1.5rem;
    border: none;
}

.calculator-card .card-header h4 {
    margin: 0;
    font-size: 1.3rem;
    font-weight: 600;
}

/* Result Cards */
.result-card {
    background: white;
    border-radius: 20px;
    padding: 2rem;
    box-shadow: 0 15px 35px rgba(0,0,0,0.1);
    text-align: center;
    transition: transform 0.3s;
}

.result-card:hover {
    transform: translateY(-10px);
}

.result-card.main-result {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
}

.result-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.result-icon {
    font-size: 2rem;
    opacity: 0.7;
}

.result-card h2 {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
}

.result-card.main-result h2 {
    font-size: 3rem;
}

/* Breakdown Card */
.breakdown-card {
    background: white;
    border-radius: 20px;
    box-shadow: 0 15px 35px rgba(0,0,0,0.1);
    overflow: hidden;
}

.breakdown-card .card-header {
    background: linear-gradient(135deg, #28a745, #20c997);
    color: white;
    padding: 1.5rem;
    border: none;
}

.breakdown-item {
    padding: 1rem;
    border-bottom: 1px solid #eee;
}

.breakdown-item:last-child {
    border-bottom: none;
}

.breakdown-label {
    display: block;
    font-weight: 600;
    color: #333;
    margin-bottom: 0.5rem;
}

.breakdown-value {
    font-size: 1.2rem;
    font-weight: 700;
    color: #28a745;
}

/* Structure Cards */
.structure-card {
    border: none;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    overflow: hidden;
}

.structure-header {
    padding: 1rem;
    text-align: center;
}

.structure-header h4 {
    margin: 0;
    font-weight: 600;
}

.commission-level {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem;
    border-bottom: 1px solid #eee;
}

.commission-level:last-child {
    border-bottom: none;
}

.level {
    font-weight: bold;
    color: #333;
}

.rate {
    font-size: 1.2rem;
    font-weight: 700;
    color: #28a745;
}

.description {
    color: #666;
    font-size: 0.9rem;
}

/* Example Cards */
.example-card {
    background: white;
    border-radius: 15px;
    padding: 1.5rem;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    text-align: center;
}

.example-header {
    margin-bottom: 1rem;
}

.example-header h5 {
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.example-params {
    margin-bottom: 1rem;
}

.example-params p {
    margin: 0.25rem 0;
    font-size: 0.9rem;
    color: #666;
}

.example-result h4 {
    font-size: 1.5rem;
    font-weight: 700;
    color: #28a745;
    margin-bottom: 0.5rem;
}

/* CTA Section */
.cta-section {
    background: linear-gradient(135deg, #28a745, #20c997);
    color: white;
    text-align: center;
}

.cta-title {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 1rem;
}

.trust-badges {
    display: flex;
    gap: 1rem;
    justify-content: center;
    margin-top: 2rem;
    flex-wrap: wrap;
}

.trust-badges span {
    background: rgba(255,255,255,0.2);
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.9rem;
}

/* Section Styles */
.section-title {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 1rem;
    color: #333;
}

.section-subtitle {
    font-size: 1.2rem;
    color: #666;
    margin-bottom: 2rem;
}

@media (max-width: 768px) {
    .hero-title {
        font-size: 2rem;
    }
    
    .quick-stats {
        flex-direction: column;
        gap: 1rem;
    }
    
    .section-title {
        font-size: 1.8rem;
    }
    
    .result-card h2 {
        font-size: 2rem;
    }
    
    .result-card.main-result h2 {
        font-size: 2.5rem;
    }
}
</style>