<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Core\Database\Database;
use Exception;

class DocumentController extends BaseController
{
    protected function skipCsrfProtection(): bool
    {
        return true;
    }

    public function listDocuments()
    {
        // Get documents
    }

    public function upload()
    {
        // Upload document
    }

    public function customerDocuments()
    {
        // Customer documents
    }

    public function getDetail($id)
    {
        // Document detail
    }

    public function preview($id)
    {
        // Preview document
    }
}