# Analyze screenshots with moondream via Ollama using PowerShell

$ollamaUrl = "http://localhost:11434/api/generate"
$images = @(
    @{ file = "homepage.png"; prompt = "Analyze this APS Dream Home homepage. Identify: 1) Visual design quality and branding consistency 2) Key sections and their clarity 3) Navigation and search functionality visibility 4) Call-to-action placement 5) Any UI issues or missing elements 6) Overall professional appearance for a real estate website" },
    @{ file = "login_page.png"; prompt = "Analyze this APS Dream Home login page. Identify: 1) Visual design and branding 2) Form field clarity and accessibility 2) Login options (email/password, Google, Phone, Air Login) 3) Registration links visibility 3) Role-based login options (Associate, Agent, Admin) 4) Any UI issues or missing elements 5) Mobile responsiveness indicators" },
    @{ file = "admin_erp_overview.png"; prompt = "Analyze this APS Dream Home Admin ERP Overview dashboard. Identify: 1) Dashboard layout and information hierarchy 2) Key metrics visibility and readability 3) Navigation sidebar organization 3) Quick actions and their relevance 4) Charts/data visualization quality 5) Data density and readability 6) Any UI issues or missing elements 6) Professional admin dashboard appearance" },
    @{ file = "mlm_dashboard.png"; prompt = "Analyze this MLM Commission Dashboard. Identify: 1) Data visualization quality (tables, charts) 2) Key metrics visibility (commission streams, rank distribution) 3) Data density and readability 4) Quick actions relevance 5) Data freshness indicators 6) Professional financial dashboard appearance 6) Any missing metrics or UI issues" }
)

function Analyze-Image($imagePath, $prompt) {
    $bytes = [System.IO.File]::ReadAllBytes($imagePath)
    $base64 = [Convert]::ToBase64String($bytes)
    
    $payload = @{
        model = "moondream"
        prompt = $prompt
        images = @($base64)
        stream = $false
        options = @{
            temperature = 0.1
            num_predict = 1000
        }
    }
    
    $json = $payload | ConvertTo-Json -Depth 10 -Compress
    
    try {
        $response = Invoke-RestMethod -Uri "http://localhost:11434/api/generate" -Method Post -Body $json -ContentType "application/json" -TimeoutSec 120
        return $response.response
    } catch {
        return "Error: $($_.Exception.Message)"
    }
}

foreach ($img in $images) {
    $path = "C:\xampp\htdocs\apsdreamhome\" + $img.file
    if (Test-Path $path) {
        Write-Host "`n============================================================"
        Write-Host "ANALYZING: $($img.file)"
        Write-Host "============================================================"
        $result = Analyze-Image -imagePath $path -prompt $img.prompt
        Write-Host $result
        Write-Host ""
    } else {
        Write-Host "File not found: $path"
    }
}