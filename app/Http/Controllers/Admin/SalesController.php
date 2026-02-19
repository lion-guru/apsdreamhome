<?php

namespace App\Http\Controllers\Admin;

use App\Models\Sale;
use App\Models\Payout;
use App\Models\Associate;
use App\Core\Database;
use Exception;

class SalesController extends AdminController
{
    public function __construct()
    {
        parent::__construct();

        // Register middlewares
        // AdminController handles role check
        $this->middleware('csrf', ['only' => ['store', 'update', 'destroy']]);
    }

    /**
     * Show the sales entry form
     */
    public function create()
    {
        try {
            // Fetch associates with their names from users table
            $associates = $this->db->fetchAll("SELECT a.associate_id as id, u.name 
                                             FROM associates a 
                                             JOIN users u ON a.user_id = u.id 
                                             WHERE a.status='active' 
                                             ORDER BY u.name ASC");

            return $this->render('admin/sales/create', [
                'associates' => $associates,
                'page_title' => $this->mlSupport->translate('Sales Entry') . ' - ' . $this->getConfig('app_name')
            ]);
        } catch (Exception $e) {
            $this->setFlash('error', $this->mlSupport->translate('Error fetching associates: ') . $e->getMessage());
            return $this->back();
        }
    }

    /**
     * Store a new sale and calculate payouts
     */
    public function store()
    {
        if ($this->request->method() !== 'POST') {
            $this->setFlash('error', $this->mlSupport->translate('Invalid request method.'));
            return $this->back();
        }

        if (!$this->validateCsrfToken()) {
            $this->setFlash('error', $this->mlSupport->translate('Security validation failed. Please try again.'));
            return $this->back();
        }

        $request = $this->request;

        // Validation
        $associate_id = intval($request->post('associate_id'));
        $amount = (float) $request->post('amount');
        $date = $request->post('date');
        $booking_id = htmlspecialchars(\trim($request->post('booking_id', '')), ENT_QUOTES, 'UTF-8');

        if ($associate_id <= 0 || $amount <= 0 || !$date) {
            $this->setFlash('error', $this->mlSupport->translate('All fields are required and must be valid.'));
            return $this->back();
        }

        try {
            $this->db->beginTransaction();

            // Insert sale using Model
            $sale_id = $this->model('Sale')->createSale([
                'associate_id' => $associate_id,
                'amount' => $amount,
                'date' => $date,
                'booking_id' => $booking_id
            ]);

            // Traverse tree and assign payouts (MLM Logic)
            $curr_id = $associate_id;
            $prev_percent = 0;

            while ($curr_id) {
                // Get associate info
                $associateData = $this->db->fetch(
                    "SELECT associate_id, commission_percent, parent_id FROM associates WHERE associate_id = ?",
                    [$curr_id]
                );

                if ($associateData) {
                    $percent = (float) $associateData['commission_percent'];
                    $diff_percent = $percent - $prev_percent;

                    if ($diff_percent > 0) {
                        $payout_amount = $amount * $diff_percent / 100.0;
                        $period = \date('Y-m', \strtotime($date));

                        // Insert payout using Model
                        $this->model('Payout')->createPayout([
                            'associate_id' => $associateData['associate_id'],
                            'sale_id' => $sale_id,
                            'payout_amount' => $payout_amount,
                            'payout_percent' => $diff_percent,
                            'period' => $period,
                            'status' => 'pending'
                        ]);
                    }

                    $prev_percent = $percent;
                    $curr_id = intval($associateData['parent_id']);
                } else {
                    break;
                }
            }

            // Log the action using BaseController logActivity
            $this->logActivity('SALE_RECORDED', "Recorded sale ID: $sale_id for associate ID: $associate_id, amount: " . htmlspecialchars($amount, ENT_QUOTES, 'UTF-8'));

            $this->db->commit();
            // Invalidate dashboard cache
            if (function_exists('getPerformanceManager')) {
                getPerformanceManager()->clearCache('query_');
            }

            $this->setFlash('success', $this->mlSupport->translate('Sale and payouts recorded successfully!'));
            $this->redirect('/admin/sales/create');
            return;
        } catch (Exception $e) {
            try {
                $this->db->rollBack();
            } catch (Exception $re) {
                // Ignore rollback errors if already rolled back or connection lost
            }
            \error_log("Sale recording error: " . $e->getMessage());
            $this->setFlash('error', $this->mlSupport->translate('Error recording sale: ') . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
            return $this->back();
        }
    }
}
