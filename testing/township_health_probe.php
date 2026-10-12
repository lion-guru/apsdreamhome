<?php
/**
 * Township Operations Health Probe (read-only, zero DB writes)
 *
 * Verifies every township/infrastructure module from Sessions 205-216 is
 * structurally intact: DB table exists, service class loads, controller
 * class loads, route registered in routes/web.php, admin view file exists.
 *
 * Usage: php testing/township_health_probe.php
 * Exit 0 = all green, 1 = failures (listed).
 */
define('APS_ROOT', dirname(__DIR__));
require_once APS_ROOT . '/config/bootstrap.php';

$pdo = \App\Core\Database\Database::getInstance()->getConnection();
$webRoutes = file_get_contents(APS_ROOT . '/routes/web.php');

$modules = [
    'water'        => ['tables' => ['township_water_logs'], 'service' => 'App\Services\TownshipWaterService', 'controller' => 'App\Http\Controllers\Admin\TownshipWaterController', 'routes' => ['/admin/township-water-supply'], 'views' => ['app/views/admin/water_supply/index.php']],
    'security'     => ['tables' => ['township_security_rosters', 'township_security_incidents'], 'service' => 'App\Services\TownshipSecurityService', 'controller' => 'App\Http\Controllers\Admin\TownshipSecurityController', 'routes' => ['/admin/township-security'], 'views' => ['app/views/admin/township_security/index.php']],
    'genset'       => ['tables' => ['township_genset_logs'], 'service' => 'App\Services\TownshipGensetService', 'controller' => 'App\Http\Controllers\Admin\TownshipGensetController', 'routes' => ['/admin/township-genset'], 'views' => ['app/views/admin/genset/index.php']],
    'horticulture' => ['tables' => ['township_horticulture_logs'], 'service' => 'App\Services\TownshipHorticultureService', 'controller' => 'App\Http\Controllers\Admin\TownshipHorticultureController', 'routes' => ['/admin/township-horticulture'], 'views' => ['app/views/admin/horticulture/index.php']],
    'cctv'         => ['tables' => ['township_cctv_logs'], 'service' => 'App\Services\TownshipCctvService', 'controller' => 'App\Http\Controllers\Admin\TownshipCctvController', 'routes' => ['/admin/township-cctv'], 'views' => ['app/views/admin/cctv/index.php']],
    'waste'        => ['tables' => ['township_waste_logs'], 'service' => 'App\Services\TownshipWasteService', 'controller' => 'App\Http\Controllers\Admin\TownshipWasteController', 'routes' => ['/admin/township-waste'], 'views' => ['app/views/admin/waste/index.php']],
    'lighting'     => ['tables' => ['township_lighting_logs'], 'service' => 'App\Services\TownshipLightingService', 'controller' => 'App\Http\Controllers\Admin\TownshipLightingController', 'routes' => ['/admin/township-lighting'], 'views' => ['app/views/admin/lighting/index.php']],
    'rwh'          => ['tables' => ['township_rwh_logs'], 'service' => 'App\Services\TownshipRwhService', 'controller' => 'App\Http\Controllers\Admin\TownshipRwhController', 'routes' => ['/admin/township-rwh'], 'views' => ['app/views/admin/rwh/index.php']],
    'fire_safety'  => ['tables' => ['township_fire_safety_logs'], 'service' => 'App\Services\TownshipFireSafetyService', 'controller' => 'App\Http\Controllers\Admin\TownshipFireSafetyController', 'routes' => ['/admin/township-fire-safety'], 'views' => ['app/views/admin/fire_safety/index.php']],
    'stp'          => ['tables' => ['township_stp_logs'], 'service' => 'App\Services\TownshipStpService', 'controller' => 'App\Http\Controllers\Admin\TownshipStpController', 'routes' => ['/admin/township-stp'], 'views' => ['app/views/admin/stp/index.php']],
    'grievances'   => ['tables' => ['township_resident_grievances'], 'service' => 'App\Services\TownshipGrievanceService', 'controller' => 'App\Http\Controllers\Admin\TownshipGrievanceController', 'routes' => ['/admin/township-grievances', '/customer/township-grievances'], 'views' => ['app/views/admin/township_grievance/index.php', 'app/views/customer/grievances/index.php']],
    'work_orders'  => ['tables' => ['construction_work_orders', 'contractor_bills'], 'service' => 'App\Services\WorkOrderService', 'controller' => 'App\Http\Controllers\Admin\WorkOrderController', 'routes' => ['/admin/work-orders'], 'views' => ['app/views/admin/construction/work_orders.php']],
    'registry'     => ['tables' => ['registry_appointments'], 'service' => 'App\Services\RegistryAppointmentService', 'controller' => 'App\Http\Controllers\Admin\RegistryAppointmentController', 'routes' => ['/admin/registry-appointments', '/customer/registry-appointments'], 'views' => ['app/views/admin/legal/registry_appointments.php']],
    'demarcation'  => ['tables' => ['plot_demarcations'], 'service' => 'App\Services\PlotDemarcationService', 'controller' => 'App\Http\Controllers\Admin\PlotDemarcationController', 'routes' => ['/admin/plots/demarcation'], 'views' => ['app/views/admin/plots/demarcation.php']],
    'solar'        => ['tables' => ['customer_solar_requests'], 'service' => 'App\Services\SolarSubsidyService', 'controller' => 'App\Http\Controllers\Admin\SolarDeskController', 'routes' => ['/admin/green-energy/solar', '/customer/solar-calculator'], 'views' => ['app/views/admin/green_energy/solar_desk.php', 'app/views/customer/solar/calculator.php']],
    'construction' => ['tables' => ['construction_approvals'], 'service' => 'App\Services\ConstructionApprovalService', 'controller' => 'App\Http\Controllers\Admin\ConstructionApprovalController', 'routes' => ['/admin/construction-approvals', '/customer/construction-approvals'], 'views' => ['app/views/admin/construction/approvals_index.php']],
    'leases'       => ['tables' => ['commercial_leases', 'lease_rent_invoices'], 'service' => 'App\Services\CommercialLeaseService', 'controller' => 'App\Http\Controllers\Admin\CommercialLeaseController', 'routes' => ['/admin/commercial-leases'], 'views' => ['app/views/admin/commercial_leases/index.php']],
    'acquisition'  => ['tables' => ['farmer_land_acquisitions', 'farmer_compensation_milestones'], 'service' => 'App\Services\FarmerAcquisitionService', 'controller' => 'App\Http\Controllers\Admin\FarmerAcquisitionController', 'routes' => ['/admin/farmer-acquisitions'], 'views' => ['app/views/admin/land_acquisition/index.php']],
    'maintenance'  => ['tables' => ['maintenance_dues'], 'service' => 'App\Services\MaintenanceBillingService', 'controller' => 'App\Http\Controllers\Admin\MaintenanceDuesController', 'routes' => ['/customer/maintenance-dues'], 'views' => ['app/views/customer/maintenance_dues.php']],
    'cash_drawer'  => ['tables' => ['cash_drawer_closings'], 'service' => null, 'controller' => 'App\Http\Controllers\Admin\CashDrawerController', 'routes' => ['/admin/finance/cash-drawer'], 'views' => ['app/views/admin/finance/cash_drawer.php']],
    'transfers'    => ['tables' => ['plot_transfers'], 'service' => 'App\Services\PlotTransferService', 'controller' => 'App\Http\Controllers\Admin\PlotTransferController', 'routes' => ['/admin/plot-transfers', '/customer/plot-transfers'], 'views' => ['app/views/admin/transfers/index.php']],
];

$pass = 0; $fail = 0; $failures = [];
foreach ($modules as $name => $m) {
    $ok = true;
    foreach ($m['tables'] as $t) {
        try {
            $exists = $pdo->query("SHOW TABLES LIKE '$t'")->fetch();
            if (!$exists) { $ok = false; $failures[] = "$name: missing table $t"; }
        } catch (\Throwable $e) { $ok = false; $failures[] = "$name: table check error $t"; }
    }
    if (!empty($m['service']) && !class_exists($m['service'])) { $ok = false; $failures[] = "$name: missing class {$m['service']}"; }
    if (!empty($m['controller']) && !class_exists($m['controller'])) { $ok = false; $failures[] = "$name: missing class {$m['controller']}"; }
    foreach ($m['routes'] as $r) {
        if (strpos($webRoutes, "'" . $r . "'") === false) { $ok = false; $failures[] = "$name: route not registered $r"; }
    }
    foreach ($m['views'] as $v) {
        if (!file_exists(APS_ROOT . '/' . $v)) { $ok = false; $failures[] = "$name: missing view $v"; }
    }
    if ($ok) { $pass++; echo "  OK $name\n"; }
    else { $fail++; echo "  FAIL $name\n"; }
}

echo "Township health: $pass passed, $fail failed (" . count($modules) . " modules)\n";
if ($failures) foreach ($failures as $f) echo "  - $f\n";
exit($fail > 0 ? 1 : 0);
