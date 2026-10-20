with open('tools/exact_watch_main.html', 'r', encoding='utf-8') as f:
    html = f.read()

import re
print("Length of exact_watch_main.html:", len(html))

# Let's inspect anis-watch-detail
idx = html.find('anis-watch-detail')
print("--- anis-watch-detail ---")
print(html[idx:idx+1200])
