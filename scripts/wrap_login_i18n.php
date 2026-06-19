<?php
$base = dirname(__DIR__);
$helper = "require_once __DIR__ . '/../../Helpers/TranslationHelper.php';\n";

// ============ 1. admin_login.php ============
$f = "$base/app/views/auth/admin_login.php";
$c = file_get_contents($f);
if (strpos($c, 'TranslationHelper') === false) {
    $c = str_replace(
        "<?php\nif (!defined('BASE_URL'))",
        "<?php\n" . $helper . "\nif (!defined('BASE_URL'))",
        $c
    );
}
$replacements = [
    ["<title>Admin Login - APS Dream Home</title>", "<title><?php echo __('auth_admin_login_title', 'Admin Login'); ?></title>"],
    ["<h1 class=\"brand-title\">APS Dream Home</h1>", "<h1 class=\"brand-title\"><?php echo __('auth_aps_dream_home', 'APS Dream Home'); ?></h1>"],
    ["<p class=\"brand-subtitle\">Admin Portal</p>", "<p class=\"brand-subtitle\"><?php echo __('auth_admin_portal', 'Admin Portal'); ?></p>"],
    ["<i class=\"fa-solid fa-user\"></i> Username or Email", "<i class=\"fa-solid fa-user\"></i> <?php echo __('auth_username_email', 'Username or Email'); ?>"],
    ["placeholder=\"Enter your username or email\"", "placeholder=\"<?php echo __('auth_enter_username_email', 'Enter your username or email'); ?>\""],
    ["<i class=\"fa-solid fa-lock\"></i> Password", "<i class=\"fa-solid fa-lock\"></i> <?php echo __('auth_password', 'Password'); ?>"],
    ["placeholder=\"Enter your password\"", "placeholder=\"<?php echo __('auth_enter_password', 'Enter your password'); ?>\""],
    ["<i class=\"fa-solid fa-shield-halved\"></i> Security Check", "<i class=\"fa-solid fa-shield-halved\"></i> <?php echo __('auth_security_check', 'Security Check'); ?>"],
    ["Sign In\n                </button>", "<?php echo __('auth_sign_in', 'Sign In'); ?>\n                </button>"],
    ["or continue with", "<?php echo __('auth_or_continue_with', 'or continue with'); ?>"],
    ["Continue with Google", "<?php echo __('auth_continue_with_google', 'Continue with Google'); ?>"],
];
foreach ($replacements as [$s, $r]) {
    if (strpos($c, $s) !== false) {
        $c = str_replace($s, $r, $c);
    }
}
file_put_contents($f, $c);
echo "1. admin_login.php DONE\n";

// ============ 2. agent_login.php ============
$f = "$base/app/views/auth/agent_login.php";
$c = file_get_contents($f);
if (strpos($c, 'TranslationHelper') === false) {
    $c = str_replace(
        "<?php\nif (!defined('BASE_URL'))",
        "<?php\n" . $helper . "\nif (!defined('BASE_URL'))",
        $c
    );
}
$replacements = [
    ["<h2>APS Dream Home</h2>", "<h2><?php echo __('auth_aps_dream_home', 'APS Dream Home'); ?></h2>"],
    ["<p>Agent Portal Login</p>", "<p><?php echo __('auth_agent_portal_login', 'Agent Portal Login'); ?></p>"],
    ["Email or Phone\n                    </label>", "<?php echo __('auth_email_or_phone', 'Email or Phone'); ?>\n                    </label>"],
    ["placeholder=\"Email or Phone\"", "placeholder=\"<?php echo __('auth_email_or_phone_ph', 'Email or Phone'); ?>\""],
    ["Password\n                    </label>", "<?php echo __('auth_password', 'Password'); ?>\n                    </label>"],
    ["placeholder=\"Password\"", "placeholder=\"<?php echo __('auth_password', 'Password'); ?>\""],
    ["Sign In\n                </button>", "<?php echo __('auth_sign_in', 'Sign In'); ?>\n                </button>"],
    ["or continue with", "<?php echo __('auth_or_continue_with', 'or continue with'); ?>"],
    ["Continue with Google", "<?php echo __('auth_continue_with_google', 'Continue with Google'); ?>"],
    ["<i class=\"fas fa-user-plus me-1\"></i>Create an Agent Account", "<i class=\"fas fa-user-plus me-1\"></i><?php echo __('auth_create_agent_account', 'Create an Agent Account'); ?>"],
    ["<i class=\"fas fa-arrow-left me-1\"></i>Back to Homepage", "<i class=\"fas fa-arrow-left me-1\"></i><?php echo __('auth_back_to_homepage', 'Back to Homepage'); ?>"],
];
foreach ($replacements as [$s, $r]) {
    if (strpos($c, $s) !== false) {
        $c = str_replace($s, $r, $c);
    }
}
file_put_contents($f, $c);
echo "2. agent_login.php DONE\n";

// ============ 3. associate_login.php ============
$f = "$base/app/views/auth/associate_login.php";
$c = file_get_contents($f);
if (strpos($c, 'TranslationHelper') === false) {
    $c = str_replace(
        "<?php\nif (!defined('BASE_URL'))",
        "<?php\n" . $helper . "\nif (!defined('BASE_URL'))",
        $c
    );
}
$replacements = [
    ["<title>Associate Login | APS Dream Home</title>", "<title><?php echo __('auth_associate_login_title', 'Associate Login'); ?></title>"],
    ["<h1 class=\"brand-title\">APS Dream Home</h1>", "<h1 class=\"brand-title\"><?php echo __('auth_aps_dream_home', 'APS Dream Home'); ?></h1>"],
    ["<p class=\"brand-subtitle\">Associate Portal Login</p>", "<p class=\"brand-subtitle\"><?php echo __('auth_associate_portal_login', 'Associate Portal Login'); ?></p>"],
    ["Email or Phone</label>", "<?php echo __('auth_email_or_phone', 'Email or Phone'); ?></label>"],
    ["placeholder=\"Enter email or phone\"", "placeholder=\"<?php echo __('auth_enter_email_phone', 'Enter email or phone'); ?>\""],
    ["Password</label>", "<?php echo __('auth_password', 'Password'); ?></label>"],
    ["placeholder=\"Enter password\"", "placeholder=\"<?php echo __('auth_enter_password', 'Enter password'); ?>\""],
];
foreach ($replacements as [$s, $r]) {
    if (strpos($c, $s) !== false) {
        $c = str_replace($s, $r, $c);
    }
}
file_put_contents($f, $c);
echo "3. associate_login.php DONE\n";

echo "\n=== DONE: 3 login files wrapped ===\n";
