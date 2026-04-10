import OpenAI from 'openai';
import dotenv from 'dotenv';

// Load environment variables
dotenv.config();

// Initialize OpenAI with API key from environment
const openai = new OpenAI({
  apiKey: process.env.OPENAI_API_KEY || 'YOUR_OPENAI_API_KEY_HERE',
});

// Main function to generate AI response
async function main(message = 'Explain how AI works in a few words') {
  try {
    console.log('🤖 Sending message to OpenAI:', message);
    
    // Use GPT-4 model for best results
    const response = await openai.chat.completions.create({
      model: 'gpt-4o-mini',
      messages: [
        {
          role: 'user',
          content: message
        }
      ],
      max_tokens: 1000,
      temperature: 0.7,
    });
    
    const text = response.choices[0].message.content;
    
    console.log('✅ OpenAI Response:', text);
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
