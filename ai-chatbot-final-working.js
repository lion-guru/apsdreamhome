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
    
    // Use the default model without specifying version
    const model = genAI.getGenerativeModel();
    
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
