<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Property Assistant - APS Dream Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --success-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .ai-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            margin: 20px auto;
            max-width: 1200px;
            overflow: hidden;
        }

        .ai-header {
            background: var(--primary-gradient);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .ai-header h1 {
            margin: 0;
            font-size: 2.5rem;
            font-weight: 700;
        }

        .ai-header p {
            margin: 10px 0 0;
            opacity: 0.9;
            font-size: 1.1rem;
        }

        .ai-content {
            padding: 30px;
        }

        .chat-container {
            height: 500px;
            border: 1px solid #e0e0e0;
            border-radius: 15px;
            overflow: hidden;
            background: white;
            display: flex;
            flex-direction: column;
        }

        .chat-messages {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
            background: #f8f9fa;
        }

        .message {
            margin-bottom: 15px;
            display: flex;
            align-items: flex-start;
        }

        .message.user {
            justify-content: flex-end;
        }

        .message-content {
            max-width: 70%;
            padding: 12px 16px;
            border-radius: 18px;
            position: relative;
        }

        .message.ai .message-content {
            background: white;
            border: 1px solid #e0e0e0;
            color: #333;
        }

        .message.user .message-content {
            background: var(--primary-gradient);
            color: white;
        }

        .chat-input-container {
            padding: 20px;
            background: white;
            border-top: 1px solid #e0e0e0;
        }

        .chat-input {
            display: flex;
            gap: 10px;
        }

        .chat-input input {
            flex: 1;
            border: 1px solid #e0e0e0;
            border-radius: 25px;
            padding: 12px 20px;
            font-size: 1rem;
            outline: none;
            transition: border-color 0.3s;
        }

        .chat-input input:focus {
            border-color: #667eea;
        }

        .chat-input button {
            background: var(--primary-gradient);
            color: white;
            border: none;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: transform 0.3s;
        }

        .chat-input button:hover {
            transform: scale(1.1);
        }

        .typing-indicator {
            display: none;
            padding: 10px 16px;
            background: white;
            border: 1px solid #e0e0e0;
            border-radius: 18px;
            width: 60px;
        }

        .typing-indicator.active {
            display: inline-block;
        }

        .typing-indicator span {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #667eea;
            margin: 0 2px;
            animation: typing 1.4s infinite;
        }

        .typing-indicator span:nth-child(2) {
            animation-delay: 0.2s;
        }

        .typing-indicator span:nth-child(3) {
            animation-delay: 0.4s;
        }

        @keyframes typing {
            0%, 60%, 100% {
                transform: translateY(0);
            }
            30% {
                transform: translateY(-10px);
            }
        }

        .recommendations-section {
            margin-top: 30px;
        }

        .property-card {
            border: 1px solid #e0e0e0;
            border-radius: 15px;
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
            background: white;
            margin-bottom: 20px;
        }

        .property-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        }

        .property-image {
            height: 200px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.2rem;
        }

        .property-content {
            padding: 20px;
        }

        .property-price {
            font-size: 1.5rem;
            font-weight: 700;
            color: #667eea;
            margin-bottom: 10px;
        }

        .property-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 10px;
            color: #333;
        }

        .property-details {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
            color: #666;
            font-size: 0.9rem;
        }

        .property-recommendation-score {
            background: var(--success-gradient);
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            display: inline-block;
            margin-bottom: 10px;
        }

        .property-reasons {
            font-size: 0.85rem;
            color: #666;
        }

        .valuation-section {
            margin-top: 30px;
            padding: 25px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 15px;
        }

        .valuation-result {
            display: none;
            margin-top: 20px;
            padding: 20px;
            background: white;
            border-radius: 10px;
            border-left: 4px solid #667eea;
        }

        .confidence-meter {
            width: 100%;
            height: 10px;
            background: #e0e0e0;
            border-radius: 5px;
            overflow: hidden;
            margin: 10px 0;
        }

        .confidence-fill {
            height: 100%;
            background: var(--success-gradient);
            transition: width 0.5s ease;
        }

        .ai-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            border: 1px solid #e0e0e0;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: #667eea;
            margin-bottom: 5px;
        }

        .stat-label {
            color: #666;
            font-size: 0.9rem;
        }

        .quick-actions {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .quick-action-btn {
            background: white;
            border: 1px solid #e0e0e0;
            border-radius: 20px;
            padding: 8px 16px;
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.3s;
        }

        .quick-action-btn:hover {
            background: var(--primary-gradient);
            color: white;
            border-color: transparent;
        }

        @media (max-width: 768px) {
            .ai-container {
                margin: 10px;
                border-radius: 15px;
            }

            .ai-header h1 {
                font-size: 2rem;
            }

            .message-content {
                max-width: 85%;
            }

            .chat-container {
                height: 400px;
            }
        }
    </style>
</head>
<body>
    <div class="ai-container">
        <div class="ai-header">
            <h1><i class="fas fa-robot"></i> AI Property Assistant</h1>
            <p>Your intelligent real estate companion powered by advanced AI</p>
        </div>

        <div class="ai-content">
            <!-- AI Statistics -->
            <div class="ai-stats">
                <div class="stat-card">
                    <div class="stat-number" id="totalProperties">245</div>
                    <div class="stat-label">Properties Analyzed</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number" id="aiRecommendations">1,234</div>
                    <div class="stat-label">AI Recommendations</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number" id="chatInteractions">567</div>
                    <div class="stat-label">Chat Interactions</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number" id="avgAccuracy">94%</div>
                    <div class="stat-label">Accuracy Rate</div>
                </div>
            </div>

            <!-- Chat Interface -->
            <div class="row">
                <div class="col-lg-8">
                    <h3><i class="fas fa-comments"></i> AI Chat Assistant</h3>
                    
                    <div class="quick-actions">
                        <button class="quick-action-btn" onclick="sendQuickMessage('Show me properties under $300,000')">
                            <i class="fas fa-dollar-sign"></i> Under $300k
                        </button>
                        <button class="quick-action-btn" onclick="sendQuickMessage('What do you recommend for me?')">
                            <i class="fas fa-star"></i> Get Recommendations
                        </button>
                        <button class="quick-action-btn" onclick="sendQuickMessage('Find 3-bedroom houses')">
                            <i class="fas fa-home"></i> 3-Bedroom Houses
                        </button>
                        <button class="quick-action-btn" onclick="sendQuickMessage('What are popular areas?')">
                            <i class="fas fa-map-marker-alt"></i> Popular Areas
                        </button>
                        <button class="quick-action-btn" onclick="sendQuickMessage('Help')">
                            <i class="fas fa-question-circle"></i> Help
                        </button>
                    </div>

                    <div class="chat-container">
                        <div class="chat-messages" id="chatMessages">
                            <div class="message ai">
                                <div class="message-content">
                                    👋 Hello! I'm your AI Property Assistant. I can help you find the perfect property, provide personalized recommendations, analyze market trends, and answer your real estate questions. How can I assist you today?
                                </div>
                            </div>
                        </div>
                        <div class="typing-indicator" id="typingIndicator">
                            <span></span>
                            <span></span>
                            <span></span>
                        </div>
                        <div class="chat-input-container">
                            <div class="chat-input">
                                <input type="text" id="messageInput" placeholder="Ask me anything about properties..." onkeypress="handleKeyPress(event)">
                                <button onclick="sendMessage()">
                                    <i class="fas fa-paper-plane"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <h3><i class="fas fa-magic"></i> AI Features</h3>
                    <div class="list-group">
                        <a href="#" class="list-group-item list-group-item-action" onclick="showRecommendations()">
                            <i class="fas fa-star text-warning"></i> Personalized Recommendations
                        </a>
                        <a href="#" class="list-group-item list-group-item-action" onclick="showValuation()">
                            <i class="fas fa-chart-line text-success"></i> Property Valuation
                        </a>
                        <a href="#" class="list-group-item list-group-item-action" onclick="showMarketTrends()">
                            <i class="fas fa-chart-bar text-info"></i> Market Trends
                        </a>
                        <a href="#" class="list-group-item list-group-item-action" onclick="showPreferences()">
                            <i class="fas fa-cog text-primary"></i> My Preferences
                        </a>
                    </div>
                </div>
            </div>

            <!-- Recommendations Section -->
            <div class="recommendations-section" id="recommendationsSection" style="display: none;">
                <h3><i class="fas fa-star"></i> AI Recommendations for You</h3>
                <div id="recommendationsContainer">
                    <!-- Recommendations will be loaded here -->
                </div>
            </div>

            <!-- Property Valuation Section -->
            <div class="valuation-section" id="valuationSection" style="display: none;">
                <h3><i class="fas fa-chart-line"></i> AI Property Valuation</h3>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="propertySelect" class="form-label">Select Property</label>
                            <select class="form-select" id="propertySelect">
                                <option value="">Choose a property...</option>
                                <option value="1">Modern Downtown Apartment - $350,000</option>
                                <option value="2">Suburban Family House - $450,000</option>
                                <option value="3">Luxury Penthouse - $750,000</option>
                                <option value="4">Cozy Studio - $180,000</option>
                                <option value="5">Beachfront Villa - $1,200,000</option>
                            </select>
                        </div>
                        <button class="btn btn-primary" onclick="generateValuation()">
                            <i class="fas fa-calculator"></i> Generate AI Valuation
                        </button>
                    </div>
                    <div class="col-md-6">
                        <div class="valuation-result" id="valuationResult">
                            <!-- Valuation results will appear here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let conversationHistory = [];

        function sendMessage() {
            const input = document.getElementById('messageInput');
            const message = input.value.trim();
            
            if (message === '') return;
            
            addMessage(message, 'user');
            input.value = '';
            
            showTypingIndicator();
            
            // Simulate AI response (replace with actual API call)
            setTimeout(() => {
                hideTypingIndicator();
                const response = generateAIResponse(message);
                addMessage(response.message, 'ai', response.properties || []);
                
                if (response.properties && response.properties.length > 0) {
                    displayPropertyResults(response.properties);
                }
            }, 1500);
        }

        function sendQuickMessage(message) {
            document.getElementById('messageInput').value = message;
            sendMessage();
        }

        function handleKeyPress(event) {
            if (event.key === 'Enter') {
                sendMessage();
            }
        }

        function addMessage(message, sender, properties = []) {
            const messagesContainer = document.getElementById('chatMessages');
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${sender}`;
            
            let messageContent = `<div class="message-content">${message}</div>`;
            
            if (properties.length > 0) {
                messageContent += '<div class="mt-2">';
                properties.forEach(property => {
                    messageContent += `
                        <div class="small bg-light p-2 rounded mb-1">
                            <strong>${property.title}</strong> - $${property.price.toLocaleString()}
                            <br><small>${property.bedrooms} bed, ${property.bathrooms} bath</small>
                        </div>
                    `;
                });
                messageContent += '</div>';
            }
            
            messageDiv.innerHTML = messageContent;
            messagesContainer.appendChild(messageDiv);
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
            
            conversationHistory.push({ sender, message, timestamp: new Date() });
        }

        function showTypingIndicator() {
            document.getElementById('typingIndicator').classList.add('active');
        }

        function hideTypingIndicator() {
            document.getElementById('typingIndicator').classList.remove('active');
        }

        function generateAIResponse(message) {
            const lowerMessage = message.toLowerCase();
            
            // Property search
            if (lowerMessage.includes('search') || lowerMessage.includes('find') || lowerMessage.includes('show me') || lowerMessage.includes('looking for')) {
                return {
                    message: "I found some great properties matching your criteria! Here are the top matches:",
                    properties: [
                        { id: 1, title: "Modern Downtown Apartment", price: 320000, bedrooms: 2, bathrooms: 2 },
                        { id: 2, title: "Cozy Suburban House", price: 280000, bedrooms: 3, bathrooms: 2 },
                        { id: 3, title: "Luxury Penthouse", price: 750000, bedrooms: 3, bathrooms: 3 }
                    ]
                };
            }
            
            // Recommendations
            if (lowerMessage.includes('recommend') || lowerMessage.includes('suggest')) {
                return {
                    message: "Based on your preferences and browsing history, here are my top recommendations for you:",
                    properties: [
                        { id: 4, title: "Family-Friendly Villa", price: 450000, bedrooms: 4, bathrooms: 3 },
                        { id: 5, title: "Investment Property", price: 380000, bedrooms: 3, bathrooms: 2 }
                    ]
                };
            }
            
            // Price inquiries
            if (lowerMessage.includes('price') || lowerMessage.includes('cost') || lowerMessage.includes('budget') || lowerMessage.includes('affordable')) {
                return {
                    message: "I can help you find properties in any price range! The current market has properties ranging from $150,000 to $1,200,000. What's your budget range?",
                    properties: []
                };
            }
            
            // Location inquiries
            if (lowerMessage.includes('location') || lowerMessage.includes('area') || lowerMessage.includes('city') || lowerMessage.includes('where')) {
                return {
                    message: "Popular areas right now include Downtown (avg price $350,000), Suburbs (avg price $280,000), and Waterfront (avg price $600,000). Which area interests you most?",
                    properties: []
                };
            }
            
            // Help
            if (lowerMessage.includes('help')) {
                return {
                    message: "I'm your AI Property Assistant! I can help you:\n\n• 🔍 Find properties based on your criteria\n• ⭐ Get personalized recommendations\n• 💰 Check prices in different areas\n• 📍 Learn about available locations\n• 📊 Analyze market trends\n• 💬 Answer property questions\n\nTry asking me things like:\n• 'Show me 3-bedroom houses under $300,000'\n• 'What do you recommend for me?'\n• 'Find properties in downtown'\n• 'What's the average price in this area?'",
                    properties: []
                };
            }
            
            // Default response
            return {
                message: "I understand you're interested in properties. Could you be more specific about what you're looking for? For example, you could tell me your budget, preferred location, or property type.",
                properties: []
            };
        }

        function displayPropertyResults(properties) {
            // This would show property cards in a separate section
            console.log('Properties to display:', properties);
        }

        function showRecommendations() {
            document.getElementById('recommendationsSection').style.display = 'block';
            document.getElementById('valuationSection').style.display = 'none';
            
            // Simulate loading recommendations
            const container = document.getElementById('recommendationsContainer');
            container.innerHTML = `
                <div class="property-card">
                    <div class="property-image">
                        <i class="fas fa-home fa-3x"></i>
                    </div>
                    <div class="property-content">
                        <div class="property-recommendation-score">95% Match</div>
                        <div class="property-price">$320,000</div>
                        <div class="property-title">Modern Downtown Apartment</div>
                        <div class="property-details">
                            <span><i class="fas fa-bed"></i> 2 Beds</span>
                            <span><i class="fas fa-bath"></i> 2 Baths</span>
                            <span><i class="fas fa-ruler-combined"></i> 1,200 sqft</span>
                        </div>
                        <div class="property-reasons">
                            <strong>Why recommended:</strong> Within your budget, downtown location, modern amenities
                        </div>
                    </div>
                </div>
                <div class="property-card">
                    <div class="property-image">
                        <i class="fas fa-home fa-3x"></i>
                    </div>
                    <div class="property-content">
                        <div class="property-recommendation-score">88% Match</div>
                        <div class="property-price">$280,000</div>
                        <div class="property-title">Cozy Suburban House</div>
                        <div class="property-details">
                            <span><i class="fas fa-bed"></i> 3 Beds</span>
                            <span><i class="fas fa-bath"></i> 2 Baths</span>
                            <span><i class="fas fa-ruler-combined"></i> 1,800 sqft</span>
                        </div>
                        <div class="property-reasons">
                            <strong>Why recommended:</strong> Great value for money, family-friendly neighborhood
                        </div>
                    </div>
                </div>
            `;
        }

        function showValuation() {
            document.getElementById('valuationSection').style.display = 'block';
            document.getElementById('recommendationsSection').style.display = 'none';
        }

        function generateValuation() {
            const propertyId = document.getElementById('propertySelect').value;
            if (!propertyId) return;
            
            const resultDiv = document.getElementById('valuationResult');
            resultDiv.style.display = 'block';
            
            // Simulate AI valuation
            setTimeout(() => {
                resultDiv.innerHTML = `
                    <h5><i class="fas fa-chart-line"></i> AI Valuation Results</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Listed Price:</strong><br>
                            <span style="font-size: 1.5rem; color: #666;">$350,000</span>
                        </div>
                        <div class="col-md-6">
                            <strong>Estimated Market Value:</strong><br>
                            <span style="font-size: 1.5rem; color: #28a745;">$365,000</span>
                        </div>
                    </div>
                    <hr>
                    <div class="mb-3">
                        <strong>Confidence Score:</strong>
                        <div class="confidence-meter">
                            <div class="confidence-fill" style="width: 87%;"></div>
                        </div>
                        <small>87% - High confidence based on comparable properties</small>
                    </div>
                    <div class="mb-3">
                        <strong>Valuation Factors:</strong>
                        <ul class="small">
                            <li>Location demand: High</li>
                            <li>Market trend: +5.2% (increasing)</li>
                            <li>Comparable sales: 8 similar properties</li>
                            <li>Property condition: Good</li>
                        </ul>
                    </div>
                    <div class="alert alert-info">
                        <strong>Recommendation:</strong> This property is slightly underpriced compared to market value. Consider increasing the price by 4.3% to maximize returns.
                    </div>
                `;
            }, 2000);
        }

        function showMarketTrends() {
            addMessage("📊 Current Market Trends Analysis:", 'ai');
            addMessage("• Downtown properties: +5.2% growth this month\n• Suburban houses: +3.1% growth\n• Average days on market: 42 days\n• Most popular price range: $250k-$400k", 'ai');
        }

        function showPreferences() {
            addMessage("⚙️ Your Current Preferences:", 'ai');
            addMessage("• Budget: $200,000 - $400,000\n• Preferred areas: Downtown, Suburbs\n• Property type: House, Apartment\n• Bedrooms: 2-3\n• Bathrooms: 2+", 'ai');
            addMessage("Would you like to update any of these preferences?", 'ai');
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            // Animate statistics on load
            animateValue('totalProperties', 0, 245, 2000);
            animateValue('aiRecommendations', 0, 1234, 2000);
            animateValue('chatInteractions', 0, 567, 2000);
            animateValue('avgAccuracy', 0, 94, 2000, '%');
        });

        function animateValue(id, start, end, duration, suffix = '') {
            const element = document.getElementById(id);
            const range = end - start;
            const increment = range / (duration / 10);
            let current = start;
            
            const timer = setInterval(() => {
                current += increment;
                if ((increment > 0 && current >= end) || (increment < 0 && current <= end)) {
                    element.textContent = end + suffix;
                    clearInterval(timer);
                } else {
                    element.textContent = Math.round(current) + suffix;
                }
            }, 10);
        }
    </script>
</body>
</html>