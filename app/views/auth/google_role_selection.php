<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete Your Registration - APS Dream Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 30px 15px;
        }

        .role-card {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 500px;
            width: 100%;
        }

        .google-user-info {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 30px;
            border-bottom: 2px solid #f0f0f0;
        }

        .google-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            margin: 0 auto 15px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2rem;
        }

        .role-option {
            background: #f8f9fa;
            border: 2px solid #e9ecef;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .role-option:hover {
            border-color: #667eea;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.2);
        }

        .role-option.selected {
            border-color: #667eea;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
        }

        .role-option .icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            margin-right: 15px;
        }

        .role-option .info h5 {
            margin-bottom: 5px;
            color: #333;
        }

        .role-option .info small {
            color: #666;
        }

        .referral-section {
            display: none;
            background: #fff3cd;
            border: 2px solid #ffc107;
            border-radius: 15px;
            padding: 20px;
            margin-top: 20px;
        }

        .referral-section.show {
            display: block;
        }

        .company-code-badge {
            background: #28a745;
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .company-code-badge:hover {
            background: #218838;
            transform: translateY(-2px);
        }

        .btn-complete {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 15px 40px;
            border-radius: 25px;
            color: white;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            width: 100%;
        }

        .btn-complete:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
        }

        .btn-complete:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }

        .loading {
            display: none;
            text-align: center;
            padding: 20px;
        }

        .loading.show {
            display: block;
        }

        .spinner {
            width: 40px;
            height: 40px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 15px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="role-card">
        <div class="google-user-info">
            <div class="google-avatar">
                <i class="fab fa-google"></i>
            </div>
            <h4>Welcome, <?php echo htmlspecialchars($googleUserData['name']); ?>!</h4>
            <p class="text-muted mb-0"><?php echo htmlspecialchars($googleUserData['email']); ?></p>
        </div>

        <h5 class="mb-3">I want to join as:</h5>

        <div class="role-option" onclick="selectRole('customer')" id="role-customer">
            <div class="d-flex align-items-center">
                <div class="icon">
                    <i class="fas fa-user"></i>
                </div>
                <div class="info">
                    <h5>Customer</h5>
                    <small>Search properties, buy/rent, get 5% discount with referral</small>
                </div>
            </div>
        </div>

        <div class="role-option" onclick="selectRole('associate')" id="role-associate">
            <div class="d-flex align-items-center">
                <div class="icon">
                    <i class="fas fa-handshake"></i>
                </div>
                <div class="info">
                    <h5>Associate</h5>
                    <small>Earn commissions, build network, mandatory referral code</small>
                </div>
            </div>
        </div>

        <div class="role-option" onclick="selectRole('agent')" id="role-agent">
            <div class="d-flex align-items-center">
                <div class="icon">
                    <i class="fas fa-briefcase"></i>
                </div>
                <div class="info">
                    <h5>Agent</h5>
                    <small>Sell properties, earn higher commissions, mandatory referral</small>
                </div>
            </div>
        </div>

        <div class="referral-section" id="referralSection">
            <h6 class="mb-3"><i class="fas fa-ticket-alt me-2"></i>Referral Code</h6>
            <div class="d-flex align-items-center mb-3">
                <input type="text" class="form-control" id="referralCode" placeholder="Enter referral code">
                <span class="company-code-badge ms-2" onclick="useCompanyCode()">
                    <i class="fas fa-building me-1"></i>Use Company Code
                </span>
            </div>
            <small class="text-muted">
                <i class="fas fa-info-circle me-1"></i>
                Associate/Agent require referral code. Use company code to join directly.
            </small>
        </div>

        <div class="mb-3 mt-4" id="phoneSection" style="display: none;">
            <label class="form-label fw-bold">Phone Number *</label>
            <input type="tel" class="form-control" id="phone" placeholder="Enter your phone number" pattern="[0-9]{10}">
        </div>

        <button class="btn-complete mt-3" id="completeBtn" onclick="completeRegistration()" disabled>
            <i class="fas fa-check-circle me-2"></i>Complete Registration
        </button>

        <div class="loading" id="loading">
            <div class="spinner"></div>
            <p>Creating your account...</p>
        </div>

        <div class="text-center mt-3">
            <a href="/login" class="text-muted text-decoration-none">
                <i class="fas fa-arrow-left me-1"></i>Back to Login
            </a>
        </div>
    </div>

    <script>
        let selectedRole = null;
        const companyReferralCode = '<?php echo $companyReferralCode; ?>';

        function selectRole(role) {
            selectedRole = role;

            // Remove selected class from all options
            document.querySelectorAll('.role-option').forEach(option => {
                option.classList.remove('selected');
            });

            // Add selected class to clicked option
            document.getElementById('role-' + role).classList.add('selected');

            // Show/hide referral section
            const referralSection = document.getElementById('referralSection');
            const phoneSection = document.getElementById('phoneSection');

            if (role === 'customer') {
                referralSection.classList.remove('show');
                phoneSection.style.display = 'block';
            } else {
                referralSection.classList.add('show');
                phoneSection.style.display = 'block';
            }

            // Enable complete button
            document.getElementById('completeBtn').disabled = false;
        }

        function useCompanyCode() {
            document.getElementById('referralCode').value = companyReferralCode;
        }

        function completeRegistration() {
            const phone = document.getElementById('phone').value;
            const referralCode = document.getElementById('referralCode').value;

            if (!phone || phone.length !== 10) {
                alert('Please enter a valid 10-digit phone number');
                return;
            }

            if (selectedRole !== 'customer' && !referralCode) {
                alert('Referral code is required for Associate/Agent registration');
                return;
            }

            // Show loading
            document.getElementById('completeBtn').style.display = 'none';
            document.getElementById('loading').classList.add('show');

            const formData = new FormData();
            formData.append('role', selectedRole);
            formData.append('phone', phone);
            formData.append('referral_code', referralCode);

            fetch('/auth/google/complete-registration', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = data.redirect;
                } else {
                    alert('Registration failed: ' + data.message);
                    document.getElementById('completeBtn').style.display = 'block';
                    document.getElementById('loading').classList.remove('show');
                }
            })
            .catch(error => {
                alert('Error: ' + error);
                document.getElementById('completeBtn').style.display = 'block';
                document.getElementById('loading').classList.remove('show');
            });
        }
    </script>
</body>
</html>
