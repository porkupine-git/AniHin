with open(r'C:\Users\Ritesh\.gemini\antigravity-ide\brain\f8daebcc-cad5-41b8-8947-1db54d4dffb4\watch_raw.html', 'r', encoding='utf-8', errors='ignore') as f:
    watch_html = f.read()

import re
print("Length of watch_raw.html:", len(watch_html))
player_div = re.findall(r'<div[^>]+id=[\"\'](?:player|iframe|watch|media)[^>]*>', watch_html, re.I)
print("Player divs:", player_div)
server_div = re.findall(r'<div[^>]+class=[\"\'][^\"\']*(?:server|servers|episodes|ep-item)[^\"\']*[\"\']', watch_html, re.I)
print("Server/episode divs:", server_div[:10])
