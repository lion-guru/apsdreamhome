<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Senior Developer Code Editor</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .card-shadow {
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        .pulse-dot {
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="gradient-bg text-white p-6">
        <div class="container mx-auto">
            <div class="text-center">
                <h1 class="text-3xl font-bold">🚀 Senior Developer Code Editor</h1>
                <p class="text-purple-200">Write & Execute Code Directly</p>
            </div>
        </div>
    </div>

    <div class="container mx-auto p-6">
        <div class="bg-white rounded-lg card-shadow p-6">
            <div class="text-center mb-6">
                <h2 class="text-2xl font-bold text-green-600 mb-4">✅ Code Editor Ready!</h2>
                <p class="text-gray-700">आप direct code लिख सकते हैं PHP, JavaScript, CSS, SQL, HTML</p>
                
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6">
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <i class="fas fa-code text-blue-600 text-2xl mb-2"></i>
                        <h3 class="font-bold">PHP</h3>
                        <p class="text-sm text-gray-600">Controllers, Models, Views</p>
                    </div>
                    <div class="bg-green-50 p-4 rounded-lg">
                        <i class="fas fa-cube text-green-600 text-2xl mb-2"></i>
                        <h3 class="font-bold">JavaScript</h3>
                        <p class="text-sm text-gray-600">Frontend functionality</p>
                    </div>
                    <div class="bg-purple-50 p-4 rounded-lg">
                        <i class="fas fa-database text-purple-600 text-2xl mb-2"></i>
                        <h3 class="font-bold">SQL</h3>
                        <p class="text-sm text-gray-600">Database queries</p>
                    </div>
                    <div class="bg-orange-50 p-4 rounded-lg">
                        <i class="fas fa-palette text-orange-600 text-2xl mb-2"></i>
                        <h3 class="font-bold">HTML/CSS</h3>
                        <p class="text-sm text-gray-600">Templates & styling</p>
                    </div>
                </div>
                
                <div class="mt-8">
                    <a href="/senior-developer/code-editor-simple" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 inline-block">
                        <i class="fas fa-edit mr-2"></i>
                        Open Code Editor
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
