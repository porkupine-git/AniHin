import re

with open(r'c:\Users\Ritesh\Downloads\anixohindi\index.html', 'r', encoding='utf-8', errors='ignore') as f:
    html = f.read()

data_srcs = re.findall(r'data-src=["\']([^"\']+)["\']', html)
print("Total data-src occurrences:", len(data_srcs))
print("Sample data-src:", data_srcs[:5])

blank_srcs = re.findall(r'src=["\'](data:image/[^"\']+)["\']', html)
print("Total data:image srcs:", len(blank_srcs))
