// Netease-style Landing Page Animation Script (Progressive Enhancement)
document.addEventListener('DOMContentLoaded', function () {

    // 1. Reveal Animations (Intersection Observer)
    const revealElements = document.querySelectorAll('.lp-reveal');

    // Fallback: If IntersectionObserver is not supported, just make everything visible
    if (!('IntersectionObserver' in window)) {
        revealElements.forEach(el => el.classList.add('is-visible'));
    } else {
        const observerOptions = {
            root: null,
            rootMargin: '0px 0px -50px 0px', // Trigger slightly before it comes fully into view
            threshold: 0.15
        };

        const revealObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                // Also reveal elements that are already visible on load
                if (entry.isIntersecting || entry.boundingClientRect.top < window.innerHeight) {
                    entry.target.classList.add('is-visible');
                    // Unobserve after revealing to ensure it only runs once
                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);

        revealElements.forEach(el => {
            // Check if already in viewport on load, to reveal immediately without scrolling
            const rect = el.getBoundingClientRect();
            if (rect.top < window.innerHeight && rect.bottom >= 0) {
                el.classList.add('is-visible');
            } else {
                revealObserver.observe(el);
            }
        });
    }

    // 2. Parallax Effect (throttled via requestAnimationFrame)
    const parallaxElements = document.querySelectorAll('.lp-parallax');

    // Check for reduced motion preference
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const isMobile = window.innerWidth <= 768;

    if (!prefersReducedMotion && !isMobile && parallaxElements.length > 0) {
        let ticking = false;
        const scroller = document.querySelector('.lp-main-scroller') || window;

        function updateParallax() {
            const scrolled = scroller === window ? window.pageYOffset : scroller.scrollTop;
            parallaxElements.forEach(el => {
                // Determine speed (default 0.3)
                const speed = el.dataset.speed || 0.3;
                const yPos = -(scrolled * speed);
                el.style.transform = `translateY(${yPos}px)`;
            });
            ticking = false;
        }

        scroller.addEventListener('scroll', () => {
            if (!ticking) {
                window.requestAnimationFrame(updateParallax);
                ticking = true;
            }
        });
    }

    // 3. Smooth Scroll offset for sticky nav (already handled by CSS scroll-behavior + offset, but ensuring clicks work smoothly)
    document.querySelectorAll('.lp-nav-menu a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const targetId = this.getAttribute('href');
            if (targetId === '#') return;

            const targetElement = document.querySelector(targetId);
            const scroller = document.querySelector('.lp-main-scroller') || window;
            
            if (targetElement) {
                const navHeight = document.querySelector('.lp-topbar').offsetHeight || 70;
                const elementPosition = targetElement.getBoundingClientRect().top;
                
                let offsetPosition;
                if (scroller === window) {
                    offsetPosition = elementPosition + window.pageYOffset - navHeight;
                } else {
                    const scrollerTop = scroller.getBoundingClientRect().top;
                    offsetPosition = scroller.scrollTop + elementPosition - scrollerTop - navHeight;
                }

                scroller.scrollTo({
                    top: offsetPosition,
                    behavior: 'smooth'
                });
            }
        });
    });
});

