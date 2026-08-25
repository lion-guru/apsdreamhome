import re, codecs

with codecs.open('mobile/apsdreamhome_app_v2/lib/core/constants/app_constants.dart', 'r', encoding='utf-8', errors='ignore') as f:
    content = f.read()

lines = content.split('\n')
for i, line in enumerate(lines):
    if 'adminEmiCollectionEndpoint' in line:
        print(f'Line {i+1}: {line.strip()}')