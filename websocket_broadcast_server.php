<?php
/**
 * Standalone broadcast HTTP server.
 *
 * Listens on port 8081 (configurable via WS_HTTP_PORT env var) and accepts
 * POST /broadcast with X-Broadcast-Key header. Decodes the JSON body, calls
 * WebSocketServer::broadcast() in-process to push to connected WebSocket
 * clients, and returns 200 with {success, sent}.
 *
 * Run as a separate process from the WebSocket server (which uses port 8080).
 * PHP can't share event loops between two IoServer instances on different ports
 * without a parent process manager.
 */
require __DIR__ . '/vendor/autoload.php';

use App\Core\Database\Database as CoreDatabase;
use App\Services\WebSocketServer;
use App\Services\BroadcastHttpHandler;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;

$db = new CoreDatabase();
$wsServer = new WebSocketServer($db->getPdo());

$port = (int)($_ENV['WS_HTTP_PORT'] ?? getenv('WS_HTTP_PORT') ?: 8081);
$server = IoServer::factory(
    new HttpServer(new BroadcastHttpHandler($wsServer)),
    $port
);
echo "Broadcast HTTP endpoint listening on http://localhost:{$port}/broadcast\n";
$server->run();?>