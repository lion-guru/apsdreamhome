<?php

namespace App\Services;

use App\Core\Database\Database;

/**
 * Enterprise PM Surya Ghar Muft Bijli Yojana & Green Solar Township Feasibility Engine
 * Calculates rooftop solar capacities, central/UP-state subsidies, net customer payable,
 * DISCOM net-metering sanctions (PVVNL), and installation vendor assignments.
 */
class SolarSubsidyService
{
    private \PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->getConnection();
    }

    /**
     * Compute solar system feasibility, subsidies, and ROI metrics.
     *
     * @param float $plotSqft
     * @param float $monthlyBill
     * @param float|null $customKw
     * @return array
     */
    public function calculateFeasibility(float $plotSqft, float $monthlyBill, ?float $customKw = null): array
    {
        // 1. Determine recommended capacity
        if ($customKw !== null && $customKw > 0) {
            $capacityKw = round($customKw, 1);
        } else {
            if ($monthlyBill <= 1500) {
                $capacityKw = 1.0;
            } elseif ($monthlyBill <= 3000) {
                $capacityKw = 2.0;
            } elseif ($monthlyBill <= 5500) {
                $capacityKw = 3.0;
            } else {
                $capacityKw = 5.0;
            }
        }

        // Required rooftop area: ~100 sq.ft per kW
        $reqRoofArea = $capacityKw * 100.0;
        // Estimated available roof (estimated ~45% of plot footprint for villa/house)
        $estRoofArea = max(200.0, $plotSqft * 0.45);
        $areaFeasible = $estRoofArea >= $reqRoofArea;

        // Estimated monthly generation (approx 120 units per kW per month in UP solar insolation)
        $monthlyUnits = (int)round($capacityKw * 120);
        $annualUnits = $monthlyUnits * 12;

        // Standard Benchmark Costs (approx ₹60,000 per kW on-grid setup)
        $costPerKw = 60000.0;
        $projectCost = round($capacityKw * $costPerKw, 2);

        // PM Surya Ghar Muft Bijli Yojana Central Subsidy:
        // 1 kW: ₹33,000
        // 2 kW: ₹60,000
        // 3 kW & above: ₹78,000 max cap
        if ($capacityKw <= 1.0) {
            $centralSubsidy = 33000.0 * $capacityKw;
        } elseif ($capacityKw <= 2.0) {
            $centralSubsidy = 33000.0 + (($capacityKw - 1.0) * 27000.0);
        } else {
            $centralSubsidy = 78000.0;
        }

        // UP State Government Solar Subsidy:
        // ₹15,000 for 1 kW, max ₹30,000 for 2 kW and above
        if ($capacityKw <= 1.0) {
            $stateSubsidy = 15000.0 * $capacityKw;
        } else {
            $stateSubsidy = 30000.0;
        }

        $totalSubsidy = round($centralSubsidy + $stateSubsidy, 2);
        $netPayable = max(0.0, round($projectCost - $totalSubsidy, 2));

        // Average UP tariff ~₹7.00/unit
        $tariffRate = 7.00;
        $monthlySavings = round($monthlyUnits * $tariffRate, 2);
        $annualSavings = round($monthlySavings * 12, 2);

        $paybackYears = $annualSavings > 0 ? round($netPayable / $annualSavings, 1) : 0;
        $twentyFiveYearSavings = round(($annualSavings * 25) - $netPayable, 2);

        return [
            'capacity_kw' => $capacityKw,
            'required_rooftop_sqft' => $reqRoofArea,
            'estimated_available_rooftop_sqft' => round($estRoofArea, 1),
            'rooftop_area_feasible' => $areaFeasible,
            'monthly_generation_units' => $monthlyUnits,
            'annual_generation_units' => $annualUnits,
            'project_cost' => $projectCost,
            'central_subsidy' => $centralSubsidy,
            'state_subsidy' => $stateSubsidy,
            'total_subsidy' => $totalSubsidy,
            'net_customer_payable' => $netPayable,
            'estimated_monthly_savings' => $monthlySavings,
            'estimated_annual_savings' => $annualSavings,
            'payback_period_years' => $paybackYears,
            'twenty_five_year_net_savings' => $twentyFiveYearSavings,
            'discom_default' => 'Purvanchal Vidyut Vitaran Nigam Ltd (PVVNL)',
        ];
    }

    /**
     * Submit a customer green solar subsidy & net-metering application.
     */
    public function submitSolarRequest(int $customerId, ?int $bookingId, array $data): array
    {
        if ($customerId <= 0) {
            return ['success' => false, 'message' => 'Invalid customer ID.'];
        }

        $capacityKw = !empty($data['system_capacity_kw']) ? (float)$data['system_capacity_kw'] : 3.0;
        $monthlyBill = !empty($data['monthly_bill']) ? (float)$data['monthly_bill'] : 3500.0;
        $plotSqft = !empty($data['plot_sqft']) ? (float)$data['plot_sqft'] : 1000.0;

        $calc = $this->calculateFeasibility($plotSqft, $monthlyBill, $capacityKw);

        $consumerNo = trim((string)($data['consumer_number'] ?? ''));
        $discom = !empty($data['discom_provider']) ? trim((string)$data['discom_provider']) : $calc['discom_default'];
        $notes = trim((string)($data['notes'] ?? ''));

        $year = date('Y');
        $random = strtoupper(substr(uniqid(), -4));
        $token = "APS-SOLAR-GOR-{$year}-{$random}";

        $stmt = $this->pdo->prepare(
            "INSERT INTO customer_solar_requests (
                tenant_id, booking_id, customer_id, request_token, system_capacity_kw,
                estimated_monthly_units, estimated_project_cost, central_subsidy_amount,
                state_subsidy_amount, net_customer_payable, estimated_annual_savings,
                discom_provider, consumer_number, status, notes, created_at
            ) VALUES (
                1, ?, ?, ?, ?,
                ?, ?, ?,
                ?, ?, ?,
                ?, ?, 'inquiry', ?, NOW()
            )"
        );

        $stmt->execute([
            $bookingId ?: null,
            $customerId,
            $token,
            $calc['capacity_kw'],
            $calc['monthly_generation_units'],
            $calc['project_cost'],
            $calc['central_subsidy'],
            $calc['state_subsidy'],
            $calc['net_customer_payable'],
            $calc['estimated_annual_savings'],
            $discom,
            $consumerNo ?: null,
            $notes ?: null
        ]);

        $id = (int)$this->pdo->lastInsertId();

        return [
            'success' => true,
            'request_id' => $id,
            'request_token' => $token,
            'calculations' => $calc,
            'message' => "Solar application #{$token} registered successfully."
        ];
    }

    /**
     * Update solar request workflow status, DISCOM sanction, and installer details.
     */
    public function updateRequestStatus(int $id, string $status, array $extra = []): array
    {
        $allowed = ['inquiry', 'site_feasibility', 'subsidy_approved', 'discom_sanctioned', 'installed', 'rejected'];
        if (!in_array($status, $allowed, true)) {
            return ['success' => false, 'message' => "Invalid status: {$status}"];
        }

        $chk = $this->pdo->prepare("SELECT id, request_token FROM customer_solar_requests WHERE id = ?");
        $chk->execute([$id]);
        $row = $chk->fetch(\PDO::FETCH_ASSOC);
        if (!$row) {
            return ['success' => false, 'message' => 'Solar request not found.'];
        }

        $partner = !empty($extra['installer_partner']) ? trim((string)$extra['installer_partner']) : null;
        $contact = !empty($extra['installer_contact']) ? trim((string)$extra['installer_contact']) : null;
        $sanctionRef = !empty($extra['sanction_ref']) ? trim((string)$extra['sanction_ref']) : null;
        $installedAt = ($status === 'installed') ? (!empty($extra['installed_at']) ? $extra['installed_at'] : date('Y-m-d H:i:s')) : null;
        $notes = !empty($extra['notes']) ? trim((string)$extra['notes']) : null;

        $up = $this->pdo->prepare(
            "UPDATE customer_solar_requests SET
                status = ?,
                installer_partner = COALESCE(?, installer_partner),
                installer_contact = COALESCE(?, installer_contact),
                sanction_ref = COALESCE(?, sanction_ref),
                installed_at = COALESCE(?, installed_at),
                notes = COALESCE(?, notes),
                updated_at = NOW()
             WHERE id = ?"
        );
        $up->execute([
            $status,
            $partner,
            $contact,
            $sanctionRef,
            $installedAt,
            $notes,
            $id
        ]);

        return [
            'success' => true,
            'request_id' => $id,
            'status' => $status,
            'message' => "Solar request #{$row['request_token']} updated to '{$status}'."
        ];
    }

    /**
     * Get solar requests for a customer.
     */
    public function getCustomerRequests(int $customerId): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT sr.*,
                    b.booking_number,
                    p.plot_number,
                    c.name AS colony_name
             FROM customer_solar_requests sr
             LEFT JOIN plot_bookings b ON sr.booking_id = b.id
             LEFT JOIN plots p ON b.plot_id = p.id
             LEFT JOIN colonies c ON p.colony_id = c.id
             WHERE sr.customer_id = ?
             ORDER BY sr.id DESC"
        );
        $stmt->execute([$customerId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Get single request detail.
     */
    public function getRequestById(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            "SELECT sr.*,
                    u.name AS customer_name,
                    u.email AS customer_email,
                    u.phone AS customer_phone,
                    b.booking_number,
                    p.plot_number,
                    c.name AS colony_name
             FROM customer_solar_requests sr
             LEFT JOIN users u ON sr.customer_id = u.id
             LEFT JOIN plot_bookings b ON sr.booking_id = b.id
             LEFT JOIN plots p ON b.plot_id = p.id
             LEFT JOIN colonies c ON p.colony_id = c.id
             WHERE sr.id = ?"
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Get all solar requests with optional status filter.
     */
    public function getAllRequests(?string $status = null): array
    {
        $sql = "SELECT sr.*,
                       u.name AS customer_name,
                       u.phone AS customer_phone,
                       b.booking_number,
                       p.plot_number,
                       c.name AS colony_name
                FROM customer_solar_requests sr
                LEFT JOIN users u ON sr.customer_id = u.id
                LEFT JOIN plot_bookings b ON sr.booking_id = b.id
                LEFT JOIN plots p ON b.plot_id = p.id
                LEFT JOIN colonies c ON p.colony_id = c.id";

        $params = [];
        if (!empty($status)) {
            $sql .= " WHERE sr.status = ?";
            $params[] = $status;
        }
        $sql .= " ORDER BY sr.id DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }
}
