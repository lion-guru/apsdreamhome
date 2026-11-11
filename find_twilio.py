with open(r'C:\xampp\htdocs\apsdreamhome\app\Http\Controllers\Admin\VoiceAgentAdminController.php', 'r', errors='ignore') as f:
    lines = f.readlines()
    for i, line in enumerate(lines, 1):
        if 'TwilioVoiceService' in line:
            print(f'{i}: {line.strip()[:200]}')