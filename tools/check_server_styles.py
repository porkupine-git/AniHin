with open(r'c:\Users\Ritesh\Downloads\anixohindi\assets\css\app.min.css', 'r', encoding='utf-8', errors='ignore') as f:
    css = f.read()

import re
matches = [m.start() for m in re.finditer(r'\.ps_-block|\.server-item|\.ps-status|\.ep-item', css)]
for m in matches[:10]:
    print(css[m:m+250])
    print("="*40)
