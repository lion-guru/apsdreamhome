<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Services\AIAggregatorService;

class AIAggregatorController extends AdminController
{
    public function triggerFetch()
    {
        // Run the aggregator service manually via Admin Panel
        $service = new AIAggregatorService();
        $result = $service->runAggregator(2); // Fetch 2 at a time

        header('Content-Type: application/json');
        echo json_encode($result);
        exit;
    }
}
