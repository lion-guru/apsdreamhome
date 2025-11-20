<?php
require_once __DIR__ . '/../data/project_availability.php';

if (!function_exists('renderProjectAvailability')) {
    function renderProjectAvailability(array $options = []): void
    {
        $projects = include __DIR__ . '/../data/project_availability.php';
        $theme = $options['theme'] ?? 'light';
        $limit = $options['limit'] ?? null;

        if ($limit !== null) {
            $projects = array_slice($projects, 0, (int) $limit);
        }

        $cardClasses = $theme === 'dark'
            ? 'bg-dark text-light border border-light border-opacity-25 shadow-lg'
            : 'bg-white text-dark border border-light-subtle shadow-lg';
        $badgeMap = [
            'available' => 'bg-success-subtle text-success fw-semibold',
            'booked' => 'bg-warning-subtle text-warning fw-semibold',
            'sold' => 'bg-danger-subtle text-danger fw-semibold',
        ];
        ?>
        <div class="row g-4">
            <?php foreach ($projects as $project): ?>
                <div class="col-xl-3 col-lg-4 col-md-6">
                    <div class="card <?= $cardClasses ?> h-100 rounded-4 overflow-hidden">
                        <div class="card-body p-4 d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h5 class="card-title mb-1 fw-bold">
                                        <?= htmlspecialchars($project['name']) ?>
                                    </h5>
                                    <span class="badge bg-primary-subtle text-primary fw-semibold">
                                        <?= htmlspecialchars($project['type']) ?>
                                    </span>
                                </div>
                                <div class="text-end small">
                                    <div class="text-muted">Last updated</div>
                                    <div class="fw-semibold">
                                        <?= htmlspecialchars(date('d M Y', strtotime($project['last_updated']))) ?>
                                    </div>
                                </div>
                            </div>

                            <p class="text-muted mb-3">
                                <i class="fas fa-map-marker-alt me-2 text-primary"></i>
                                <?= htmlspecialchars($project['location']) ?>
                            </p>

                            <ul class="list-group list-group-flush mb-3">
                                <?php foreach ($project['inventory'] as $key => $value): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center border-0 px-0 pb-2">
                                        <span class="text-uppercase small text-muted">
                                            <?= htmlspecialchars($key) ?>
                                        </span>
                                        <span class="badge <?= $badgeMap[$key] ?? 'bg-secondary' ?> rounded-pill px-3 py-2">
                                            <?= (int) $value ?>
                                        </span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>

                            <div class="mt-auto">
                                <div class="d-flex justify-content-between align-items-center small text-muted mb-3">
                                    <span><i class="fas fa-certificate me-2 text-warning"></i>RERA</span>
                                    <span class="fw-semibold text-uppercase">
                                        <?= htmlspecialchars($project['rera']) ?>
                                    </span>
                                </div>
                                <a href="<?= htmlspecialchars($project['cta_url']) ?>" class="btn btn-outline-primary w-100 rounded-pill">
                                    View project details
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php
    }
}
