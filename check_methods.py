import re

files = [
    r'C:\xampp\htdocs\apsdreamhome\app\Services\Voice\AsteriskService.php',
    r'C:\xampp\htdocs\apsdreamhome\app\Services\Voice\TwilioVoiceService.php',
    r'C:\xampp\htdocs\apsdreamhome\app\Services\Voice\AIVoicePipeline.php',
    r'C:\xampp\htdocs\apsdreamhome\app\Services\Voice\OLNService.php',
    r'C:\xampp\htdocs\apsdreamhome\app\Services\CRMVoiceService.php',
    r'C:\xampp\htdocs\apsdreamhome\app\Services\VoiceAssistantService.php',
    r'C:\xampp\htdocs\apsdreamhome\app\Services\AI\VoiceService.php',
]

for fp in files:
    print(f'=== {fp} ===')
    try:
        with open(fp, 'r', errors='ignore') as f:
            content = f.read()
            methods = re.findall(r'public\s+function\s+(\w+)\s*\(', content)
            for m in methods:
                print(f'  {m}')
    except Exception as e:
        print(f'  Error: {e}')
    print()
