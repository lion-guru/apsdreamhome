"""
Test Script for APS Dream Home Python Client
Run with: python test_python_client.py
"""
import sys
import os

# Add the current directory to the Python path
sys.path.append(os.path.dirname(os.path.abspath(__file__)))

from client.ApsDreamClient import ApsDreamClient

# Configuration
CONFIG = {
    'base_url': 'http://localhost/apsdreamhome/api/v1',
    'test_email': 'admin@example.com',
    'test_password': 'admin123',
    'debug': True
}

def print_success(message):
    """Print a success message with a checkmark."""
    print(f"✅ {message}")

def print_error(message):
    """Print an error message with a cross."""
    print(f"❌ {message}")

async def run_tests():
    """Run all API tests."""
    print("=== Starting APS Dream Home API Tests ===\n")
    
    # Initialize client
    client = ApsDreamClient(
        base_url=CONFIG['base_url'],
        debug=CONFIG['debug']
    )
    
    try:
        # 1. Test Login
        print("1. Testing login...")
        login = client.login(CONFIG['test_email'], CONFIG['test_password'])
        print_success(f"Logged in as: {login['user']['email']}")
        
        # 2. Test Get Profile
        print("\n2. Testing get profile...")
        profile = client.get_profile()
        print_success(f"Profile retrieved. Welcome, {profile['first_name']}!")
        
        # 3. Test Get Properties
        print("\n3. Testing get properties...")
        properties = client.get_properties(status='available')
        print_success(f"Found {len(properties)} available properties")
        
        if properties:
            first_property = properties[0]
            print(f"   - First property: {first_property['title']} (${first_property['price']:,})")
            
            # 4. Test Get Single Property
            print("\n4. Testing get single property...")
            property_data = client.get_property(first_property['id'])
            print_success(f"Retrieved property: {property_data['title']}")
        
        # 5. Test Logout
        print("\n5. Testing logout...")
        client.logout()
        print_success("Logged out successfully")
        
        print("\n=== All tests completed successfully! ===\n")
        
    except Exception as e:
        print_error(f"Error: {str(e)}")
        if hasattr(e, 'response') and hasattr(e.response, 'status_code'):
            print(f"   - Status: {e.response.status_code}")
            try:
                print(f"   - Data: {e.response.json()}")
            except:
                print(f"   - Response: {e.response.text}")
        sys.exit(1)

if __name__ == "__main__":
    import asyncio
    asyncio.run(run_tests())
