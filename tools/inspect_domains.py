import re

with open(r'C:\Users\Ritesh\.gemini\antigravity-ide\brain\f8daebcc-cad5-41b8-8947-1db54d4dffb4\.system_generated\steps\149\content.md', 'r', encoding='utf-8', errors='ignore') as f:
    txt = f.read()

srcs = re.findall(r'src=["\']([^"\']+)["\']', txt)
domains = set()
for s in srcs:
    m = re.match(r'https?://([^/]+)', s)
    if m:
        domains.add(m.group(1))
    else:
        domains.add('relative: ' + s[:30])
print('Domains in step 149:', domains)

# Let's check data-src as well (lazyloading in HiAnime!)
lazy_srcs = re.findall(r'data-src=["\']([^"\']+)["\']', txt)
print('Total data-src images (lazyloaded):', len(lazy_srcs))
lazy_domains = set()
for s in lazy_srcs:
    m = re.match(r'https?://([^/]+)', s)
    if m:
        lazy_domains.add(m.group(1))
print('Lazy domains:', lazy_domains)
print('Sample lazy srcs:', lazy_srcs[:5])
