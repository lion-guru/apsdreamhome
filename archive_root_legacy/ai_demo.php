<?php
/**
 * APS Dream Home - AI Features Demo
 * Demonstrates AI integration capabilities
 */

require_once 'includes/config.php';

// Check if AI is enabled
if (!$config['ai']['enabled']) {
    die('AI features are currently disabled. Please enable them in the configuration.');
}

$page_title = 'AI Features Demo - APS Dream Home';
include 'includes/enhanced_universal_template.php';
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow-lg">
                <div class="card-header bg-gradient-primary text-white">
                    <h2 class="mb-0">
                        <i class="fas fa-robot me-2"></i>
                        APS Dream Home AI Features Demo
                    </h2>
                </div>
                <div class="card-body">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>AI Integration Active:</strong> Powered by OpenRouter API with Qwen3-Coder (Code Specialist)
                            </div>

                    <!-- Property Description Generator -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h4><i class="fas fa-home me-2"></i>AI Property Description Generator</h4>
                        </div>
                        <div class="card-body">
                            <form id="propertyForm">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="propType" class="form-label">Property Type</label>
                                            <select class="form-control" id="propType" required>
                                                <option value="">Select Type</option>
                                                <option value="Luxury Villa">Luxury Villa</option>
                                                <option value="Modern Apartment">Modern Apartment</option>
                                                <option value="Independent House">Independent House</option>
                                                <option value="Commercial Property">Commercial Property</option>
                                                <option value="Plot/Land">Plot/Land</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="propLocation" class="form-label">Location</label>
                                            <input type="text" class="form-control" id="propLocation" placeholder="e.g., Gorakhpur, Indira Nagar" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="propPrice" class="form-label">Price (₹)</label>
                                            <input type="number" class="form-control" id="propPrice" placeholder="5000000" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="propBedrooms" class="form-label">Bedrooms</label>
                                            <input type="number" class="form-control" id="propBedrooms" placeholder="3" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="propArea" class="form-label">Area (sq ft)</label>
                                            <input type="number" class="form-control" id="propArea" placeholder="1500" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="propFeatures" class="form-label">Key Features (comma-separated)</label>
                                    <input type="text" class="form-control" id="propFeatures" placeholder="Swimming Pool, Garden, Parking, Security" required>
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-magic me-2"></i>Generate AI Description
                                </button>
                            </form>
                            <div id="propertyResult" class="mt-3" style="display: none;">
                                <h5>Generated Description:</h5>
                                <div class="alert alert-success">
                                    <div id="propertyDescription"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- AI Chatbot -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h4><i class="fas fa-comments me-2"></i>AI Customer Support Chatbot</h4>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="userQuery" class="form-label">Ask me anything about real estate:</label>
                                <input type="text" class="form-control" id="userQuery" placeholder="e.g., How to choose the right property?">
                            </div>
                            <button type="button" class="btn btn-success" onclick="askAI()">
                                <i class="fas fa-paper-plane me-2"></i>Ask AI Assistant
                            </button>
                            <div id="chatbotResult" class="mt-3" style="display: none;">
                                <h5>AI Response:</h5>
                                <div class="alert alert-info">
                                    <div id="chatbotResponse"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Property Valuation -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h4><i class="fas fa-calculator me-2"></i>AI Property Valuation</h4>
                        </div>
                        <div class="card-body">
                            <form id="valuationForm">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="valLocation" class="form-label">Location</label>
                                            <input type="text" class="form-control" id="valLocation" placeholder="Gorakhpur" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="valType" class="form-label">Property Type</label>
                                            <select class="form-control" id="valType" required>
                                                <option value="">Select Type</option>
                                                <option value="2BHK Apartment">2BHK Apartment</option>
                                                <option value="3BHK Apartment">3BHK Apartment</option>
                                                <option value="Independent House">Independent House</option>
                                                <option value="Villa">Villa</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="valArea" class="form-label">Area (sq ft)</label>
                                            <input type="number" class="form-control" id="valArea" placeholder="1200" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="valBedrooms" class="form-label">Bedrooms</label>
                                            <input type="number" class="form-control" id="valBedrooms" placeholder="3" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="valCondition" class="form-label">Condition</label>
                                            <select class="form-control" id="valCondition" required>
                                                <option value="Excellent">Excellent</option>
                                                <option value="Good">Good</option>
                                                <option value="Fair">Fair</option>
                                                <option value="Needs Renovation">Needs Renovation</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-warning">
                                    <i class="fas fa-chart-line me-2"></i>Get AI Valuation
                                </button>
                            </form>
                            <div id="valuationResult" class="mt-3" style="display: none;">
                                <h5>AI Property Valuation:</h5>
                                <div class="alert alert-warning">
                                    <div id="propertyValuation"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- AI Stats -->
                    <div class="card">
                        <div class="card-header bg-light">
                            <h4><i class="fas fa-chart-bar me-2"></i>AI Usage Statistics</h4>
                        </div>
                        <div class="card-body">
                            <div id="aiStats">
                                <?php
                                $ai = new AIDreamHome();
                                $stats = $ai->getUsageStats();
                                ?>
                                <div class="row text-center">
                                    <div class="col-md-4">
                                        <h3 class="text-primary"><?php echo number_format($stats['total_requests']); ?></h3>
                                        <p>Total AI Requests</p>
                                    </div>
                                    <div class="col-md-4">
                                        <h3 class="text-success"><?php echo number_format($stats['total_input_tokens']); ?></h3>
                                        <p>Input Characters</p>
                                    </div>
                                    <div class="col-md-4">
                                        <h3 class="text-info"><?php echo number_format($stats['total_output_tokens']); ?></h3>
                                        <p>Output Characters</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <!-- Code Analysis Tool -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h4><i class="fas fa-code me-2"></i>AI Code Analysis & Development Assistant</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="mb-3">
                                        <label for="codeLanguage" class="form-label">Programming Language</label>
                                        <select class="form-control" id="codeLanguage">
                                            <option value="php">PHP</option>
                                            <option value="javascript">JavaScript</option>
                                            <option value="html">HTML</option>
                                            <option value="css">CSS</option>
                                            <option value="sql">SQL</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="codeInput" class="form-label">Code to Analyze</label>
                                        <textarea class="form-control" id="codeInput" rows="8" placeholder="Paste your code here for analysis..."></textarea>
                                    </div>
                                    <button class="btn btn-dark me-2" onclick="analyzeCode()">
                                        <i class="fas fa-search me-2"></i>Analyze Code
                                    </button>
                                    <button class="btn btn-outline-dark" onclick="generateCode()">
                                        <i class="fas fa-magic me-2"></i>Generate Code
                                    </button>
                                </div>
                                <div class="col-md-4">
                                    <h6>Code Analysis Features</h6>
                                    <ul class="list-unstyled">
                                        <li><i class="fas fa-check text-success me-2"></i>Code Quality Review</li>
                                        <li><i class="fas fa-check text-success me-2"></i>Bug Detection</li>
                                        <li><i class="fas fa-check text-success me-2"></i>Performance Optimization</li>
                                        <li><i class="fas fa-check text-success me-2"></i>Security Analysis</li>
                                        <li><i class="fas fa-check text-success me-2"></i>Best Practices</li>
                                    </ul>
                                </div>
                            </div>
                            <div id="codeResult" class="mt-3" style="display: none;">
                                <h5>Code Analysis Result:</h5>
                                <div class="alert alert-dark">
                                    <div id="codeAnalysis"></div>
                                </div>
                            </div>
                        </div>
                    </div>

<script>
function askAI() {
    const query = document.getElementById('userQuery').value.trim();
    if (!query) {
        alert('Please enter a question');
        return;
    }

    // Use the new JavaScript AI client
    sendEnhancedMessage(query, [])
    .then(response => {
        document.getElementById('chatbotResult').style.display = 'block';
        document.getElementById('chatbotResponse').innerHTML = response;
    })
    .catch(error => {
        document.getElementById('chatbotResponse').innerHTML = 'Error: ' + error.message;
    });
}

// Property Description Form Handler
document.getElementById('propertyForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const propertyData = {
        type: document.getElementById('propType').value,
        location: document.getElementById('propLocation').value,
        price: document.getElementById('propPrice').value,
        bedrooms: document.getElementById('propBedrooms').value,
        area: document.getElementById('propArea').value,
        features: document.getElementById('propFeatures').value.split(',').map(f => f.trim())
    };

    document.getElementById('propertyDescription').innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Generating description...';

    try {
        const result = await apsAI.generatePropertyDescription(propertyData);

        document.getElementById('propertyResult').style.display = 'block';
        if (result.success) {
            document.getElementById('propertyDescription').innerHTML = result.response;

            // Store for learning
            await apsAI.storeInteraction(
                `Generate description for ${propertyData.type} in ${propertyData.location}`,
                result.response,
                { type: 'property_description', data: propertyData }
            );
        } else {
            document.getElementById('propertyDescription').innerHTML = result.error;
        }
    } catch (error) {
        document.getElementById('propertyDescription').innerHTML = 'Error: ' + error.message;
    }
});

// Property Valuation Form Handler
document.getElementById('valuationForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const valuationData = {
        location: document.getElementById('valLocation').value,
        type: document.getElementById('valType').value,
        area: document.getElementById('valArea').value,
        bedrooms: document.getElementById('valBedrooms').value,
        bathrooms: document.getElementById('valBathrooms').value || document.getElementById('valBedrooms').value,
        condition: document.getElementById('valCondition').value,
        year_built: document.getElementById('valYearBuilt').value || '2020',
        amenities: ['Parking', 'Security', 'Lift', 'Garden'] // Default amenities
    };

    document.getElementById('propertyValuation').innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Analyzing market value...';

    try {
        const result = await apsAI.estimatePropertyValue(valuationData);

        document.getElementById('valuationResult').style.display = 'block';
        if (result.success) {
            document.getElementById('propertyValuation').innerHTML = result.response;

            // Store for learning
            await apsAI.storeInteraction(
                `Estimate value for ${valuationData.type} in ${valuationData.location}`,
                result.response,
                { type: 'property_valuation', data: valuationData }
            );
        } else {
            document.getElementById('propertyValuation').innerHTML = result.error;
        }
    } catch (error) {
        document.getElementById('propertyValuation').innerHTML = 'Error: ' + error.message;
    }
});

// Code Analysis Functions
async function analyzeCode() {
    const code = document.getElementById('codeInput').value.trim();
    const language = document.getElementById('codeLanguage').value;

    if (!code) {
        alert('Please enter code to analyze');
        return;
    }

    document.getElementById('codeAnalysis').innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Analyzing code...';

    try {
        const result = await apsAI.analyzeCode(code, language);

        document.getElementById('codeResult').style.display = 'block';
        if (result.success) {
            document.getElementById('codeAnalysis').innerHTML = result.response;

            // Store for learning
            await apsAI.storeInteraction(
                `Analyze ${language.toUpperCase()} code`,
                result.response,
                { type: 'code_analysis', language: language, code_length: code.length }
            );
        } else {
            document.getElementById('codeAnalysis').innerHTML = result.error;
        }
    } catch (error) {
        document.getElementById('codeAnalysis').innerHTML = 'Error: ' + error.message;
    }
}

async function generateCode() {
    const requirements = document.getElementById('codeInput').value.trim();
    const language = document.getElementById('codeLanguage').value;

    if (!requirements) {
        alert('Please describe what code you want to generate');
        return;
    }

    document.getElementById('codeAnalysis').innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Generating code...';

    try {
        const result = await apsAI.generateCodeSnippet(requirements, language);

        document.getElementById('codeResult').style.display = 'block';
        if (result.success) {
            document.getElementById('codeAnalysis').innerHTML = `<pre><code class="language-${language}">${result.response}</code></pre>`;

            // Store for learning
            await apsAI.storeInteraction(
                `Generate ${language.toUpperCase()} code for: ${requirements.substring(0, 50)}...`,
                result.response,
                { type: 'code_generation', language: language, requirements: requirements }
            );
        } else {
            document.getElementById('codeAnalysis').innerHTML = result.error;
        }
    } catch (error) {
        document.getElementById('codeAnalysis').innerHTML = 'Error: ' + error.message;
    }
}
</script>

<script src="assets/js/ai_client.js"></script>

<?php include 'includes/enhanced_universal_template.php'; ?>
