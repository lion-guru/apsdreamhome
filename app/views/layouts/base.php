<?php
if (class_exists('\App\Helpers\SecurityHelper')) {
    \App\Helpers\SecurityHelper::setSecurityHeaders();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'APS Dream Home - Premium Real Estate in Uttar Pradesh'; ?></title>

    <!-- JSON-LD Structured Data -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "RealEstateAgent",
        "name": "APS Dream Home",
        "image": "<?php echo defined('BASE_URL') ? BASE_URL : ''; ?>/assets/images/logo/apslogonew.jpg",
        "url": "<?php echo defined('BASE_URL') ? BASE_URL : ''; ?>",
        "telephone": "+91-9277121112",
        "address": {
            "@type": "PostalAddress",
            "streetAddress": "1st floor, Singhariya Chauraha, Kunraghat",
            "addressLocality": "Gorakhpur",
            "addressRegion": "Uttar Pradesh",
            "postalCode": "273008",
            "addressCountry": "IN"
        },
        "aggregateRating": {
            "@type": "AggregateRating",
            "ratingValue": "4.8",
            "reviewCount": "200"
        },
        "openingHoursSpecification": [
            {"@type": "OpeningHoursSpecification", "dayOfWeek": ["Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"], "opens": "09:00", "closes": "19:00"}
        ],
        "sameAs": [
            "https://www.facebook.com/apsdreamhomes/",
            "https://www.instagram.com/apsdreamhomes/",
            "https://www.youtube.com/@apsdreamhomes",
            "https://www.linkedin.com/company/apsdreamhomes"
        ]
    }
    </script>

    <meta name="description" content="<?php echo $page_description ?? 'Discover premium residential and commercial properties in Gorakhpur, Lucknow, Kushinagar, and across Uttar Pradesh with APS Dream Home. Premium plots, modern amenities, and trusted service.'; ?>">
    <meta name="keywords" content="real estate, plots, homes, Gorakhpur, Lucknow, Kushinagar, Varanasi, Uttar Pradesh, property, residential, commercial">
    <meta name="author" content="APS Dream Home">
    <meta name="robots" content="index, follow">

    <!-- Open Graph / Social Media -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="<?php echo $page_title ?? 'APS Dream Home - Premium Real Estate'; ?>">
    <meta property="og:description" content="<?php echo $page_description ?? 'Discover premium residential and commercial properties in Gorakhpur and across Uttar Pradesh.'; ?>">
    <meta property="og:image" content="<?php echo BASE_URL; ?>/assets/images/logo/apslogonew.jpg">
    <meta property="og:url" content="<?php echo BASE_URL . ($_SERVER['REQUEST_URI'] ?? '/'); ?>">
    <meta property="og:site_name" content="APS Dream Home">

    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo $page_title ?? 'APS Dream Home'; ?>">
    <meta name="twitter:description" content="<?php echo $page_description ?? 'Premium Real Estate in Uttar Pradesh'; ?>">

    <!-- Canonical URL -->
    <link rel="canonical" href="<?php echo BASE_URL . ($_SERVER['REQUEST_URI'] ?? '/'); ?>">

    <!-- Favicon -->
    <link rel="icon" type="image/jpeg" href="<?php echo BASE_URL; ?>/assets/images/logo/apslogonew.jpg">

    <!-- PWA Meta Tags -->
    <meta name="theme-color" content="#2c3e50">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="APS Dream Home">
    <link rel="manifest" href="<?php echo defined('BASE_URL') ? BASE_URL : ''; ?>/manifest.json">
    <link rel="apple-touch-icon" href="<?php echo defined('BASE_URL') ? BASE_URL : ''; ?>/assets/images/icons/icon-192x192.png">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">

    <!-- Header CSS -->
    <link href="<?php echo BASE_URL; ?>/assets/css/header.css" rel="stylesheet">

    <!-- Extra head content from views -->
    <?php if (!empty($extraHead)) echo $extraHead; ?>
    <script>
        // Dynamically set header height for page offset
        (function() {
            function setHeaderHeight() {
                var hdr = document.querySelector('header.premium-header');
                var h = hdr ? hdr.offsetHeight : 80;
                document.documentElement.style.setProperty('--header-height', h + 'px');
            }
            window.addEventListener('load', setHeaderHeight);
            window.addEventListener('resize', setHeaderHeight);
        })();
    </script>

    <!-- style.css must load BEFORE frontend.css to match original cascade order -->
    <link href="<?php echo BASE_URL; ?>/assets/css/style.css" rel="stylesheet">
    <!-- Frontend CSS (extracted inline styles, highest priority like original) -->
    <link href="<?php echo BASE_URL; ?>/assets/css/frontend.css" rel="stylesheet">
    <!-- Chatbot CSS -->
    <link href="<?php echo BASE_URL; ?>/assets/css/chatbot.css" rel="stylesheet">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?php echo BASE_URL; ?>/assets/images/apple-touch-icon.png">
</head>

<body>
    <?php
    // Admin pages skip public header entirely
    $isAdminPage = isset($admin_layout) && $admin_layout === true;
    $isPremiumPage = isset($premium_layout) && $premium_layout === true;

    if (!$isAdminPage) {
        if ($isPremiumPage) {
            include __DIR__ . '/active/header_new.php';
        } else {
            include __DIR__ . '/header.php';
        }
    }
    ?>

    <main>
        <?php echo $content ?? ''; ?>
    </main>

    <?php
    if (!$isAdminPage) {
        if ($isPremiumPage) {
            include __DIR__ . '/active/footer_new.php';
        } else {
            include __DIR__ . '/footer.php';
        }
    }
    ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- AI Chatbot (Left Side) -->
    <div id="ai-chatbot" class="ai-chatbot-container">
        <!-- Chat Popup -->
        <div class="ai-chat-popup" id="chatPopup">
            <div class="ai-chat-header">
                <div class="ai-avatar">
                    <img src="<?php echo BASE_URL; ?>/assets/images/logo/apslogonew.jpg" class="img-fluid" alt="APS Assistant" onerror="this.style.display='none'">
                    <span class="online-indicator"></span>
                </div>
                <div class="ai-header-info">
                    <h5>APS Property Assistant</h5>
                    <span class="status-text">Online • Ready to Help</span>
                </div>
                <button class="ai-lang-btn" id="langToggle" onclick="toggleChatLanguage()" title="Switch Language">🇮🇳 HI</button>
                <button class="ai-close-btn" onclick="toggleChat()">&times;</button>
            </div>
            <div class="ai-chat-body" id="chatBody">
                <div class="ai-message bot">
                    <div class="ai-message-content">
                        Namaste! 🙏<br><br>
                        Welcome to <strong>APS Dream Home</strong>! 🏠<br><br>
                        I'm your personal property assistant. Tell me what you're looking for!
                    </div>
                    <span class="ai-time">Just now</span>
                </div>
                <div class="quick-actions">
                    <button class="quick-btn" onclick="sendQuickMessage('View Properties')">
                        <i class="fas fa-home"></i> Properties
                    </button>
                    <button class="quick-btn" onclick="sendQuickMessage('Plot Prices')">
                        <i class="fas fa-tag"></i> Prices
                    </button>
                    <button class="quick-btn" onclick="sendQuickMessage('Book Site Visit')">
                        <i class="fas fa-calendar-check"></i> Book Visit
                    </button>
                    <button class="quick-btn" onclick="sendQuickMessage('Home Loan Help')">
                        <i class="fas fa-university"></i> Home Loan
                    </button>
                    <button class="quick-btn" onclick="sendQuickMessage('RERA Info')">
                        <i class="fas fa-shield-alt"></i> RERA Verified
                    </button>
                    <button class="quick-btn" onclick="sendQuickMessage('Contact Agent')">
                        <i class="fas fa-phone"></i> Call Us
                    </button>
                </div>
            </div>
            <div class="ai-chat-footer">
                <input type="text" id="chatInput" placeholder="Ask about properties..." onkeypress="handleChatKeypress(event)">
                <button class="ai-send-btn" onclick="sendChatMessage()">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
        </div>

        <!-- Floating Button -->
        <button class="ai-float-btn" id="aiFloatBtn" onclick="toggleChat()">
            <i class="fas fa-comments"></i>
            <span class="ai-pulse"></span>
        </button>
    </div>

    <!-- WhatsApp Button (Right Side - Manual Chat) -->
    <a href="https://wa.me/919277121112?text=Hi, I'm interested in APS Dream Home properties" target="_blank" class="whatsapp-float-btn" title="Chat on WhatsApp">
        <i class="fab fa-whatsapp"></i>
    </a>

    <!-- Chatbot JS -->
    <script>
        window.chatbotApiUrl = '<?php echo defined('BASE_URL') ? BASE_URL : ''; ?>/api/gemini/chat';
        window.chatbotUserContext = {
            role: '<?php echo isset($_SESSION['admin_id']) ? 'admin' : (isset($_SESSION['user_role']) ? $_SESSION['user_role'] : (isset($_SESSION['user_id']) ? 'customer' : 'guest')); ?>',
            userId: '<?php echo $_SESSION['user_id'] ?? $_SESSION['admin_id'] ?? ''; ?>',
            userName: '<?php echo addslashes($_SESSION['user_name'] ?? $_SESSION['admin_name'] ?? ''); ?>',
            isLoggedIn: <?php echo (isset($_SESSION['user_id']) || isset($_SESSION['admin_id'])) ? 'true' : 'false'; ?>
        };
    </script>
    <script src="<?php echo BASE_URL; ?>/assets/js/chatbot.js"></script>

    <!-- Custom JS -->
    <script src="<?php echo BASE_URL; ?>/assets/js/main.js"></script>
    <script src="<?php echo BASE_URL; ?>/assets/js/premium-header.js"></script>

    <!-- Utility JS -->
    <script>
        document.querySelectorAll('a[href^="#"]').forEach(function(anchor) {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                var target = document.querySelector(this.getAttribute('href'));
                if (target) target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
        });

        var counters = document.querySelectorAll('.stat-number');
        counters.forEach(function(counter) {
            function animate() {
                var value = +counter.getAttribute('data-target');
                var data = +counter.innerText;
                if (data < value) {
                    counter.innerText = Math.ceil(data + value / 200);
                    setTimeout(animate, 1);
                } else {
                    counter.innerText = value;
                }
            }
            var observer = new IntersectionObserver(function(entries) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) { animate(); observer.unobserve(entry.target); }
                });
            });
            observer.observe(counter);
        });

        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('<?php echo defined('BASE_URL') ? BASE_URL : ''; ?>/pwa/service-worker')
                    .then(function(reg) { console.log('SW registered:', reg.scope); })
                    .catch(function(err) { console.log('SW registration failed:', err); });
            });
        }
    </script>
</body>

</html>