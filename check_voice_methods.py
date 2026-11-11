import re

# Check VoiceService public methods
with open('app/Services/AI/VoiceService.php', 'r', errors='ignore') as f:
    content = f.read()
    
methods = re.finditer(r'public\s+function\s+(\w+)\s*\(', content)
voice_service_methods = sorted([m.group(1) for m in methods])

print("=== VoiceService public methods ===")
for m in voice_service_methods:
    print(f"  {m}")

print(f"\nTotal: {len(voice_service_methods)} public methods")

# Check each caller's usage
callers = {
    'app/Http/Controllers/Admin/VoiceCallSchedulerController.php': ['getScheduleAnalytics', 'getAgentList', 'getCallsOverTime', 'getPendingCalls', 'scheduleCall', 'getLeadCallHistory', 'rescheduleCall', 'cancelSchedule', 'processScheduledCalls'],
    'app/Http/Controllers/Admin/AICallingController.php': [],  # Check this separately
    'app/Http/Controllers/Admin/CRMVoiceController.php': [],  # Check this separately
    'app/Http/Controllers/Admin/SIMCallingController.php': [],  # Check this separately
    'app/Http/Controllers/Admin/AICallingController.php': [],  # Check this separately
    'app/Http/Controllers/Admin/CRMVoiceController.php': [],  # Check this separately
    'app/Http/Controllers/Admin/AICallingController.php': [],  # Check this separately
}

for caller_path, expected_methods in callers.items():
    try:
        with open(f'C:\\xampp\\htdocs\\apsdreamhome\\{caller_path}', 'r', errors='ignore') as f:
            caller_content = f.read()
            caller_methods = re.finditer(r'\$this->\w+Service->(\w+)\s*\(', caller_content)
            found = [m.group(1) for m in caller_methods]
            print(f"\n{caller_path}:")
            for m in found:
                status = "OK" if m in voice_service_methods else "MISSING"
                print(f"  {m} - {status}")
    except FileNotFoundError:
        print(f"\n{caller_path}: File not found")
