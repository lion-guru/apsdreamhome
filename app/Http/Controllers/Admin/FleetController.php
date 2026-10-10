<?php

namespace App\Http\Controllers\Admin;

/**
 * Sunday Mega Site-Visit Fleet Manager
 *
 * Vehicles + drivers, trip scheduling with pickup points, and a
 * real-time conversion counter (customers visited vs on-spot tokens).
 * Trips link to existing site_visits rows for the visit side of the funnel.
 */
class FleetController extends AdminController
{
    use \App\Traits\TenantAwareTrait;

    public function __construct()
    {
        parent::__construct();
    }

    private function pdo(): \PDO
    {
        return \App\Core\Database\Database::getInstance()->getConnection();
    }

    private function tid(): int
    {
        try { return (int)$this->tenantId(); } catch (\Throwable $e) { return 1; }
    }

    /* =========================================================
     *  Dashboard — upcoming trips + conversion counters
     * ========================================================= */

    public function dashboard()
    {
        $this->requireAdmin();
        $pdo = $this->pdo();
        $tid = $this->tid();

        $stats = ['trips_today' => 0, 'trips_sunday' => 0, 'visited' => 0, 'tokens' => 0, 'vehicles' => 0];
        $trips = [];
        $perAssociate = [];
        try {
            $stats['vehicles'] = (int)$pdo->query("SELECT COUNT(*) FROM fleet_vehicles WHERE is_active = 1 AND tenant_id = {$tid}")->fetchColumn();
            $stats['trips_today'] = (int)$pdo->query("SELECT COUNT(*) FROM fleet_trips WHERE trip_date = CURDATE() AND status != 'cancelled' AND tenant_id = {$tid}")->fetchColumn();
            // Next Sunday (0=Sunday in DAYOFWEEK)
            $stats['trips_sunday'] = (int)$pdo->query("SELECT COUNT(*) FROM fleet_trips WHERE trip_date = DATE_ADD(CURDATE(), INTERVAL ((8 - DAYOFWEEK(CURDATE())) % 7) DAY) AND status != 'cancelled' AND tenant_id = {$tid}")->fetchColumn();
            $agg = $pdo->query("SELECT COALESCE(SUM(customers_visited),0) AS v, COALESCE(SUM(tokens_collected),0) AS t FROM fleet_trips WHERE status = 'completed' AND trip_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) AND tenant_id = {$tid}")->fetch(\PDO::FETCH_ASSOC);
            $stats['visited'] = (int)($agg['v'] ?? 0);
            $stats['tokens'] = (int)($agg['t'] ?? 0);

            $st = $pdo->query(
                "SELECT ft.*, fv.vehicle_name, fv.vehicle_no, c.name AS colony_name, u.name AS associate_name
                 FROM fleet_trips ft
                 LEFT JOIN fleet_vehicles fv ON fv.id = ft.vehicle_id
                 LEFT JOIN colonies c ON c.id = ft.colony_id
                 LEFT JOIN users u ON u.id = ft.associate_id
                 WHERE ft.trip_date >= CURDATE() AND ft.status != 'cancelled' AND ft.tenant_id = {$tid}
                 ORDER BY ft.trip_date ASC, ft.id ASC LIMIT 50"
            );
            $trips = $st->fetchAll(\PDO::FETCH_ASSOC) ?: [];

            $pa = $pdo->query(
                "SELECT ft.associate_id, u.name AS associate_name,
                        COUNT(*) AS trips, COALESCE(SUM(ft.customers_visited),0) AS visited,
                        COALESCE(SUM(ft.tokens_collected),0) AS tokens
                 FROM fleet_trips ft LEFT JOIN users u ON u.id = ft.associate_id
                 WHERE ft.status = 'completed' AND ft.trip_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) AND ft.tenant_id = {$tid}
                 GROUP BY ft.associate_id ORDER BY tokens DESC LIMIT 20"
            );
            $perAssociate = $pa->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) { error_log('FleetController::dashboard: ' . $e->getMessage()); }

        $stats['conversion'] = $stats['visited'] > 0 ? round($stats['tokens'] * 100 / $stats['visited'], 1) : 0;

        $this->render('admin/fleet/dashboard', [
            'page_title' => 'Site-Visit Fleet Manager',
            'stats' => $stats,
            'trips' => $trips,
            'perAssociate' => $perAssociate,
        ]);
    }

    /* =========================================================
     *  Vehicles
     * ========================================================= */

    public function vehicles()
    {
        $this->requireAdmin();
        $pdo = $this->pdo();
        $tid = $this->tid();
        $rows = [];
        try {
            $rows = $pdo->query("SELECT * FROM fleet_vehicles WHERE tenant_id = {$tid} ORDER BY is_active DESC, vehicle_name")->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) { error_log('FleetController::vehicles: ' . $e->getMessage()); }
        $this->render('admin/fleet/vehicles', [
            'page_title' => 'Fleet Vehicles & Drivers',
            'vehicles' => $rows,
        ]);
    }

    public function vehicleStore()
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        $pdo = $this->pdo();
        $tid = $this->tid();
        try {
            $types = ['innova','bolero','scorpio','ertiga','traveller','other'];
            $type = in_array($_POST['vehicle_type'] ?? '', $types, true) ? $_POST['vehicle_type'] : 'other';
            $st = $pdo->prepare(
                "INSERT INTO fleet_vehicles (tenant_id, vehicle_name, vehicle_no, vehicle_type, capacity, driver_name, driver_phone, is_active)
                 VALUES (?, ?, ?, ?, ?, ?, ?, 1)"
            );
            $st->execute([
                $tid,
                trim((string)($_POST['vehicle_name'] ?? '')),
                trim((string)($_POST['vehicle_no'] ?? '')),
                $type,
                max(1, (int)($_POST['capacity'] ?? 6)),
                trim((string)($_POST['driver_name'] ?? '')),
                trim((string)($_POST['driver_phone'] ?? '')),
            ]);
            $this->setFlash('success', 'Vehicle added to fleet.');
        } catch (\Throwable $e) {
            error_log('FleetController::vehicleStore: ' . $e->getMessage());
            $this->setFlash('error', 'Failed to add vehicle.');
        }
        return $this->redirect('/admin/site-visits/fleet/vehicles');
    }

    public function vehicleToggle($id)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        $pdo = $this->pdo();
        $tid = $this->tid();
        try {
            $pdo->prepare("UPDATE fleet_vehicles SET is_active = 1 - is_active WHERE id = ? AND tenant_id = ?")->execute([(int)$id, $tid]);
            $this->setFlash('success', 'Vehicle status updated.');
        } catch (\Throwable $e) { $this->setFlash('error', 'Update failed.'); }
        return $this->redirect('/admin/site-visits/fleet/vehicles');
    }

    /* =========================================================
     *  Trips
     * ========================================================= */

    public function trips()
    {
        $this->requireAdmin();
        $pdo = $this->pdo();
        $tid = $this->tid();
        $rows = $vehicles = $colonies = $associates = [];
        try {
            $rows = $pdo->query(
                "SELECT ft.*, fv.vehicle_name, fv.vehicle_no, c.name AS colony_name, u.name AS associate_name
                 FROM fleet_trips ft
                 LEFT JOIN fleet_vehicles fv ON fv.id = ft.vehicle_id
                 LEFT JOIN colonies c ON c.id = ft.colony_id
                 LEFT JOIN users u ON u.id = ft.associate_id
                 WHERE ft.tenant_id = {$tid}
                 ORDER BY ft.trip_date DESC, ft.id DESC LIMIT 100"
            )->fetchAll(\PDO::FETCH_ASSOC) ?: [];
            $vehicles = $pdo->query("SELECT id, vehicle_name, vehicle_no, capacity, driver_name, driver_phone FROM fleet_vehicles WHERE is_active = 1 AND tenant_id = {$tid} ORDER BY vehicle_name")->fetchAll(\PDO::FETCH_ASSOC) ?: [];
            $colonies = $pdo->query("SELECT id, name FROM colonies ORDER BY name LIMIT 200")->fetchAll(\PDO::FETCH_ASSOC) ?: [];
            [$aw, $ap] = $this->tenantWhere();
            $as = $pdo->prepare("SELECT id, name, phone FROM users WHERE role = 'associate'{$aw} ORDER BY name LIMIT 300");
            $as->execute($ap);
            $associates = $as->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) { error_log('FleetController::trips: ' . $e->getMessage()); }
        $this->render('admin/fleet/trips', [
            'page_title' => 'Fleet Trips',
            'trips' => $rows,
            'vehicles' => $vehicles,
            'colonies' => $colonies,
            'associates' => $associates,
        ]);
    }

    public function tripStore()
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        $pdo = $this->pdo();
        $tid = $this->tid();
        try {
            $vehicleId = (int)($_POST['vehicle_id'] ?? 0);
            $driverName = trim((string)($_POST['driver_name'] ?? ''));
            $driverPhone = trim((string)($_POST['driver_phone'] ?? ''));
            // Snapshot driver from vehicle when not overridden
            if ($vehicleId > 0 && ($driverName === '' || $driverPhone === '')) {
                $v = $pdo->prepare("SELECT driver_name, driver_phone FROM fleet_vehicles WHERE id = ? AND tenant_id = ?");
                $v->execute([$vehicleId, $tid]);
                if ($vd = $v->fetch(\PDO::FETCH_ASSOC)) {
                    if ($driverName === '') $driverName = (string)($vd['driver_name'] ?? '');
                    if ($driverPhone === '') $driverPhone = (string)($vd['driver_phone'] ?? '');
                }
            }
            $st = $pdo->prepare(
                "INSERT INTO fleet_trips (tenant_id, trip_date, vehicle_id, driver_name, driver_phone, pickup_point, colony_id, associate_id, customers_planned, notes, created_by)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );
            $st->execute([
                $tid,
                $_POST['trip_date'] ?? date('Y-m-d'),
                $vehicleId > 0 ? $vehicleId : null,
                $driverName, $driverPhone,
                trim((string)($_POST['pickup_point'] ?? '')),
                (int)($_POST['colony_id'] ?? 0) ?: null,
                (int)($_POST['associate_id'] ?? 0) ?: null,
                max(0, (int)($_POST['customers_planned'] ?? 0)),
                trim((string)($_POST['notes'] ?? '')),
                $_SESSION['user_id'] ?? $_SESSION['admin_id'] ?? null,
            ]);
            $this->setFlash('success', 'Trip scheduled.');
        } catch (\Throwable $e) {
            error_log('FleetController::tripStore: ' . $e->getMessage());
            $this->setFlash('error', 'Failed to schedule trip.');
        }
        return $this->redirect('/admin/site-visits/fleet/trips');
    }

    public function tripStatus($id)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        $pdo = $this->pdo();
        $tid = $this->tid();
        $allowed = ['scheduled','in_progress','completed','cancelled'];
        $status = (string)($_POST['status'] ?? '');
        if (!in_array($status, $allowed, true)) {
            $this->setFlash('error', 'Invalid status.');
            return $this->redirect('/admin/site-visits/fleet/trips');
        }
        try {
            $pdo->prepare("UPDATE fleet_trips SET status = ? WHERE id = ? AND tenant_id = ?")->execute([$status, (int)$id, $tid]);
            $this->setFlash('success', 'Trip marked ' . $status . '.');
        } catch (\Throwable $e) { $this->setFlash('error', 'Update failed.'); }
        return $this->redirect('/admin/site-visits/fleet/trips');
    }

    /**
     * Close a trip with the outcome counts that feed the conversion counter.
     */
    public function tripOutcome($id)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        $pdo = $this->pdo();
        $tid = $this->tid();
        try {
            $st = $pdo->prepare(
                "UPDATE fleet_trips SET customers_visited = ?, tokens_collected = ?, status = 'completed', notes = CONCAT(COALESCE(notes,''), '\nOutcome: ', ?)
                 WHERE id = ? AND tenant_id = ?"
            );
            $st->execute([
                max(0, (int)($_POST['customers_visited'] ?? 0)),
                max(0, (int)($_POST['tokens_collected'] ?? 0)),
                trim((string)($_POST['outcome_notes'] ?? '')),
                (int)$id, $tid,
            ]);
            $this->setFlash('success', 'Trip closed. Conversion counter updated.');
        } catch (\Throwable $e) {
            error_log('FleetController::tripOutcome: ' . $e->getMessage());
            $this->setFlash('error', 'Failed to close trip.');
        }
        return $this->redirect('/admin/site-visits/fleet');
    }

    /**
     * Attach an existing site visit to a trip (visit side of the funnel).
     */
    public function linkVisit($tripId)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        $pdo = $this->pdo();
        try {
            $st = $pdo->prepare("INSERT IGNORE INTO fleet_trip_visits (trip_id, site_visit_id) VALUES (?, ?)");
            $st->execute([(int)$tripId, (int)($_POST['site_visit_id'] ?? 0)]);
            $this->setFlash('success', 'Site visit linked to trip.');
        } catch (\Throwable $e) { $this->setFlash('error', 'Link failed.'); }
        return $this->redirect('/admin/site-visits/fleet');
    }
}
