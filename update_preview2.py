import re

with open('preview.html', 'r', encoding='utf-8') as f:
    content = f.read()

# Fix news items
def repl_news(m, c=[2]):
    res = f'<a href="#" class="lp-news-item lp-reveal tilt-card" style="--i:{c[0]}">'
    c[0] += 1
    return res

content = re.sub(r'<a href="#" class="lp-news-item tilt-card">', repl_news, content)

# Fix map markers
def repl_marker(m):
    idx_str = m.group(1)
    idx = int(idx_str) if idx_str.isdigit() else 0
    return f'<div class="lp-discover-marker lp-reveal lp-reveal--pop" data-idx="{idx_str}" style="--i:{idx+1}; {m.group(2)}">'

# In preview.html, it might look like: <div class="lp-discover-marker " data-idx="0" style="left: 6.9404%; top: 40.5976%;">
content = re.sub(r'<div class="lp-discover-marker.*?" data-idx="(.*?)" style="(.*?)">', repl_marker, content)

with open('preview.html', 'w', encoding='utf-8') as f:
    f.write(content)

print("Fixed news and markers")
