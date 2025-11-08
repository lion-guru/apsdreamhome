# APS Dream Home API

This directory contains the API implementation and client libraries for the APS Dream Home platform.

## Directory Structure

```
api/
├── client/                 # Client libraries
│   ├── ApsDreamClient.php   # PHP client
│   ├── ApsDreamClient.js    # JavaScript/Node.js client
│   └── ApsDreamClient.py    # Python client
├── v1/                      # API version 1
│   ├── index.php            # Main API entry point
│   ├── endpoints/           # API endpoint implementations
│   └── README.md            # API documentation
├── tests/                   # Test files
│   ├── test_php_client.php  # PHP client tests
│   ├── test_js_client.js    # JavaScript client tests
│   └── test_python_client.py # Python client tests
├── run_tests.bat            # Run all tests
├── setup_dependencies.bat    # Setup script for dependencies
├── package.json             # Node.js dependencies
├── requirements.txt         # Python dependencies
└── TESTING.md               # Testing documentation
```

## Getting Started

### Prerequisites

- PHP 7.4+
- Node.js 14+
- Python 3.8+
- XAMPP/WAMP/LAMP

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/yourusername/aps-dream-home.git
   cd aps-dream-home/api
   ```

2. **Set up dependencies**
   ```bash
   # Install Node.js dependencies
   npm install
   
   # Install Python dependencies
   pip install -r requirements.txt
   ```

3. **Configure the API**
   - Copy `.env.example` to `.env` and update the configuration
   - Make sure your database is properly configured

## Running the API

1. **Start your local server**
   - Make sure Apache/NGINX and MySQL are running
   - The API will be available at `http://localhost/apsdreamhome/api/v1/`

2. **Run the tests**
   ```bash
   # Run all tests
   .\run_tests.bat
   
   # Or run tests individually
   php test_php_client.php
   node test_js_client.js
   python test_python_client.py
   ```

## Client Libraries

### PHP Client

```php
require_once 'client/ApsDreamClient.php';

$client = new ApsDreamClient('http://localhost/apsdreamhome/api/v1');

// Login
$login = $client->login('admin@example.com', 'password');

// Get properties
$properties = $client->getProperties(['status' => 'available']);
```

### JavaScript/Node.js Client

```javascript
const ApsDreamClient = require('./client/ApsDreamClient');

const client = new ApsDreamClient('http://localhost/apsdreamhome/api/v1');

async function getData() {
  const login = await client.login('admin@example.com', 'password');
  const properties = await client.getProperties({ status: 'available' });
  console.log(properties);
}

getData();
```

### Python Client

```python
from ApsDreamClient import ApsDreamClient

client = ApsDreamClient(base_url='http://localhost/apsdreamhome/api/v1')

# Login
login = client.login('admin@example.com', 'password')


# Get properties
properties = client.get_properties(status='available')
print(properties)
```

## API Documentation

For detailed API documentation, see [v1/README.md](v1/README.md).

## Testing

See [TESTING.md](TESTING.md) for information on running tests.

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.
