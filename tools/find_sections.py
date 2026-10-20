import re

with open(r'C:\Users\Ritesh\.gemini\antigravity-ide\brain\f8daebcc-cad5-41b8-8947-1db54d4dffb4\.system_generated\steps\149\content.md', 'r', encoding='utf-8', errors='ignore') as f:
    html = f.read()

keywords = ['slider', 'spotlight', 'trending', 'top-airing', 'latest-episode', 'top-ten', 'genre']
for kw in keywords:
    matches = [m.start() for m in re.finditer(kw, html, re.IGNORECASE)]
    print(f"Keyword '{kw}': found {len(matches)} times. First at char {matches[0] if matches else -1}")
