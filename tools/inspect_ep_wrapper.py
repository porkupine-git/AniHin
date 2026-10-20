with open('tools/exact_watch_main.html', 'r', encoding='utf-8') as f:
    html = f.read()

idx = html.find('episodes-content')
print(html[idx-100:idx+400])
