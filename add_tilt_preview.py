import re

with open('preview.html', 'r', encoding='utf-8') as f:
    content = f.read()

# Add tilt-card to cards
content = content.replace('class="lp-featured-card reveal reveal-delay-1"', 'class="lp-featured-card reveal reveal-delay-1 tilt-card"')
content = content.replace('class="lp-featured-card reveal reveal-delay-2"', 'class="lp-featured-card reveal reveal-delay-2 tilt-card"')
content = content.replace('class="lp-featured-card reveal reveal-delay-3"', 'class="lp-featured-card reveal reveal-delay-3 tilt-card"')

content = content.replace('class="lp-row-item"', 'class="lp-row-item tilt-card"')
content = content.replace('class="lp-news-item"', 'class="lp-news-item tilt-card"')
content = content.replace('class="lp-news-featured reveal reveal-delay-1"', 'class="lp-news-featured reveal reveal-delay-1 tilt-card"')

# Add float-anim
content = content.replace('class="lp-fc-icon"', 'class="lp-fc-icon float-anim"')
content = content.replace('class="lp-row-icon"', 'class="lp-row-icon float-anim"')
content = content.replace('class="lp-news-img-placeholder"', 'class="lp-news-img-placeholder float-anim"')

# Add JS logic
js_logic = """
            // 3D Tilt Effect Logic
            const tiltCards = document.querySelectorAll('.tilt-card');
            tiltCards.forEach(card => {
                card.addEventListener('mousemove', (e) => {
                    const rect = card.getBoundingClientRect();
                    const x = e.clientX - rect.left; 
                    const y = e.clientY - rect.top;
                    const centerX = rect.width / 2;
                    const centerY = rect.height / 2;
                    
                    // Calculate rotation (max 10 degrees)
                    const rotateX = ((y - centerY) / centerY) * -10;
                    const rotateY = ((x - centerX) / centerX) * 10;
                    
                    card.style.transform = `scale(1.02) rotateX(${rotateX}deg) rotateY(${rotateY}deg)`;
                });
                
                card.addEventListener('mouseleave', () => {
                    card.style.transform = `scale(1) rotateX(0deg) rotateY(0deg)`;
                });
            });
        });
    </script>"""

if 'const tiltCards' not in content:
    content = content.replace('        });\n    </script>', js_logic)

with open('preview.html', 'w', encoding='utf-8') as f:
    f.write(content)
print("preview.html updated with 3D tilt!")
