with open(r'c:\Users\Ritesh\Downloads\anixohindi\index.html', 'r', encoding='utf-8', errors='ignore') as f:
    html = f.read()

import re
idx = html.find('Latest Episode')
print("Found Latest Episode at:", idx)
sub = html[idx:idx+3500]
print(sub[:2500])
