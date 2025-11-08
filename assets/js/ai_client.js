// APS Dream Home - JavaScript AI Integration with OpenRouter
// Advanced AI client using OpenRouter API for real-time interactions

class APS_AI_Client {
    constructor(apiKey = null) {
        this.apiKey = apiKey || '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';
        this.baseURL = "https://openrouter.ai/api/v1";
        this.model = "qwen/qwen3-coder:free";
        this.siteURL = window.location.origin || "http://localhost";
        this.siteName = "APS Dream Home";
    }

    async generateResponse(messages, options = {}) {
        try {
            const response = await fetch(`${this.baseURL}/chat/completions`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${this.apiKey}`,
                    'HTTP-Referer': this.siteURL,
                    'X-Title': this.siteName,
                },
                body: JSON.stringify({
                    model: options.model || this.model,
                    messages: messages,
                    max_tokens: options.max_tokens || 1000,
                    temperature: options.temperature || 0.7,
                    top_p: options.top_p || 1,
                    frequency_penalty: options.frequency_penalty || 0,
                    presence_penalty: options.presence_penalty || 0,
                })
            });

            if (!response.ok) {
                throw new Error(`OpenRouter API error: ${response.status}`);
            }

            const data = await response.json();
            return {
                success: true,
                response: data.choices[0].message.content,
                usage: data.usage,
                model: data.model,
                finish_reason: data.choices[0].finish_reason
            };

        } catch (error) {
            console.error('AI API Error:', error);
            return {
                success: false,
                error: error.message,
                response: "I'm sorry, I'm having trouble connecting to my AI brain right now. Please try again in a moment! 🤖"
            };
        }
    }

    async generatePropertyDescription(propertyData) {
        const messages = [
            {
                role: "system",
                content: "You are an expert real estate copywriter for APS Dream Home. Create compelling, professional property descriptions that highlight key features and appeal to potential buyers."
            },
            {
                role: "user",
                content: `Create an engaging property description for:
Type: ${propertyData.type}
Location: ${propertyData.location}
Price: ₹${propertyData.price}
Bedrooms: ${propertyData.bedrooms}
Area: ${propertyData.area} sq ft
Features: ${propertyData.features.join(', ')}

Make it compelling, highlight lifestyle benefits, and encourage inquiries.`
            }
        ];

        return await this.generateResponse(messages, { max_tokens: 300 });
    }

    async estimatePropertyValue(propertyData) {
        const messages = [
            {
                role: "system",
                content: "You are a real estate valuation expert. Provide accurate market value estimates based on current market trends and property characteristics."
            },
            {
                role: "user",
                content: `Analyze this property for market valuation:
Location: ${propertyData.location}
Property Type: ${propertyData.type}
Area: ${propertyData.area} sq ft
Bedrooms: ${propertyData.bedrooms}
Bathrooms: ${propertyData.bathrooms}
Year Built: ${propertyData.year_built || '2020'}
Condition: ${propertyData.condition}
Nearby Amenities: ${propertyData.amenities?.join(', ') || 'Standard amenities'}

Provide a realistic price range with detailed justification based on current market analysis.`
            }
        ];

        return await this.generateResponse(messages, { max_tokens: 400 });
    }

    async generateChatbotResponse(userQuery, context = []) {
        const systemPrompt = `You are APS Assistant, an intelligent AI helper for APS Dream Home - a comprehensive real estate management platform.

Your role:
- Help with PHP development, database management, and web development
- Assist with real estate business processes and workflows
- Provide technical guidance for deployment and maintenance
- Learn from user interactions to provide better assistance
- Be professional, helpful, and proactive

Guidelines:
- Always learn from interactions to improve responses
- Provide actionable solutions with code examples when relevant
- Ask clarifying questions when information is insufficient
- Follow up on important tasks and suggestions
- Maintain context awareness across conversations`;

        const messages = [
            {
                role: "system",
                content: systemPrompt
            }
        ];

        // Add context if available
        if (context.length > 0) {
            messages.push({
                role: "assistant",
                content: `Previous conversation context: ${context.slice(-3).map(c => `User: ${c.user}\nAssistant: ${c.ai}`).join('\n\n')}`
            });
        }

        messages.push({
            role: "user",
            content: userQuery
        });

        return await this.generateResponse(messages, {
            max_tokens: 800,
            temperature: 0.7
        });
    }

    async analyzeCode(codeSnippet, language = 'php') {
        const messages = [
            {
                role: "system",
                content: `You are a senior software engineer and code reviewer. Analyze the provided ${language.toUpperCase()} code for:
1. Code quality and best practices
2. Potential bugs or issues
3. Performance optimizations
4. Security vulnerabilities
5. Maintainability improvements
6. Documentation needs

Provide specific, actionable feedback with examples.`
            },
            {
                role: "user",
                content: `Please analyze this ${language.toUpperCase()} code:

${codeSnippet}

Provide a comprehensive code review with specific recommendations.`
            }
        ];

        return await this.generateResponse(messages, { max_tokens: 600 });
    }

    async generateCodeSnippet(requirements, language = 'php') {
        const messages = [
            {
                role: "system",
                content: `You are a senior ${language.toUpperCase()} developer. Generate clean, efficient, well-documented code that follows best practices and PSR standards for PHP.`
            },
            {
                role: "user",
                content: `Generate ${language.toUpperCase()} code for the following requirements:

${requirements}

Include:
- Proper error handling
- Input validation
- Security considerations
- Documentation/comments
- Best practices implementation`
            }
        ];

        return await this.generateResponse(messages, { max_tokens: 1000 });
    }

    async suggestImprovements(currentState, goals) {
        const messages = [
            {
                role: "system",
                content: "You are a strategic technology consultant helping improve development workflows and system architecture. Provide actionable recommendations with implementation priorities."
            },
            {
                role: "user",
                content: `Current system state: ${currentState}

Goals: ${goals}

Provide strategic recommendations for improvement with:
1. Priority levels (High/Medium/Low)
2. Implementation complexity
3. Expected impact
4. Required resources
5. Timeline estimates`
            }
        ];

        return await this.generateResponse(messages, { max_tokens: 600 });
    }

    // Learning and memory functions
    async storeInteraction(userInput, aiResponse, context = {}) {
        try {
            const response = await fetch('api/ai_learn_interaction.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    user_input: userInput,
                    ai_response: aiResponse,
                    context: context,
                    source: 'javascript_client'
                })
            });

            return await response.json();
        } catch (error) {
            console.error('Learning storage error:', error);
            return { success: false, error: error.message };
        }
    }

    async getPersonalizedRecommendations() {
        try {
            const response = await fetch('api/ai_recommendations.php');
            return await response.json();
        } catch (error) {
            console.error('Recommendations error:', error);
            return { error: 'Unable to get recommendations' };
        }
    }

    async getAgentStatus() {
        try {
            const response = await fetch('api/ai_agent_status.php');
            return await response.json();
        } catch (error) {
            console.error('Agent status error:', error);
            return { error: 'Unable to get agent status' };
        }
    }
}

// Global AI client instance
const apsAI = new APS_AI_Client();

// Enhanced chat functions for the dashboard
async function sendEnhancedMessage(message, context = []) {
    try {
        // Show typing indicator
        showTypingIndicator();

        // Get AI response
        const result = await apsAI.generateChatbotResponse(message, context);

        // Hide typing indicator
        hideTypingIndicator();

        if (result.success) {
            // Store interaction for learning
            await apsAI.storeInteraction(message, result.response, {
                context: context,
                model: result.model,
                tokens: result.usage
            });

            // Add to chat history
            chatHistory.push({
                user: message,
                ai: result.response,
                timestamp: new Date().toISOString(),
                model: result.model,
                tokens: result.usage
            });

            return result.response;
        } else {
            return result.error || "I'm having trouble responding right now. Please try again.";
        }

    } catch (error) {
        hideTypingIndicator();
        console.error('Enhanced chat error:', error);
        return "Sorry, I encountered an error. Please try again.";
    }
}

// Property analysis functions
async function analyzePropertyWithAI(propertyData) {
    try {
        const [descriptionResult, valuationResult] = await Promise.all([
            apsAI.generatePropertyDescription(propertyData),
            apsAI.estimatePropertyValue(propertyData)
        ]);

        return {
            description: descriptionResult.success ? descriptionResult.response : descriptionResult.error,
            valuation: valuationResult.success ? valuationResult.response : valuationResult.error
        };
    } catch (error) {
        console.error('Property analysis error:', error);
        return {
            description: 'Unable to generate description',
            valuation: 'Unable to generate valuation'
        };
    }
}

// Code analysis functions
async function analyzeCodeWithAI(code, language = 'php') {
    try {
        const result = await apsAI.analyzeCode(code, language);
        return result.success ? result.response : result.error;
    } catch (error) {
        console.error('Code analysis error:', error);
        return 'Unable to analyze code at this time';
    }
}

// Improvement suggestions
async function getImprovementSuggestions(currentState, goals) {
    try {
        const result = await apsAI.suggestImprovements(currentState, goals);
        return result.success ? result.response : result.error;
    } catch (error) {
        console.error('Improvement suggestions error:', error);
        return 'Unable to generate suggestions at this time';
    }
}

// Real-time AI features for the dashboard
class AIDashboardFeatures {
    constructor() {
        this.aiClient = apsAI;
        this.isInitialized = false;
    }

    async initialize() {
        try {
            // Get AI agent status
            const status = await this.aiClient.getAgentStatus();
            if (status.error) {
                console.warn('AI Agent status unavailable:', status.error);
            } else {
                this.updateAgentStatus(status);
            }

            // Get personalized recommendations
            const recommendations = await this.aiClient.getPersonalizedRecommendations();
            if (recommendations.error) {
                console.warn('Recommendations unavailable:', recommendations.error);
            } else {
                this.displayRecommendations(recommendations);
            }

            this.isInitialized = true;
            return true;

        } catch (error) {
            console.error('Dashboard initialization error:', error);
            return false;
        }
    }

    updateAgentStatus(status) {
        // Update UI elements with agent status
        const moodElement = document.querySelector('.ai-agent-status .mood-indicator');
        if (moodElement && status.current_mood) {
            const moodBadge = document.createElement('span');
            moodBadge.className = 'badge bg-info';
            moodBadge.textContent = `🎭 ${status.current_mood}`;
            moodElement.appendChild(moodBadge);
        }
    }

    displayRecommendations(recommendations) {
        // Display personalized recommendations in the UI
        console.log('Personalized recommendations:', recommendations);
    }

    async generateLiveSuggestions(userActivity) {
        try {
            const suggestions = await getImprovementSuggestions(
                `User is currently working on: ${userActivity}`,
                "Provide immediate actionable suggestions for current task"
            );

            this.displayLiveSuggestions(suggestions);
        } catch (error) {
            console.error('Live suggestions error:', error);
        }
    }

    displayLiveSuggestions(suggestions) {
        // Show real-time suggestions to user
        console.log('Live suggestions:', suggestions);
    }
}

// Initialize dashboard features when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    const dashboardFeatures = new AIDashboardFeatures();

    // Initialize after a short delay to ensure all elements are loaded
    setTimeout(() => {
        dashboardFeatures.initialize();
    }, 1000);

    // Track user activity for live suggestions
    let userActivity = [];
    const activityTracker = setInterval(() => {
        const currentPage = window.location.pathname;
        const currentTime = new Date().toLocaleTimeString();

        userActivity.push(`${currentTime}: ${currentPage}`);

        // Keep only last 10 activities
        if (userActivity.length > 10) {
            userActivity.shift();
        }

        // Generate live suggestions if user seems stuck
        if (userActivity.length >= 5) {
            const lastActivities = userActivity.slice(-3);
            if (lastActivities.every(activity => activity.includes(currentPage))) {
                dashboardFeatures.generateLiveSuggestions(`User spending time on ${currentPage}`);
            }
        }
    }, 30000); // Check every 30 seconds
});

// Export for use in other scripts
window.APS_AI_Client = APS_AI_Client;
window.apsAI = apsAI;
