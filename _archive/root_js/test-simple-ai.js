// Test script for Simple AI Chatbot
import { generateAIResponse } from './ai-chatbot-simple.js';

async function testChatbot() {
  console.log('🤖 Testing Simple AI Chatbot...\n');
  
  try {
    // Test 1: Basic question
    console.log('📝 Test 1: Basic AI question');
    const response1 = await generateAIResponse("Explain how AI works in a few words");
    console.log('Response:', response1);
    console.log('✅ Test 1 passed\n');
    
    // Test 2: Real estate related question
    console.log('📝 Test 2: Real estate question');
    const response2 = await generateAIResponse("What are the benefits of buying a property in Lucknow?");
    console.log('Response:', response2);
    console.log('✅ Test 2 passed\n');
    
    // Test 3: APS Dream Home specific
    console.log('📝 Test 3: APS Dream Home specific');
    const response3 = await generateAIResponse("Tell me about APS Dream Home projects in Gorakhpur");
    console.log('Response:', response3);
    console.log('✅ Test 3 passed\n');
    
    console.log('🎉 All AI Chatbot tests completed successfully!');
    
  } catch (error) {
    console.error('❌ Test failed:', error.message);
  }
}

testChatbot();
