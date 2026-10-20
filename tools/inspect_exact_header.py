with open(r'C:\Users\Ritesh\.gemini\antigravity-ide\brain\f8daebcc-cad5-41b8-8947-1db54d4dffb4\.system_generated\steps\149\content.md', 'r', encoding='utf-8', errors='ignore') as f:
    home_html = f.read()

start_header = home_html.find('<div id="header"')
end_header = home_html.find('<!--/header-->')
if end_header == -1:
    end_header = home_html.find('<div class="deslide-wrap">')

exact_header = home_html[start_header:end_header]
print("=== EXACT REAL HIANIME NAVBAR ===")
print("Start:", start_header, "End:", end_header, "Length:", len(exact_header))
print(exact_header)
