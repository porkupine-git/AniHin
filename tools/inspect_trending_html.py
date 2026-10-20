with open(r'c:\Users\Ritesh\Downloads\anixohindi\index.html', 'r', encoding='utf-8', errors='ignore') as f:
    html = f.read()

import re
idx = html.find('trending-list')
if idx != -1:
    print(html[idx-100:idx+800])
else:
    print("trending-list not found")
