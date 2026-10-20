import re

with open(r'C:\Users\Ritesh\.gemini\antigravity-ide\brain\f8daebcc-cad5-41b8-8947-1db54d4dffb4\.system_generated\steps\149\content.md', 'r', encoding='utf-8', errors='ignore') as f:
    html = f.read()

for title in ['Latest Episode', 'Top 10', 'Top Airing', 'Most Popular', 'Black Clover']:
    idx = html.find(title)
    print(f"'{title}' index: {idx}")
    if idx != -1:
        start = max(0, idx - 200)
        end = min(len(html), idx + 200)
        print("Context:\n", html[start:end])
        print("="*50)
