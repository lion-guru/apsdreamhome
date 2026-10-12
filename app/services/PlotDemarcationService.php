<?php

namespace App\Services;

use App\Core\Database\Database;

/**
 * Enterprise Township Plot Demarcation & Boundary Pillar Geotagging Service
 * Manages on-site corner pegging, 4-corner GPS coordinates (NW, NE, SW, SE),
 * boundary marker inspections, and official possession demarcation certificates.
 */
class PlotDemarcationService
{
    private \PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->getConnection();
    }

    /**
     * Record or update physical demarcation for a plot.
     */
    public function recordDemarcation(int $plotId, array $data): array
    {
        $surveyor = trim((string)($data['surveyor_name'] ?? ''));
        $phone = trim((string)($data['surveyor_phone'] ?? ''));
        $demDate = !empty($data['demarcation_date']) ? $data['demarcation_date'] : date('Y-m-d');

        if ($plotId <= 0 || $surveyor === '' || $phone === '') {
            return ['success' => false, 'message' => 'Valid plot, surveyor name, and contact phone are required.'];
        }

        // Verify plot exists
        $chkPlot = $this->pdo->prepare("SELECT id, plot_number, colony_id FROM plots WHERE id = ?");
        $chkPlot->execute([$plotId]);
        $plot = $chkPlot->fetch(\PDO::FETCH_ASSOC);
        if (!$plot) {
            return ['success' => false, 'message' => 'Plot record not found.'];
        }

        // Check if demarcation already exists
        $exist = $this->pdo->prepare("SELECT id, certificate_token FROM plot_demarcations WHERE plot_id = ?");
        $exist->execute([$plotId]);
        $existing = $exist->fetch(\PDO::FETCH_ASSOC);

        $status = in_array($data['pillar_status'] ?? '', ['pending', 'pegs_driven', 'pillars_erected', 'verified', 'disputed'], true)
            ? $data['pillar_status'] : 'pegs_driven';
        $pegHandedOver = !empty($data['possession_peg_handed_over']) ? 1 : 0;
        $notes = trim((string)($data['boundary_notes'] ?? ''));
        $photosJson = !empty($data['marker_photos_json']) ? $data['marker_photos_json'] : null;

        $nwLat = !empty($data['corner_nw_lat']) ? (float)$data['corner_nw_lat'] : null;
        $nwLng = !empty($data['corner_nw_lng']) ? (float)$data['corner_nw_lng'] : null;
        $neLat = !empty($data['corner_ne_lat']) ? (float)$data['corner_ne_lat'] : null;
        $neLng = !empty($data['corner_ne_lng']) ? (float)$data['corner_ne_lng'] : null;
        $swLat = !empty($data['corner_sw_lat']) ? (float)$data['corner_sw_lat'] : null;
        $swLng = !empty($data['corner_sw_lng']) ? (float)$data['corner_sw_lng'] : null;
        $seLat = !empty($data['corner_se_lat']) ? (float)$data['corner_se_lat'] : null;
        $seLng = !empty($data['corner_se_lng']) ? (float)$data['corner_se_lng'] : null;

        if ($existing) {
            $id = (int)$existing['id'];
            $token = $existing['certificate_token'];

            $st = $this->pdo->prepare(
                "UPDATE plot_demarcations SET
                    surveyor_name = ?, surveyor_phone = ?, demarcation_date = ?,
                    pillar_status = ?, corner_nw_lat = ?, corner_nw_lng = ?,
                    corner_ne_lat = ?, corner_ne_lng = ?, corner_sw_lat = ?,
                    corner_sw_lng = ?, corner_se_lat = ?, corner_se_lng = ?,
                    possession_peg_handed_over = ?, boundary_notes = ?,
                    marker_photos_json = COALESCE(?, marker_photos_json),
                    updated_at = NOW()
                 WHERE id = ?"
            );
            $st->execute([
                $surveyor, $phone, $demDate,
                $status, $nwLat, $nwLng,
                $neLat, $neLng, $swLat,
                $swLng, $seLat, $seLng,
                $pegHandedOver, $notes,
                $photosJson, $id
            ]);

            return ['success' => true, 'demarcation_id' => $id, 'certificate_token' => $token, 'message' => "Demarcation #{$token} updated successfully."];
        }

        // New demarcation record: generate token APS-DEM-YEAR-XXXX
        $year = date('Y');
        $random = strtoupper(substr(uniqid(), -4));
        $token = "APS-DEM-GOR-{$year}-{$random}";

        $ins = $this->pdo->prepare(
            "INSERT INTO plot_demarcations (
                tenant_id, plot_id, certificate_token, surveyor_name, surveyor_phone,
                demarcation_date, pillar_status, corner_nw_lat, corner_nw_lng,
                corner_ne_lat, corner_ne_lng, corner_sw_lat, corner_sw_lng,
                corner_se_lat, corner_se_lng, marker_photos_json, possession_peg_handed_over,
                customer_acknowledged, boundary_notes, created_at
            ) VALUES (1, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, ?, NOW())"
        );
        $ins->execute([
            $plotId, $token, $surveyor, $phone,
            $demDate, $status, $nwLat, $nwLng,
            $neLat, $neLng, $swLat, $swLng,
            $seLat, $seLng, $photosJson, $pegHandedOver,
            $notes
        ]);

        $id = (int)$this->pdo->lastInsertId();

        return [
            'success' => true,
            'demarcation_id' => $id,
            'certificate_token' => $token,
            'message' => "Plot demarcation record created! Token: {$token}"
        ];
    }

    /**
     * Update pillar status (e.g. verified by head civil engineer).
     */
    public function updateStatus(int $id, string $status, ?int $verifiedBy = null): bool
    {
        $st = $this->pdo->prepare(
            "UPDATE plot_demarcations SET pillar_status = ?, verified_by = COALESCE(?, verified_by), updated_at = NOW() WHERE id = ?"
        );
        return $st->execute([$status, $verifiedBy, $id]);
    }

    /**
     * Customer marks receipt / possession acknowledgement of boundary markers.
     */
    public function customerAcknowledge(int $id): bool
    {
        $st = $this->pdo->prepare(
            "UPDATE plot_demarcations SET customer_acknowledged = 1, updated_at = NOW() WHERE id = ?"
        );
        return $st->execute([$id]);
    }

    /**
     * Get detailed demarcation info for a plot.
     */
    public function getPlotDemarcation(int $plotId): ?array
    {
        $st = $this->pdo->prepare(
            "SELECT pd.*, p.plot_number, p.area_sqft, p.status AS plot_status,
                    c.name AS colony_name, c.location AS colony_location
             FROM plot_demarcations pd
             JOIN plots p ON p.id = pd.plot_id
             JOIN colonies c ON c.id = p.colony_id
             WHERE pd.plot_id = ?"
        );
        $st->execute([$plotId]);
        return $st->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Get demarcation by ID.
     */
    public function getDemarcationById(int $id): ?array
    {
        $st = $this->pdo->prepare(
            "SELECT pd.*, p.plot_number, p.area_sqft, p.status AS plot_status,
                    c.name AS colony_name, c.location AS colony_location
             FROM plot_demarcations pd
             JOIN plots p ON p.id = pd.plot_id
             JOIN colonies c ON c.id = p.colony_id
             WHERE pd.id = ?"
        );
        $st->execute([$id]);
        return $st->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * List demarcations by colony and status filter.
     */
    public function getDemarcations(?int $colonyId = null, ?string $status = null): array
    {
        $sql = "SELECT pd.*, p.plot_number, p.area_sqft, c.name AS colony_name
                FROM plot_demarcations pd
                JOIN plots p ON p.id = pd.plot_id
                JOIN colonies c ON c.id = p.colony_id
                WHERE 1=1";
        $params = [];

        if ($colonyId && $colonyId > 0) {
            $sql .= " AND p.colony_id = ?";
            $params[] = $colonyId;
        }

        if ($status && $status !== 'all') {
            $sql .= " AND pd.pillar_status = ?";
            $params[] = $status;
        }

        $sql .= " ORDER BY pd.demarcation_date DESC, pd.id DESC";

        $st = $this->pdo->prepare($sql);
        $st->execute($params);
        return $st->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Summary stats for demarcation radar.
     */
    public function getSummaryStats(): array
    {
        $st = $this->pdo->query(
            "SELECT COUNT(*) AS total_demarcated,
                    SUM(CASE WHEN pillar_status = 'pillars_erected' THEN 1 ELSE 0 END) AS erected,
                    SUM(CASE WHEN pillar_status = 'verified' THEN 1 ELSE 0 END) AS verified,
                    SUM(CASE WHEN possession_peg_handed_over = 1 THEN 1 ELSE 0 END) AS pegs_handed
             FROM plot_demarcations"
        )->fetch(\PDO::FETCH_ASSOC);

        return [
            'total_demarcated' => (int)($st['total_demarcated'] ?? 0),
            'pillars_erected' => (int)($st['erected'] ?? 0),
            'verified' => (int)($st['verified'] ?? 0),
            'pegs_handed' => (int)($st['pegs_handed'] ?? 0),
        ];
    }
}
