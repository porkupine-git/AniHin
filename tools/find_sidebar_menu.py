with open(r'C:\Users\Ritesh\.gemini\antigravity-ide\brain\f8daebcc-cad5-41b8-8947-1db54d4dffb4\.system_generated\steps\149\content.md', 'r', encoding='utf-8', errors='ignore') as f:
    home_html = f.read()

import re
idx = home_html.find('sidebar_menu')
while idx != -1:
    print(home_html[max(0, idx-30):min(len(home_html), idx+150)])
    print('='*30)
    idx = home_html.find('sidebar_menu', idx+1)
