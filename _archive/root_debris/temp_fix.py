with open('app/views/layouts/header.php', 'r', encoding='utf-8') as f: content = f.read()
content = content.replace('class=\
navbar
navbar-expand-xl\>', 'class=\navbar
navbar-expand-xl
align-items-center\>')
content = content.replace('<div class=\
container\>', '<div class=\container
d-flex
align-items-center\>')
content = content.replace('class=\
navbar-brand
d-flex
align-items-center\', 'class=\navbar-brand
d-flex
align-items-center
me-auto\')
with open('app/views/layouts/header.php', 'w', encoding='utf-8') as f: f.write(content)
