
        // Classic Android Live Wallpaper Style (Phase Beam / Floating Orbs)
        (function() {
            const canvas = document.getElementById('hero-particles');
            const ctx = canvas.getContext('2d');
            let width, height;

            function resize() {
                width = window.innerWidth;
                height = window.innerHeight;
                canvas.width = width;
                canvas.height = height;
            }
            window.addEventListener('resize', resize);
            resize();

            // OPTIMIZATION: Disable heavy canvas animation on mobile devices
            if (width <= 768) {
                // Set static background or just return to avoid loop
                ctx.fillStyle = '#022c22';
                ctx.fillRect(0, 0, width, height);
                return;
            }

            const orbs = [];
            const colors = [
                'rgba(16, 185, 129,',   // Emerald
                'rgba(247, 215, 116,',  // Gold
                'rgba(4, 120, 87,',     // Darker Green
                'rgba(52, 211, 153,'    // Light Green
            ];

            // Create 30 glowing orbs
            for (let i = 0; i < 30; i++) {
                orbs.push({
                    x: Math.random() * window.innerWidth,
                    y: Math.random() * window.innerHeight,
                    radius: Math.random() * 80 + 20,
                    vx: (Math.random() - 0.5) * 0.8,
                    vy: Math.random() * -1 - 0.2, // always drift upwards
                    baseAlpha: Math.random() * 0.15 + 0.05,
                    colorStr: colors[Math.floor(Math.random() * colors.length)]
                });
            }

            const mouse = { x: -100, y: -100 };
            document.addEventListener('mousemove', function(e) {
                mouse.x = e.clientX;
                mouse.y = e.clientY;
            });

            function animate() {
                // Base dark green background
                ctx.globalCompositeOperation = 'source-over';
                ctx.fillStyle = '#022c22';
                ctx.fillRect(0, 0, width, height);
                
                ctx.globalCompositeOperation = 'screen';

                // Draw moving orbs
                orbs.forEach(orb => {
                    orb.x += orb.vx;
                    orb.y += orb.vy;

                    // Interaction with mouse (gentle push)
                    const dx = orb.x - mouse.x;
                    const dy = orb.y - mouse.y;
                    const dist = Math.sqrt(dx * dx + dy * dy);
                    if (dist < 150) {
                        orb.x += dx * 0.01;
                        orb.y += dy * 0.01;
                    }

                    // Reset if out of bounds (loop to bottom)
                    if (orb.y < -orb.radius) {
                        orb.y = height + orb.radius;
                        orb.x = Math.random() * width;
                    }
                    if (orb.x < -orb.radius) orb.x = width + orb.radius;
                    if (orb.x > width + orb.radius) orb.x = -orb.radius;

                    // Draw soft glowing orb
                    const grad = ctx.createRadialGradient(orb.x, orb.y, 0, orb.x, orb.y, orb.radius);
                    grad.addColorStop(0, `${orb.colorStr} ${orb.baseAlpha})`);
                    grad.addColorStop(0.5, `${orb.colorStr} ${orb.baseAlpha * 0.5})`);
                    grad.addColorStop(1, `${orb.colorStr} 0)`);

                    ctx.beginPath();
                    ctx.arc(orb.x, orb.y, orb.radius, 0, Math.PI * 2);
                    ctx.fillStyle = grad;
                    ctx.fill();
                });

                // Add cursor glow (optional, subtle)
                if (mouse.x > 0) {
                    const cursorGrad = ctx.createRadialGradient(mouse.x, mouse.y, 0, mouse.x, mouse.y, 100);
                    cursorGrad.addColorStop(0, 'rgba(247, 215, 116, 0.1)');
                    cursorGrad.addColorStop(1, 'rgba(247, 215, 116, 0)');
                    ctx.beginPath();
                    ctx.arc(mouse.x, mouse.y, 100, 0, Math.PI * 2);
                    ctx.fillStyle = cursorGrad;
                    ctx.fill();
                }

                ctx.globalCompositeOperation = 'source-over';
                requestAnimationFrame(animate);
            }
            animate();
        })();
    