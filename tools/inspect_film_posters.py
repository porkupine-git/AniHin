import re

with open(r'c:\Users\Ritesh\Downloads\anixohindi\index.html', 'r', encoding='utf-8', errors='ignore') as f:
    html = f.read()

# Find film-poster-img tags
imgs = re.findall(r'<img[^>]+class=["\'][^"\']*film-poster-img[^"\']*["\'][^>]*>', html)
print(f"Total film-poster-img tags: {len(imgs)}")
for i, img in enumerate(imgs[:15]):
    src = re.findall(r'src=["\']([^"\']+)["\']', img)
    print(f"{i+1}: {src[0] if src else 'NO SRC'}")
