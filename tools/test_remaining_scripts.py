with open(r'c:\Users\Ritesh\Downloads\anixohindi\index.html', 'r', encoding='utf-8', errors='ignore') as f:
    html = f.read()

import re, subprocess

scripts = re.findall(r'<script(?![^>]*src=)[^>]*>(.*?)</script>', html, re.DOTALL)
for i, s in enumerate(scripts):
    if i == 2: continue # skip the one we just found
    with open('temp_test.js', 'w', encoding='utf-8') as tf:
        tf.write(s)
    res = subprocess.run(['node', '-c', 'temp_test.js'], capture_output=True, text=True)
    if res.returncode != 0:
        print(f"=== Script {i} SYNTAX ERROR ===")
        print(res.stderr[:300])
