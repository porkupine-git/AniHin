with open(r'C:\Users\Ritesh\.gemini\antigravity-ide\brain\f8daebcc-cad5-41b8-8947-1db54d4dffb4\watch_raw.html', 'r', encoding='utf-8', errors='ignore') as f:
    watch_html = f.read()

import re
idx = watch_html.find('player-wrapper')
if idx != -1:
    print(watch_html[idx-100:idx+600])
else:
    idx2 = watch_html.find('iframe-embed')
    print("iframe-embed:", watch_html[idx2-100:idx2+600] if idx2 != -1 else "not found")
