<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Services\CurrencyService;
use Exception;

class CurrencyController extends AdminController
{
    protected $currencyService;

    public function __construct()
    {
        parent::__construct();
        $this->currencyService = new CurrencyService();
    }

    public function index()
    {
        $this->requireAdmin();
        try {
            $rates = $this->currencyService->getRates();
            $stats = $this->currencyService->getStats();
            $currencies = $this->currencyService->getSupportedCurrencies();
            return $this->render('admin/multi_currency/index', [
                'page_title' => 'Multi-Currency - APS Dream Home',
                'active_page' => 'multi_currency',
                'rates' => $rates,
                'stats' => $stats,
                'currencies' => $currencies,
                'today' => date('Y-m-d'),
            ]);
        } catch (Exception $e) {
            $this->setFlash('error', 'Failed: ' . $e->getMessage());
            return $this->redirect('/admin/accounting');
        }
    }

    public function create()
    {
        $this->requireAdmin();
        return $this->render('admin/multi_currency/create', [
            'page_title' => 'Add Exchange Rate',
            'active_page' => 'multi_currency',
            'currencies' => $this->currencyService->getSupportedCurrencies(),
        ]);
    }

    public function store()
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonError('Invalid request', 400);
        try {
            $required = ['base_currency','target_currency','rate'];
            foreach ($required as $f) {
                if (empty($_POST[$f])) return $this->jsonError(ucfirst($f).' is required', 400);
            }
            if ((float)$_POST['rate'] <= 0) return $this->jsonError('Rate must be > 0', 400);
            $existing = $this->db->fetchOne("SELECT id FROM exchange_rates WHERE base_currency = ? AND target_currency = ?", [strtoupper($_POST['base_currency']), strtoupper($_POST['target_currency'])]);
            if ($existing) return $this->jsonError('Rate pair already exists', 400);
            $id = $this->currencyService->addRate($_POST);
            return $this->jsonResponse(['success'=>true,'message'=>'Rate added','rate_id'=>$id,'redirect'=>BASE_URL.'/admin/multi-currency']);
        } catch (Exception $e) {
            return $this->jsonError('Failed: '.$e->getMessage(), 500);
        }
    }

    public function edit($id)
    {
        $this->requireAdmin();
        $rate = $this->currencyService->getRate((int)$id);
        if (!$rate) { $this->setFlash('error','Rate not found'); return $this->redirect('/admin/multi-currency'); }
        return $this->render('admin/multi_currency/edit', [
            'page_title' => 'Edit Rate: '.$rate['base_currency'].'/'.$rate['target_currency'],
            'active_page' => 'multi_currency',
            'rate' => $rate,
            'currencies' => $this->currencyService->getSupportedCurrencies(),
        ]);
    }

    public function update($id)
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonError('Invalid request', 400);
        try {
            if (isset($_POST['rate']) && (float)$_POST['rate'] <= 0) return $this->jsonError('Rate must be > 0', 400);
            $this->currencyService->updateRate((int)$id, $_POST);
            return $this->jsonResponse(['success'=>true,'message'=>'Updated','redirect'=>BASE_URL.'/admin/multi-currency']);
        } catch (Exception $e) {
            return $this->jsonError('Failed: '.$e->getMessage(), 500);
        }
    }

    public function delete($id)
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonError('Invalid request', 400);
        try {
            $this->currencyService->deleteRate((int)$id);
            return $this->jsonResponse(['success'=>true,'message'=>'Deleted']);
        } catch (Exception $e) {
            return $this->jsonError($e->getMessage(), 500);
        }
    }

    public function refresh()
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonError('Invalid request', 400);
        try {
            $result = $this->currencyService->refreshRates();
            return $this->jsonResponse(['success'=>true,'message'=>"Updated {$result['updated']} rates",'details'=>$result]);
        } catch (Exception $e) {
            return $this->jsonError($e->getMessage(), 500);
        }
    }

    public function convert()
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonError('Invalid request', 400);
        try {
            $amount = (float)($_POST['amount'] ?? 0);
            $from = $_POST['from_currency'] ?? 'INR';
            $to = $_POST['to_currency'] ?? 'USD';
            $result = $this->currencyService->convert($amount, $from, $to);
            return $this->jsonResponse($result);
        } catch (Exception $e) {
            return $this->jsonError($e->getMessage(), 500);
        }
    }
}