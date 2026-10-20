with open(r'C:\Users\Ritesh\.gemini\antigravity-ide\brain\f8daebcc-cad5-41b8-8947-1db54d4dffb4\.system_generated\steps\149\content.md', 'r', encoding='utf-8', errors='ignore') as f:
    html = f.read()

import re
matches = [m.start() for m in re.finditer(r'header', html, re.I)]
for m in matches[:10]:
    print(html[m-20:m+150])
    print('='*30)
