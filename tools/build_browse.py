import re

filter_raw_path = r'C:\Users\Ritesh\.gemini\antigravity-ide\brain\f8daebcc-cad5-41b8-8947-1db54d4dffb4\filter_raw.html'
output_path = r'c:\Users\Ritesh\Downloads\anixohindi\browse.html'
filter_output_path = r'c:\Users\Ritesh\Downloads\anixohindi\filter.html'

with open(filter_raw_path, 'r', encoding='utf-8', errors='ignore') as f:
    html = f.read()

# Replace CSS
html = re.sub(r'<link rel="stylesheet" href="https://hianime\.at/theme/css/app\.min\.css[^"]*">',
              '<link rel="stylesheet" href="assets/css/app.min.css">\n    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">\n    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&display=swap">\n    <link rel="stylesheet" href="assets/css/custom.css">', html)

html = re.sub(r'<link rel="stylesheet" href="https://hianime\.at/build/assets/frontend-DDmvDXjb\.css[^"]*"\s*/?>',
              '<link rel="stylesheet" href="assets/css/frontend.css">', html)

# Replace JS
html = re.sub(r'<script data-cfasync="false" src="https://hianime\.at/theme/js/vendor\.min\.js[^"]*"></script>',
              '<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>\n<script src="assets/js/vendor.min.js"></script>', html)

html = re.sub(r'<script data-cfasync="false" src="https://hianime\.at/theme/js/theme\.min\.js[^"]*"></script>',
              '<script src="assets/js/theme.min.js"></script>', html)

html = re.sub(r'<script data-cfasync="false" src="https://hianime\.at/theme/js/jquery\.qtip\.min\.js[^"]*"></script>',
              '<script src="assets/js/jquery.qtip.min.js"></script>\n<script src="assets/js/main.js"></script>', html)

# Remove tracking scripts
html = re.sub(r'<script type="module" src="https://hianime\.at/build/assets/[^"]*".*?</script>', '', html)
html = re.sub(r'<link rel="modulepreload"[^>]*>', '', html)
html = re.sub(r'<link rel="preload" as="style" href="https://hianime\.at/build/assets/[^"]*" />', '', html)
html = re.sub(r'<script async src="https://www.googletagmanager.com[^"]*"></script>', '', html)
html = re.sub(r'gtag\([^\)]*\);?', '', html)

# Navigation links
html = re.sub(r'href="https://hianime\.at/home"', 'href="index.html"', html)
html = re.sub(r'href="/home"', 'href="index.html"', html)
html = re.sub(r'href="https://hianime\.at/"', 'href="index.html"', html)
html = re.sub(r'href="/"', 'href="index.html"', html)

# Watch links -> watch.html?anime=...
def fix_watch_link(match):
    full_url = match.group(1)
    slug = full_url.rstrip('/').split('/')[-1]
    return f'href="watch.html?anime={slug}"'

html = re.sub(r'href="https://hianime\.at/watch/([^"]+)"', fix_watch_link, html)
html = re.sub(r'href="/watch/([^"]+)"', fix_watch_link, html)

categories = [
    'recently-updated', 'recently-added', 'top-airing', 'most-popular', 'most-favorite', 
    'completed', 'filter', 'subbed-anime', 'dubbed-anime', 'movie', 'tv', 'ova', 'ona', 'special',
    'genre/[^"]+', 'az-list'
]
for cat in categories:
    html = re.sub(rf'href="https://hianime\.at/{cat}"', 'href="browse.html"', html)
    html = re.sub(rf'href="/{cat}"', 'href="browse.html"', html)

html = re.sub(r'https://hianime\.at/theme/images/logo\.png', 'assets/images/logo.png', html)
html = re.sub(r'action="https://hianime\.at/filter"', 'action="browse.html"', html)

# Title
html = re.sub(r'<title>.*?</title>', '<title>Filter & Browse Anime - HiAnime Hindi</title>', html)

with open(output_path, 'w', encoding='utf-8') as f:
    f.write(html)

with open(filter_output_path, 'w', encoding='utf-8') as f:
    f.write(html)

print("Created browse.html and filter.html, size:", len(html))
