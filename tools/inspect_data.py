import re

def inspect(path, name):
    with open(path, 'r', encoding='utf-8', errors='ignore') as f:
        html = f.read()
    print(f"=== {name} ===")
    print("Length:", len(html))
    images = re.findall(r'src=["\']([^"\']+\.(?:jpg|jpeg|png|webp|svg))["\']', html)
    print("Total images:", len(images))
    print("Sample images:", images[:5])
    links = re.findall(r'href=["\']([^"\']+)["\']', html)
    anime_links = [l for l in links if '/watch/' in l or '/detail/' in l]
    print("Sample anime links:", anime_links[:5])

inspect(r'C:\Users\Ritesh\.gemini\antigravity-ide\brain\f8daebcc-cad5-41b8-8947-1db54d4dffb4\.system_generated\steps\149\content.md', 'Home')
inspect(r'C:\Users\Ritesh\.gemini\antigravity-ide\brain\f8daebcc-cad5-41b8-8947-1db54d4dffb4\watch_raw.html', 'Watch')
inspect(r'C:\Users\Ritesh\.gemini\antigravity-ide\brain\f8daebcc-cad5-41b8-8947-1db54d4dffb4\filter_raw.html', 'Filter')
