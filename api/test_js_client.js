/**
 * Test Script for APS Dream Home JavaScript Client
 * Run with: node test_js_client.js
 */

const ApsDreamClient = require('./client/ApsDreamClient');

// Configuration
const config = {
    baseUrl: 'http://localhost/apsdreamhome/api/v1',
    testEmail: 'admin@example.com',
    testPassword: 'admin123',
    debug: true
};

async function runTests() {
    console.log('=== Starting APS Dream Home API Tests ===\n');
    
    // Initialize client
    const client = new ApsDreamClient(config.baseUrl, null, { debug: config.debug });
    
    try {
        // 1. Test Login
        console.log('1. Testing login...');
        const login = await client.login(config.testEmail, config.testPassword);
        console.log(`✅ Logged in as: ${login.user.email}`);
        
        // 2. Test Get Profile
        console.log('\n2. Testing get profile...');
        const profile = await client.getProfile();
        console.log(`✅ Profile retrieved. Welcome, ${profile.first_name}!`);
        
        // 3. Test Get Properties
        console.log('\n3. Testing get properties...');
        const properties = await client.getProperties({ status: 'available' });
        console.log(`✅ Found ${properties.length} available properties`);
        
        if (properties.length > 0) {
            const firstProperty = properties[0];
            console.log(`   - First property: ${firstProperty.title} ($${firstProperty.price.toLocaleString()})`);
            
            // 4. Test Get Single Property
            console.log('\n4. Testing get single property...');
            const property = await client.getProperty(firstProperty.id);
            console.log(`✅ Retrieved property: ${property.title}`);
        }
        
        // 5. Test Logout
        console.log('\n5. Testing logout...');
        await client.logout();
        console.log('✅ Logged out successfully');
        
        console.log('\n=== All tests completed successfully! ===\n');
        
    } catch (error) {
        console.error('\n❌ Error:', error.message);
        if (error.response) {
            console.error('   - Status:', error.response.status);
            console.error('   - Data:', error.response.data);
        }
        process.exit(1);
    }
}

// Run the tests
runTests();
