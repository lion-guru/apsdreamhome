<?php
if (!defined('BASE_URL')) {
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $basePath = preg_replace('#/public$#', '', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
    define('BASE_URL', $protocol . '://' . $host . $basePath);
}
if (session_status() === PHP_SESSION_NONE) @session_start();
$csrf_token = $csrf_token ?? $_SESSION['csrf_token'] ?? '';
$user = $user ?? [];
$session = $session ?? [];
$base = BASE_URL;
$token = $_GET['token'] ?? '';
$completionPct = $session['profile_completion_pct'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete Your Profile - APS Dream Home</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <style nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:'Inter',-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;min-height:100vh;background:linear-gradient(135deg,#0f172a 0%,#1e3a5f 50%,#1e293b 100%);display:flex;align-items:center;justify-content:center;position:relative;overflow:hidden;padding:2rem 1rem}
        body::before{content:'';position:absolute;width:600px;height:600px;background:radial-gradient(circle,rgba(102,126,234,.3) 0%,transparent 70%);top:-200px;right:-100px;border-radius:50%}
        body::after{content:'';position:absolute;width:500px;height:500px;background:radial-gradient(circle,rgba(118,75,162,.25) 0%,transparent 70%);bottom:-150px;left:-100px;border-radius:50%}

        .profile-wrapper{position:relative;z-index:1;width:100%;max-width:500px}

        .brand-section{text-align:center;margin-bottom:1.5rem}
        .brand-logo{width:70px;height:70px;background:linear-gradient(135deg,#0d9488,#0f766e);border-radius:18px;display:inline-flex;align-items:center;justify-content:center;margin-bottom:.75rem;box-shadow:0 10px 30px rgba(102,126,234,.4);animation:float 3s ease-in-out infinite}
        .brand-logo i{font-size:1.8rem;color:#fff}
        .brand-section h1{color:#fff;font-size:1.4rem;font-weight:800}
        @keyframes float{0%,100%{transform:translateY(0px)}50%{transform:translateY(-10px)}}

        .profile-card{background:rgba(255,255,255,.98);border-radius:24px;padding:0;box-shadow:0 25px 60px rgba(0,0,0,.3);backdrop-filter:blur(20px);border:1px solid rgba(255,255,255,.3);position:relative;overflow:hidden}
        .profile-card::before{content:'';position:absolute;top:0;left:0;right:0;height:5px;background:linear-gradient(90deg,#0d9488,#059669,#10b981,#0d9488);background-size:200% 100%;animation:shimmer 3s ease-in-out infinite;z-index:10}
        @keyframes shimmer{0%{background-position:-200% 0}100%{background-position:200% 0}}

        .card-header{padding:1.5rem 2rem 0;text-align:center}
        .step-indicator{display:flex;justify-content:center;gap:.5rem;margin-bottom:1rem}
        .step-dot{width:12px;height:12px;border-radius:50%;background:#e2e8f0;transition:all .3s}
        .step-dot.done{background:#10b981}

        .welcome-banner{background:linear-gradient(135deg,#f0fdfa,#ccfbf1);border:1px solid #99f6e4;border-radius:12px;padding:1rem;margin:0 2rem 1.25rem;display:flex;align-items:center;gap:1rem}
        .welcome-avatar{width:50px;height:50px;background:linear-gradient(135deg,#0d9488,#0f766e);border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0}
        .welcome-avatar i{font-size:1.5rem;color:#fff}
        .welcome-text h3{font-size:1rem;font-weight:700;color:#1e293b;margin-bottom:.15rem}
        .welcome-text p{font-size:.78rem;color:#64748b;margin:0}

        .progress-section{padding:0 2rem;margin-bottom:1.25rem}
        .progress-header{display:flex;justify-content:space-between;margin-bottom:.5rem}
        .progress-header span{font-size:.82rem;font-weight:600;color:#1e293b}
        .progress-header small{font-size:.78rem;color:#64748b}
        .progress-bar{height:8px;background:#e2e8f0;border-radius:10px;overflow:hidden}
        .progress-fill{height:100%;background:linear-gradient(90deg,#0d9488,#10b981);border-radius:10px;transition:width .5s ease}

        .benefits-callout{background:linear-gradient(135deg,#fff7ed,#ffedd5);border:1px solid #fed7aa;border-radius:12px;padding:1rem;margin:0 2rem 1.25rem}
        .benefits-callout h4{font-size:.85rem;font-weight:700;color:#9a3412;margin-bottom:.5rem}
        .benefit-list{list-style:none;padding:0;margin:0}
        .benefit-list li{font-size:.8rem;color:#9a3412;padding:.3rem 0;display:flex;align-items:center;gap:.5rem}
        .benefit-list li i{color:#ea580c;font-size:.75rem}

        .card-body{padding:0 2rem 2rem}

        .form-section-title{font-size:.75rem;font-weight:700;color:#0d9488;text-transform:uppercase;letter-spacing:1px;margin-bottom:.75rem;display:flex;align-items:center;gap:.5rem}
        .form-section-title::after{content:'';flex:1;height:1px;background:linear-gradient(to right,#e2e8f0,transparent)}

        .input-group-custom{position:relative;margin-bottom:.85rem}
        .input-group-custom>i{position:absolute;left:14px;top:2.35rem;color:#94a3b8;font-size:.85rem;z-index:5;pointer-events:none;transition:color .2s}
        .input-group-custom .form-control:focus~i{color:#0d9488}
        .form-label-custom{font-size:.82rem;font-weight:600;color:#334155;margin-bottom:.3rem;display:block}
        .form-control,.form-select{border:1.5px solid #e2e8f0;border-radius:10px;padding:.6rem .9rem .6rem 2.5rem;font-size:.9rem;transition:all .3s ease;background:#f8fafc;height:46px}
        .form-control:focus,.form-select:focus{border-color:#0d9488;box-shadow:0 0 0 .2rem rgba(13,148,136,.1);background:#fff}
        .form-select{padding-left:2.5rem}

        .btn-save{width:100%;height:52px;border:none;border-radius:14px;background:linear-gradient(135deg,#0d9488,#0f766e);color:#fff;font-size:1rem;font-weight:700;cursor:pointer;transition:all .3s;position:relative;overflow:hidden}
        .btn-save::before{content:'';position:absolute;top:0;left:-100%;width:100%;height:100%;background:linear-gradient(90deg,transparent,rgba(255,255,255,.2),transparent);transition:left .5s}
        .btn-save:hover::before{left:100%}
        .btn-save:hover{transform:translateY(-2px);box-shadow:0 10px 30px rgba(13,148,136,.4)}

        .skip-section{text-align:center;margin-top:1rem;padding-top:1rem;border-top:1px solid #e2e8f0}
        .skip-btn{background:none;border:none;color:#64748b;font-size:.85rem;cursor:pointer;text-decoration:none}
        .skip-btn:hover{color:#1e293b;text-decoration:underline}

        .success-overlay{display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,.6);z-index:1000;align-items:center;justify-content:center}
        .success-overlay.show{display:flex}
        .success-modal{background:#fff;border-radius:20px;padding:2.5rem;text-align:center;max-width:400px;width:90%;animation:popIn .3s ease}
        @keyframes popIn{from{transform:scale(.8);opacity:0}to{transform:scale(1);opacity:1}}
        .success-icon{width:80px;height:80px;background:linear-gradient(135deg,#10b981,#059669);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 1.5rem}
        .success-icon i{font-size:2.5rem;color:#fff}
        .success-modal h3{font-size:1.3rem;font-weight:800;color:#1e293b;margin-bottom:.5rem}
        .success-modal p{font-size:.9rem;color:#64748b;margin-bottom:1.5rem}
        .success-modal .btn-primary{background:linear-gradient(135deg,#0d9488,#0f766e);border:none;border-radius:12px;padding:.75rem 2rem;font-weight:600}

        @media(max-width:576px){
            body{padding:1rem .5rem;align-items:flex-start;padding-top:1.5rem}
            .card-header,.progress-section,.benefits-callout,.card-body{padding-left:1.25rem;padding-right:1.25rem}
            .benefits-callout{margin:0 1.25rem 1rem}
            .welcome-banner{margin:0 1.25rem 1rem}
        }
    </style>
</head>
<body>
    <div class="profile-wrapper">
        <div class="brand-section">
            <div class="brand-logo"><i class="fas fa-home"></i></div>
            <h1>APS Dream Home</h1>
        </div>

        <div class="profile-card">
            <div class="card-header">
                <div class="step-indicator">
                    <div class="step-dot done"></div>
                    <div class="step-dot done"></div>
                    <div class="step-dot done"></div>
                </div>
            </div>

            <div class="welcome-banner">
                <div class="welcome-avatar">
                    <i class="fas fa-user"></i>
                </div>
                <div class="welcome-text">
                    <h3>Welcome, <?php echo htmlspecialchars($user['name'] ?? 'User'); ?>!</h3>
                    <p>Account created successfully. Let's complete your profile!</p>
                </div>
            </div>

            <div class="progress-section">
                <div class="progress-header">
                    <span>Profile Completion</span>
                    <small id="completionText"><?php echo $completionPct; ?>% Complete</small>
                </div>
                <div class="progress-bar">
                    <div class="progress-fill" id="progressFill" style="width: <?php echo $completionPct; ?>%"></div>
                </div>
            </div>

            <div class="benefits-callout">
                <h4><i class="fas fa-gift me-2"></i>Complete your profile to unlock:</h4>
                <ul class="benefit-list">
                    <li><i class="fas fa-check-circle"></i> Save favorite properties</li>
                    <li><i class="fas fa-check-circle"></i> Get personalized recommendations</li>
                    <li><i class="fas fa-check-circle"></i> Earn ₹500 referral bonus</li>
                    <li><i class="fas fa-check-circle"></i> Priority customer support</li>
                </ul>
            </div>

            <div class="card-body">
                <form id="profileForm">
                    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">

                    <div class="form-section-title"><i class="fa-solid fa-user"></i> Basic Info</div>

                    <div class="input-group-custom">
                        <i class="fa-solid fa-user-pen"></i>
                        <label class="form-label-custom">Your Name</label>
                        <input type="text" class="form-control" name="name" placeholder="Enter your name" value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>">
                    </div>

                    <div class="input-group-custom">
                        <i class="fa-solid fa-envelope"></i>
                        <label class="form-label-custom">Email Address</label>
                        <input type="email" class="form-control" name="email" placeholder="you@example.com" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>">
                    </div>

                    <div class="form-section-title"><i class="fa-solid fa-map-marker-alt"></i> Location</div>

                    <div class="input-group-custom">
                        <i class="fa-solid fa-city"></i>
                        <label class="form-label-custom">City</label>
                        <select class="form-select" name="city">
                            <option value="">Select your city</option>
                            <option value="Mumbai" <?php echo (($user['city'] ?? '') === 'Mumbai') ? 'selected' : ''; ?>>Mumbai</option>
                            <option value="Delhi" <?php echo (($user['city'] ?? '') === 'Delhi') ? 'selected' : ''; ?>>Delhi</option>
                            <option value="Bangalore" <?php echo (($user['city'] ?? '') === 'Bangalore') ? 'selected' : ''; ?>>Bangalore</option>
                            <option value="Pune" <?php echo (($user['city'] ?? '') === 'Pune') ? 'selected' : ''; ?>>Pune</option>
                            <option value="Other" <?php echo (($user['city'] ?? '') === 'Other') ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>

                    <div class="form-section-title"><i class="fa-solid fa-briefcase"></i> Preferences</div>

                    <div class="input-group-custom">
                        <i class="fa-solid fa-briefcase"></i>
                        <label class="form-label-custom">Occupation</label>
                        <select class="form-select" name="occupation">
                            <option value="">Select occupation</option>
                            <option value="salaried">Salaried</option>
                            <option value="business">Business</option>
                            <option value="self_employed">Self Employed</option>
                            <option value="student">Student</option>
                            <option value="retired">Retired</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <div class="input-group-custom">
                        <i class="fa-solid fa-indian-rupee-sign"></i>
                        <label class="form-label-custom">Budget Range</label>
                        <select class="form-select" name="budget_range">
                            <option value="">Select budget</option>
                            <option value="10-25l">₹10-25 Lakhs</option>
                            <option value="25-50l">₹25-50 Lakhs</option>
                            <option value="50l-1cr">₹50 Lakhs - 1 Crore</option>
                            <option value="1cr+">Above ₹1 Crore</option>
                        </select>
                    </div>

                    <button type="submit" class="btn-save mt-3" id="saveBtn">
                        <i class="fas fa-check-circle me-2"></i>Save & Continue
                    </button>
                </form>

                <div class="skip-section">
                    <button class="skip-btn" onclick="skipProfile()">
                        Skip for now, I'll complete later
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Success Overlay -->
    <div class="success-overlay" id="successOverlay">
        <div class="success-modal">
            <div class="success-icon">
                <i class="fas fa-check"></i>
            </div>
            <h3>Profile Complete!</h3>
            <p>Your profile is now set up. Start exploring properties and earning rewards!</p>
            <a href="<?php echo $base; ?>/" class="btn btn-primary">
                <i class="fas fa-home me-2"></i>Go to Homepage
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
        const form = document.getElementById('profileForm');
        const progressFill = document.getElementById('progressFill');
        const completionText = document.getElementById('completionText');

        // Auto-save on field change
        const inputs = form.querySelectorAll('input, select');
        inputs.forEach(input => {
            input.addEventListener('change', saveProgress);
        });

        function saveProgress() {
            const formData = new FormData(form);
            const data = {};
            formData.forEach((value, key) => {
                if (key !== 'token') data[key] = value;
            });

            fetch('<?php echo $base; ?>/register/smart/save-profile', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    token: '<?php echo htmlspecialchars($token); ?>',
                    ...data
                })
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    progressFill.style.width = result.completion_pct + '%';
                    completionText.textContent = result.completion_pct + '% Complete';

                    if (result.is_complete) {
                        document.getElementById('successOverlay').classList.add('show');
                    }
                }
            })
            .catch(error => console.error('Save failed:', error));
        }

        form.addEventListener('submit', function(e) {
            e.preventDefault();
            saveProgress();
        });

        function skipProfile() {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '<?php echo $base; ?>/register/smart/skip-profile';
            
            const tokenInput = document.createElement('input');
            tokenInput.type = 'hidden';
            tokenInput.name = 'token';
            tokenInput.value = '<?php echo htmlspecialchars($token); ?>';
            form.appendChild(tokenInput);
            
            document.body.appendChild(form);
            form.submit();
        }

        // Entrance animation
        document.addEventListener('DOMContentLoaded', function() {
            const card = document.querySelector('.profile-card');
            if (card) {
                card.style.opacity = '0';
                card.style.transform = 'translateY(30px)';
                setTimeout(() => {
                    card.style.transition = 'all 0.6s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, 100);
            }
        });
    </script>
</body>
</html>
