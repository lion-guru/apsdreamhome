<?php
if (!defined('BASE_URL')) {
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    define('BASE_URL', $protocol . '://' . $host . '/apsdreamhome');
}
if (session_status() === PHP_SESSION_NONE) @session_start();
$csrf_token = $csrf_token ?? $_SESSION['csrf_token'] ?? '';
$errors = $errors ?? $_SESSION['errors'] ?? [];
$old = $old ?? $_SESSION['old_input'] ?? [];
unset($_SESSION['errors'], $_SESSION['old_input']);
$base = BASE_URL;
$ref = $ref ?? $_GET['ref'] ?? $old['sponsor_code'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Become an Associate - APS Dream Home</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:'Inter',-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;min-height:100vh;background:linear-gradient(135deg,#7c2d12 0%,#c2410c 30%,#ea580c 60%,#f97316 100%);display:flex;align-items:center;justify-content:center;position:relative;overflow:hidden;padding:2rem 1rem}
        body::before{content:'';position:absolute;width:600px;height:600px;background:radial-gradient(circle,rgba(234,88,12,.3) 0%,transparent 70%);top:-200px;right:-100px;border-radius:50%}
        body::after{content:'';position:absolute;width:500px;height:500px;background:radial-gradient(circle,rgba(249,115,22,.25) 0%,transparent 70%);bottom:-150px;left:-100px;border-radius:50%}

        .register-wrapper{position:relative;z-index:1;width:100%;max-width:1100px;display:flex;gap:2rem;align-items:center;justify-content:center}

        /* Benefits Panel */
        .benefits-panel{flex:0 0 420px;padding:2rem;background:rgba(255,255,255,.95);border-radius:20px;backdrop-filter:blur(20px);border:1px solid rgba(255,255,255,.3);box-shadow:0 25px 60px rgba(0,0,0,.15);position:relative;overflow:hidden}
        .benefits-panel::before{content:'';position:absolute;top:0;left:0;right:0;height:4px;background:linear-gradient(90deg,#ea580c,#f97316,#fb923c,#f97316,#ea580c);background-size:200% 100%;animation:shimmer 3s ease-in-out infinite}
        @keyframes shimmer{0%{background-position:-200% 0}100%{background-position:200% 0}}

        .benefits-title{font-size:1.2rem;font-weight:800;color:#1e293b;margin-bottom:.5rem;display:flex;align-items:center;gap:.5rem}
        .benefits-subtitle{font-size:.85rem;color:#64748b;margin-bottom:1.25rem;line-height:1.5}

        .benefit-item{display:flex;align-items:flex-start;gap:.75rem;margin-bottom:1rem;padding:.85rem;background:#f8fafc;border-radius:12px;border:1px solid #fed7aa;transition:all .3s ease}
        .benefit-item:hover{transform:translateX(5px);box-shadow:0 4px 12px rgba(0,0,0,.08)}
        .benefit-icon{width:42px;height:42px;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:1rem;background:#fff7ed;color:#ea580c}
        .benefit-text h4{font-size:.85rem;font-weight:700;color:#1e293b;margin-bottom:.15rem}
        .benefit-text p{font-size:.75rem;color:#64748b;line-height:1.4;margin:0}

        .earnings-display{background:linear-gradient(135deg,#fff7ed,#ffedd5);border:1px solid #fed7aa;border-radius:12px;padding:1rem;margin-top:1rem}
        .earnings-display h4{font-size:.85rem;font-weight:700;color:#9a3412;margin-bottom:.5rem}
        .earnings-row{display:flex;justify-content:space-between;align-items:center;padding:.35rem 0;border-bottom:1px solid #fed7aa}
        .earnings-row:last-child{border-bottom:none}
        .earnings-row .label{font-size:.78rem;color:#9a3412}
        .earnings-row .value{font-size:.85rem;font-weight:700;color:#ea580c}

        .stats-bar{display:grid;grid-template-columns:repeat(3,1fr);gap:.75rem;margin-top:1.25rem;padding-top:1.25rem;border-top:1px solid #fed7aa}
        .stat-item{text-align:center}
        .stat-value{font-size:1.4rem;font-weight:800;color:#ea580c;display:block}
        .stat-label{font-size:.65rem;color:#9a3412;text-transform:uppercase;letter-spacing:.5px;font-weight:600}

        /* Register Card */
        .register-card{background:rgba(255,255,255,.98);border-radius:20px;padding:0;box-shadow:0 25px 60px rgba(0,0,0,.3);backdrop-filter:blur(20px);border:1px solid rgba(255,255,255,.3);position:relative;overflow:hidden;width:100%;max-width:500px;transition:all .4s ease}
        .register-card::before{content:'';position:absolute;top:0;left:0;right:0;height:5px;background:linear-gradient(90deg,#ea580c,#f97316,#fb923c,#f97316,#ea580c);background-size:200% 100%;animation:shimmer 3s ease-in-out infinite;z-index:10}
        .register-card:hover{transform:translateY(-5px);box-shadow:0 35px 70px rgba(0,0,0,.2)}

        .card-header-custom{padding:2rem 2rem 0;text-align:center}
        .brand-icon{width:80px;height:80px;background:linear-gradient(135deg,#ea580c,#f97316);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;box-shadow:0 15px 30px rgba(234,88,12,.3);animation:float 3s ease-in-out infinite}
        .brand-icon i{font-size:2.2rem;color:#fff}
        @keyframes float{0%,100%{transform:translateY(0)}50%{transform:translateY(-10px)}}

        .brand-title{font-size:1.4rem;font-weight:800;color:#1e293b;margin-bottom:.2rem}
        .brand-subtitle{color:#64748b;font-size:.88rem;margin-bottom:0}

        .card-body-custom{padding:1.5rem 2rem 2rem}

        /* Form Styles */
        .form-section-title{font-size:.78rem;font-weight:700;color:#ea580c;text-transform:uppercase;letter-spacing:1.2px;margin-bottom:.75rem;display:flex;align-items:center;gap:.5rem}
        .form-section-title::after{content:'';flex:1;height:1px;background:linear-gradient(to right,#fed7aa,transparent)}
        .input-group-custom{position:relative;margin-bottom:.85rem}
        .input-group-custom>i{position:absolute;left:14px;top:2.35rem;color:#94a3b8;font-size:.85rem;z-index:5;pointer-events:none;transition:color .2s}
        .input-group-custom .form-control:focus~i,.input-group-custom .form-select:focus~i{color:#ea580c}
        .form-label-custom{font-size:.82rem;font-weight:600;color:#334155;margin-bottom:.3rem;display:block}
        .form-control,.form-select{border:1.5px solid #e2e8f0;border-radius:10px;padding:.6rem .9rem .6rem 2.5rem;font-size:.9rem;transition:all .3s ease;background:#f8fafc;height:46px}
        .form-control:focus,.form-select:focus{border-color:#ea580c;box-shadow:0 0 0 .2rem rgba(234,88,12,.1);background:#fff}
        .form-select{padding-left:2.5rem}
        .required-badge{color:#dc2626;font-weight:700}

        .sponsor-note{background:#fff7ed;border:1px solid #fed7aa;border-radius:8px;padding:.6rem .8rem;margin-top:.5rem;font-size:.8rem;color:#9a3412;display:flex;align-items:flex-start;gap:.5rem}

        .btn-register{width:100%;height:52px;border:none;border-radius:14px;background:linear-gradient(135deg,#ea580c,#f97316);color:#fff;font-size:1rem;font-weight:700;cursor:pointer;transition:all .3s ease;position:relative;overflow:hidden;margin-top:.75rem;letter-spacing:.3px}
        .btn-register::before{content:'';position:absolute;top:0;left:-100%;width:100%;height:100%;background:linear-gradient(90deg,transparent,rgba(255,255,255,.2),transparent);transition:left .5s ease}
        .btn-register:hover::before{left:100%}
        .btn-register:hover{transform:translateY(-2px);box-shadow:0 10px 30px rgba(234,88,12,.4)}
        .btn-register:active{transform:translateY(0)}
        .btn-register.loading{pointer-events:none;opacity:.8}

        .error-box{background:linear-gradient(135deg,#fef2f2,#fee2e2);border:1px solid #fecaca;border-radius:12px;padding:1rem 1.25rem;margin-bottom:1.25rem;animation:slideInDown .4s ease}
        .error-box .error-title{color:#dc2626;font-weight:700;font-size:.85rem;margin-bottom:.4rem}
        .error-box ul{margin:0;padding-left:1.25rem}
        .error-box li{color:#991b1b;font-size:.83rem;margin-bottom:.15rem}
        @keyframes slideInDown{from{opacity:0;transform:translateY(-20px)}to{opacity:1;transform:translateY(0)}}

        .terms-text{font-size:.78rem;color:#94a3b8;line-height:1.5;text-align:center;margin:1rem 0}
        .terms-text a{color:#ea580c;text-decoration:none}

        .login-section{text-align:center;padding:1rem 2rem 1.5rem;background:#f8fafc;border-top:1px solid #e2e8f0}
        .login-section p{color:#64748b;font-size:.88rem}
        .login-section a{color:#ea580c;text-decoration:none;font-weight:600}
        .login-section a:hover{text-decoration:underline}

        .back-home{display:block;text-align:center;margin-top:.75rem;color:rgba(255,255,255,.4);text-decoration:none;font-size:.82rem;transition:color .2s}
        .back-home:hover{color:rgba(255,255,255,.7)}

        @media(max-width:992px){
            .register-wrapper{flex-direction:column}
            .benefits-panel{flex:none;max-width:100%}
        }
        @media(max-width:576px){
            body{padding:1rem .5rem;align-items:flex-start;padding-top:1.5rem}
            .card-header-custom{padding:1.5rem 1.25rem 0}
            .card-body-custom{padding:1rem 1.25rem 1.5rem}
            .benefits-panel{padding:1.25rem}
            .brand-title{font-size:1.2rem}
            .brand-icon{width:65px;height:65px}
            .brand-icon i{font-size:1.8rem}
        }
    </style>
</head>
<body>
    <div class="register-wrapper">
        <!-- Benefits Panel -->
        <div class="benefits-panel">
            <div class="benefits-title"><i class="fas fa-gem"></i> Associate Income Potential</div>
            <div class="benefits-subtitle">Join our network of successful associates and start earning passive income</div>

            <div class="benefit-item">
                <div class="benefit-icon"><i class="fas fa-money-bill-wave"></i></div>
                <div class="benefit-text">
                    <h4>Up to 20% Commission</h4>
                    <p>Earn based on your rank from 5% to 20% on every sale</p>
                </div>
            </div>
            <div class="benefit-item">
                <div class="benefit-icon"><i class="fas fa-layer-group"></i></div>
                <div class="benefit-text">
                    <h4>4 Revenue Streams</h4>
                    <p>Plot Sales, Investment Plans, Salary & Incentives, Telecaller Commission</p>
                </div>
            </div>
            <div class="benefit-item">
                <div class="benefit-icon"><i class="fas fa-sitemap"></i></div>
                <div class="benefit-text">
                    <h4>Binary Network Tree</h4>
                    <p>Build left and right teams with matching bonuses up to 3 generations</p>
                </div>
            </div>
            <div class="benefit-item">
                <div class="benefit-icon"><i class="fas fa-crown"></i></div>
                <div class="benefit-text">
                    <h4>Royalty Pool</h4>
                    <p>Top performers share 2% of total revenue as royalty bonuses</p>
                </div>
            </div>

            <div class="earnings-display">
                <h4><i class="fas fa-calculator me-2"></i>Example: ₹1 Lakh Sale</h4>
                <div class="earnings-row">
                    <span class="label">Track A (Direct Sale)</span>
                    <span class="value">₹15,000</span>
                </div>
                <div class="earnings-row">
                    <span class="label">Track B (Performance)</span>
                    <span class="value">₹3,000</span>
                </div>
                <div class="earnings-row">
                    <span class="label">Track C (Milestone)</span>
                    <span class="value">₹2,000</span>
                </div>
                <div class="earnings-row" style="border-top:2px solid #ea580c;padding-top:.5rem;margin-top:.3rem">
                    <span class="label" style="font-weight:700">Total Earning</span>
                    <span class="value" style="font-size:1rem">₹20,000</span>
                </div>
            </div>

            <div class="stats-bar">
                <div class="stat-item">
                    <span class="stat-value">₹1.05Cr</span>
                    <span class="stat-label">Total Paid</span>
                </div>
                <div class="stat-item">
                    <span class="stat-value">311</span>
                    <span class="stat-label">Commissions</span>
                </div>
                <div class="stat-item">
                    <span class="stat-value">20%</span>
                    <span class="stat-label">Max Rate</span>
                </div>
            </div>
        </div>

        <!-- Register Card -->
        <div class="register-card">
            <div class="card-header-custom">
                <div class="brand-icon">
                    <i class="fas fa-handshake"></i>
                </div>
                <h1 class="brand-title">Become an Associate</h1>
                <p class="brand-subtitle">Start Your Earning Journey Today</p>
            </div>

            <div class="card-body-custom">
                <?php if (!empty($errors)): ?>
                    <div class="error-box">
                        <div class="error-title"><i class="fa-solid fa-circle-exclamation me-1"></i>Please fix the following errors:</div>
                        <ul>
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="POST" action="<?php echo $base; ?>/associate/register" id="associateRegisterForm" novalidate>
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

                    <div class="form-section-title"><i class="fa-solid fa-user"></i> Personal Details</div>

                    <div class="input-group-custom">
                        <i class="fa-solid fa-user-pen"></i>
                        <label class="form-label-custom">Full Name <span class="required-badge">*</span></label>
                        <input type="text" class="form-control" name="full_name" placeholder="Enter your full name" value="<?php echo htmlspecialchars($old['full_name'] ?? ''); ?>" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="input-group-custom">
                                <i class="fa-solid fa-envelope"></i>
                                <label class="form-label-custom">Email Address <span class="required-badge">*</span></label>
                                <input type="email" class="form-control" name="email" placeholder="you@example.com" value="<?php echo htmlspecialchars($old['email'] ?? ''); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="input-group-custom">
                                <i class="fa-solid fa-phone"></i>
                                <label class="form-label-custom">Phone Number <span class="required-badge">*</span></label>
                                <input type="tel" class="form-control" name="phone" placeholder="10-digit number" pattern="[0-9]{10}" maxlength="10" value="<?php echo htmlspecialchars($old['phone'] ?? ''); ?>" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-section-title"><i class="fa-solid fa-lock"></i> Security</div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="input-group-custom">
                                <i class="fa-solid fa-key"></i>
                                <label class="form-label-custom">Password <span class="required-badge">*</span></label>
                                <input type="password" class="form-control" name="password" id="regPassword" placeholder="Create a password" required minlength="6">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="input-group-custom">
                                <i class="fa-solid fa-shield-halved"></i>
                                <label class="form-label-custom">Confirm Password <span class="required-badge">*</span></label>
                                <input type="password" class="form-control" name="confirm_password" id="regConfirmPassword" placeholder="Re-enter password" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-section-title"><i class="fa-solid fa-sitemap"></i> Sponsor Info</div>

                    <div class="input-group-custom">
                        <i class="fa-solid fa-ticket"></i>
                        <label class="form-label-custom">Sponsor Code <span class="required-badge">*</span></label>
                        <input type="text" class="form-control" name="sponsor_code" placeholder="Enter your sponsor's code" required value="<?php echo htmlspecialchars($ref); ?>">
                    </div>
                    <div class="sponsor-note">
                        <i class="fa-solid fa-circle-info"></i>
                        <span><strong>Required:</strong> Your sponsor code connects you to the network tree and enables auto-approval.</span>
                    </div>

                    <div class="terms-text">
                        By registering, you agree to our <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a>.
                    </div>

                    <button type="submit" class="btn-register" id="submitBtn">
                        <i class="fa-solid fa-user-plus me-2"></i>Join as Associate
                    </button>
                </form>
            </div>

            <div class="login-section">
                <p>Already have an account? <a href="<?php echo $base; ?>/associate/login">Sign in here</a></p>
            </div>
        </div>
    </div>

    <a href="<?php echo $base; ?>/" class="back-home">
        <i class="fas fa-arrow-left me-1"></i> Back to Home
    </a>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var form = document.getElementById('associateRegisterForm');
            var phoneInput = form.querySelector('input[name="phone"]');
            if (phoneInput) {
                phoneInput.addEventListener('input', function() {
                    this.value = this.value.replace(/[^0-9]/g, '').substring(0, 10);
                });
            }

            var confirmPwd = document.getElementById('regConfirmPassword');
            var pwd = document.getElementById('regPassword');
            if (confirmPwd && pwd) {
                confirmPwd.addEventListener('blur', function() {
                    if (this.value && this.value !== pwd.value) {
                        this.style.borderColor = '#dc2626';
                    } else if (this.value) {
                        this.style.borderColor = '#059669';
                    }
                });
            }

            form.addEventListener('submit', function() {
                var btn = document.getElementById('submitBtn');
                btn.classList.add('loading');
                btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Creating Account...';
                btn.disabled = true;
            });

            // Entrance animation
            var card = document.querySelector('.register-card');
            if (card) {
                card.style.opacity = '0';
                card.style.transform = 'translateY(30px)';
                setTimeout(function() {
                    card.style.transition = 'all 0.6s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, 100);
            }

            var benefits = document.querySelector('.benefits-panel');
            if (benefits) {
                benefits.style.opacity = '0';
                benefits.style.transform = 'translateX(-30px)';
                setTimeout(function() {
                    benefits.style.transition = 'all 0.6s ease';
                    benefits.style.opacity = '1';
                    benefits.style.transform = 'translateX(0)';
                }, 200);
            }
        });
    </script>
<?php include __DIR__ . '/../partials/_chatbot_icons.php'; ?>
</body>
</html>
