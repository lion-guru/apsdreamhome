<?php
/**
 * Compliance & Trust information widget
 * Usage: include this file and call renderComplianceHub([...])
 */

if (!function_exists('renderComplianceHub')) {
    /**
     * Render compliance hub row
     *
     * @param array $options Supported keys:
     *  - theme: 'light' (default) or 'dark'
     */
    function renderComplianceHub(array $options = []): void
    {
        $theme = $options['theme'] ?? 'light';
        $isDark = $theme === 'dark';

        $wrapperClasses = $isDark
            ? 'p-4 bg-secondary bg-opacity-10 border border-light border-opacity-25 rounded-4'
            : 'p-4 bg-white border border-light-subtle rounded-4 shadow-sm';

        $labelClass = $isDark ? 'text-light opacity-75' : 'text-muted text-uppercase';
        $textClass = $isDark ? 'text-light' : 'text-dark';
        $linkClass = $isDark ? 'text-light text-decoration-none' : 'text-primary text-decoration-none fw-semibold';

        $registrations = [
            [
                'icon' => 'fa-id-card-alt',
                'label' => 'Registered Developer',
                'title' => 'APS Dream Homes Pvt Ltd',
                'details' => 'CIN: U70109UP2022PTC163047',
            ],
            [
                'icon' => 'fa-certificate',
                'label' => 'RERA Registration',
                'title' => 'UPRERAAGT15004',
                'details' => 'Uttar Pradesh Real Estate Regulatory Authority',
            ],
            [
                'icon' => 'fa-balance-scale',
                'label' => 'Compliance Desk',
                'title' => 'legal@apsdreamhomes.com',
                'details' => '+91-9554000001',
                'isContact' => true,
            ],
            [
                'icon' => 'fa-university',
                'label' => 'Banking Partners',
                'title' => 'HDFC Bank · SBI · PNB Housing Finance',
                'details' => BASE_URL . 'bank',
                'isLink' => true,
            ],
        ];
        ?>
        <div class="<?= $wrapperClasses ?>">
            <div class="row g-3 align-items-center">
                <?php foreach ($registrations as $entry): ?>
                    <div class="col-lg-3 col-md-6">
                        <div class="d-flex align-items-start gap-3 <?= $textClass ?>">
                            <i class="fas <?= htmlspecialchars($entry['icon'], ENT_QUOTES) ?> fa-lg mt-1"></i>
                            <div>
                                <h6 class="text-uppercase <?= $labelClass ?> mb-1">
                                    <?= htmlspecialchars($entry['label']) ?>
                                </h6>
                                <?php if (!empty($entry['isContact'])): ?>
                                    <div>
                                        <small>Email:</small><br>
                                        <a href="mailto:<?= htmlspecialchars($entry['title']) ?>" class="<?= $linkClass ?>">
                                            <?= htmlspecialchars($entry['title']) ?>
                                        </a><br>
                                        <small>Phone:</small><br>
                                        <a href="tel:<?= htmlspecialchars($entry['details']) ?>" class="<?= $linkClass ?>">
                                            <?= htmlspecialchars($entry['details']) ?>
                                        </a>
                                    </div>
                                <?php elseif (!empty($entry['isLink'])): ?>
                                    <div>
                                        <strong><?= htmlspecialchars($entry['title']) ?></strong><br>
                                        <a href="<?= htmlspecialchars($entry['details']) ?>" class="<?= $linkClass ?>">
                                            View all lenders →
                                        </a>
                                    </div>
                                <?php else: ?>
                                    <strong><?= htmlspecialchars($entry['title']) ?></strong><br>
                                    <small class="<?= $labelClass ?> fw-normal">
                                        <?= htmlspecialchars($entry['details']) ?>
                                    </small>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
    }
}
