import re

def main():
    try:
        with open('preview.html', 'r', encoding='utf-8') as f:
            content = f.read()
    except FileNotFoundError:
        print("preview.html not found.")
        return

    # Add .js class
    if "document.documentElement.classList.add('js')" not in content:
        content = re.sub(
            r'(<meta name="viewport" content="width=device-width, initial-scale=1.0">)',
            r'\1\n    <script>document.documentElement.classList.add("js");</script>',
            content
        )

    # Hero Section
    content = re.sub(r'<video class="lp-hero-video"(.*?)>', r'<video class="lp-hero-video lp-parallax" data-speed="0.25"\1>', content)
    content = re.sub(r'<h1 class="lp-hero-title(.*?)"(.*?)>(.*?)</h1>', r'<h1 class="lp-hero-title lp-reveal" style="--i:1">\3</h1>', content)
    content = re.sub(r'<p class="lp-hero-subtitle(.*?)"(.*?)>(.*?)</p>', r'<p class="lp-hero-subtitle lp-reveal" style="--i:2">\3</p>', content)
    content = re.sub(r'<p class="lp-hero-lead(.*?)"(.*?)>(.*?)</p>', r'<p class="lp-hero-lead lp-reveal" style="--i:3">\3</p>', content)
    content = re.sub(r'<div class="lp-hero-action(.*?)" style="(.*?)">', r'<div class="lp-hero-action lp-reveal" style="\2; --i:4;">', content)

    # Fitur Utama
    content = re.sub(
        r'<h2 class="lp-section-title"(.*?)>Fitur Utama</h2>',
        r'<h2 class="lp-section-title lp-reveal"\1>Fitur Utama</h2>',
        content
    )
    # Fitur cards
    # They look like: <div class="lp-featured-card tilt-card"> or <div class="lp-featured-card tilt-card" ...>
    # In preview.html they might be <div class="lp-featured-card tilt-card">
    
    parts = content.split('<div class="lp-featured-card')
    for i in range(1, len(parts)):
        # insert lp-reveal style="--i:{i}"
        parts[i] = re.sub(r' tilt-card"(.*?)>', f' lp-reveal tilt-card" style="--i:{i}"\g<1>>', parts[i], count=1)
    content = '<div class="lp-featured-card'.join(parts)

    # Layanan Lainnya
    content = re.sub(
        r'<h2 class="lp-section-title"(.*?)>Layanan Lainnya</h2>',
        r'<h2 class="lp-section-title lp-reveal"\1>Layanan Lainnya</h2>',
        content
    )
    # Rows
    parts = content.split('<a href="#" class="lp-row-item">')
    for i in range(1, len(parts)):
        parts[i] = f'<a href="#" class="lp-row-item lp-reveal" style="--i:{i}">' + parts[i][:] # this is wrong, split just breaks the delimiter
    
    # Correct way to replace multiple identical strings
    content = re.sub(r'<a href="#" class="lp-row-item">', lambda m, c={"i":1}: f'<a href="#" class="lp-row-item lp-reveal" style="--i:{c.setdefault("i", 1) and c["i"]}">{c.update({"i": c["i"]+1}) or ""}', content)
    # The lambda trick: c["i"] gets updated. But simpler way:
    
    # We will just write a function to replace it
    def repl_row(m, c=[1]):
        res = f'<a href="#" class="lp-row-item lp-reveal" style="--i:{c[0]}">'
        c[0] += 1
        return res
    content = re.sub(r'<a href="#" class="lp-row-item">', repl_row, content)


    # Notifikasi
    content = re.sub(r'<div class="lp-section-header"', r'<div class="lp-section-header lp-reveal"', content)
    content = re.sub(r'<div class="lp-news-featured tilt-card">', r'<div class="lp-news-featured lp-reveal tilt-card" style="--i:1">', content)
    
    def repl_news(m, c=[2]):
        res = f'<a href="{m.group(1)}" target="_blank" class="lp-news-item lp-reveal{" tilt-card" if m.group(2) else ""}" style="--i:{c[0]}">'
        c[0] += 1
        return res
    content = re.sub(r'<a href="(.*?)" target="_blank" class="lp-news-item( tilt-card)?">', repl_news, content)


    # Peta
    content = re.sub(r'<h2 class="lp-section-title"(.*?)>Jaringan\s*Peradilan</h2>', r'<h2 class="lp-section-title lp-reveal"\1>Jaringan Peradilan</h2>', content)
    content = re.sub(r'<div class="lp-map-discover">', r'<div class="lp-map-discover lp-reveal">', content)
    
    def repl_marker(m):
        idx_str = m.group(2)
        idx = int(idx_str) if idx_str.isdigit() else 0
        return f'<div class="lp-discover-marker lp-reveal lp-reveal--pop{m.group(1)}" data-idx="{idx_str}" style="--i:{idx+1};{m.group(3)}">'
    
    # Wait, preview.html might have: <div class="lp-discover-marker" data-idx="0" style="left: ...; top: ...;">
    # Let's just catch all markers
    content = re.sub(r'<div class="lp-discover-marker(.*?)" data-idx="(.*?)" style="(.*?)">', repl_marker, content)

    # JS
    script_pattern = r'<script>\s*document\.addEventListener\(\'DOMContentLoaded\', function\(\) \{[\s\S]*?</script>\s*</body>'
    if re.search(script_pattern, content):
        content = re.sub(script_pattern, r'<script src="assets/landing.js"></script>\n</body>', content)
    else:
        # Just replace </body>
        if '<script src="assets/landing.js"></script>' not in content:
            content = content.replace('</body>', '<script src="assets/landing.js"></script>\n</body>')

    with open('preview.html', 'w', encoding='utf-8') as f:
        f.write(content)
        
    print("Updated preview.html successfully!")

if __name__ == "__main__":
    main()
