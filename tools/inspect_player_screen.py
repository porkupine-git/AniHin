with open(r'C:\Users\Ritesh\.gemini\antigravity-ide\brain\f8daebcc-cad5-41b8-8947-1db54d4dffb4\watch_raw.html', 'r', encoding='utf-8', errors='ignore') as f:
    watch_html = f.read()

idx = watch_html.find('pc-autoskipintro')
if idx != -1:
    print(watch_html[idx-1200:idx])
