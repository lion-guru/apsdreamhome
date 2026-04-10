import { GoogleGenAI } from '@google/genai';
import dotenv from 'dotenv';

// Load environment variables
dotenv.config();

// The client gets the API key from the environment variable `GEMINI_API_KEY`
const ai = new GoogleGenAI({
  apiKey: process.env.GEMINI_API_KEY,
});

// Main function to generate AI response
async function main(message = 'Explain how AI works in a few words') {
  try {
    console.log('🤖 Sending message to AI:', message);

    const response = await ai.models.generateContent({
      model: 'gemini-3-flash-preview',
      contents: message,
    });

    console.log('✅ AI Response:', response.text);
    return response.text;
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
