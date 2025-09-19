# APS Dream Home API Testing

This directory contains automated tests for the APS Dream Home API client libraries in multiple programming languages.

## Prerequisites

1. **PHP 7.4+**
   - Required for running PHP client tests
   - [Download PHP](https://windows.php.net/download/)

2. **Node.js 14+**
   - Required for running JavaScript client tests
   - [Download Node.js](https://nodejs.org/)

3. **Python 3.8+**
   - Required for running Python client tests
   - [Download Python](https://www.python.org/downloads/)

4. **XAMPP / WAMP / LAMP**
   - Required for running the API locally
   - [Download XAMPP](https://www.apachefriends.org/download.html)

## Setup

1. **Install Dependencies**
   ```bash
   # Run the setup script
   setup_dependencies.bat
   ```

2. **Start Your Local Server**
   - Make sure XAMPP/WAMP/LAMP is running
   - Ensure the APS Dream Home project is accessible at `http://localhost/apsdreamhomefinal`

## Running Tests

### Run All Tests

```bash
run_tests.bat
```

### Run Individual Tests

#### PHP Client Tests
```bash
php test_php_client.php
```

#### JavaScript Client Tests
```bash
node test_js_client.js
```

#### Python Client Tests
```bash
python test_python_client.py
```

## Test Cases

1. **Authentication**
   - Login with valid credentials
   - Get user profile
   - Logout

2. **Property Management**
   - List available properties
   - Get property details

## Troubleshooting

### Common Issues

1. **Connection Refused**
   - Make sure your local web server is running
   - Verify the API URL in the test files matches your setup

2. **Authentication Failed**
   - Check the test email and password in the test files
   - Make sure the user exists in the database

3. **Module Not Found**
   - Run `setup_dependencies.bat` to install required packages
   - Make sure Node.js and Python are properly installed

### Debugging

Set `debug: true` in the client configuration to see detailed request/response information.

## Test Data

The tests use the following test account:
- Email: `admin@example.com`
- Password: `admin123`

Make sure this user exists in your database before running the tests.

## License

This test suite is part of the APS Dream Home project.
