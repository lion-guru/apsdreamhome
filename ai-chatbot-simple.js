import { GoogleGenAI } from "@google/genai";
import dotenv from 'dotenv';

// Load environment variables
dotenv.config();

// Initialize Google AI with API key from environment
const ai = new GoogleGenAI({
  apiKey: process.env.GEMINI_API_KEY || 'YOUR_API_KEY_HERE',
});

// Main function to generate AI response
async function main(message = 'Explain how AI works in a few words') {
  try {
    // Use the correct method for Google AI
    const response = await ai.generateContent({
      contents: message,
    });

    console.log(response.response.text());
    return response.response.text();
  } catch (error) {
    console.error('Error generating AI response:', error.message);
    return 'Sorry, I encountered an error. Please try again.';
  }
}

// Export for use in other modules
export { main as generateAIResponse };

// Run if called directly
if (import.meta.url === `file://${process.argv[1]}`) {
  await main();
}
