import os
files = [
    'app/Services/Voice/VoiceCallService.php',
    'app/Services/Voice/TwilioVoiceService.php',
    'app/Services/Voice/AIVoicePipeline.php',
    'app/Services/Voice/AsteriskService.php',
    'app/Services/Voice/OLNService.php',
    'app/Services/Voice/TwiMLBuilder.php',
    'app/Services/CRMVoiceService.php',
    'app/Services/VoiceProviderManager.php',
    'app/Services/AI/VoiceService.php',
]

for fp in files:
    full_path = os.path.join('C:\\xampp\\htdocs\\apsdreamhome', fp)
    try:
        with open(full_path, 'r', errors='ignore') as f:
            lines = f.readlines()
            print(f'{fp}: {len(lines)} lines, {os.path.getsize(full_path)} bytes')
    except Exception as e:
        print(f'{fp}: ERROR {e}')
