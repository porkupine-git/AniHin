with open(r'C:\Users\Ritesh\.gemini\antigravity-ide\brain\f8daebcc-cad5-41b8-8947-1db54d4dffb4\.system_generated\steps\149\content.md', 'r', encoding='utf-8', errors='ignore') as f:
    html = f.read()

import re
scripts = re.findall(r'<script(?![^>]*src=)[^>]*>(.*?)</script>', html, re.DOTALL)
print(f"Total inline scripts: {len(scripts)}")
for i, s in enumerate(scripts):
    if any(k in s for k in ['Swiper', 'slider', 'trending', 'spotlight', 'qtip']):
        print(f"Script {i} (len {len(s)}):\n", s[:400])
        print("="*40)
