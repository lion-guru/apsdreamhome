<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Property Assistant</title>
    <link href="css/style.css" rel="stylesheet">
    <style>
        .container {
            max-width: 800px;
            margin: 40px auto;
            padding: 20px;
        }
        .chat-container {
            border: 1px solid #ddd;
            border-radius: 8px;
            height: 500px;
            display: flex;
            flex-direction: column;
        }
        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
        }
        .message {
            margin-bottom: 15px;
            padding: 10px 15px;
            border-radius: 15px;
            max-width: 80%;
        }
        .user-message {
            background: #007bff;
            color: white;
            margin-left: auto;
        }
        .bot-message {
            background: #f1f1f1;
            margin-right: auto;
        }
        .chat-input {
            display: flex;
            padding: 20px;
            border-top: 1px solid #ddd;
            gap: 10px;
        }
        .chat-input input {
            flex: 1;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .chat-input button {
            padding: 10px 20px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .chat-input button:hover {
            background: #0056b3;
        }
        .loading {
            display: none;
            text-align: center;
            padding: 10px;
            font-style: italic;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Property Assistant</h1>
        <p>आपका स्वागत है! मैं आपकी प्रॉपर्टी से जुड़े सवालों का जवाब देने में मदद कर सकता हूं। कृपया अपना सवाल पूछें।</p>
        
        <div class="chat-container">
            <div class="chat-messages" id="chatMessages"></div>
            <div class="loading" id="loading">जवाब दे रहा हूं...</div>
            <div class="chat-input">
                <input type="text" id="userInput" placeholder="अपना सवाल यहाँ टाइप करें..." />
                <button onclick="sendMessage()">भेजें</button>
            </div>
        </div>
    </div>

    <script>
    const context = `You are a helpful real estate assistant for APS Dream Homes. You help users with their property related queries primarily in Hindi, but can also respond in English if asked.

Provide detailed information about:
- Property buying and selling process
- Legal documentation and requirements
- Property valuation and market trends
- Location analysis and neighborhood details
- Investment advice and ROI calculations
- Construction quality and specifications
- Property maintenance and upkeep
- Amenities and facilities
- Payment plans and financing options
- Booking process and procedures

Key guidelines:
- Keep responses concise yet informative
- Use simple language that's easy to understand
- Provide practical, actionable advice
- Be polite and professional
- If unsure, guide users to contact APS Dream Homes directly

Focus on APS Dream Homes projects in:
- Gorakhpur
- Lucknow
- Varanasi`;

    async function sendMessage() {
        const userInput = document.getElementById('userInput');
        const message = userInput.value.trim();
        if (!message) return;

        // Add user message to chat
        addMessage(message, true);
        userInput.value = '';

        // Show loading
        document.getElementById('loading').style.display = 'block';

        // Prepare prompt with context
        const prompt = `${context}\n\nUser: ${message}\n\nAssistant:`;

        try {
            const response = await fetch('/api/gemini.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ prompt })
            });

            const data = await response.json();
            if (data.error) {
                addMessage(`Error: ${data.message}`, false);
            } else {
                addMessage(data.candidates[0].content.parts[0].text, false);
            }
        } catch (error) {
            addMessage(`Error: ${error.message}`, false);
        } finally {
            document.getElementById('loading').style.display = 'none';
        }
    }

    function addMessage(text, isUser) {
        const messagesDiv = document.getElementById('chatMessages');
        const messageDiv = document.createElement('div');
        messageDiv.className = `message ${isUser ? 'user-message' : 'bot-message'}`;
        messageDiv.textContent = text;
        messagesDiv.appendChild(messageDiv);
        messagesDiv.scrollTop = messagesDiv.scrollHeight;
    }

    // Handle Enter key
    document.getElementById('userInput').addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            sendMessage();
        }
    });
    </script>
</body>
</html>