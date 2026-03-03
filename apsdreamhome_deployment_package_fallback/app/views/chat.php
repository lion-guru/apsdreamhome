
<div class="chat-container">
    <div class="chat-header">
        <h5>Real-time Chat</h5>
        <div class="chat-controls">
            <button class="btn btn-sm btn-outline-primary" id="toggle-chat">
                <i class="fas fa-minus"></i>
            </button>
            <button class="btn btn-sm btn-outline-secondary" id="close-chat">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>
    
    <div class="chat-body">
        <div class="chat-users">
            <h6>Online Users</h6>
            <div id="online-users-list">
                <!-- Online users will be populated here -->
            </div>
        </div>
        
        <div class="chat-messages">
            <div id="messages-container">
                <!-- Messages will be displayed here -->
            </div>
            
            <div class="typing-indicator" id="typing-indicator" style="display: none;">
                <span id="typing-user"></span> is typing...
            </div>
        </div>
    </div>
    
    <div class="chat-footer">
        <div class="input-group">
            <input type="text" class="form-control" id="message-input" placeholder="Type your message...">
            <button class="btn btn-primary" id="send-message">
                <i class="fas fa-paper-plane"></i>
            </button>
        </div>
    </div>
</div>

<style>
.chat-container {
    position: fixed;
    bottom: 20px;
    right: 20px;
    width: 350px;
    height: 500px;
    background: white;
    border: 1px solid #ddd;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    display: flex;
    flex-direction: column;
    z-index: 1000;
}

.chat-header {
    padding: 15px;
    border-bottom: 1px solid #eee;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.chat-body {
    flex: 1;
    display: flex;
    overflow: hidden;
}

.chat-users {
    width: 100px;
    border-right: 1px solid #eee;
    padding: 10px;
    overflow-y: auto;
}

.chat-messages {
    flex: 1;
    display: flex;
    flex-direction: column;
    position: relative;
}

#messages-container {
    flex: 1;
    padding: 15px;
    overflow-y: auto;
}

.chat-footer {
    padding: 15px;
    border-top: 1px solid #eee;
}

.typing-indicator {
    padding: 5px 15px;
    font-size: 0.8em;
    color: #666;
    font-style: italic;
}

.message {
    margin-bottom: 10px;
    padding: 8px 12px;
    border-radius: 8px;
    max-width: 80%;
}

.message.own {
    background: #007bff;
    color: white;
    margin-left: auto;
}

.message.other {
    background: #f1f3f4;
    color: #333;
}

.message-header {
    font-size: 0.8em;
    font-weight: bold;
    margin-bottom: 2px;
}

.message-time {
    font-size: 0.7em;
    opacity: 0.7;
}

.online-user {
    padding: 5px;
    margin-bottom: 5px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 0.9em;
}

.online-user:hover {
    background: #f0f0f0;
}

.online-user.active {
    background: #e3f2fd;
}
</style>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const chatContainer = document.querySelector(".chat-container");
    const messageInput = document.getElementById("message-input");
    const sendButton = document.getElementById("send-message");
    const messagesContainer = document.getElementById("messages-container");
    const onlineUsersList = document.getElementById("online-users-list");
    const typingIndicator = document.getElementById("typing-indicator");
    const typingUser = document.getElementById("typing-user");
    
    let currentUser = "User" + Math.floor(Math.random() * 1000);
    let currentRoom = "general_chat";
    let isTyping = false;
    let typingTimeout;
    
    // Join chat room
    if (window.wsClient) {
        wsClient.joinRoom(currentRoom, currentUser);
        
        // Listen for events
        wsClient.on("welcome", (data) => {
            console.log("Connected to chat:", data.message);
        });
        
        wsClient.on("user_joined", (data) => {
            addSystemMessage(data.user + " joined the chat");
            updateOnlineUsers();
        });
        
        wsClient.on("user_left", (data) => {
            addSystemMessage(data.user + " left the chat");
            updateOnlineUsers();
        });
        
        wsClient.on("chat_message", (data) => {
            addMessage(data.user, data.message, data.timestamp, data.user !== currentUser);
        });
        
        wsClient.on("typing", (data) => {
            if (data.is_typing) {
                typingUser.textContent = data.user;
                typingIndicator.style.display = "block";
            } else {
                typingIndicator.style.display = "none";
            }
        });
    }
    
    // Send message
    function sendMessage() {
        const message = messageInput.value.trim();
        if (message && window.wsClient) {
            wsClient.sendMessage(message);
            messageInput.value = "";
            stopTyping();
        }
    }
    
    // Add message to chat
    function addMessage(user, message, timestamp, isOther = false) {
        const messageDiv = document.createElement("div");
        messageDiv.className = "message " + (isOther ? "other" : "own");
        
        const time = new Date(timestamp * 1000).toLocaleTimeString();
        
        messageDiv.innerHTML = \`
            <div class="message-header">\${user}</div>
            <div class="message-content">\${message}</div>
            <div class="message-time">\${time}</div>
        \`;
        
        messagesContainer.appendChild(messageDiv);
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }
    
    // Add system message
    function addSystemMessage(message) {
        const messageDiv = document.createElement("div");
        messageDiv.className = "system-message";
        messageDiv.style.textAlign = "center";
        messageDiv.style.color = "#666";
        messageDiv.style.fontSize = "0.8em";
        messageDiv.style.margin = "10px 0";
        messageDiv.textContent = message;
        
        messagesContainer.appendChild(messageDiv);
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }
    
    // Update online users
    function updateOnlineUsers() {
        // This would be populated from the server
        onlineUsersList.innerHTML = \`
            <div class="online-user active">\${currentUser}</div>
            <div class="online-user">User123</div>
            <div class="online-user">User456</div>
        \`;
    }
    
    // Typing indicators
    function startTyping() {
        if (!isTyping && window.wsClient) {
            isTyping = true;
            wsClient.sendTyping(true);
        }
        
        clearTimeout(typingTimeout);
        typingTimeout = setTimeout(stopTyping, 3000);
    }
    
    function stopTyping() {
        if (isTyping && window.wsClient) {
            isTyping = false;
            wsClient.sendTyping(false);
        }
        clearTimeout(typingTimeout);
    }
    
    // Event listeners
    sendButton.addEventListener("click", sendMessage);
    messageInput.addEventListener("keypress", (e) => {
        if (e.key === "Enter") {
            sendMessage();
        } else {
            startTyping();
        }
    });
    
    // Initialize
    updateOnlineUsers();
});
</script>
