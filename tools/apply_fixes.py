import re

def fix_page(fpath):
    with open(fpath, 'r', encoding='utf-8', errors='ignore') as f:
        html = f.read()

    # 1. Add meta referrer no-referrer if missing
    if '<meta name="referrer"' not in html:
        html = html.replace('<head>', '<head>\n    <meta name="referrer" content="no-referrer">')

    # 2. Add Swiper 11 CSS in head if missing
    if 'swiper-bundle.min.css' not in html:
        html = html.replace('</head>', '    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />\n</head>')

    # 3. Add Swiper 11 JS before theme.min.js
    if 'swiper-bundle.min.js' not in html:
        html = html.replace('<script src="assets/js/theme.min.js"></script>',
                            '<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>\n<script src="assets/js/theme.min.js"></script>')

    # 4. Remove broken dataLayer script
    html = re.sub(r'<script>\s*window\.dataLayer\s*=\s*window\.dataLayer\s*\|\|\s*\[\];\s*function\s*\{dataLayer\.push\(arguments\);\}\s*\);\s*</script>', '', html)

    # 5. Remove loading="lazy" from the first 40 images so they load immediately
    lazy_matches = list(re.finditer(r'loading=["\']lazy["\']', html))
    for m in lazy_matches[:40]:
        html = html[:m.start()] + 'loading="eager"' + html[m.end():]

    with open(fpath, 'w', encoding='utf-8') as f:
        f.write(html)
    print(f"Applied fixes to {fpath}")

fix_page(r'c:\Users\Ritesh\Downloads\anixohindi\index.html')
fix_page(r'c:\Users\Ritesh\Downloads\anixohindi\watch.html')
fix_page(r'c:\Users\Ritesh\Downloads\anixohindi\browse.html')
fix_page(r'c:\Users\Ritesh\Downloads\anixohindi\filter.html')
