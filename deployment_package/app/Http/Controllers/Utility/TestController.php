<?php

namespace App\Controllers;

use App\Core\Http\Response;

class TestController
{
    public function downloadFile()
    {
        $filePath = ROOT . '/public/test-download.pdf';
        return Response::download($filePath, 'test-document.pdf');
    }

    public function viewFile()
    {
        $filePath = ROOT . '/public/test-download.pdf';
        return Response::file($filePath, 'test-document.pdf');
    }
}
