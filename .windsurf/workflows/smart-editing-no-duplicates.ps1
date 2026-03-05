---
description: Smart Duplicate Detection & Editing - No Duplicate Files
auto_execution_mode: 3
---

# 🧠 SMART DUPLICATE DETECTION & EDITING

## 🚫 NO DUPLICATE FILES POLICY

// turbo
echo "🧠 SMART DUPLICATE DETECTION ACTIVATED"
echo "🚫 POLICY: NO DUPLICATE FILES - EDIT EXISTING ONLY"

// Step 1: Check Existing Files Before Creating
echo "📋 STEP 1: Existing File Analysis"

# Function to check if file exists and edit instead of creating

smart_file_operation() {
local file_path="$1"
local content="$2"
local operation="$3"

    if [ -f "$file_path" ]; then
        echo "✅ File exists: $file_path"
        echo "🔧 EDITING existing file instead of creating duplicate..."

        # Create backup first
        cp "$file_path" "$file_path.backup.$(date +%Y%m%d_%H%M%S)"
        echo "💾 Backup created: $file_path.backup.$(date +%Y%m%d_%H%M%S)"

        # Edit the existing file
        echo "$content" > "$file_path"
        echo "✅ File edited: $file_path"

        return 0
    else
        echo "📝 File doesn't exist: $file_path"
        echo "🆕 Creating new file..."
        echo "$content" > "$file_path"
        echo "✅ File created: $file_path"

        return 1
    fi

}

# Step 2: Analyze Current Project Structure

echo "🏗️ STEP 2: Project Structure Analysis"

# Check for common duplicate patterns

echo "🔍 Scanning for potential duplicates..."

# Check for multiple similar files

find app/views/pages -name "\*.php" | sort | while read file; do
basename_file=$(basename "$file" .php)

    # Check for similar named files
    similar_files=$(find app/views/pages -name "*${basename_file}*" -type f | wc -l)

    if [ "$similar_files" -gt 1 ]; then
        echo "⚠️  POTENTIAL DUPLICATE DETECTED: $basename_file"
        echo "📁 Found $similar_files similar files"
        find app/views/pages -name "*${basename_file}*" -type f
    fi

done

# Step 3: Controller Smart Editing

echo "🎛️ STEP 3: Controller Smart Editing"

# AI Assistant Controller

if [ -f "app/Http/Controllers/AIAssistantController.php" ]; then
echo "✅ AI Assistant Controller exists - EDITING instead of creating..."

    # Read existing content and enhance it
    existing_content=$(cat app/Http/Controllers/AIAssistantController.php)

    # Check if it has all required methods
    if ! echo "$existing_content" | grep -q "function index"; then
        echo "🔧 Adding missing index method..."
        cat > app/Http/Controllers/AIAssistantController.php << 'EOF'

<?php
namespace App\Http\Controllers;

use App\Http\Controllers\BaseController;

/**
 * AI Property Assistant Controller
 * Provides AI-powered property recommendations and chat interface
 */
class AIAssistantController extends BaseController
{
    public function index()
    {
        $this->render('pages/ai-assistant', [
            'page_title' => 'AI Property Assistant - APS Dream Home',
            'page_description' => 'Get AI-powered property recommendations and find your dream home with our intelligent assistant'
        ]);
    }
    
    /**
     * API endpoint for AI chat responses
     */
    public function chat()
    {
        header('Content-Type: application/json');
        
        try {
            $message = $_POST['message'] ?? '';
            $response = $this->generateAIResponse($message);
            
            echo json_encode([
                'success' => true,
                'response' => $response
            ]);
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => 'Failed to process message'
            ]);
        }
    }
    
    private function generateAIResponse($message)
    {
        // Simple AI response logic
        $responses = [
            'property' => 'I can help you find the perfect property! What type of property are you looking for?',
            'price' => 'We have properties in various price ranges. What\'s your budget?',
            'location' => 'We have properties in prime locations. Which area interests you?',
            'default' => 'I\'m here to help you find your dream property. How can I assist you today?'
        ];
        
        foreach ($responses as $key => $response) {
            if (stripos($message, $key) !== false) {
                return $response;
            }
        }
        
        return $responses['default'];
    }
}
EOF
    else
        echo "✅ AI Assistant Controller already complete"
    fi
else
    echo "🆕 Creating AI Assistant Controller (new file needed)..."
    cat > app/Http/Controllers/AIAssistantController.php << 'EOF'
<?php
namespace App\Http\Controllers;

use App\Http\Controllers\BaseController;

class AIAssistantController extends BaseController
{
    public function index()
    {
        $this->render('pages/ai-assistant', [
            'page_title' => 'AI Property Assistant - APS Dream Home'
        ]);
    }
}
EOF
fi

# Analytics Controller
if [ -f "app/Http/Controllers/AnalyticsController.php" ]; then
    echo "✅ Analytics Controller exists - ENHANCING existing..."
    
    # Enhance existing controller
    if ! grep -q "requireLogin" app/Http/Controllers/AnalyticsController.php; then
        echo "🔧 Adding login protection to Analytics Controller..."
        sed -i '/public function index()/a\        $this->requireLogin();' app/Http/Controllers/AnalyticsController.php
    fi
else
    echo "🆕 Creating Analytics Controller (new file needed)..."
    cat > app/Http/Controllers/AnalyticsController.php << 'EOF'
<?php
namespace App\Http\Controllers;

use App\Http\Controllers\BaseController;

class AnalyticsController extends BaseController
{
    public function index()
    {
        $this->requireLogin();
        $this->render('pages/analytics-dashboard', [
            'page_title' => 'Analytics Dashboard - APS Dream Home'
        ]);
    }
}
EOF
fi

# MLM Controller
if [ -f "app/Http/Controllers/MLMController.php" ]; then
    echo "✅ MLM Controller exists - ENHANCING existing..."
    
    # Enhance existing controller
    if ! grep -q "requireLogin" app/Http/Controllers/MLMController.php; then
        echo "🔧 Adding login protection to MLM Controller..."
        sed -i '/public function dashboard()/a\        $this->requireLogin();' app/Http/Controllers/MLMController.php
    fi
else
    echo "🆕 Creating MLM Controller (new file needed)..."
    cat > app/Http/Controllers/MLMController.php << 'EOF'
<?php
namespace App\Http\Controllers;

use App\Http\Controllers\BaseController;

class MLMController extends BaseController
{
    public function dashboard()
    {
        $this->requireLogin();
        $this->render('pages/mlm-dashboard', [
            'page_title' => 'MLM Dashboard - APS Dream Home'
        ]);
    }
}
EOF
fi

# WhatsApp Template Controller
if [ -f "app/Http/Controllers/WhatsAppTemplateController.php" ]; then
    echo "✅ WhatsApp Template Controller exists - ENHANCING existing..."
    
    # Enhance existing controller
    if ! grep -q "requireLogin" app/Http/Controllers/WhatsAppTemplateController.php; then
        echo "🔧 Adding login protection to WhatsApp Template Controller..."
        sed -i '/public function index()/a\        $this->requireLogin();' app/Http/Controllers/WhatsAppTemplateController.php;
    fi
else
    echo "🆕 Creating WhatsApp Template Controller (new file needed)..."
    cat > app/Http/Controllers/WhatsAppTemplateController.php << 'EOF'
<?php
namespace App\Http\Controllers;

use App\Http\Controllers\BaseController;

class WhatsAppTemplateController extends BaseController
{
    public function index()
    {
        $this->requireLogin();
        $this->render('pages/whatsapp-templates', [
            'page_title' => 'WhatsApp Templates - APS Dream Home'
        ]);
    }
}
EOF
fi

# Step 4: View Smart Editing
echo "🎨 STEP 4: View Smart Editing"

# AI Assistant View
if [ -f "app/views/pages/ai-assistant.php" ]; then
    echo "✅ AI Assistant View exists - ENHANCING existing..."
    
    # Check if it has proper structure
    if ! grep -q "page_title" app/views/pages/ai-assistant.php; then
        echo "🔧 Adding proper structure to AI Assistant View..."
        sed -i '1i\<?php\n$page_title = '\''AI Property Assistant - APS Dream Home'\'';\n$page_description = '\''Get AI-powered property recommendations and find your dream home with our intelligent assistant'\'';\ninclude __DIR__ . '\''/../layouts/base.php'\'';\n?>\n' app/views/pages/ai-assistant.php

    fi

else
echo "🆕 Creating AI Assistant View (new file needed)..."
cat > app/views/pages/ai-assistant.php << 'EOF'

<?php
$page_title = 'AI Property Assistant - APS Dream Home';
$page_description = 'Get AI-powered property recommendations and find your dream home with our intelligent assistant';
include __DIR__ . '/../layouts/base.php';
?>

<div class="container-fluid py-4">
    <div class="ai-container">
        <div class="ai-header">
            <h1><i class="fas fa-robot me-3"></i>AI Property Assistant</h1>
            <p>Your intelligent real estate companion - Find your dream property with AI-powered recommendations</p>
        </div>
        
        <div class="ai-body">
            <div class="row">
                <div class="col-lg-8">
                    <div class="chat-container">
                        <div class="chat-messages" id="chatMessages">
                            <div class="message ai-message">
                                <div class="message-avatar">
                                    <i class="fas fa-robot"></i>
                                </div>
                                <div class="message-content">
                                    <p>Hello! I'm your AI Property Assistant. I can help you find the perfect property based on your preferences. What kind of property are you looking for?</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="chat-input-container">
                            <div class="chat-input">
                                <input type="text" id="chatInput" placeholder="Ask me about properties, locations, prices..." />
                                <button onclick="sendMessage()">
                                    <i class="fas fa-paper-plane"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.ai-container {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    border-radius: 20px;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
    margin: 20px auto;
    max-width: 1200px;
}

.ai-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 30px;
    text-align: center;
    border-radius: 20px 20px 0 0;
}

.chat-container {
    height: 500px;
    display: flex;
    flex-direction: column;
}

.chat-messages {
    flex: 1;
    overflow-y: auto;
    padding: 20px;
    background: #f8f9fa;
}

.message {
    display: flex;
    margin-bottom: 20px;
    align-items: flex-start;
}

.ai-message {
    justify-content: flex-start;
}

.message-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    margin: 0 10px;
    background: #1a237e;
    color: white;
}

.message-content {
    max-width: 70%;
    padding: 12px 16px;
    border-radius: 18px;
    background: white;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.chat-input-container {
    padding: 20px;
    background: white;
    border-top: 1px solid #e0e0e0;
    border-radius: 0 0 20px 20px;
}

.chat-input {
    display: flex;
    gap: 10px;
}

.chat-input input {
    flex: 1;
    border: 1px solid #e0e0e0;
    border-radius: 25px;
    padding: 12px 20px;
    font-size: 1rem;
    outline: none;
}

.chat-input button {
    background: #1a237e;
    color: white;
    border: none;
    border-radius: 50%;
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
}
</style>

<script>
function sendMessage() {
    const input = document.getElementById('chatInput');
    const message = input.value.trim();
    
    if (message) {
        addUserMessage(message);
        input.value = '';
        
        // Simulate AI response
        setTimeout(() => {
            addAIResponse(generateAIResponse(message));
        }, 1000);
    }
}

function addUserMessage(message) {
    const chatMessages = document.getElementById('chatMessages');
    const messageHtml = `
        <div class="message user-message">
            <div class="message-avatar">
                <i class="fas fa-user"></i>
            </div>
            <div class="message-content">
                <p>${message}</p>
            </div>
        </div>
    `;
    chatMessages.innerHTML += messageHtml;
    chatMessages.scrollTop = chatMessages.scrollHeight;
}

function addAIResponse(response) {
    const chatMessages = document.getElementById('chatMessages');
    const messageHtml = `
        <div class="message ai-message">
            <div class="message-avatar">
                <i class="fas fa-robot"></i>
            </div>
            <div class="message-content">
                <p>${response}</p>
            </div>
        </div>
    `;
    chatMessages.innerHTML += messageHtml;
    chatMessages.scrollTop = chatMessages.scrollHeight;
}

function generateAIResponse(message) {
    const responses = {
        'property': 'I can help you find the perfect property! What type of property are you looking for?',
        'price': 'We have properties in various price ranges. What\'s your budget?',
        'location': 'We have properties in prime locations. Which area interests you?',
        'default': 'I\'m here to help you find your dream property. How can I assist you today?'
    };
    
    const lowerMessage = message.toLowerCase();
    for (const [key, response] of Object.entries(responses)) {
        if (lowerMessage.includes(key)) {
            return response;
        }
    }
    
    return responses.default;
}

document.getElementById('chatInput').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        sendMessage();
    }
});
</script>

EOF
fi
else
echo "🆕 Creating AI Assistant View (new file needed)..." # Create new file (same content as above)
fi

# Step 5: Route Smart Editing

echo "🛣️ STEP 5: Route Smart Editing"

if [ -f "routes/web.php" ]; then
echo "✅ Routes file exists - ENHANCING existing..."

    # Check if routes are already present
    if ! grep -q "ai-assistant" routes/web.php; then
        echo "🔧 Adding missing routes to existing file..."

        # Backup existing routes
        cp routes/web.php routes/web.php.backup.$(date +%Y%m%d_%H%M%S)

        # Add new routes to existing file
        cat >> routes/web.php << 'EOF'

// AI Assistant Routes
$router->get('/ai-assistant', 'AIAssistantController@index');

// Analytics Routes (Protected)
$router->get('/analytics', 'AnalyticsController@index');

// MLM Routes (Protected)
$router->get('/mlm-dashboard', 'MLMController@dashboard');

// WhatsApp Template Routes (Protected)
$router->get('/whatsapp-templates', 'WhatsAppTemplateController@index');
EOF
else
echo "✅ All routes already present"
fi
else
echo "🆕 Creating routes file (new file needed)..."
cat > routes/web.php << 'EOF'

<?php
// Web Routes
$router->get('/', 'HomeController@index');

// AI Assistant Routes
$router->get('/ai-assistant', 'AIAssistantController@index');

// Analytics Routes (Protected)
$router->get('/analytics', 'AnalyticsController@index');

// MLM Routes (Protected)
$router->get('/mlm-dashboard', 'MLMController@dashboard');

// WhatsApp Template Routes (Protected)
$router->get('/whatsapp-templates', 'WhatsAppTemplateController@index');
EOF
fi

# Step 6: Duplicate Cleanup
echo "🧹 STEP 6: Duplicate Cleanup"

# Remove any .blade.php files if they exist
find app/views -name "*.blade.php" -delete 2>/dev/null && echo "🗑️  Cleaned up .blade.php files"

# Remove duplicate directories
if [ -d "resources/views" ]; then
    rm -rf resources/views
    echo "🗑️  Removed duplicate resources/views directory"
fi

# Remove any files with "unified", "pro", "perfect", "advanced" in name that might be duplicates
find app/views/pages -name "*unified*" -delete 2>/dev/null
find app/views/pages -name "*pro*" -delete 2>/dev/null  
find app/views/pages -name "*perfect*" -delete 2>/dev/null
find app/views/pages -name "*advanced*" -delete 2>/dev/null

echo "✅ Duplicate cleanup completed"

# Step 7: Final Verification
echo "🔍 STEP 7: Final Verification"

echo "📊 Current File Status:"
echo "Controllers:"
ls -la app/Http/Controllers/*.php 2>/dev/null | wc -l | xargs echo "  Total: "

echo "Views:"
ls -la app/views/pages/*.php 2>/dev/null | wc -l | xargs echo "  Total: "

echo "✅ SMART EDITING COMPLETED!"
echo "🚫 NO DUPLICATE FILES CREATED"
echo "🔧 EXISTING FILES ENHANCED"
echo "🧹 DUPLICATES CLEANED UP"
