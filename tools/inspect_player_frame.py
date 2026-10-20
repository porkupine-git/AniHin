with open('tools/exact_watch_main.html', 'r', encoding='utf-8') as f:
    html = f.read()

idx = html.find('player-frame')
print("--- player-frame ---")
print(html[idx:idx+600])
