import re

with open('preview.html', 'r', encoding='utf-8') as f:
    content = f.read()

# Fix body style
content = content.replace('<body class="lp-body">', '<body class="lp-body" style="overflow: hidden; height: 100vh;">')

# Wrap with main if not already wrapped
if '<main class="lp-main-scroller">' not in content:
    content = content.replace('    <!-- HERO SECTION (FASE 1) -->\n    <nav class="lp-topbar">', '    <!-- HERO SECTION (FASE 1) -->\n    <nav class="lp-topbar">')
    # Wait, the nav is inside.
    # Let's just find the end of nav
    nav_end = content.find('</nav>')
    if nav_end != -1:
        content = content[:nav_end+6] + '\n    <main class="lp-main-scroller">' + content[nav_end+6:]
    
    # Close main before footer
    footer_start = content.find('<footer class="lp-footer">')
    if footer_start != -1:
        content = content[:footer_start] + '    </main>\n' + content[footer_start:]

# Inject observer script if not present
observer_script = """    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('in-view');
                    }
                });
            }, { threshold: 0.15 });
            document.querySelectorAll('.reveal').forEach(el => observer.observe(el));
        });
    </script>
"""
if 'IntersectionObserver' not in content:
    content = content.replace('</body>', observer_script + '</body>')

# Add reveal classes to elements inside preview.html that match
content = content.replace('<h1 class="lp-hero-title">', '<h1 class="lp-hero-title reveal reveal-delay-1">')
content = content.replace('<p class="lp-hero-subtitle">', '<p class="lp-hero-subtitle reveal reveal-delay-2">')
content = content.replace('<p class="lp-hero-lead">', '<p class="lp-hero-lead reveal reveal-delay-3">')
content = content.replace('<div class="lp-hero-action"', '<div class="lp-hero-action reveal reveal-delay-4"')

with open('preview.html', 'w', encoding='utf-8') as f:
    f.write(content)
print("preview.html fixed!")
