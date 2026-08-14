<?php
require __DIR__ . '/vendor/autoload.php';

// Use the actual Database class to avoid alias conflict
use App\Core\Database\Database as CoreDatabase;
use App\Services\WebSocketServer;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;

// Database connection
$db = new CoreDatabase();

// WebSocket server
$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new WebSocketServer($db->getPdo())
        )
    ),
    8080 // Port for WebSocket server
);

echo "WebSocket server started on ws://localhost:8080\n";
echo "(For HTTP cross-process broadcast, run: php websocket_broadcast_server.php)\n";
$server->run();?>