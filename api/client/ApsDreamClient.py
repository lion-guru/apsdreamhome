"""
APS Dream Home API Client (Python)
"""

import json
import requests
from typing import Dict, List, Optional, Union, Any
from urllib.parse import urljoin, urlencode

class ApsDreamClient:
    """
    A client for interacting with the APS Dream Home API.
    
    Args:
        base_url (str): Base URL of the API (e.g., 'http://localhost/apsdreamhomefinal/api/v1')
        api_key (str, optional): API key for authentication
        debug (bool, optional): Enable debug logging
        **session_kwargs: Additional arguments to pass to the requests.Session
    """
    
    def __init__(self, base_url: str, api_key: str = None, debug: bool = False, **session_kwargs):
        self.base_url = base_url.rstrip('/')
        self.api_key = api_key
        self.debug = debug
        self.token = None
        self.session = requests.Session()
        
        # Update session with any additional kwargs
        for key, value in session_kwargs.items():
            setattr(self.session, key, value)
            
        # Set default headers
        self.session.headers.update({
            'Accept': 'application/json',
            'Content-Type': 'application/json',
        })
        
        # Set auth header if API key is provided
        if self.api_key:
            self.session.headers['Authorization'] = f'Bearer {self.api_key}'
    
    def _request(self, method: str, endpoint: str, data: dict = None, 
                params: dict = None, requires_auth: bool = True) -> dict:
        """
        Make an HTTP request to the API.
        
        Args:
            method (str): HTTP method (GET, POST, PUT, DELETE, etc.)
            endpoint (str): API endpoint (e.g., 'properties/123')
            data (dict, optional): Request body data
            params (dict, optional): Query parameters
            requires_auth (bool, optional): Whether authentication is required
            
        Returns:
            dict: Response data as a dictionary
            
        Raises:
            requests.exceptions.RequestException: If the request fails
            ValueError: If the response contains an error
        """
        url = urljoin(f"{self.base_url}/", endpoint.lstrip('/'))
        
        if self.debug:
            print(f"[{method.upper()}] {url}")
            if params:
                print(f"Params: {params}")
            if data:
                print(f"Data: {data}")
        
        # Set auth header if token is available
        headers = {}
        if requires_auth:
            if self.token:
                headers['Authorization'] = f'Bearer {self.token}'
            elif not self.api_key:
                raise ValueError("Authentication required but no API key or token provided")
        
        try:
            response = self.session.request(
                method=method.upper(),
                url=url,
                json=data,
                params=params,
                headers=headers
            )
            
            if self.debug:
                print(f"Response [{response.status_code}]: {response.text[:500]}...")
            
            response.raise_for_status()
            
            # Try to parse JSON response
            try:
                return response.json()
            except ValueError:
                return response.text
                
        except requests.exceptions.RequestException as e:
            if self.debug:
                print(f"Request failed: {str(e)}")
            raise

    # ===== AUTHENTICATION =====
    
    def login(self, email: str, password: str) -> dict:
        """
        Login with email and password.
        
        Args:
            email (str): User email
            password (str): User password
            
        Returns:
            dict: Response data with user and token
        """
        data = {
            'email': email,
            'password': password
        }
        response = self._request('POST', 'auth/login', data=data, requires_auth=False)
        
        # Store the token if present
        if 'token' in response:
            self.token = response['token']
            self.session.headers['Authorization'] = f'Bearer {self.token}'
            
        return response
    
    def logout(self) -> dict:
        """
        Logout (invalidate current token).
        
        Returns:
            dict: Response data
        """
        response = self._request('POST', 'auth/logout')
        self.token = None
        if 'Authorization' in self.session.headers:
            del self.session.headers['Authorization']
        return response
    
    # ===== PROFILE =====
    
    def get_profile(self) -> dict:
        """
        Get current user profile.
        
        Returns:
            dict: User profile data
        """
        return self._request('GET', 'profile')
    
    def update_profile(self, profile_data: dict) -> dict:
        """
        Update current user profile.
        
        Args:
            profile_data (dict): Profile data to update
            
        Returns:
            dict: Updated profile data
        """
        return self._request('PUT', 'profile', data=profile_data)
    
    # ===== PROPERTIES =====
    
    def get_properties(self, **filters) -> List[dict]:
        """
        List properties with optional filters.
        
        Args:
            **filters: Filter criteria (e.g., status='available')
            
        Returns:
            List[dict]: List of properties
        """
        return self._request('GET', 'properties', params=filters, requires_auth=False)
    
    def get_property(self, property_id: Union[str, int]) -> dict:
        """
        Get a single property by ID.
        
        Args:
            property_id (str|int): Property ID
            
        Returns:
            dict: Property data
        """
        return self._request('GET', f'properties/{property_id}', requires_auth=False)
    
    def create_property(self, property_data: dict) -> dict:
        """
        Create a new property.
        
        Args:
            property_data (dict): Property data
            
        Returns:
            dict: Created property data
        """
        return self._request('POST', 'properties', data=property_data)
    
    def update_property(self, property_id: Union[str, int], property_data: dict) -> dict:
        """
        Update a property.
        
        Args:
            property_id (str|int): Property ID
            property_data (dict): Property data to update
            
        Returns:
            dict: Updated property data
        """
        return self._request('PUT', f'properties/{property_id}', data=property_data)
    
    def delete_property(self, property_id: Union[str, int]) -> dict:
        """
        Delete a property.
        
        Args:
            property_id (str|int): Property ID
            
        Returns:
            dict: Deletion result
        """
        return self._request('DELETE', f'properties/{property_id}')
    
    # ===== USERS (Admin only) =====
    
    def get_users(self, **filters) -> List[dict]:
        """
        List users (admin only).
        
        Args:
            **filters: Filter criteria
            
        Returns:
            List[dict]: List of users
        """
        return self._request('GET', 'users', params=filters)
    
    def get_user(self, user_id: Union[str, int]) -> dict:
        """
        Get a single user by ID (admin only).
        
        Args:
            user_id (str|int): User ID
            
        Returns:
            dict: User data
        """
        return self._request('GET', f'users/{user_id}')
    
    def create_user(self, user_data: dict) -> dict:
        """
        Create a new user (admin only).
        
        Args:
            user_data (dict): User data
            
        Returns:
            dict: Created user data
        """
        return self._request('POST', 'users', data=user_data)
    
    def update_user(self, user_id: Union[str, int], user_data: dict) -> dict:
        """
        Update a user (admin only).
        
        Args:
            user_id (str|int): User ID
            user_data (dict): User data to update
            
        Returns:
            dict: Updated user data
        """
        return self._request('PUT', f'users/{user_id}', data=user_data)
    
    def delete_user(self, user_id: Union[str, int]) -> dict:
        """
        Delete a user (admin only).
        
        Args:
            user_id (str|int): User ID
            
        Returns:
            dict: Deletion result
        """
        return self._request('DELETE', f'users/{user_id}')


# Example usage
if __name__ == '__main__':
    # Initialize the client
    client = ApsDreamClient(
        base_url='http://localhost/apsdreamhomefinal/api/v1',
        debug=True
    )
    
    try:
        # Login
        login_response = client.login('admin@example.com', 'password')
        print("Logged in as:", login_response.get('user', {}).get('email'))
        
        # Get properties
        properties = client.get_properties(status='available')
        print(f"Found {len(properties)} available properties")
        
        # Get current profile
        profile = client.get_profile()
        print("Profile:", profile)
        
        # Logout
        logout_response = client.logout()
        print("Logged out")
        
    except Exception as e:
        print(f"Error: {str(e)}")
