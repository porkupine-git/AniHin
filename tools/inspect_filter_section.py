with open(r'C:\Users\Ritesh\.gemini\antigravity-ide\brain\f8daebcc-cad5-41b8-8947-1db54d4dffb4\filter_raw.html', 'r', encoding='utf-8', errors='ignore') as f:
    filter_html = f.read()

import re
idx = filter_html.find('cate-filter')
if idx != -1:
    print(filter_html[idx-100:idx+600])
else:
    print("cate-filter not found")
