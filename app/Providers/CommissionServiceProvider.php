<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Commission\CommissionEngine;
use App\Services\Commission\Strategies\DirectSaleStrategy;
use App\Services\Commission\Strategies\InvestmentStrategy;
use App\Services\Commission\Strategies\SalaryStrategy;
use App\Services\Commission\Strategies\PayoutStrategy;
use App\Services\Commission\CommissionEngine;
use App\Services\Commission\CommissionLedgerService;
use App\Services\Commission\CommissionPlanService;

/**
 * Commission Service Provider
 * Registers CommissionEngine and all strategies.
 */
class CommissionServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Commission Ledger Service (single writer)
        $this->app->singleton(\App\Services\Commission\CommissionLedgerService::class, function ($app) {
            return new \App\Services\Commission\CommissionLedgerService();
        });

        // Commission Plan Service
        $this->app->singleton(\App\Services\Commission\CommissionPlanService::class, function ($app) {
            return new \App\Services\Commission\CommissionPlanService();
        });

        // Commission Engine with all strategies
        $this->app->singleton(CommissionEngine::class, function ($app) {
            $engine = new CommissionEngine([
                new \App\Services\Commission\Strategies\DirectSaleStrategy(),
                new \App\Services\Commission\Strategies\InvestmentStrategy(),
                new \App\Services\Commission\Strategies\SalaryStrategy(),
                new \App\Services\Commission\Strategies\PayoutStrategy(),
            ]);
            return $engine;
        });

        // Individual strategies (for direct injection if needed)
        $this->app->singleton(\App\Services\Commission\Strategies\DirectSaleStrategy::class);
        $this->app->singleton(\App\Services\Commission\Strategies\InvestmentStrategy::class);
        $this->app->singleton(\App\Services\Commission\Strategies\SalaryStrategy::class);
        $this->app->singleton(\App\Services\Commission\Strategies\PayoutStrategy::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}