import re, codecs

with codecs.open('mobile/apsdreamhome_app_v2/lib/core/router/app_router.dart', 'r', encoding='utf-8', errors='ignore') as f:
    content = f.read()

matches = re.findall(r'path:\s*[\'"](/[^\'"]+)[\'"]', content)
for m in sorted(set(matches)):
    print(f'  {m}')