<?php

namespace App\Services\Legacy\Classes;

use App\Core\ErrorHandler;

/**
 * Enhanced Error Pages for MVC Structure
 * Provides beautiful, user-friendly error pages
 */
class ErrorPages {
    /**
     * Display 404 Not Found page
     */
    public static function show404() {
        ErrorHandler::render(404);
    }

    /**
     * Display 500 Internal Server Error page
     */
    public static function show500() {
        ErrorHandler::render(500);
    }

    /**
     * Display 403 Forbidden page
     */
    public static function show403() {
        ErrorHandler::render(403);
    }

    /**
     * Display 401 Unauthorized page
     */
    public static function show401() {
        ErrorHandler::render(401);
    }

    /**
     * Display 400 Bad Request page
     */
    public static function show400() {
        ErrorHandler::render(400);
    }
}
