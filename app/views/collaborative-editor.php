
<div class="collaborative-editor">
    <div class="editor-header">
        <h5>Collaborative Document Editor</h5>
        <div class="editor-controls">
            <button class="btn btn-sm btn-outline-primary" id="save-document">
                <i class="fas fa-save"></i> Save
            </button>
            <button class="btn btn-sm btn-outline-secondary" id="share-document">
                <i class="fas fa-share"></i> Share
            </button>
        </div>
    </div>
    
    <div class="editor-body">
        <div class="active-users">
            <h6>Active Users</h6>
            <div id="active-users-list">
                <!-- Active users will be shown here -->
            </div>
        </div>
        
        <div class="editor-content">
            <div id="editor" contenteditable="true">
                <!-- Document content will be here -->
            </div>
        </div>
    </div>
    
    <div class="editor-footer">
        <div class="document-info">
            <span id="word-count">0 words</span>
            <span id="char-count">0 characters</span>
            <span id="last-saved">Never saved</span>
        </div>
    </div>
</div>

<style>
.collaborative-editor {
    background: white;
    border: 1px solid #ddd;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    height: 600px;
    display: flex;
    flex-direction: column;
}

.editor-header {
    padding: 15px;
    border-bottom: 1px solid #eee;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.editor-body {
    flex: 1;
    display: flex;
    overflow: hidden;
}

.active-users {
    width: 150px;
    border-right: 1px solid #eee;
    padding: 15px;
    overflow-y: auto;
}

.editor-content {
    flex: 1;
    padding: 20px;
    overflow-y: auto;
}

#editor {
    min-height: 400px;
    padding: 20px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-family: "Courier New", monospace;
    font-size: 14px;
    line-height: 1.6;
    white-space: pre-wrap;
    word-wrap: break-word;
}

#editor:focus {
    outline: none;
    border-color: #007bff;
    box-shadow: 0 0 0 2px rgba(0,123,255,0.25);
}

.editor-footer {
    padding: 10px 15px;
    border-top: 1px solid #eee;
    background: #f8f9fa;
}

.document-info {
    display: flex;
    gap: 20px;
    font-size: 0.9em;
    color: #666;
}

.active-user {
    display: flex;
    align-items: center;
    padding: 5px;
    margin-bottom: 5px;
    border-radius: 4px;
    font-size: 0.9em;
}

.user-avatar {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    margin-right: 8px;
    background: #007bff;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.8em;
}

.user-cursor {
    position: absolute;
    width: 2px;
    height: 20px;
    background: #007bff;
    pointer-events: none;
}

.user-selection {
    background: rgba(0, 123, 255, 0.2);
}
</style>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const editor = document.getElementById("editor");
    const activeUsersList = document.getElementById("active-users-list");
    const wordCount = document.getElementById("word-count");
    const charCount = document.getElementById("char-count");
    const lastSaved = document.getElementById("last-saved");
    
    let currentUser = "User" + Math.floor(Math.random() * 1000);
    let currentRoom = "document_" + Math.floor(Math.random() * 1000);
    let documentContent = "";
    let lastSentContent = "";
    let activeUsers = {};
    let userColors = {};
    
    // Initialize collaborative editing
    if (window.wsClient) {
        wsClient.joinRoom(currentRoom, currentUser);
        
        wsClient.on("collaborative_edit", (data) => {
            handleCollaborativeEdit(data);
        });
        
        wsClient.on("user_joined", (data) => {
            addUser(data.user);
        });
        
        wsClient.on("user_left", (data) => {
            removeUser(data.user);
        });
    }
    
    // Handle collaborative edits
    function handleCollaborativeEdit(data) {
        if (data.user !== currentUser) {
            applyRemoteEdit(data);
        }
    }
    
    function applyRemoteEdit(data) {
        const operation = data.operation;
        const content = data.content;
        const position = data.position;
        
        switch (operation) {
            case "insert":
                insertTextAt(position, content);
                break;
            case "delete":
                deleteTextAt(position, content.length);
                break;
            case "replace":
                replaceTextAt(position, content);
                break;
        }
        
        updateStats();
    }
    
    function insertTextAt(position, text) {
        const currentContent = editor.innerText;
        const newContent = currentContent.slice(0, position) + text + currentContent.slice(position);
        editor.innerText = newContent;
    }
    
    function deleteTextAt(position, length) {
        const currentContent = editor.innerText;
        const newContent = currentContent.slice(0, position) + currentContent.slice(position + length);
        editor.innerText = newContent;
    }
    
    function replaceTextAt(position, text) {
        const currentContent = editor.innerText;
        const newContent = currentContent.slice(0, position) + text + currentContent.slice(position + text.length);
        editor.innerText = newContent;
    }
    
    // Send local edits
    function sendLocalEdit() {
        const currentContent = editor.innerText;
        
        if (currentContent !== lastSentContent && window.wsClient) {
            // Calculate diff and send
            const diff = calculateDiff(lastSentContent, currentContent);
            
            if (diff) {
                wsClient.sendCollaborativeEdit(diff.operation, diff.content, diff.position);
                lastSentContent = currentContent;
            }
        }
    }
    
    function calculateDiff(oldText, newText) {
        // Simple diff calculation - in production, use a proper diff algorithm
        const minLength = Math.min(oldText.length, newText.length);
        let firstDiff = 0;
        
        while (firstDiff < minLength && oldText[firstDiff] === newText[firstDiff]) {
            firstDiff++;
        }
        
        if (firstDiff === minLength) {
            if (oldText.length === newText.length) {
                return null; // No changes
            } else if (oldText.length < newText.length) {
                return {
                    operation: "insert",
                    content: newText.slice(firstDiff),
                    position: firstDiff
                };
            } else {
                return {
                    operation: "delete",
                    content: oldText.slice(firstDiff),
                    position: firstDiff
                };
            }
        }
        
        return {
            operation: "replace",
            content: newText.slice(firstDiff),
            position: firstDiff
        };
    }
    
    // User management
    function addUser(userId) {
        if (!activeUsers[userId]) {
            activeUsers[userId] = {
                name: userId,
                color: getUserColor(userId),
                cursor: null
            };
        }
        
        updateActiveUsersList();
    }
    
    function removeUser(userId) {
        delete activeUsers[userId];
        updateActiveUsersList();
    }
    
    function getUserColor(userId) {
        if (!userColors[userId]) {
            const colors = ["#007bff", "#28a745", "#dc3545", "#ffc107", "#6f42c1", "#fd7e14"];
            userColors[userId] = colors[Object.keys(userColors).length % colors.length];
        }
        return userColors[userId];
    }
    
    function updateActiveUsersList() {
        activeUsersList.innerHTML = "";
        
        Object.values(activeUsers).forEach(user => {
            const userDiv = document.createElement("div");
            userDiv.className = "active-user";
            userDiv.innerHTML = \`
                <div class="user-avatar" style="background: \${user.color}">
                    \${user.name.charAt(0).toUpperCase()}
                </div>
                <span>\${user.name}</span>
            \`;
            activeUsersList.appendChild(userDiv);
        });
    }
    
    // Update statistics
    function updateStats() {
        const content = editor.innerText;
        const words = content.trim().split(/\s+/).filter(word => word.length > 0).length;
        const chars = content.length;
        
        wordCount.textContent = words + " words";
        charCount.textContent = chars + " characters";
    }
    
    // Auto-save
    function autoSave() {
        if (window.wsClient) {
            // Send save operation
            wsClient.sendCollaborativeEdit("save", editor.innerText, 0);
            lastSaved.textContent = "Saved just now";
        }
    }
    
    // Event listeners
    editor.addEventListener("input", () => {
        updateStats();
        sendLocalEdit();
    });
    
    editor.addEventListener("keyup", () => {
        clearTimeout(window.saveTimeout);
        window.saveTimeout = setTimeout(autoSave, 2000);
    });
    
    document.getElementById("save-document").addEventListener("click", autoSave);
    
    // Initialize
    updateStats();
    addUser(currentUser);
});
</script>
