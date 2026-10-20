import re

# 1. Inspect Header from step 149
with open(r'C:\Users\Ritesh\.gemini\antigravity-ide\brain\f8daebcc-cad5-41b8-8947-1db54d4dffb4\.system_generated\steps\149\content.md', 'r', encoding='utf-8', errors='ignore') as f:
    home_html = f.read()

start_header = home_html.find('<header id="header"')
end_header = home_html.find('</header>') + len('</header>')
exact_header = home_html[start_header:end_header]
print("=== EXACT REAL HIANIME HEADER ===")
print("Length:", len(exact_header))
print(exact_header[:2000])

# 2. Inspect Watch Page Main Wrapper
with open(r'C:\Users\Ritesh\.gemini\antigravity-ide\brain\f8daebcc-cad5-41b8-8947-1db54d4dffb4\watch_raw.html', 'r', encoding='utf-8', errors='ignore') as f:
    watch_html = f.read()

start_watch = watch_html.find('<div id="main-wrapper"')
end_watch = watch_html.find('</main>')
if end_watch == -1: end_watch = watch_html.find('<footer')
print("\n=== EXACT REAL HIANIME WATCH MAIN ===")
print("Watch main start:", start_watch, "end:", end_watch)
print(watch_html[start_watch:start_watch+2000])

# 3. Inspect Filter Page Main Wrapper
with open(r'C:\Users\Ritesh\.gemini\antigravity-ide\brain\f8daebcc-cad5-41b8-8947-1db54d4dffb4\filter_raw.html', 'r', encoding='utf-8', errors='ignore') as f:
    filter_html = f.read()

start_filter = filter_html.find('<div id="main-wrapper"')
end_filter = filter_html.find('<footer')
print("\n=== EXACT REAL HIANIME FILTER MAIN ===")
print("Filter main start:", start_filter, "end:", end_filter)
print(filter_html[start_filter:start_filter+2000])
