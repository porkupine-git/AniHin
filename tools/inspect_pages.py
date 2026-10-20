with open(r'C:\Users\Ritesh\.gemini\antigravity-ide\brain\f8daebcc-cad5-41b8-8947-1db54d4dffb4\watch_raw.html', 'r', encoding='utf-8', errors='ignore') as f:
    watch_html = f.read()

import re
print("Watch page title:", re.findall(r'<title>(.*?)</title>', watch_html))
main_divs = re.findall(r'<div[^>]+id=[\"\']([^\"\']+)[\"\']', watch_html)
print("Watch page main IDs:", main_divs[:15])

with open(r'C:\Users\Ritesh\.gemini\antigravity-ide\brain\f8daebcc-cad5-41b8-8947-1db54d4dffb4\filter_raw.html', 'r', encoding='utf-8', errors='ignore') as f:
    filter_html = f.read()

print("Filter page title:", re.findall(r'<title>(.*?)</title>', filter_html))
filter_divs = re.findall(r'<div[^>]+id=[\"\']([^\"\']+)[\"\']', filter_html)
print("Filter page main IDs:", filter_divs[:15])
