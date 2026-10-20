with open(r'c:\Users\Ritesh\Downloads\anixohindi\assets\js\theme.min.js', 'r', encoding='utf-8', errors='ignore') as f:
    js = f.read()

import re
matches = re.findall(r'(\b[a-zA-Z0-9_-]+\s*:\s*function|Swiper|slider|trending|spotlight)', js)
print("Matches in theme.min.js:", set(matches[:30]))
print("Length of theme.min.js:", len(js))
print("Sample snippet of theme.min.js:\n", js[:500])
