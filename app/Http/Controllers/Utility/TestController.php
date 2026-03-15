<?php

// TODO: Add proper error handling with try-catch blocks


namespace App\Http\Controllers\Utility;

use App\Http\Controllers\BaseController;

class TestController extends BaseController
{
    public function downloadFile()
    {
        $filePath = BASE_URL . '/public/test-download.pdf';
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="test-document.pdf"');
        readfile($filePath);
        exit;
    }

    public function viewFile()
    {
        $filePath = BASE_URL . '/public/test-download.pdf';
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="test-document.pdf"');
        readfile($filePath);
        exit;
    }
}
