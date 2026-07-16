<?php

namespace Tests\Unit\Services;

use PHPUnit\Framework\TestCase;
use App\Services\Accounting\MoneyWorkflowService;

class MoneyWorkflowServiceTest extends TestCase
{
    private MoneyWorkflowService $service;

    protected function setUp(): void
    {
        $this->service = new MoneyWorkflowService();
    }

    public function testInstantiation(): void
    {
        $this->assertInstanceOf(MoneyWorkflowService::class, $this->service);
    }

    public function testHasKeyMethods(): void
    {
        $methods = [
            'createBankAccount',
            'listBankAccounts',
            'getBankBalance',
            'recordCashTransaction',
            'topupPettyCash',
            'recordPettyExpense',
            'getPettyCashBalance',
            'issueCheque',
            'markChequeCleared',
            'markChequeBounced',
            'startBankReconciliation',
            'reconcileItem',
            'recordTDS',
            'recordGST',
            'generateDemandLetter',
            'forecastCashFlow',
            'approveExpense',
            'createVendor',
            'autoDetectTdsSection',
            'getTdsRateForSection',
            'verifyVendorKyc',
            'rejectVendorKyc',
            'getVendor',
            'listVendors',
            'payVendor',
            'issueTDSCertificate',
            'postJournalEntry',
            'getLedger',
        ];

        foreach ($methods as $method) {
            $this->assertTrue(
                method_exists($this->service, $method),
                "Method $method should exist on MoneyWorkflowService"
            );
        }
    }

    public function testHasDashboardMethod(): void
    {
        $this->assertTrue(method_exists($this->service, 'getDashboardStats'));
    }
}