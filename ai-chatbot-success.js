import { GoogleGenerativeAI } from "@google/generative-ai";
import dotenv from 'dotenv';

// Load environment variables
dotenv.config();

// Initialize Google AI with API key from environment
const genAI = new GoogleGenerativeAI(process.env.GEMINI_API_KEY || 'YOUR_API_KEY_HERE');

// Main function to generate AI response
async function main(message = 'Explain how AI works in a few words') {
  try {
    console.log('🤖 Sending message to AI:', message);
    
    // Get the model with correct configuration
    const model = genAI.getGenerativeModel({ 
      model: "gemini-1.5-flash",
      generationConfig: {
        temperature: 0.7,
        topK: 40,
        topP: 0.95,
        maxOutputTokens: 8192,
      }
    });
    
    // Generate content
    const result = await model.generateContent(message);
    
    const response = result.response;
    const text = response.text();
    
    console.log('✅ AI Response:', text);
    return text;
  } catch (error) {
    console.error('❌ Error generating AI response:', error.message);
    return 'Sorry, I encountered an error. Please try again.';
  }
}

// Export for use in other modules
export { main as generateAIResponse };

// Run if called directly
if (import.meta.url === `file://${process.argv[1]}`) {
  await main();
}
