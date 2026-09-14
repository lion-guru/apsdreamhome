import re, codecs

# Parse Flutter app_constants.dart endpoints
flutter_endpoints = set()
with codecs.open('mobile/apsdreamhome_app_v2/lib/core/constants/app_constants.dart', 'r', encoding='utf-8', errors='ignore') as f:
    content = f.read()
    matches = re.findall(r"static const String (\w+Endpoint)\s*=\s*['\"]([^'\"]+)['\"]", content)
    for name, endpoint in matches:
        flutter_endpoints.add(endpoint)

# Parse backend API routes
backend_endpoints = set()
with codecs.open('routes/api.php', 'r', encoding='utf-8', errors='ignore') as f:
    content = f.read()
    matches = re.findall(r"\$router->(?:get|post|put|delete|patch)\s*\(\s*['\"](/api/v2/mobile/[^'\"]+)['\"]", content)
    for endpoint in matches:
        backend_endpoints.add(endpoint)

print(f"Flutter endpoints: {len(flutter_endpoints)}")
print(f"Backend endpoints: {len(backend_endpoints)}")

# Find missing in backend
missing_in_backend = []
for ep in flutter_endpoints:
    if ep not in backend_endpoints:
        prefixed = f"/api/v2/mobile{ep}"
        if prefixed not in backend_endpoints:
            missing_in_backend.append(ep)

print(f"\nMissing in backend ({len(missing_in_backend)}):")
for ep in sorted(missing_in_backend):
    print(f"  {ep}")

# Find extra in backend (not in Flutter)
extra_in_backend = []
for ep in backend_endpoints:
    if ep.startswith('/api/v2/mobile/'):
        short = ep[len('/api/v2/mobile'):]
        if short not in flutter_endpoints:
            extra_in_backend.append(ep)
    else:
        extra_in_backend.append(ep)

print(f"\nExtra in backend (not in Flutter constants) ({len(extra_in_backend)}):")
for ep in sorted(extra_in_backend)[:50]:
    print(f"  {ep}")