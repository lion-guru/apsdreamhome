import re, codecs

with codecs.open('routes/web.php', 'r', encoding='utf-8', errors='ignore') as f:
    content = f.read()

matches = re.findall(r"\$router->(?:get|post|put|delete|patch)\s*\(\s*['\"]([^'\"]+)['\"]", content)
for m in sorted(set(matches)):
    print(f'  {m}')