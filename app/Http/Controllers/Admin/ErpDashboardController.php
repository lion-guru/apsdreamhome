<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;

class ErpDashboardController extends AdminController
{
    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = \App\Core\Database\Database::getInstance();
    }

    public function inventory()
    {
        $plots = $this->db->fetchAll("
            SELECT p.id, p.plot_number, p.area_sqft, p.total_price, p.status, p.block, p.sector,
                   c.name as colony_name,
                   b.id as booking_id, b.booking_number, b.status as booking_status,
                   u.name as customer_name, u.phone as customer_phone,
                   (SELECT SUM(amount) FROM booking_payments WHERE booking_id = b.id) as amount_paid,
                   p.booking_date, p.customer_id
            FROM plots p
            LEFT JOIN colonies c ON p.colony_id = c.id
            LEFT JOIN bookings b ON b.property_id = p.id
            LEFT JOIN users u ON b.customer_id = u.id
            ORDER BY FIELD(p.status,'available','booked','sold','hold','reserved','under_construction'), ISNULL(b.id), c.name, p.plot_number
        ");

        $stats = ['available' => 0, 'reserved' => 0, 'booked' => 0, 'sold' => 0, 'hold' => 0, 'under_construction' => 0];
        foreach ($plots as $p) {
            $s = $p['status'] ?? 'available';
            $stats[$s] = ($stats[$s] ?? 0) + 1;
        }
        $stats['total'] = count($plots);

        $this->render('admin/erp/inventory', [
            'page_title' => 'Plot Inventory',
            'plots' => $plots,
            'stats' => $stats,
            'currentPage' => 'erp-inventory'
        ]);
    }

    public function plotProfit()
    {
        $plots = $this->db->fetchAll("
            SELECT p.id, p.plot_number, p.area_sqft, p.total_price as sale_price, p.status,
                   c.name as colony_name,
                   COALESCE(lp.amount, 0) as land_cost,
                   COALESCE((SELECT SUM(amount) FROM colony_development_costs WHERE colony_id = p.colony_id), 0) as dev_cost
            FROM plots p
            LEFT JOIN colonies c ON p.colony_id = c.id
            LEFT JOIN land_purchases lp ON lp.land_holding_id = p.land_holding_id
            ORDER BY c.name, p.plot_number
        ");

        $totals = ['land_cost' => 0, 'dev_cost' => 0, 'sale_price' => 0, 'profit' => 0];
        foreach ($plots as &$p) {
            $p['total_cost'] = $p['land_cost'] + $p['dev_cost'];
            $p['profit'] = $p['sale_price'] - $p['total_cost'];
            $p['margin_pct'] = $p['total_cost'] > 0 ? round(($p['profit'] / $p['total_cost']) * 100, 1) : 0;
            $totals['land_cost'] += $p['land_cost'];
            $totals['dev_cost'] += $p['dev_cost'];
            $totals['sale_price'] += $p['sale_price'];
            $totals['profit'] += $p['profit'];
        }
        $totalCost = $totals['land_cost'] + $totals['dev_cost'];
        $totals['margin_pct'] = $totalCost > 0 ? round(($totals['profit'] / $totalCost) * 100, 1) : 0;

        $this->render('admin/erp/plot_profit', [
            'page_title' => 'Plot P&L Report',
            'plots' => $plots,
            'totals' => $totals,
            'currentPage' => 'erp-profit'
        ]);
    }

    public function landMapping()
    {
        $data = $this->db->fetchAll("
            SELECT f.name as farmer_name, f.phone as farmer_phone,
                   flh.khasra_number, flh.land_area, flh.land_area_unit,
                   lp.amount as purchase_amount, lp.purchase_date, lp.registry_no,
                   p.plot_number, p.plot_code, p.area_sqft,
                   p.total_price as sale_price,
                   p.status as plot_status, p.block, p.sector,
                   c.name as colony_name
            FROM farmers f
            JOIN farmer_land_holdings flh ON flh.farmer_id = f.id
            LEFT JOIN land_purchases lp ON lp.land_holding_id = flh.id
            LEFT JOIN plots p ON p.land_holding_id = flh.id
            LEFT JOIN colonies c ON p.colony_id = c.id
            ORDER BY f.name, flh.khasra_number
        ");

        $this->render('admin/erp/land_mapping', [
            'page_title' => 'Farmer → Land → Plot Mapping',
            'data' => $data,
            'currentPage' => 'erp-land'
        ]);
    }
}
