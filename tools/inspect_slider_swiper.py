with open(r'c:\Users\Ritesh\Downloads\anixohindi\assets\js\theme.min.js', 'r', encoding='utf-8', errors='ignore') as f:
    js = f.read()

import re
idx = js.find('slider')
while idx != -1:
    print(js[max(0, idx-50):min(len(js), idx+250)])
    print("="*40)
    idx = js.find('slider', idx+1)
