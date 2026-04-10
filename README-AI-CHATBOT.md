# AI Chatbot Integration Guide

## 🤖 Google AI Chatbot Setup

### **Files Created:**
1. `ai-chatbot.js` - Main AI chatbot module
2. `package.json` - Updated with Google AI dependencies
3. `.env.example` - Environment configuration template

### **Installation & Setup:**

#### 1. Install Dependencies
```bash
npm install @google/genai dotenv
```

#### 2. Set Up Environment
```bash
# Copy environment template
cp .env.example .env

# Edit .env file and add your Google AI API key
GOOGLE_AI_API_KEY=your_actual_api_key_here
```

#### 3. Run the Chatbot
```bash
# Test the AI chatbot
npm run ai

# Or run directly
node ai-chatbot.js
```

### **Code Structure:**

#### **ai-chatbot.js**
```javascript
import { GoogleGenAI } from "@google/genai";

// Initialize Google AI with API key from environment
const ai = new GoogleGenAI({
  apiKey: process.env.GOOGLE_AI_API_KEY || 'YOUR_API_KEY_HERE'
});

async function main() {
  try {
    const response = await ai.models.generateContent({
      model: "gemini-2.0-flash-exp",
      contents: "Explain how AI works in a few words",
    });
    
    console.log(response.text);
    return response.text;
  } catch (error) {
    console.error('Error generating AI response:', error);
    return 'Sorry, I encountered an error. Please try again.';
  }
}

// Export for use in other modules
export { main as generateAIResponse };

// Run if called directly
if (import.meta.url === `file://${process.argv[1]}`) {
  await main();
}
```

### **Integration with APS Dream Home:**

#### **Option 1: Server-side Integration**
```php
<?php
// In your PHP controller
exec('node ai-chatbot.js 2>&1', $output, $return_code);
$ai_response = implode("\n", $output);
?>
```

#### **Option 2: API Endpoint**
```javascript
// Create API endpoint
app.post('/api/ai/chat', async (req, res) => {
  const { message } = req.body;
  const response = await generateAIResponse(message);
  res.json({ response });
});
```

#### **Option 3: Frontend Integration**
```javascript
// In your frontend JavaScript
import { generateAIResponse } from './ai-chatbot.js';

async function sendMessage(message) {
  const response = await generateAIResponse(message);
  console.log('AI Response:', response);
}
```

### **Features:**
- ✅ **Google Gemini 2.0 Flash** integration
- ✅ **Environment variable** configuration
- ✅ **Error handling** and fallbacks
- ✅ **ES6 modules** support
- ✅ **Exportable functions** for integration

### **Required API Key:**
Get your Google AI API key from:
1. Visit [Google AI Studio](https://aistudio.google.com/)
2. Create a new API key
3. Add it to your `.env` file

### **Security Notes:**
- 🔒 **Never commit** `.env` file to version control
- 🔒 **Use environment variables** in production
- 🔒 **Validate user input** before sending to AI
- 🔒 **Rate limit** API calls to prevent abuse

### **Next Steps:**
1. Get Google AI API key
2. Configure environment variables
3. Test the basic functionality
4. Integrate with your existing chatbot UI
5. Add conversation history and context

---

**🚀 Your APS Dream Home now has AI-powered chatbot capabilities!**
