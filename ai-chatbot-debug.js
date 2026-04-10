import { GoogleGenerativeAI } from "@google/generative-ai";
import dotenv from 'dotenv';

// Load environment variables
dotenv.config();

console.log('🔑 API Key loaded:', process.env.GEMINI_API_KEY ? '✅ Found' : '❌ Missing');

// Initialize Google AI with API key from environment
const genAI = new GoogleGenerativeAI(process.env.GEMINI_API_KEY || 'YOUR_API_KEY_HERE');

// Main function to generate AI response
async function main(message = 'Explain how AI works in a few words') {
  try {
    console.log('🤖 Sending message to AI:', message);
    
    // Debug: Check if genAI is properly initialized
    console.log('🔍 GenAI instance:', genAI ? '✅ Created' : '❌ Failed');
    
    // Try to get available models first
    try {
      console.log('🔍 Attempting to get default model...');
      const model = genAI.getGenerativeModel();
      console.log('📝 Model created:', model ? '✅ Success' : '❌ Failed');
      
      if (!model) {
        throw new Error('Failed to create model instance');
      }
      
      // Generate content
      const result = await model.generateContent(message);
      console.log('📄 Content generated:', result ? '✅ Success' : '❌ Failed');
      
      const response = result.response;
      const text = response.text();
      
      console.log('✅ AI Response:', text);
      return text;
    } catch (modelError) {
      console.error('❌ Model creation failed:', modelError.message);
      throw modelError;
    }
  } catch (error) {
    console.error('❌ Error generating AI response:', error.message);
    console.error('🔍 Full error details:', error);
    return 'Sorry, I encountered an error. Please try again.';
  }
}

// Export for use in other modules
export { main as generateAIResponse };

// Run if called directly
if (import.meta.url === `file://${process.argv[1]}`) {
  await main();
}
