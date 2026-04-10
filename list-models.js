import { GoogleGenAI } from '@google/genai';
import dotenv from 'dotenv';

// Load environment variables
dotenv.config();

// Initialize Google AI with API key from environment
const ai = new GoogleGenAI({
  apiKey: process.env.GEMINI_API_KEY || 'YOUR_API_KEY_HERE',
});

async function listModels() {
  try {
    console.log('🔍 Fetching available Gemini models...\n');

    const models = await ai.getGenerativeModel({ model: 'gemini-1.5-flash' });

    console.log('📋 Available Models:');
    console.log('==================');

    models.forEach(model => {
      console.log(`📝 Name: ${model.name}`);
      console.log(`   📄 Display Name: ${model.displayName}`);
      console.log(`   📝 Description: ${model.description}`);
      console.log(`   🎯 Supported Methods: ${model.supportedGenerationMethods?.join(', ') || 'N/A'}`);
      console.log('---');
    });

    // Filter for text generation models
    const textModels = models.filter(model => model.supportedGenerationMethods?.includes('generateContent'));

    console.log('\n🎯 Text Generation Models:');
    console.log('=============================');
    textModels.forEach(model => {
      console.log(`✅ ${model.name} - ${model.displayName}`);
    });

    return models;
  } catch (error) {
    console.error('❌ Error listing models:', error.message);
    return [];
  }
}

// Run the function
listModels();
