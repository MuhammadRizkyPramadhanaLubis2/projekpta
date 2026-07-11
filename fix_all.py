import re

# --- 1. Fix app.css ---
with open('assets/app.css', 'r', encoding='utf-8') as f:
    css = f.read()

# Remove overflow: hidden from lp-featured-card to allow 3D pop-out
css = css.replace('overflow: hidden;', '/* overflow: hidden; removed for 3D */')

# Enhance the reveal animation to be more cinematic (NetEase style)
css = re.sub(r'\.reveal \{[^}]+\}', """.reveal {
    opacity: 0;
    transform: translateY(60px) scale(0.95);
    transition: opacity 1s cubic-bezier(0.19, 1, 0.22, 1), transform 1s cubic-bezier(0.19, 1, 0.22, 1);
}""", css)

# Fix float-anim and 3D pop conflict by targeting the <i> tag for float, and the div for 3D pop
# We will just redefine the advanced animations at the end of the file.
advanced_css = """
/* --- Advanced Animations (NetEase Style) --- */
@keyframes float-idle {
    0% { transform: translateY(0px); }
    50% { transform: translateY(-10px); }
    100% { transform: translateY(0px); }
}
.float-icon i {
    animation: float-idle 3s ease-in-out infinite;
    display: inline-block;
}

/* 3D Tilt Card Base Styles */
.tilt-card {
    transform-style: preserve-3d;
    transition: box-shadow 0.4s ease, transform 0.1s;
    will-change: transform;
    perspective: 1000px;
}

/* Cinematic Glow on Hover */
.tilt-card:hover {
    box-shadow: 0 30px 60px rgba(16, 185, 129, 0.3), 0 0 40px rgba(16, 185, 129, 0.1) inset;
    z-index: 10;
}

/* 3D Pop Out Elements */
.tilt-card .lp-fc-icon, .tilt-card .lp-row-icon, .tilt-card .lp-news-img-placeholder {
    transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    transform-style: preserve-3d;
}
.tilt-card:hover .lp-fc-icon, .tilt-card:hover .lp-row-icon, .tilt-card:hover .lp-news-img-placeholder {
    transform: translateZ(50px) scale(1.1);
}

.tilt-card:hover .lp-fc-title, .tilt-card:hover .lp-row-title, .tilt-card:hover .lp-news-title {
    transform: translateZ(30px);
    transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}
"""

if '/* --- Advanced Animations (NetEase Style) --- */' not in css:
    # Remove old advanced animations
    css = css.split('/* --- Advanced Animations --- */')[0]
    css += advanced_css

with open('assets/app.css', 'w', encoding='utf-8') as f:
    f.write(css)

# --- 2. Fix HTML files (beranda.php & preview.html) ---
js_logic_advanced = """
            // Advanced 3D Tilt Effect Logic
            const tiltCards = document.querySelectorAll('.tilt-card');
            tiltCards.forEach(card => {
                let rafId;
                
                card.addEventListener('mousemove', (e) => {
                    if(rafId) cancelAnimationFrame(rafId);
                    rafId = requestAnimationFrame(() => {
                        const rect = card.getBoundingClientRect();
                        const x = e.clientX - rect.left; 
                        const y = e.clientY - rect.top;
                        const centerX = rect.width / 2;
                        const centerY = rect.height / 2;
                        
                        const rotateX = ((y - centerY) / centerY) * -15; // Increased tilt
                        const rotateY = ((x - centerX) / centerX) * 15;
                        
                        card.style.transform = `perspective(1000px) scale(1.05) rotateX(${rotateX}deg) rotateY(${rotateY}deg)`;
                    });
                });
                
                card.addEventListener('mouseleave', () => {
                    if(rafId) cancelAnimationFrame(rafId);
                    card.style.transform = `perspective(1000px) scale(1) rotateX(0deg) rotateY(0deg)`;
                });
            });
"""

def process_html(filepath):
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()
    
    # Change float-anim to float-icon so it matches the new CSS
    content = content.replace('float-anim', 'float-icon')
    
    # Replace old JS with new JS
    if '// Advanced 3D Tilt Effect Logic' not in content:
        # Regex to replace the old script block
        content = re.sub(r'// 3D Tilt Effect Logic.*?}\);', js_logic_advanced.strip(), content, flags=re.DOTALL)
    
    with open(filepath, 'w', encoding='utf-8') as f:
        f.write(content)

process_html('pages/beranda.php')
process_html('preview.html')
print("All files forcefully updated with premium NetEase styles!")
