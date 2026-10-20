import re

with open(r'C:\Users\Ritesh\.gemini\antigravity-ide\brain\f8daebcc-cad5-41b8-8947-1db54d4dffb4\.system_generated\steps\149\content.md', 'r', encoding='utf-8', errors='ignore') as f:
    home_html = f.read()

start_header = home_html.find('<div id="header"')
end_header = home_html.find('<div class="deslide-wrap">')
exact_header = home_html[start_header:end_header].strip()

# Also find sidebar_menu in step 149
start_sidebar = home_html.find('<div id="sidebar_menu">')
end_sidebar = home_html.find('<div id="wrapper">')
exact_sidebar = home_html[start_sidebar:end_sidebar].strip()

print("Header length:", len(exact_header))
print("Sidebar length:", len(exact_sidebar))

# Save them to scratch files so we can inspect and use them directly
with open('tools/exact_header.html', 'w', encoding='utf-8') as f:
    f.write(exact_header)

with open('tools/exact_sidebar.html', 'w', encoding='utf-8') as f:
    f.write(exact_sidebar)

print("Saved exact_header.html and exact_sidebar.html successfully!")
