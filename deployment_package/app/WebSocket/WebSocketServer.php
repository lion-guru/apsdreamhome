<?php
namespace App\WebSocket;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;

class WebSocketServer implements MessageComponentInterface {
    protected $clients;
    protected $users;
    
    public function __construct() {
        $this->clients = new \SplObjectStorage;
        $this->users = [];
    }
    
    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
        echo "New connection! ({$conn->resourceId})\n";
        
        // Send welcome message
        $conn->send(json_encode([
            "type" => "welcome",
            "message" => "Connected to APS Dream Home real-time server",
            "timestamp" => time()
        ]));
    }
    
    public function onMessage(ConnectionInterface $from, $msg) {
        $data = json_decode($msg, true);
        
        if (!$data) {
            return;
        }
        
        switch ($data["type"]) {
            case "join_room":
                $this->joinRoom($from, $data["room"], $data["user"]);
                break;
            case "leave_room":
                $this->leaveRoom($from, $data["room"]);
                break;
            case "chat_message":
                $this->broadcastMessage($from, $data);
                break;
            case "typing":
                $this->broadcastTyping($from, $data);
                break;
            case "collaborative_edit":
                $this->handleCollaborativeEdit($from, $data);
                break;
        }
    }
    
    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);
        echo "Connection {$conn->resourceId} has disconnected\n";
        
        // Remove user from all rooms
        foreach ($this->users as $resourceId => $userData) {
            if ($resourceId === $conn->resourceId) {
                $this->leaveRoom($conn, $userData["room"]);
                break;
            }
        }
    }
    
    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";
        $conn->close();
    }
    
    private function joinRoom($conn, $room, $user) {
        $this->users[$conn->resourceId] = [
            "room" => $room,
            "user" => $user,
            "connection" => $conn
        ];
        
        // Notify others in room
        $this->broadcastToRoom($room, [
            "type" => "user_joined",
            "user" => $user,
            "room" => $room,
            "timestamp" => time()
        ], $conn);
    }
    
    private function leaveRoom($conn, $room) {
        if (isset($this->users[$conn->resourceId])) {
            $user = $this->users[$conn->resourceId]["user"];
            unset($this->users[$conn->resourceId]);
            
            // Notify others in room
            $this->broadcastToRoom($room, [
                "type" => "user_left",
                "user" => $user,
                "room" => $room,
                "timestamp" => time()
            ]);
        }
    }
    
    private function broadcastMessage($from, $data) {
        $user = $this->users[$from->resourceId]["user"];
        $room = $this->users[$from->resourceId]["room"];
        
        $this->broadcastToRoom($room, [
            "type" => "chat_message",
            "user" => $user,
            "message" => $data["message"],
            "room" => $room,
            "timestamp" => time()
        ], $from);
    }
    
    private function broadcastTyping($from, $data) {
        $user = $this->users[$from->resourceId]["user"];
        $room = $this->users[$from->resourceId]["room"];
        
        $this->broadcastToRoom($room, [
            "type" => "typing",
            "user" => $user,
            "is_typing" => $data["is_typing"],
            "room" => $room,
            "timestamp" => time()
        ], $from);
    }
    
    private function handleCollaborativeEdit($from, $data) {
        $user = $this->users[$from->resourceId]["user"];
        $room = $this->users[$from->resourceId]["room"];
        
        $this->broadcastToRoom($room, [
            "type" => "collaborative_edit",
            "user" => $user,
            "operation" => $data["operation"],
            "content" => $data["content"],
            "position" => $data["position"],
            "room" => $room,
            "timestamp" => time()
        ], $from);
    }
    
    private function broadcastToRoom($room, $message, $exclude = null) {
        foreach ($this->users as $resourceId => $userData) {
            if ($userData["room"] === $room && $userData["connection"] !== $exclude) {
                $userData["connection"]->send(json_encode($message));
            }
        }
    }
}

// Start WebSocket server
$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new WebSocketServer()
        )
    ),
    8080
);

echo "WebSocket server started on port 8080\n";
$server->run();
