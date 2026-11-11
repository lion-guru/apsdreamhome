<?php
/**
 * App\Core\App - autoload entry point.
 *
 * NOTE (S451): the class `App\Core\App` is DECLARED in
 * app/Core/Legacy/AppCoreService.php, but the autoloader resolves the FQCN
 * `App\Core\App` to the PATH app/Core/App.php - which did not exist. So:
 *
 *   - `class_exists('App\Core\App')` was ALWAYS false, which made the
 *     guarded paths in Core\Config::config() and AuthService silently skip
 *     the App-based config / session bootstrap and fall through to the
 *     environment fallback.
 *   - `App\Core\App::database()` threw "Class App\Core\App not found".
 *     BaseApiController::validateApiKey() calls it inside a try/catch, so
 *     EVERY API-key-protected request came back
 *     "Internal server error during API key validation" (500) instead of
 *     validating the key.
 *   - App\Core\Http\Request, App\Core\Http\Response, App\Core\Router and
 *     App\Core\Http\View all `use App\Core\App;`.
 *
 * app/Core/Controller.php already pulled the legacy file in by hand
 * (`require_once __DIR__ . '/Legacy/AppCoreService.php';`), so the class was
 * only ever reachable if that file happened to be loaded first. This file
 * gives the AUTOLOADER the file it expects, so the FQCN resolves on its own.
 *
 * `require_once` keeps it safe when Controller.php has already loaded it.
 */

require_once __DIR__ . '/Legacy/AppCoreService.php';
