<?php
namespace App\Http\Controllers\Front;

/**
 * PlotIndexController — public plot browsing.
 * Routes: /plots, /colony/{slug}/plots, /plot/{id}, /api/plots/by-colony/{colonyId}
 */
class PlotIndexController extends PlotBaseController
{
    /**
     * List all colonies with available plots.
     * Single query with LEFT JOIN + GROUP BY (no per-colony subqueries),
     * fully parameterized tenant scoping.
     */
    public function index()
    {
        $colonies = [];
        try {
            $tid = (int)$this->tenantId();
            if ($tid > 1) {
                $colonies = $this->db->fetchAll("
                    SELECT c.*, d.name as district_name, s.name as state_name,
                        COUNT(CASE WHEN p.status = 'available' THEN 1 END) as available_plots
                    FROM colonies c
                    LEFT JOIN districts d ON c.district_id = d.id
                    LEFT JOIN states s ON d.state_id = s.id
                    LEFT JOIN plots p ON p.colony_id = c.id AND p.tenant_id = ?
                    WHERE c.is_active = 1
                    GROUP BY c.id
                    ORDER BY c.name
                ", [$tid]) ?: [];
            } else {
                $colonies = $this->db->fetchAll("
                    SELECT c.*, d.name as district_name, s.name as state_name,
                        COUNT(CASE WHEN p.status = 'available' THEN 1 END) as available_plots
                    FROM colonies c
                    LEFT JOIN districts d ON c.district_id = d.id
                    LEFT JOIN states s ON d.state_id = s.id
                    LEFT JOIN plots p ON p.colony_id = c.id
                    WHERE c.is_active = 1
                    GROUP BY c.id
                    ORDER BY c.name
                ") ?: [];
            }
        } catch (\Throwable $e) {
            error_log('PlotIndexController::index error: ' . $e->getMessage());
            $colonies = [];
        }

        $this->render('pages/plots', [
            'page_title' => 'Available Plots - APS Dream Home',
            'meta_description' => 'Browse available residential and commercial plots for sale in Gorakhpur, Deoria, Kushinagar, and Varanasi.',
            'colonies' => $colonies,
        ]);
    }

    /**
     * Show available plots in a specific colony with filters + pagination.
     */
    public function colonyPlots($slug)
    {
        $slug = trim(strip_tags((string)$slug));
        $colony = $this->db->fetchRow("
            SELECT c.*, d.name as district_name, s.name as state_name
            FROM colonies c
            LEFT JOIN districts d ON c.district_id = d.id
            LEFT JOIN states s ON d.state_id = s.id
            WHERE c.slug = ? AND c.is_active = 1
        ", [$slug]);

        if (!$colony) {
            return $this->redirect('/plots');
        }

        // Build filter query (all values bound — no interpolation)
        $where = "p.colony_id = ? AND p.is_active = 1";
        $params = [$colony['id']];
        $tid = (int)$this->tenantId();
        if ($tid > 1) { $where .= " AND p.tenant_id = ?"; $params[] = $tid; }

        // Status filter (whitelisted)
        $status = $_GET['status'] ?? 'available';
        if (in_array($status, self::FILTERABLE_STATUSES, true)) {
            $where .= " AND p.status = ?";
            $params[] = $status;
        } else {
            $status = 'available';
            $where .= " AND p.status = ?";
            $params[] = $status;
        }

        // Plot-number search (the filter form sends ?q=)
        $search = trim(strip_tags((string)($_GET['q'] ?? '')));
        if ($search !== '') {
            $where .= " AND p.plot_number LIKE ?";
            $params[] = '%' . $search . '%';
        }

        // Dimension filter (20x40, 30x50, etc.)
        $dimension = $_GET['dimension'] ?? '';
        if ($dimension && preg_match('/^\d+x\d+$/', $dimension)) {
            $where .= " AND p.dimension_label = ?";
            $params[] = $dimension;
        } else {
            $dimension = '';
        }

        // Block filter
        $block = trim(strip_tags((string)($_GET['block'] ?? '')));
        if ($block !== '') {
            $where .= " AND p.block = ?";
            $params[] = $block;
        }

        // Price range filter
        $minPrice = floatval($_GET['min_price'] ?? 0);
        $maxPrice = floatval($_GET['max_price'] ?? 0);
        if ($minPrice > 0) {
            $where .= " AND p.total_price >= ?";
            $params[] = $minPrice;
        }
        if ($maxPrice > 0) {
            $where .= " AND p.total_price <= ?";
            $params[] = $maxPrice;
        }

        // Area range filter
        $minArea = floatval($_GET['min_area'] ?? 0);
        $maxArea = floatval($_GET['max_area'] ?? 0);
        if ($minArea > 0) {
            $where .= " AND p.area_sqft >= ?";
            $params[] = $minArea;
        }
        if ($maxArea > 0) {
            $where .= " AND p.area_sqft <= ?";
            $params[] = $maxArea;
        }

        // Sorting (whitelisted map — no user input reaches ORDER BY raw)
        $sort = $_GET['sort'] ?? 'plot_number';
        $allowedSorts = ['plot_number' => 'p.plot_number', 'price_asc' => 'p.total_price ASC', 'price_desc' => 'p.total_price DESC', 'area_asc' => 'p.area_sqft ASC', 'area_desc' => 'p.area_sqft DESC'];
        $orderBy = $allowedSorts[$sort] ?? 'p.plot_number';
        if (!isset($allowedSorts[$sort])) $sort = 'plot_number';

        // Pagination (OFFSET/LIMIT are cast ints — safe to interpolate)
        [$page, $perPage, $offset] = $this->pagination(self::PLOTS_PER_PAGE);
        $total = (int)$this->db->fetchColumn("SELECT COUNT(*) FROM plots p WHERE $where", $params);
        $totalPages = max(1, (int)ceil($total / $perPage));
        if ($page > $totalPages) { $page = $totalPages; $offset = ($page - 1) * $perPage; }

        $sql = "SELECT p.* FROM plots p WHERE $where ORDER BY p.block, $orderBy LIMIT $perPage OFFSET $offset";
        $plots = $this->db->fetchAll($sql, $params) ?: [];

        // Filter options: ONE distinct query, derived in PHP
        $tidPlots = $tid > 1 ? ' AND tenant_id = ?' : '';
        $tidPlotParam = $tid > 1 ? [$tid] : [];
        $filterRows = $this->db->fetchAll(
            "SELECT DISTINCT dimension_label, block FROM plots WHERE colony_id = ?" . $tidPlots,
            array_merge([$colony['id']], $tidPlotParam)
        ) ?: [];
        $dimensions = [];
        $blocks = [];
        foreach ($filterRows as $fr) {
            if (!empty($fr['dimension_label'])) $dimensions[] = ['dimension_label' => $fr['dimension_label']];
            if (!empty($fr['block'])) $blocks[] = ['block' => $fr['block']];
        }
        usort($dimensions, fn($a, $b) => strcmp($a['dimension_label'], $b['dimension_label']));
        usort($blocks, fn($a, $b) => strcmp($a['block'], $b['block']));

        // Stats (single aggregate query)
        $stats = $this->db->fetchRow("
            SELECT
                COUNT(*) as total,
                SUM(CASE WHEN status = 'available' THEN 1 ELSE 0 END) as available,
                SUM(CASE WHEN status = 'booked' THEN 1 ELSE 0 END) as booked,
                SUM(CASE WHEN status = 'sold' THEN 1 ELSE 0 END) as sold,
                MIN(total_price) as min_price,
                MAX(total_price) as max_price,
                MIN(area_sqft) as min_area,
                MAX(area_sqft) as max_area
            FROM plots WHERE colony_id = ? AND is_active = 1" . $tidPlots . "
        ", array_merge([$colony['id']], $tidPlotParam));

        $this->render('pages/colony_plots', [
            'page_title' => $colony['name'] . ' - Available Plots',
            'meta_description' => $colony['meta_description'] ?: "Browse available plots in {$colony['name']}, {$colony['district_name']}",
            'colony' => $colony,
            'plots' => $plots,
            'dimensions' => $dimensions,
            'blocks' => $blocks,
            'stats' => $stats,
            'current_status' => $status,
            'current_dimension' => $dimension,
            'current_block' => $block,
            'current_sort' => $sort,
            'current_min_price' => $minPrice,
            'current_max_price' => $maxPrice,
            'current_min_area' => $minArea,
            'current_max_area' => $maxArea,
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => $totalPages,
            ],
        ]);
    }

    /**
     * Show single plot detail (public view)
     */
    public function show($id)
    {
        $id = (int)$id;
        if ($id <= 0) {
            return $this->redirect('/plots');
        }
        $tid = (int)$this->tenantId();
        $plotParams = [$id];
        $tidScope = '';
        if ($tid > 1) { $tidScope = ' AND p.tenant_id = ?'; $plotParams[] = $tid; }
        $plot = $this->db->fetchRow("
            SELECT p.*, c.name as colony_name, c.slug as colony_slug, c.description as colony_description,
                c.amenities, c.nearby_places, c.gallery_images, c.map_link, c.image_path as colony_image,
                d.name as district_name, s.name as state_name
            FROM plots p
            JOIN colonies c ON p.colony_id = c.id
            LEFT JOIN districts d ON c.district_id = d.id
            LEFT JOIN states s ON d.state_id = s.id
            WHERE p.id = ? AND p.is_active = 1" . $tidScope . "
        ", $plotParams);

        if (!$plot) {
            return $this->redirect('/plots');
        }

        // Price history
        $priceHistory = $this->db->fetchAll("
            SELECT * FROM price_history WHERE plot_id = ? ORDER BY created_at DESC LIMIT 10
        ", [$id]) ?: [];

        // Nearby plots in same block
        $nearbyPlots = $this->db->fetchAll("
            SELECT id, plot_number, block, area_sqft, total_price, dimension_label, status
            FROM plots WHERE colony_id = ? AND block = ? AND id != ? AND is_active = 1
            LIMIT 6
        ", [$plot['colony_id'], $plot['block'], $id]) ?: [];

        $this->render('pages/plot_detail', [
            'page_title' => "Plot {$plot['plot_number']} - {$plot['colony_name']}",
            'meta_description' => "{$plot['dimension_label']} plot in {$plot['colony_name']}, {$plot['district_name']}. Area: {$plot['area_sqft']} sqft, Price: ₹{$plot['total_price']}.",
            'plot' => $plot,
            'priceHistory' => $priceHistory,
            'nearbyPlots' => $nearbyPlots,
        ]);
    }

    /**
     * API: Get plots by colony (JSON for AJAX loading).
     * Paginated, rate-limited, cacheable. Response keeps the legacy
     * {success, plots} shape and adds {meta}.
     */
    public function apiByColony($colonyId)
    {
        // Rate limit (fail-open: logging errors never block the API)
        try {
            if (class_exists('\\App\\Core\\Middleware\\RateLimitMiddleware')) {
                $rl = new \App\Core\Middleware\RateLimitMiddleware();
                if (!$rl->check('api')) {
                    return $this->jsonResponse(['success' => false, 'error' => 'Too many requests. Please try again later.'], 429);
                }
            }
        } catch (\Throwable $e) {
            error_log('PlotIndexController::apiByColony rate-limit: ' . $e->getMessage());
        }

        $colonyId = (int)$colonyId;
        if ($colonyId <= 0) {
            return $this->jsonResponse(['success' => false, 'error' => 'Invalid colony'], 400);
        }
        $tid = (int)$this->tenantId();
        $status = $_GET['status'] ?? 'available';
        if (!in_array($status, self::FILTERABLE_STATUSES, true)) {
            $status = 'available';
        }
        $block = trim(strip_tags((string)($_GET['block'] ?? '')));

        $where = "colony_id = ? AND is_active = 1" . ($tid > 1 ? ' AND tenant_id = ?' : '');
        $params = [$colonyId];
        if ($tid > 1) {
            $params[] = $tid;
        }

        $where .= " AND status = ?";
        $params[] = $status;
        if ($block !== '') {
            $where .= " AND block = ?";
            $params[] = $block;
        }

        [$page, $perPage, $offset] = $this->pagination(self::API_PER_PAGE);
        $total = (int)$this->db->fetchColumn("SELECT COUNT(*) FROM plots WHERE $where", $params);

        $plots = $this->db->fetchAll(
            "SELECT id, plot_number, block, dimension_label, area_sqft, base_price_per_sqft, total_price, status, corner_plot, park_facing FROM plots WHERE $where ORDER BY block, plot_number LIMIT $perPage OFFSET $offset",
            $params
        ) ?: [];

        if (!headers_sent()) {
            header('Cache-Control: public, max-age=60');
        }
        return $this->jsonResponse([
            'success' => true,
            'plots' => $plots,
            'meta' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => max(1, (int)ceil($total / $perPage)),
            ],
        ]);
    }
}
