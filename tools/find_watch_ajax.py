with open(r'C:\Users\Ritesh\.gemini\antigravity-ide\brain\f8daebcc-cad5-41b8-8947-1db54d4dffb4\watch_raw.html', 'r', encoding='utf-8', errors='ignore') as f:
    watch_html = f.read()

import re
matches = re.findall(r'https?://[^\s"\'<>]+(?:ajax|server|episode)[^\s"\'<>]*', watch_html)
print("Found URLs in watch_raw.html:")
for m in set(matches):
    print(m)
