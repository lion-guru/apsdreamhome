import re
import os

services_dir = r'C:\xampp\htdocs\apsdreamhome\app\Services'
services = [
    'PropertyService.php',
    'PropertyRecommendationService.php',
    'PropertyComparisonService.php',
    'PropertyMarketplaceService.php',
    'PropertyTransferService.php',
    'PropertyTaxService.php',
    'PropertyVerificationService.php',
    'PropertySubmissionService.php',
]

for fp in services:
    filepath = os.path.join(services_dir, fp)
    try:
        with open(filepath, 'r', errors='ignore') as f:
            content = f.read()
            methods = re.findall(r'public\s+function\s+(\w+)\s*\(', content)
            print(f"=== {fp} ({len(methods)} methods) ===")
            for m in methods:
                print(f"  {m}")
    except Exception as e:
        print(f'  Error: {e}')
    print()
