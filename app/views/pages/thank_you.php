<?php if (!isset($sc)) { $sc = function($k, $d='') { return $GLOBALS['_site_settings_cache'][$k] ?? $d; }; } $phoneRaw = preg_replace('/[^0-9]/', '', $sc('contact_whatsapp', '919277121112')); $phoneDisplay = $sc('contact_phone', '<?= htmlspecialchars($phoneDisplay) ?>'); $emailDisplay = $sc('contact_email', '<?= htmlspecialchars($emailDisplay) ?>'); ?>
<?php

// TODO: Add proper error handling with try-catch blocks

?>
<style>
        .thank-you-container {
            text-align: center;
            color: white;
            max-width: 800px;
            padding: 40px 20px;
            position: relative;
            z-index: 2;
        }

        .success-icon {
            font-size: 120px;
            margin-bottom: 30px;
            animation: bounce 2s infinite;
            color: rgba(255, 255, 255, 0.9);
        }

        .thank-you-title {
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 20px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        .thank-you-subtitle {
            font-size: 1.5rem;
            margin-bottom: 30px;
            opacity: 0.9;
        }

        .action-buttons {
            display: flex;
            gap: 20px;
            justify-content: center;
            margin: 40px 0;
            flex-wrap: wrap;
        }

        .btn-custom {
            padding: 15px 30px;
            font-size: 1.1rem;
            border-radius: 50px;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .btn-primary-custom {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 2px solid rgba(255, 255, 255, 0.3);
            backdrop-filter: blur(10px);
        }

        .btn-primary-custom:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .btn-secondary-custom {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            border: 2px solid rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
        }

        .btn-secondary-custom:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .contact-info {
            background: rgba(255, 255, 255, 0.1);
            padding: 30px;
            border-radius: 15px;
            backdrop-filter: blur(10px);
            margin-top: 40px;
        }

        .contact-item {
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 10px 0;
            font-size: 1.1rem;
        }

        .contact-item i {
            margin-right: 15px;
            font-size: 1.3rem;
        }

        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {
                transform: translateY(0);
            }
            40% {
                transform: translateY(-20px);
            }
            60% {
                transform: translateY(-10px);
            }
        }

        @media (max-width: 768px) {
            .thank-you-title {
                font-size: 2.5rem;
            }
            
            .thank-you-subtitle {
                font-size: 1.2rem;
            }
            
            .success-icon {
                font-size: 80px;
            }
            
            .action-buttons {
                flex-direction: column;
                align-items: center;
            }
            
            .btn-custom {
                width: 100%;
                max-width: 300px;
            }
        }
    </style>
<div class="thank-you-container">
        <div class="success-icon">
            <i class="fas fa-check-circle"></i>
        </div>
        
        <h1 class="thank-you-title">Thank You!</h1>
        <p class="thank-you-subtitle">Your submission has been received successfully.</p>
        
        <div class="action-buttons">
            <a href="<?= BASE_URL ?>" class="btn-custom btn-primary-custom">
                <i class="fas fa-home"></i>
                Back to Home
            </a>
            <a href="<?= BASE_URL ?>properties" class="btn-custom btn-secondary-custom">
                <i class="fas fa-building"></i>
                Browse Properties
            </a>
            <a href="<?= BASE_URL ?>contact" class="btn-custom btn-secondary-custom">
                <i class="fas fa-phone"></i>
                Contact Us
            </a>
        </div>
        
        <div class="contact-info">
            <h3>Need Assistance?</h3>
            <div class="contact-item">
                <i class="fas fa-envelope"></i>
                <span><?= htmlspecialchars($emailDisplay) ?></span>
            </div>
            <div class="contact-item">
                <i class="fas fa-phone"></i>
                <span><?= htmlspecialchars($phoneDisplay) ?></span>
            </div>
            <div class="contact-item">
                <i class="fas fa-map-marker-alt"></i>
                <span>Gorakhpur, Uttar Pradesh</span>
            </div>
        </div>
    </div>

    <script>
        // Auto-redirect after 10 seconds (optional)
        setTimeout(() => {
            const autoRedirect = confirm('Would you like to return to the homepage?');
            if (autoRedirect) {
                window.location.href = '<?= BASE_URL ?>';
            }
        }, 10000);
    </script>
