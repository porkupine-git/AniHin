with open(r'c:\Users\Ritesh\Downloads\anixohindi\assets\css\app.min.css', 'r', encoding='utf-8', errors='ignore') as f:
    css = f.read()

import re
matches = [m.start() for m in re.finditer(r'servers-content|ps-block|ep-item|ss-item', css)]
for m in matches[:10]:
    print(css[m-20:m+150])
    print("="*40)
