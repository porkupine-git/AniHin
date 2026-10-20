with open(r'c:\Users\Ritesh\Downloads\anixohindi\assets\css\app-home.min.css', 'r', encoding='utf-8', errors='ignore') as f:
    css = f.read()

import re
hover_rules = re.findall(r'([^{}]*hover[^{}]*\{[^{}]*\})', css)
print("Total hover rules in app-home.min.css:", len(hover_rules))
for r in hover_rules[:10]:
    print(r.strip())
    print("-" * 30)
