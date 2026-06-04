<?php
/**
 * Reusable language switcher component.
 *
 * Usage in any view:
 *   <?php $langSwitcherVariant = 'dropdown'; include __DIR__ . '/language_switcher.php'; ?>
 *   <?php $langSwitcherVariant = 'inline';   include __DIR__ . '/language_switcher.php'; ?>
 *   <?php $langSwitcherVariant = 'footer';   include __DIR__ . '/language_switcher.php'; ?>
 *
 * If $langSwitcherVariant is not set, defaults to 'dropdown'.
 */

if (!defined('LANG_SWITCHER_LOADED')) {
    define('LANG_SWITCHER_LOADED', true);

    $variant = $langSwitcherVariant ?? 'dropdown';
    $current = function_exists('__current_lang') ? __current_lang() : 'en';
    $baseUrl = defined('BASE_URL') ? BASE_URL : '';
    ?>
    <?php if ($variant === 'dropdown'): ?>
        <li class="nav-item dropdown lang-switcher">
            <a class="nav-link dropdown-toggle" href="#" id="langDropdown" role="button"
               data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fa-solid fa-globe"></i>
                <?= strtoupper($current) ?>
            </a>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="langDropdown">
                <li>
                    <a class="dropdown-item <?= $current === 'en' ? 'active' : '' ?>"
                       href="<?= $baseUrl ?>/language/set/en">
                        <span class="lang-flag">🇬🇧</span> English
                    </a>
                </li>
                <li>
                    <a class="dropdown-item <?= $current === 'hi' ? 'active' : '' ?>"
                       href="<?= $baseUrl ?>/language/set/hi">
                        <span class="lang-flag">🇮🇳</span> हिन्दी
                    </a>
                </li>
            </ul>
        </li>
    <?php elseif ($variant === 'inline'): ?>
        <div class="lang-switcher-inline d-inline-flex align-items-center">
            <a class="lang-link <?= $current === 'en' ? 'active' : '' ?>"
               href="<?= $baseUrl ?>/language/set/en">EN</a>
            <span class="lang-divider">|</span>
            <a class="lang-link <?= $current === 'hi' ? 'active' : '' ?>"
               href="<?= $baseUrl ?>/language/set/hi">हि</a>
        </div>
    <?php elseif ($variant === 'footer'): ?>
        <div class="lang-switcher-footer">
            <h6 class="footer-heading"><?= htmlspecialchars(__('language') ?: 'Language') ?></h6>
            <ul class="list-unstyled mb-0">
                <li>
                    <a class="lang-link <?= $current === 'en' ? 'active' : '' ?>"
                       href="<?= $baseUrl ?>/language/set/en">
                        🇬🇧 English
                    </a>
                </li>
                <li>
                    <a class="lang-link <?= $current === 'hi' ? 'active' : '' ?>"
                       href="<?= $baseUrl ?>/language/set/hi">
                        🇮🇳 हिन्दी
                    </a>
                </li>
            </ul>
        </div>
    <?php endif; ?>
    <?php
}
