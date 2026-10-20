with open(r'c:\Users\Ritesh\Downloads\anixohindi\index.html', 'r', encoding='utf-8', errors='ignore') as f:
    html = f.read()

import re
scripts = re.findall(r'<script(?![^>]*src=)[^>]*>(.*?)</script>', html, re.DOTALL)
for i, s in enumerate(scripts):
    try:
        # test simple syntax issues
        # let's look for suspicious syntax
        lines = s.split('\n')
        for lineno, line in enumerate(lines):
            stripped = line.strip()
            if stripped.startswith('{{') or stripped.endswith('}}'):
                print(f"Script {i} Line {lineno}: {stripped}")
    except Exception as e:
        print(f"Script {i} error: {e}")
