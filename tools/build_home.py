import re
import os

step149_path = r'C:\Users\Ritesh\.gemini\antigravity-ide\brain\f8daebcc-cad5-41b8-8947-1db54d4dffb4\.system_generated\steps\149\content.md'
output_path = r'c:\Users\Ritesh\Downloads\anixohindi\index.html'

with open(step149_path, 'r', encoding='utf-8', errors='ignore') as f:
    raw = f.read()

# Extract from <!DOCTYPE html> to </html>
doctype_pos = raw.find('<!DOCTYPE html>')
if doctype_pos != -1:
    html = raw[doctype_pos:]
else:
    html = raw

# Remove tracking scripts and google tag manager
html = re.sub(r'<script[^>]*googletagmanager[^>]*>.*?</script>', '', html, flags=re.DOTALL)
html = re.sub(r'<script async src="https://www.googletagmanager.com[^"]*"></script>', '', html)
html = re.sub(r'gtag\([^\)]*\);?', '', html)

# Replace external CSS links with local paths
html = re.sub(r'<link rel="stylesheet" href="https://hianime\.at/theme/css/app-home\.min\.css[^"]*">',
              '<link rel="stylesheet" href="assets/css/app-home.min.css">\n    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">\n    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&display=swap">\n    <link rel="stylesheet" href="assets/css/custom.css">', html)

html = re.sub(r'<link rel="stylesheet" href="https://hianime\.at/build/assets/frontend-DDmvDXjb\.css[^"]*"\s*/?>',
              '<link rel="stylesheet" href="assets/css/frontend.css">', html)

# Replace remote js files with local js
html = re.sub(r'<script data-cfasync="false" src="https://hianime\.at/theme/js/vendor\.min\.js[^"]*"></script>',
              '<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>\n<script src="assets/js/vendor.min.js"></script>', html)

html = re.sub(r'<script data-cfasync="false" src="https://hianime\.at/theme/js/theme\.min\.js[^"]*"></script>',
              '<script src="assets/js/theme.min.js"></script>', html)

html = re.sub(r'<script data-cfasync="false" src="https://hianime\.at/theme/js/jquery\.qtip\.min\.js[^"]*"></script>',
              '<script src="assets/js/jquery.qtip.min.js"></script>\n<script src="assets/js/main.js"></script>', html)

# Remove modulepreload / build script tags that fail or 404
html = re.sub(r'<script type="module" src="https://hianime\.at/build/assets/[^"]*".*?</script>', '', html)
html = re.sub(r'<link rel="modulepreload"[^>]*>', '', html)
html = re.sub(r'<link rel="preload" as="style" href="https://hianime\.at/build/assets/[^"]*" />', '', html)

# Update internal navigation links
# Home links
html = re.sub(r'href="https://hianime\.at/home"', 'href="index.html"', html)
html = re.sub(r'href="/home"', 'href="index.html"', html)
html = re.sub(r'href="https://hianime\.at/"', 'href="index.html"', html)
html = re.sub(r'href="/"', 'href="index.html"', html)

# Watch links -> watch.html?anime=...
def fix_watch_link(match):
    full_url = match.group(1)
    # extract slug
    slug = full_url.rstrip('/').split('/')[-1]
    return f'href="watch.html?anime={slug}"'

html = re.sub(r'href="https://hianime\.at/watch/([^"]+)"', fix_watch_link, html)
html = re.sub(r'href="/watch/([^"]+)"', fix_watch_link, html)

# Browse / filter / category links -> browse.html
categories = [
    'recently-updated', 'recently-added', 'top-airing', 'most-popular', 'most-favorite', 
    'completed', 'filter', 'subbed-anime', 'dubbed-anime', 'movie', 'tv', 'ova', 'ona', 'special',
    'genre/[^"]+', 'az-list'
]
for cat in categories:
    html = re.sub(rf'href="https://hianime\.at/{cat}"', 'href="browse.html"', html)
    html = re.sub(rf'href="/{cat}"', 'href="browse.html"', html)

# Watch2gether & Random links
html = re.sub(r'href="https://hianime\.at/watch2gether"', 'href="watch.html"', html)
html = re.sub(r'href="/watch2gether"', 'href="watch.html"', html)
html = re.sub(r'href="https://hianime\.at/random"', 'href="watch.html?anime=random"', html)
html = re.sub(r'href="/random"', 'href="watch.html?anime=random"', html)

# Replace logo with local assets/images/logo.png
html = re.sub(r'https://hianime\.at/theme/images/logo\.png', 'assets/images/logo.png', html)

# Title and meta description
html = re.sub(r'<title>.*?</title>', '<title>HiAnime - Watch Free HD Anime Online with Hindi Sub & Dub</title>', html)

with open(output_path, 'w', encoding='utf-8') as f:
    f.write(html)

print("Created index.html, size:", len(html))
