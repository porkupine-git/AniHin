# Extract exact watch and filter mains
with open(r'C:\Users\Ritesh\.gemini\antigravity-ide\brain\f8daebcc-cad5-41b8-8947-1db54d4dffb4\watch_raw.html', 'r', encoding='utf-8', errors='ignore') as f:
    watch_html = f.read()

start_watch = watch_html.find('<div id="main-wrapper"')
end_watch = watch_html.find('<div id="footer"')
if end_watch == -1: end_watch = watch_html.find('<footer')
exact_watch = watch_html[start_watch:end_watch].strip()

with open('tools/exact_watch_main.html', 'w', encoding='utf-8') as f:
    f.write(exact_watch)
print("Saved exact_watch_main.html, length:", len(exact_watch))

with open(r'C:\Users\Ritesh\.gemini\antigravity-ide\brain\f8daebcc-cad5-41b8-8947-1db54d4dffb4\filter_raw.html', 'r', encoding='utf-8', errors='ignore') as f:
    filter_html = f.read()

start_filter = filter_html.find('<div id="main-wrapper"')
end_filter = filter_html.find('<div id="footer"')
if end_filter == -1: end_filter = filter_html.find('<footer')
exact_filter = filter_html[start_filter:end_filter].strip()

with open('tools/exact_filter_main.html', 'w', encoding='utf-8') as f:
    f.write(exact_filter)
print("Saved exact_filter_main.html, length:", len(exact_filter))
