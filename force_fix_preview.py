import re
import time

with open('preview.html', 'r', encoding='utf-8') as f:
    content = f.read()

# 1. Cache busting for app.css
content = re.sub(r'href="assets/app\.css(\?v=\d+)?"', f'href="assets/app.css?v={int(time.time())}"', content)

# 2. Add tilt-card to lp-featured-card and lp-news-featured
content = content.replace('class="lp-featured-card"', 'class="lp-featured-card tilt-card"')
content = content.replace('class="lp-news-featured"', 'class="lp-news-featured tilt-card"')
# Also fix any that were mistakenly left out
if 'tilt-card' not in content.split('class="lp-featured-card"')[0]:
    pass # Already replacing

# 3. Add float-anim to lp-fc-icon if not present
content = re.sub(r'class="lp-fc-icon"', 'class="lp-fc-icon float-anim"', content)
# Make sure we don't double float-anim
content = content.replace('class="lp-fc-icon float-anim float-anim"', 'class="lp-fc-icon float-anim"')

with open('preview.html', 'w', encoding='utf-8') as f:
    f.write(content)
print("Preview HTML forcefully updated!")
