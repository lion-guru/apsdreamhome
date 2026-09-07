#!/usr/bin/env python3
import re

file_path = 'C:/xampp/htdocs/apsdreamhome/routes/web.php'

with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

# Find the exact section
idx = content.find('$router->get(\"/admin/document-esign\"')
if idx >= 0:
    print('Found with double quotes at:', idx)
    print(repr(content[idx:idx+500]))
else:
    print('Not found with double quotes')
    # Try finding the section by looking for "Document E-Sign management"
    idx2 = content.find('Document E-Sign management')
    if idx2 >= 0:
        print('Found comment at:', idx2)
        print(repr(content[idx2:idx2+800]))