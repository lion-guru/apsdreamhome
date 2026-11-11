import os

files = [
    'app/Http/Controllers/Admin/DripCampaignController.php',
    'app/Http/Controllers/Admin/LeadController.php',
    'app/Http/Controllers/Admin/MarketingCampaignController.php',
    'app/Http/Controllers/Admin/AdvancedFeaturesController.php',
]

for fp in files:
    print(f'=== {fp} ===')
    with open(fp, 'r', errors='ignore') as f:
        content = f.read()
        lines = content.split('\n')
        for i, line in enumerate(lines, 1):
            if '->' in line and ('service' in line.lower() or '$this->' in line or 'svc' in line.lower()):
                print(f'  {i}: {line.strip()[:180]}')
    print()
