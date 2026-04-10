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
    
    // Try to get model - if this fails, we'll know the issue
    console.log('🔍 Getting AI model...');
    const model = genAI.getGenerativeModel();
    
    if (!model) {
      console.error('❌ Model is null/undefined');
      throw new Error('Failed to initialize AI model - model is null');
    }
    
    console.log('✅ Model created successfully');
    
    // Generate content
    console.log('📝 Generating content...');
    const result = await model.generateContent(message);
    
    if (!result) {
      console.error('❌ No result from AI');
      throw new Error('No result received from AI');
    }
    
    if (!result.response) {
      console.error('❌ No response in result');
      throw new Error('No response in AI result');
    }
    
    console.log('✅ Response received from AI');
    
    const text = result.response.text();
    
    console.log('✅ AI Response:', text);
    return text;
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
