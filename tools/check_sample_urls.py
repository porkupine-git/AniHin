import re

with open(r'C:\Users\Ritesh\.gemini\antigravity-ide\brain\f8daebcc-cad5-41b8-8947-1db54d4dffb4\.system_generated\steps\149\content.md', 'r', encoding='utf-8', errors='ignore') as f:
    txt = f.read()

srcs = re.findall(r'src=["\']([^"\']+)["\']', txt)
anipix = [s for s in srcs if 'anipixcdn' in s]
print('Anipix sample:', anipix[:5])
mal = [s for s in srcs if 'myanimelist' in s]
print('MAL sample:', mal[:5])
