<!DOCTYPE html>
<html>
<head>
    <title>AI Assistant - APS Dream Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <div class="row">
            <div class="col-12">
                <h1 class="h3 mb-4">AI Assistant</h1>
                <div class="card">
                    <div class="card-body">
                        <div class="chat-container" style="height: 400px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; margin-bottom: 10px;">
                            <div class="message mb-2">
                                <div class="alert alert-info">
                                    <strong>AI Assistant:</strong> Hello! How can I help you today?
                                </div>
                            </div>
                        </div>
                        <div class="input-group">
                            <input type="text" class="form-control" id="chatInput" placeholder="Type your message...">
                            <button class="btn btn-primary" onclick="sendMessage()">Send</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        function sendMessage() {
            const input = document.getElementById('chatInput');
            const message = input.value.trim();
            
            if (message) {
                const chatContainer = document.querySelector('.chat-container');
                
                // Add user message
                chatContainer.innerHTML += '<div class="message mb-2"><div class="alert alert-primary"><strong>You:</strong> ' + message + '</div></div>';
                
                // Simulate AI response
                setTimeout(() => {
                    chatContainer.innerHTML += '<div class="message mb-2"><div class="alert alert-info"><strong>AI Assistant:</strong> I understand your question. Let me help you with that.</div></div>';
                    chatContainer.scrollTop = chatContainer.scrollHeight;
                }, 1000);
                
                input.value = '';
                chatContainer.scrollTop = chatContainer.scrollHeight;
            }
        }
        
        document.getElementById('chatInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                sendMessage();
            }
        });
    </script>
</body>
</html>