with open(r'c:\Users\Ritesh\Downloads\anixohindi\assets\css\app-home.min.css', 'r', encoding='utf-8', errors='ignore') as f:
    css = f.read()

import re
matches = [m.start() for m in re.finditer(r'\.film-poster', css)]
for m in matches[:10]:
    print(css[m:m+200])
    print("="*40)
