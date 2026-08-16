<?php
/**
 * Gamification Widget — Reusable badge + progress bar block
 *
 * Required variables:
 *   $gamify['title']       string  Widget title (e.g. "MLM Rank")
 *   $gamify['icon']        string  FontAwesome class (e.g. "fa-trophy")
 *   $gamify['level']       string  Current level name (e.g. "Silver")
 *   $gamify['level_color'] string  CSS color token: primary|secondary|orange|green|purple|indigo
 *   $gamify['metric']      string  Primary metric (e.g. "Total Team Sales: 12")
 *   $gamify['progress_pct'] float  0-100
 *   $gamify['next_label']  string  Next level name
 *   $gamify['next_target'] string  Next-level target value (e.g. "₹5,00,000")
 *   $gamify['cta_url']     string  Upgrade CTA link
 *   $gamify['cta_text']    string  CTA button text
 *   $gamify['gradient']    string  (optional) CSS gradient for bg
 */
$gamify = $gamify ?? [];
$gTitle = $gamify['title'] ?? __('component_progress', 'Progress');
$gIcon = $gamify['icon'] ?? 'fa-trophy';
$gLevel = $gamify['level'] ?? __('component_bronze', 'Bronze');
$gColor = $gamify['level_color'] ?? 'primary';
$gMetric = $gamify['metric'] ?? '';
$gPct = (float)($gamify['progress_pct'] ?? 0);
$gNextLabel = $gamify['next_label'] ?? __('component_next', 'Next');
$gNextTarget = $gamify['next_target'] ?? '';
$gCta = $gamify['cta_url'] ?? '#';
$gCtaText = $gamify['cta_text'] ?? __('component_upgrade', 'Upgrade');
$gGradient = $gamify['gradient'] ?? 'linear-gradient(135deg, #fff 0%, #ede9fe 100%)';
?>
<div class="aps-cp-card mb-4" class="style-3203">
    <div class="aps-cp-card-header" class="style-58119">
        <h5 class="mb-0"><i class="fas <?= htmlspecialchars($gIcon ?? '') ?>" class="style-96303"></i> <?= htmlspecialchars($gTitle ?? '') ?></h5>
    </div>
    <div class="aps-cp-card-body text-center">
        <div class="display-5 fw-bold mb-1" class="style-96303"><?= htmlspecialchars($gLevel ?? '') ?></div>
        <?php if ($gMetric !== ''): ?>
        <small class="text-muted d-block mb-3"><?= htmlspecialchars($gMetric ?? '') ?></small>
        <?php endif; ?>
        <div class="aps-cp-progress" class="style-51045">
            <div class="aps-cp-progress-bar" class="style-5100"></div>
        </div>
        <p class="text-muted small mt-2 mb-0">
            <?php if ($gNextTarget !== ''): ?>
                <?= htmlspecialchars($gNextTarget ?? '') ?> <?= __('component_more_to_reach', 'more to reach') ?> <strong><?= htmlspecialchars($gNextLabel ?? '') ?></strong>
            <?php else: ?>
                Reach <strong><?= htmlspecialchars($gNextLabel ?? '') ?></strong>
            <?php endif; ?>
        </p>
        <?php if ($gCta !== '#'): ?>
        <a href="<?= htmlspecialchars($gCta ?? '') ?>" class="aps-cp-btn aps-cp-btn-sm aps-cp-btn-<?= htmlspecialchars($gColor ?? '') ?> mt-3"><i class="fas fa-arrow-up"></i> <?= htmlspecialchars($gCtaText ?? '') ?></a>
        <?php endif; ?>
    </div>
</div>
