import re, codecs

with codecs.open('routes/api.php', 'r', encoding='utf-8', errors='ignore') as f:
    content = f.read()

matches = re.findall(r"\$router->(?:get|post|put|delete|patch)\s*\(\s*['\"](/api/v2/mobile/[^'\"]+)['\"]", content)
extra = [m for m in matches if m.startswith('/api/v2/mobile/')]

for m in sorted(extra)[50:]:
    print(f"  {m}")