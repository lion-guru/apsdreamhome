<?php
$extraHead = '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">';
$indian_states = ['Andhra Pradesh','Arunachal Pradesh','Assam','Bihar','Chhattisgarh','Goa','Gujarat','Haryana','Himachal Pradesh','Jharkhand','Karnataka','Kerala','Madhya Pradesh','Maharashtra','Manipur','Meghalaya','Mizoram','Nagaland','Odisha','Punjab','Rajasthan','Sikkim','Tamil Nadu','Telangana','Tripura','Uttar Pradesh','Uttarakhand','West Bengal','Delhi','Jammu and Kashmir','Ladakh'];
$current_year = date('Y');
?>
<style>
.builder-reg-wrapper { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; padding: 40px 0; }
.builder-reg-card { background: white; border-radius: 20px; box-shadow: 0 20px 60px rgba(0,0,0,0.1); overflow: hidden; max-width: 1000px; margin: 0 auto; }
.builder-reg-header { background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; padding: 2rem; text-align: center; }
.builder-reg-body { padding: 2rem; }
.builder-reg-body .form-control:focus { border-color: #28a745; box-shadow: 0 0 0 0.2rem rgba(40,167,69,0.25); }
.btn-register { background: linear-gradient(135deg, #28a745 0%, #20c997 100%); border: none; padding: 12px 30px; border-radius: 25px; color: white; font-weight: 600; transition: all 0.3s; }
.btn-register:hover { transform: translateY(-2px); box-shadow: 0 10px 20px rgba(0,0,0,0.15); color: white; }
.info-box { background: #e3f2fd; border-left: 4px solid #2196f3; padding: 1rem; margin-bottom: 1rem; border-radius: 0 10px 10px 0; }
.partnership-info { background: #d4edda; border-left: 4px solid #28a745; padding: 1rem; margin-bottom: 1rem; border-radius: 0 10px 10px 0; }
.benefits-section { background: #f8f9fa; padding: 1.5rem; border-radius: 10px; margin-bottom: 1rem; }
.feature-icon { width: 50px; height: 50px; background: linear-gradient(135deg, #28a745, #20c997); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 20px; margin-right: 1rem; flex-shrink: 0; }
</style>

<div class="builder-reg-wrapper">
    <div class="builder-reg-card">
        <div class="builder-reg-header">
            <h3 class="mb-1"><i class="fas fa-home me-2"></i>APS DREAM HOMES</h3>
            <p class="mb-2 small opacity-75"><?= __('breg_subtitle', [], 'Developer Partner Program') ?></p>
            <h1 class="mb-1"><?= __('breg_heading', [], 'Builder Registration') ?></h1>
            <p class="mb-0 small opacity-75"><?= __('breg_tagline', [], "Join India's Premier Real Estate Network") ?></p>
        </div>

        <div class="builder-reg-body">
            <?php if (!empty($success)): ?>
                <div class="alert alert-success alert-dismissible fade show"><?php echo $success; ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
            <?php endif; ?>
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show"><?php echo $error; ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
            <?php endif; ?>

            <div class="info-box">
                <h6><i class="fas fa-info-circle me-2"></i><?= __('breg_info_title', [], 'Developer Program Information') ?></h6>
                <small><?= __('breg_info_desc', [], 'Partner with us to showcase your projects - List new developments and reach thousands of potential buyers.') ?></small>
            </div>

            <div class="partnership-info">
                <h6 class="mb-3"><i class="fas fa-handshake me-2"></i><?= __('breg_benefits_title', [], 'Partnership Benefits') ?></h6>
                <div class="row text-center">
                    <div class="col-md-4">
                        <h5 class="text-success"><?= __('breg_benefit_visibility', [], 'Premium Visibility') ?></h5>
                        <p class="mb-0 small"><?= __('breg_benefit_visibility_desc', [], 'Featured listings with priority placement') ?></p>
                    </div>
                    <div class="col-md-4">
                        <h5 class="text-primary"><?= __('breg_benefit_leads', [], 'Lead Generation') ?></h5>
                        <p class="mb-0 small"><?= __('breg_benefit_leads_desc', [], 'Quality leads from verified buyers') ?></p>
                    </div>
                    <div class="col-md-4">
                        <h5 class="text-warning"><?= __('breg_benefit_marketing', [], 'Marketing Support') ?></h5>
                        <p class="mb-0 small"><?= __('breg_benefit_marketing_desc', [], 'Digital marketing and social promotion') ?></p>
                    </div>
                </div>
            </div>

            <div class="benefits-section">
                <div class="row">
                    <div class="col-md-6">
                        <div class="d-flex align-items-center mb-2"><div class="feature-icon"><i class="fas fa-building"></i></div><small><?= __('breg_feat_listings', [], 'Unlimited project listings') ?></small></div>
                        <div class="d-flex align-items-center mb-2"><div class="feature-icon"><i class="fas fa-users"></i></div><small><?= __('breg_feat_interaction', [], 'Direct customer interaction') ?></small></div>
                        <div class="d-flex align-items-center"><div class="feature-icon"><i class="fas fa-chart-line"></i></div><small><?= __('breg_feat_analytics', [], 'Real-time analytics dashboard') ?></small></div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex align-items-center mb-2"><div class="feature-icon"><i class="fas fa-mobile-alt"></i></div><small><?= __('breg_feat_mobile', [], 'Mobile-friendly dashboard') ?></small></div>
                        <div class="d-flex align-items-center mb-2"><div class="feature-icon"><i class="fas fa-credit-card"></i></div><small><?= __('breg_feat_commission', [], 'Commission tracking') ?></small></div>
                        <div class="d-flex align-items-center"><div class="feature-icon"><i class="fas fa-headset"></i></div><small><?= __('breg_feat_support', [], 'Dedicated support team') ?></small></div>
                    </div>
                </div>
            </div>

            <form method="POST">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label"><i class="fas fa-building me-1"></i><?= __('breg_label_company', [], 'Company Name') ?> *</label>
                        <input type="text" class="form-control" name="company_name" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label"><i class="fas fa-user me-1"></i><?= __('breg_label_contact', [], 'Contact Person') ?> *</label>
                        <input type="text" class="form-control" name="contact_person" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label"><i class="fas fa-mobile-alt me-1"></i><?= __('breg_label_mobile', [], 'Mobile Number') ?> *</label>
                        <input type="tel" class="form-control" name="mobile" pattern="[0-9]{10}" placeholder="<?= __('breg_placeholder_mobile', [], '10 digit mobile') ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label"><i class="fas fa-envelope me-1"></i><?= __('breg_label_email', [], 'Email Address') ?> *</label>
                        <input type="email" class="form-control" name="email" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label"><i class="fas fa-industry me-1"></i><?= __('breg_label_company_type', [], 'Company Type') ?> *</label>
                        <select class="form-control" name="company_type" required>
                            <option value=""><?= __('breg_select_type', [], 'Select Type') ?></option>
                            <option value="private_limited"><?= __('breg_type_private', [], 'Private Limited') ?></option>
                            <option value="llp"><?= __('breg_type_llp', [], 'LLP') ?></option>
                            <option value="partnership"><?= __('breg_type_partnership', [], 'Partnership') ?></option>
                            <option value="proprietorship"><?= __('breg_type_proprietorship', [], 'Proprietorship') ?></option>
                            <option value="other"><?= __('breg_type_other', [], 'Other') ?></option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label"><i class="fas fa-calendar me-1"></i><?= __('breg_label_year', [], 'Established Year') ?> *</label>
                        <select class="form-control" name="established_year" required>
                            <option value=""><?= __('breg_select_year', [], 'Select Year') ?></option>
                            <?php for ($y = $current_year; $y >= 1950; $y--): ?>
                                <option value="<?= htmlspecialchars($y, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($y, ENT_QUOTES, 'UTF-8') ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label"><i class="fas fa-home me-1"></i><?= __('breg_label_total_projects', [], 'Total Projects Completed') ?></label>
                        <input type="number" class="form-control" name="total_projects" min="0">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label"><i class="fas fa-tools me-1"></i><?= __('breg_label_ongoing', [], 'Ongoing Projects') ?></label>
                        <input type="number" class="form-control" name="ongoing_projects" min="0">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label"><i class="fas fa-map-marker-alt me-1"></i><?= __('breg_label_city', [], 'City') ?> *</label>
                        <input type="text" class="form-control" name="city" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label"><i class="fas fa-map me-1"></i><?= __('breg_label_state', [], 'State') ?> *</label>
                        <select class="form-control" name="state" required>
                            <option value=""><?= __('breg_select_state', [], 'Select State') ?></option>
                            <?php foreach ($indian_states as $st): ?>
                                <option value="<?= htmlspecialchars($st, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($st, ENT_QUOTES, 'UTF-8') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label"><i class="fas fa-lock me-1"></i><?= __('breg_label_password', [], 'Password') ?> *</label>
                        <input type="password" class="form-control" name="password" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label"><i class="fas fa-lock me-1"></i><?= __('breg_label_confirm', [], 'Confirm Password') ?> *</label>
                        <input type="password" class="form-control" name="confirm_password" required>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="terms_accepted" id="terms_accepted" required>
                        <label class="form-check-label" for="terms_accepted"><?= __('breg_terms', [], 'I accept the Terms & Conditions and Privacy Policy') ?> *</label>
                    </div>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-register btn-lg">
                        <i class="fas fa-user-plus me-2"></i><?= __('breg_register', [], 'Register as Builder') ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
