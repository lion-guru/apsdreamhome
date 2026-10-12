<?php

namespace App\Http\Controllers\Admin;

class PlotCadExchangeController extends AdminController
{
    /**
     * GET /admin/plots/cad-exchange
     */
    public function index()
    {
        $this->requireAdmin();

        [$tSql, $tParams] = $this->tenantWhere();
        $colonies = $this->db->fetchAll(
            "SELECT id, name, location, latitude, longitude FROM colonies WHERE 1=1 $tSql ORDER BY name ASC",
            $tParams
        ) ?: [];

        $colonyId = !empty($_GET['colony_id']) ? (int)$_GET['colony_id'] : (!empty($colonies[0]['id']) ? (int)$colonies[0]['id'] : 0);

        $selectedColony = null;
        $stats = ['total_plots' => 0, 'surveyed_plots' => 0, 'synthetic_plots' => 0, 'total_sqft' => 0.0];

        if ($colonyId > 0) {
            foreach ($colonies as $c) {
                if ((int)$c['id'] === $colonyId) {
                    $selectedColony = $c;
                    break;
                }
            }

            $plots = $this->db->fetchAll(
                "SELECT id, plot_number, block, area_sqft, status, polygon_coordinates, latitude, longitude
                 FROM plots WHERE colony_id = ? $tSql",
                array_merge([$colonyId], $tParams)
            ) ?: [];

            $stats['total_plots'] = count($plots);
            foreach ($plots as $p) {
                $stats['total_sqft'] += (float)($p['area_sqft'] ?? 0);
                if (!empty($p['polygon_coordinates'])) {
                    $stats['surveyed_plots']++;
                } else {
                    $stats['synthetic_plots']++;
                }
            }
        }

        $this->render('admin/plots/cad_exchange', [
            'page_title' => 'CAD & GeoJSON Batch Plot Exchange',
            'colonies' => $colonies,
            'colony_id' => $colonyId,
            'selected_colony' => $selectedColony,
            'stats' => $stats
        ]);
    }

    /**
     * GET /admin/plots/cad-exchange/export?colony_id={id}
     * Exports entire colony layout as an RFC 7946 GeoJSON FeatureCollection
     */
    public function export()
    {
        $this->requireAdmin();
        $colonyId = (int)($_GET['colony_id'] ?? 0);

        [$tSql, $tParams] = $this->tenantWhere();
        $colony = $this->db->fetchOne("SELECT * FROM colonies WHERE id = ? $tSql", array_merge([$colonyId], $tParams));

        if (!$colony) {
            $this->setFlash('error', 'Colony not found.');
            return $this->redirect('/admin/plots/cad-exchange');
        }

        $plots = $this->db->fetchAll(
            "SELECT id, plot_number, block, area_sqft, width_ft, length_ft, status, 
                    price_per_sqft, total_price, corner_plot, park_facing, road_width_ft, 
                    gata_number, khasra_number, facing, latitude, longitude, polygon_coordinates
             FROM plots WHERE colony_id = ? $tSql ORDER BY block, plot_number",
            array_merge([$colonyId], $tParams)
        ) ?: [];

        $features = [];
        $centerLat = $colony['latitude'] ? (float)$colony['latitude'] : 26.76;
        $centerLng = $colony['longitude'] ? (float)$colony['longitude'] : 83.37;

        $idx = 0;
        foreach ($plots as $p) {
            $coordinates = null;
            if (!empty($p['polygon_coordinates'])) {
                $decoded = json_decode($p['polygon_coordinates'], true);
                if (is_array($decoded) && !empty($decoded)) {
                    $coordinates = $decoded;
                }
            }

            // Fallback grid polygon if surveyor polygon is not stored
            if (!$coordinates) {
                $scale = 0.000008;
                $w = max((float)($p['width_ft'] ?? 30), 20);
                $l = max((float)($p['length_ft'] ?? 50), 30);
                $x1 = $centerLng + (($idx % 10) * $w * $scale * 1.2);
                $y1 = $centerLat + (floor($idx / 10) * $l * $scale * 1.2);
                $x2 = $x1 + ($w * $scale);
                $y2 = $y1 + ($l * $scale);
                $coordinates = [[
                    [$x1, $y1], [$x2, $y1], [$x2, $y2], [$x1, $y2], [$x1, $y1]
                ]];
            }

            $features[] = [
                'type' => 'Feature',
                'geometry' => [
                    'type' => 'Polygon',
                    'coordinates' => $coordinates
                ],
                'properties' => [
                    'plot_number' => $p['plot_number'],
                    'block' => $p['block'] ?? '',
                    'area_sqft' => (float)$p['area_sqft'],
                    'width_ft' => (float)($p['width_ft'] ?? 0),
                    'length_ft' => (float)($p['length_ft'] ?? 0),
                    'facing' => $p['facing'] ?? '',
                    'status' => $p['status'] ?? 'available',
                    'price_per_sqft' => (float)($p['price_per_sqft'] ?? 0),
                    'total_price' => (float)($p['total_price'] ?? 0),
                    'corner_plot' => (bool)$p['corner_plot'],
                    'park_facing' => (bool)$p['park_facing'],
                    'road_width_ft' => (float)($p['road_width_ft'] ?? 0),
                    'gata_number' => $p['gata_number'] ?? '',
                    'khasra_number' => $p['khasra_number'] ?? ''
                ]
            ];
            $idx++;
        }

        $geojson = [
            'type' => 'FeatureCollection',
            'name' => 'Colony_' . preg_replace('/[^A-Za-z0-9_-]/', '_', $colony['name']) . '_Layout',
            'colony_id' => $colonyId,
            'colony_name' => $colony['name'],
            'exported_at' => date('c'),
            'features' => $features
        ];

        $filename = 'colony_' . $colonyId . '_layout_' . date('Ymd_His') . '.geojson';
        header('Content-Type: application/geo+json');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        echo json_encode($geojson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        exit;
    }

    /**
     * POST /admin/plots/cad-exchange/import
     * Parses uploaded GeoJSON or CAD file and upserts plots with exact boundary polygon
     */
    public function import()
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();

        $colonyId = (int)($_POST['colony_id'] ?? 0);
        if ($colonyId <= 0) {
            $this->setFlash('error', 'Select a target colony for importing plots.');
            return $this->redirect('/admin/plots/cad-exchange');
        }

        if (empty($_FILES['cad_file']['tmp_name']) || $_FILES['cad_file']['error'] !== UPLOAD_ERR_OK) {
            $this->setFlash('error', 'Please upload a valid GeoJSON or CAD file.');
            return $this->redirect('/admin/plots/cad-exchange?colony_id=' . $colonyId);
        }

        $rawContent = file_get_contents($_FILES['cad_file']['tmp_name']);
        $data = json_decode($rawContent, true);

        if (!$data || !isset($data['features']) || !is_array($data['features'])) {
            $this->setFlash('error', 'Invalid GeoJSON: Root must be a FeatureCollection with a features array.');
            return $this->redirect('/admin/plots/cad-exchange?colony_id=' . $colonyId);
        }

        $tid = (int)$this->tenantId();
        $defaultRate = max(100.0, (float)($_POST['default_rate'] ?? 1000.0));
        $updateExisting = !empty($_POST['update_existing']);

        $created = 0;
        $updated = 0;
        $skipped = 0;

        foreach ($data['features'] as $feat) {
            $props = $feat['properties'] ?? [];
            $geom = $feat['geometry'] ?? [];

            $plotNum = trim((string)($props['plot_number'] ?? $props['plot_no'] ?? $props['name'] ?? ''));
            if ($plotNum === '') {
                $skipped++;
                continue;
            }

            $block = trim((string)($props['block'] ?? 'A'));
            $areaSqft = (float)($props['area_sqft'] ?? $props['area'] ?? 0);
            $widthFt = (float)($props['width_ft'] ?? $props['width'] ?? 0);
            $lengthFt = (float)($props['length_ft'] ?? $props['length'] ?? 0);
            $facing = trim((string)($props['facing'] ?? 'North'));
            $corner = !empty($props['corner_plot']) || !empty($props['is_corner']) ? 1 : 0;
            $park = !empty($props['park_facing']) || !empty($props['is_park_facing']) ? 1 : 0;
            $roadWidth = (float)($props['road_width_ft'] ?? $props['road_width'] ?? 30.0);
            $rate = (float)($props['price_per_sqft'] ?? $props['rate'] ?? $defaultRate);
            $totalPrice = (float)($props['total_price'] ?? ($areaSqft > 0 ? $areaSqft * $rate : 0));
            $gata = trim((string)($props['gata_number'] ?? $props['gata'] ?? ''));

            // Extract polygon coordinates & calculate centroid lat/lng
            $polyCoords = null;
            $lat = null;
            $lng = null;

            if (!empty($geom['coordinates']) && ($geom['type'] === 'Polygon' || $geom['type'] === 'MultiPolygon')) {
                $polyCoords = json_encode($geom['coordinates']);
                
                // Centroid calculation from first polygon ring
                $ring = ($geom['type'] === 'Polygon') ? ($geom['coordinates'][0] ?? []) : ($geom['coordinates'][0][0] ?? []);
                if (!empty($ring)) {
                    $sumLat = 0;
                    $sumLng = 0;
                    $ptCount = 0;
                    foreach ($ring as $pt) {
                        if (is_array($pt) && count($pt) >= 2) {
                            $sumLng += (float)$pt[0];
                            $sumLat += (float)$pt[1];
                            $ptCount++;
                        }
                    }
                    if ($ptCount > 0) {
                        $lng = round($sumLng / $ptCount, 8);
                        $lat = round($sumLat / $ptCount, 8);
                    }
                }
            }

            // Check if plot exists in colony
            $existing = $this->db->fetchOne(
                "SELECT id FROM plots WHERE colony_id = ? AND plot_number = ? AND (block = ? OR block IS NULL) AND tenant_id = ?",
                [$colonyId, $plotNum, $block, $tid]
            );

            if ($existing) {
                if ($updateExisting) {
                    $this->db->execute(
                        "UPDATE plots 
                         SET area_sqft = CASE WHEN ? > 0 THEN ? ELSE area_sqft END,
                             width_ft = CASE WHEN ? > 0 THEN ? ELSE width_ft END,
                             length_ft = CASE WHEN ? > 0 THEN ? ELSE length_ft END,
                             facing = COALESCE(?, facing),
                             corner_plot = ?, park_facing = ?, road_width_ft = ?,
                             price_per_sqft = CASE WHEN ? > 0 THEN ? ELSE price_per_sqft END,
                             total_price = CASE WHEN ? > 0 THEN ? ELSE total_price END,
                             gata_number = COALESCE(?, gata_number),
                             latitude = COALESCE(?, latitude),
                             longitude = COALESCE(?, longitude),
                             polygon_coordinates = COALESCE(?, polygon_coordinates)
                         WHERE id = ?",
                        [
                            $areaSqft, $areaSqft,
                            $widthFt, $widthFt,
                            $lengthFt, $lengthFt,
                            $facing ?: null,
                            $corner, $park, $roadWidth,
                            $rate, $rate,
                            $totalPrice, $totalPrice,
                            $gata ?: null,
                            $lat, $lng, $polyCoords,
                            (int)$existing['id']
                        ]
                    );
                    $updated++;
                } else {
                    $skipped++;
                }
            } else {
                $this->db->execute(
                    "INSERT INTO plots 
                     (tenant_id, colony_id, plot_number, block, area_sqft, width_ft, length_ft, 
                      facing, corner_plot, park_facing, road_width_ft, price_per_sqft, 
                      base_price_per_sqft, total_price, gata_number, latitude, longitude, 
                      polygon_coordinates, status, is_active)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'available', 1)",
                    [
                        $tid, $colonyId, $plotNum, $block, $areaSqft, $widthFt, $lengthFt,
                        $facing, $corner, $park, $roadWidth, $rate,
                        $rate, $totalPrice, $gata, $lat, $lng,
                        $polyCoords
                    ]
                );
                $created++;
            }
        }

        $this->setFlash(
            'success',
            "CAD/GeoJSON import complete: {$created} plots created, {$updated} updated, {$skipped} skipped."
        );

        return $this->redirect('/admin/plots/cad-exchange?colony_id=' . $colonyId);
    }
}
