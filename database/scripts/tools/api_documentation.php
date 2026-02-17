<?php
/**
 * APS Dream Home - API Documentation Generator
 * 
 * This tool automatically scans and documents all API endpoints in the system,
 * providing comprehensive documentation for developers.
 */

// Set header for browser output
header('Content-Type: text/html; charset=utf-8');

// Connect to database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "apsdreamhome";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize variables
$message = '';
$error = '';
$apiPath = dirname(__DIR__) . '/api/';
$apiEndpoints = [];
$selectedEndpoint = '';
$endpointDetails = null;
$generatedDocs = '';

// Function to scan directory recursively
function scanDirectory($dir, &$results = []) {
    $files = scandir($dir);
    
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        
        $path = $dir . '/' . $file;
        
        if (is_dir($path)) {
            scanDirectory($path, $results);
        } else if (pathinfo($path, PATHINFO_EXTENSION) === 'php') {
            $results[] = $path;
        }
    }
    
    return $results;
}

// Function to extract API documentation from file
function extractApiDoc($filePath) {
    $content = file_get_contents($filePath);
    $relativePath = str_replace(dirname(__DIR__), '', $filePath);
    $relativePath = str_replace('\\', '/', $relativePath);
    
    $endpoint = [
        'file' => $relativePath,
        'name' => basename($filePath, '.php'),
        'description' => '',
        'methods' => [],
        'parameters' => [],
        'responses' => [],
        'examples' => []
    ];
    
    // Extract API description from file docblock
    if (preg_match('/\/\*\*(.*?)\*\//s', $content, $matches)) {
        $docblock = $matches[1];
        
        // Extract description
        if (preg_match('/@description\s+(.*?)(\n\s*\*\s*@|\n\s*\*\/)/s', $docblock, $descMatches)) {
            $endpoint['description'] = trim(preg_replace('/\n\s*\*\s*/', ' ', $descMatches[1]));
        } else if (preg_match('/\n\s*\*\s+(.*?)(\n\s*\*\s*@|\n\s*\*\/)/s', $docblock, $descMatches)) {
            $endpoint['description'] = trim(preg_replace('/\n\s*\*\s*/', ' ', $descMatches[1]));
        }
        
        // Extract methods
        if (preg_match('/@methods\s+(.*?)(\n\s*\*\s*@|\n\s*\*\/)/s', $docblock, $methodMatches)) {
            $methods = explode(',', $methodMatches[1]);
            foreach ($methods as $method) {
                $endpoint['methods'][] = trim($method);
            }
        }
        
        // Extract parameters
        if (preg_match_all('/@param\s+(\{([^}]+)\})?\s*([^\s]+)\s+([^\n]+)/s', $docblock, $paramMatches, PREG_SET_ORDER)) {
            foreach ($paramMatches as $match) {
                $type = isset($match[2]) ? trim($match[2]) : '';
                $name = trim($match[3]);
                $description = trim($match[4]);
                
                $endpoint['parameters'][] = [
                    'name' => $name,
                    'type' => $type,
                    'description' => $description,
                    'required' => strpos($description, '[optional]') === false
                ];
            }
        }
        
        // Extract responses
        if (preg_match_all('/@response\s+(\{([^}]+)\})?\s*([^\n]+)/s', $docblock, $responseMatches, PREG_SET_ORDER)) {
            foreach ($responseMatches as $match) {
                $code = isset($match[2]) ? trim($match[2]) : '200';
                $description = trim($match[3]);
                
                $endpoint['responses'][] = [
                    'code' => $code,
                    'description' => $description
                ];
            }
        }
        
        // Extract examples
        if (preg_match_all('/@example\s+(.*?)(\n\s*\*\s*@|\n\s*\*\/)/s', $docblock, $exampleMatches, PREG_SET_ORDER)) {
            foreach ($exampleMatches as $match) {
                $example = trim(preg_replace('/\n\s*\*\s*/', "\n", $match[1]));
                $endpoint['examples'][] = $example;
            }
        }
    }
    
    // If no methods were found in docblock, try to detect from code
    if (empty($endpoint['methods'])) {
        if (strpos($content, '$_GET') !== false) {
            $endpoint['methods'][] = 'GET';
        }
        if (strpos($content, '$_POST') !== false) {
            $endpoint['methods'][] = 'POST';
        }
        if (strpos($content, '$_PUT') !== false || strpos($content, 'file_get_contents("php://input")') !== false) {
            $endpoint['methods'][] = 'PUT';
        }
        if (strpos($content, '$_DELETE') !== false) {
            $endpoint['methods'][] = 'DELETE';
        }
    }
    
    // If no parameters were found in docblock, try to detect from code
    if (empty($endpoint['parameters'])) {
        // Check for GET parameters
        if (in_array('GET', $endpoint['methods'])) {
            preg_match_all('/\$_GET\s*\[\s*[\'"]([^\'"]+)[\'"]\s*\]/', $content, $getMatches);
            if (!empty($getMatches[1])) {
                foreach ($getMatches[1] as $param) {
                    $endpoint['parameters'][] = [
                        'name' => $param,
                        'type' => 'string',
                        'description' => 'Detected GET parameter',
                        'required' => strpos($content, 'isset($_GET[\'' . $param . '\'])') !== false || 
                                      strpos($content, 'isset($_GET["' . $param . '"])') !== false
                    ];
                }
            }
        }
        
        // Check for POST parameters
        if (in_array('POST', $endpoint['methods'])) {
            preg_match_all('/\$_POST\s*\[\s*[\'"]([^\'"]+)[\'"]\s*\]/', $content, $postMatches);
            if (!empty($postMatches[1])) {
                foreach ($postMatches[1] as $param) {
                    $endpoint['parameters'][] = [
                        'name' => $param,
                        'type' => 'string',
                        'description' => 'Detected POST parameter',
                        'required' => strpos($content, 'isset($_POST[\'' . $param . '\'])') !== false || 
                                      strpos($content, 'isset($_POST["' . $param . '"])') !== false
                    ];
                }
            }
        }
    }
    
    return $endpoint;
}

// Function to generate API documentation
function generateApiDoc($endpoint) {
    $doc = '';
    
    // API Endpoint
    $doc .= '<div class="api-section">';
    $doc .= '<h3 id="' . $endpoint['name'] . '">Endpoint: ' . $endpoint['file'] . '</h3>';
    
    // Description
    if (!empty($endpoint['description'])) {
        $doc .= '<div class="api-description">' . $endpoint['description'] . '</div>';
    }
    
    // HTTP Methods
    if (!empty($endpoint['methods'])) {
        $doc .= '<div class="api-methods">';
        $doc .= '<h4>HTTP Methods</h4>';
        $doc .= '<div class="method-badges">';
        foreach ($endpoint['methods'] as $method) {
            $methodClass = strtolower($method);
            $doc .= '<span class="method-badge method-' . $methodClass . '">' . $method . '</span>';
        }
        $doc .= '</div>';
        $doc .= '</div>';
    }
    
    // Parameters
    if (!empty($endpoint['parameters'])) {
        $doc .= '<div class="api-parameters">';
        $doc .= '<h4>Parameters</h4>';
        $doc .= '<table class="parameters-table">';
        $doc .= '<thead><tr><th>Name</th><th>Type</th><th>Required</th><th>Description</th></tr></thead>';
        $doc .= '<tbody>';
        foreach ($endpoint['parameters'] as $param) {
            $doc .= '<tr>';
            $doc .= '<td>' . htmlspecialchars($param['name']) . '</td>';
            $doc .= '<td>' . htmlspecialchars($param['type']) . '</td>';
            $doc .= '<td>' . ($param['required'] ? 'Yes' : 'No') . '</td>';
            $doc .= '<td>' . htmlspecialchars($param['description']) . '</td>';
            $doc .= '</tr>';
        }
        $doc .= '</tbody>';
        $doc .= '</table>';
        $doc .= '</div>';
    }
    
    // Responses
    if (!empty($endpoint['responses'])) {
        $doc .= '<div class="api-responses">';
        $doc .= '<h4>Responses</h4>';
        $doc .= '<table class="responses-table">';
        $doc .= '<thead><tr><th>Code</th><th>Description</th></tr></thead>';
        $doc .= '<tbody>';
        foreach ($endpoint['responses'] as $response) {
            $doc .= '<tr>';
            $doc .= '<td>' . htmlspecialchars($response['code']) . '</td>';
            $doc .= '<td>' . htmlspecialchars($response['description']) . '</td>';
            $doc .= '</tr>';
        }
        $doc .= '</tbody>';
        $doc .= '</table>';
        $doc .= '</div>';
    }
    
    // Examples
    if (!empty($endpoint['examples'])) {
        $doc .= '<div class="api-examples">';
        $doc .= '<h4>Examples</h4>';
        foreach ($endpoint['examples'] as $index => $example) {
            $doc .= '<div class="example">';
            $doc .= '<h5>Example ' . ($index + 1) . '</h5>';
            $doc .= '<pre><code>' . htmlspecialchars($example) . '</code></pre>';
            $doc .= '</div>';
        }
        $doc .= '</div>';
    }
    
    $doc .= '</div>';
    
    return $doc;
}

// Function to generate a Postman collection
function generatePostmanCollection($endpoints) {
    $collection = [
        'info' => [
            'name' => 'APS Dream Home API',
            'description' => 'API collection for APS Dream Home system',
            'schema' => 'https://schema.getpostman.com/json/collection/v2.1.0/collection.json'
        ],
        'item' => []
    ];
    
    $baseUrl = 'http://localhost/apsdreamhome';
    
    foreach ($endpoints as $endpoint) {
        $methods = $endpoint['methods'];
        if (empty($methods)) {
            $methods = ['GET'];
        }
        
        foreach ($methods as $method) {
            $item = [
                'name' => $endpoint['name'] . ' (' . $method . ')',
                'request' => [
                    'method' => $method,
                    'header' => [],
                    'url' => [
                        'raw' => $baseUrl . $endpoint['file'],
                        'host' => [$baseUrl],
                        'path' => explode('/', trim($endpoint['file'], '/')),
                        'query' => []
                    ]
                ],
                'response' => []
            ];
            
            // Add parameters
            if (!empty($endpoint['parameters'])) {
                foreach ($endpoint['parameters'] as $param) {
                    if ($method === 'GET') {
                        $item['request']['url']['query'][] = [
                            'key' => $param['name'],
                            'value' => '',
                            'description' => $param['description'],
                            'disabled' => !$param['required']
                        ];
                    } else {
                        $item['request']['body'] = [
                            'mode' => 'formdata',
                            'formdata' => []
                        ];
                        
                        $item['request']['body']['formdata'][] = [
                            'key' => $param['name'],
                            'value' => '',
                            'description' => $param['description'],
                            'type' => 'text'
                        ];
                    }
                }
            }
            
            $collection['item'][] = $item;
        }
    }
    
    return json_encode($collection, JSON_PRETTY_PRINT);
}

// Scan API directory for endpoints
if (is_dir($apiPath)) {
    $apiFiles = scanDirectory($apiPath);
    
    foreach ($apiFiles as $file) {
        $apiEndpoints[] = extractApiDoc($file);
    }
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Generate Postman collection
    if (isset($_POST['generate_postman'])) {
        $postmanCollection = generatePostmanCollection($apiEndpoints);
        $collectionFile = dirname(__DIR__) . '/api/aps_dream_home_api_collection.json';
        
        if (file_put_contents($collectionFile, $postmanCollection)) {
            $message = "Postman collection generated successfully: /api/aps_dream_home_api_collection.json";
        } else {
            $error = "Failed to generate Postman collection. Check directory permissions.";
        }
    }
    
    // Generate full documentation
    else if (isset($_POST['generate_docs'])) {
        $docsContent = '';
        
        foreach ($apiEndpoints as $endpoint) {
            $docsContent .= generateApiDoc($endpoint);
        }
        
        $generatedDocs = $docsContent;
    }
    
    // View endpoint details
    else if (isset($_POST['view_endpoint'])) {
        $selectedEndpoint = $_POST['endpoint'];
        
        foreach ($apiEndpoints as $endpoint) {
            if ($endpoint['file'] === $selectedEndpoint) {
                $endpointDetails = $endpoint;
                break;
            }
        }
    }
}

// Get URL parameters
if (isset($_GET['message'])) {
    $message = $_GET['message'];
}
if (isset($_GET['error'])) {
    $error = $_GET['error'];
}

// Close connection
$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
    <title>APS Dream Home - API Documentation</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f7fa;
            color: #333;
            line-height: 1.6;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        header {
            background-color: #2c3e50;
            color: white;
            padding: 20px 0;
            margin-bottom: 30px;
        }
        h1 {
            margin: 0;
            padding: 0 20px;
            font-size: 28px;
        }
        h2 {
            color: #3498db;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
            margin-top: 30px;
        }
        h3 {
            color: #2c3e50;
            margin-top: 25px;
            margin-bottom: 15px;
        }
        h4 {
            color: #3498db;
            margin-top: 20px;
            margin-bottom: 10px;
        }
        .card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        .btn {
            display: inline-block;
            background-color: #3498db;
            color: white;
            padding: 10px 15px;
            border-radius: 4px;
            text-decoration: none;
            transition: background-color 0.2s;
            border: none;
            cursor: pointer;
            font-size: 14px;
        }
        .btn:hover {
            background-color: #2980b9;
        }
        .btn-secondary {
            background-color: #95a5a6;
        }
        .btn-secondary:hover {
            background-color: #7f8c8d;
        }
        .btn-success {
            background-color: #2ecc71;
        }
        .btn-success:hover {
            background-color: #27ae60;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .alert {
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .api-section {
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }
        .api-description {
            margin-bottom: 15px;
            line-height: 1.6;
        }
        .method-badges {
            margin-bottom: 15px;
        }
        .method-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 4px;
            color: white;
            font-weight: bold;
            margin-right: 5px;
        }
        .method-get {
            background-color: #61affe;
        }
        .method-post {
            background-color: #49cc90;
        }
        .method-put {
            background-color: #fca130;
        }
        .method-delete {
            background-color: #f93e3e;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            margin-bottom: 15px;
        }
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f8f9fa;
        }
        pre {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            overflow: auto;
            font-family: Consolas, Monaco, 'Andale Mono', monospace;
            border: 1px solid #ddd;
        }
        code {
            font-family: Consolas, Monaco, 'Andale Mono', monospace;
        }
        .example {
            margin-bottom: 20px;
        }
        .api-toc {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .api-toc ul {
            list-style-type: none;
            padding-left: 0;
        }
        .api-toc li {
            margin-bottom: 8px;
        }
        .api-toc a {
            color: #3498db;
            text-decoration: none;
        }
        .api-toc a:hover {
            text-decoration: underline;
        }
        footer {
            margin-top: 50px;
            text-align: center;
            color: #7f8c8d;
            font-size: 14px;
            padding: 20px;
        }
        .actions {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <h1>APS Dream Home - API Documentation</h1>
        </div>
    </header>
    
    <div class="container">
        <?php if (!empty($message)): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="card">
            <h2>API Overview</h2>
            <p>
                This documentation provides details about all available API endpoints in the APS Dream Home system.
                Use the tools below to explore the API and generate documentation.
            </p>
            
            <div class="actions">
                <form method="post">
                    <button type="submit" name="generate_docs" class="btn btn-primary">Generate Full Documentation</button>
                </form>
                
                <form method="post">
                    <button type="submit" name="generate_postman" class="btn btn-success">Generate Postman Collection</button>
                </form>
            </div>
            
            <div class="form-group">
                <form method="post">
                    <label for="endpoint">View Endpoint Details:</label>
                    <select name="endpoint" id="endpoint">
                        <option value="">Select an endpoint...</option>
                        <?php foreach ($apiEndpoints as $endpoint): ?>
                            <option value="<?php echo $endpoint['file']; ?>"><?php echo $endpoint['file']; ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" name="view_endpoint" class="btn">View Details</button>
                </form>
            </div>
        </div>
        
        <?php if ($endpointDetails): ?>
            <div class="card">
                <h2>Endpoint Details: <?php echo $endpointDetails['name']; ?></h2>
                <?php echo generateApiDoc($endpointDetails); ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($generatedDocs)): ?>
            <div class="card">
                <h2>API Documentation</h2>
                
                <div class="api-toc">
                    <h3>Table of Contents</h3>
                    <ul>
                        <?php foreach ($apiEndpoints as $endpoint): ?>
                            <li><a href="#<?php echo $endpoint['name']; ?>"><?php echo $endpoint['file']; ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                
                <?php echo $generatedDocs; ?>
            </div>
        <?php endif; ?>
        
        <div style="text-align: center; margin-top: 20px;">
            <a href="index.php" class="btn btn-secondary">Return to Database Management Hub</a>
        </div>
    </div>
    
    <footer>
        <div class="container">
            <p>APS Dream Home API Documentation &copy; <?php echo date('Y'); ?></p>
        </div>
    </footer>
</body>
</html>
