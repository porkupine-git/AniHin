with open(r'C:\Users\Ritesh\.gemini\antigravity-ide\brain\f8daebcc-cad5-41b8-8947-1db54d4dffb4\.system_generated\steps\149\content.md', 'r', encoding='utf-8', errors='ignore') as f:
    home_html = f.read()

start_sidebar = home_html.find('<nav id="sidebar_menu"')
end_sidebar = home_html.find('</nav>') + len('</nav>')
exact_sidebar = home_html[start_sidebar:end_sidebar].strip()

print("Sidebar length:", len(exact_sidebar))

with open('tools/exact_sidebar.html', 'w', encoding='utf-8') as f:
    f.write(exact_sidebar)
print("Saved exact_sidebar.html successfully!")
