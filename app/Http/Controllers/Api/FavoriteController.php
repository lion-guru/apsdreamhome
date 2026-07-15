<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Core\Database\Database;
use Exception;

class FavoriteController extends BaseController
{
    protected function skipCsrfProtection(): bool
    {
        return true;
    }

    public function list()
    {
        // Get favorites
    }

    public function add()
    {
        // Add favorite
    }

    public function remove()
    {
        // Remove favorite
    }

    public function check()
    {
        // Check favorite
    }

    public function stats()
    {
        // Favorite stats
    }
}