// PHPCop - Dependency Patrol JavaScript
// Interactive elements for the project site

// Copy Code Functionality
function copyCode(button) {
    const codeBlock = button.parentElement;
    const code = codeBlock.querySelector('code').textContent;

    navigator.clipboard.writeText(code).then(() => {
        const originalText = button.textContent;
        button.textContent = 'Copied!';
        button.style.background = 'rgba(34, 197, 94, 0.3)';

        setTimeout(() => {
            button.textContent = originalText;
            button.style.background = '';
        }, 2000);
    }).catch(err => {
        console.error('Failed to copy code:', err);
        button.textContent = 'Failed';

        setTimeout(() => {
            button.textContent = 'Copy';
        }, 2000);
    });
}

// Smooth Scroll for Navigation Links
document.addEventListener('DOMContentLoaded', () => {
    // Smooth scroll for anchor links
    const navLinks = document.querySelectorAll('a[href^="#"]');

    navLinks.forEach(link => {
        link.addEventListener('click', (e) => {
            const href = link.getAttribute('href');

            // Skip if it's just "#"
            if (href === '#') return;

            e.preventDefault();

            const targetId = href.substring(1);
            const targetElement = document.getElementById(targetId);

            if (targetElement) {
                const navbarHeight = document.querySelector('.navbar').offsetHeight;
                const targetPosition = targetElement.offsetTop - navbarHeight - 20;

                window.scrollTo({
                    top: targetPosition,
                    behavior: 'smooth'
                });
            }
        });
    });

    // Add active class to navigation on scroll
    const sections = document.querySelectorAll('section[id]');
    const navLinksWithHref = document.querySelectorAll('.nav-links a');

    function highlightNavOnScroll() {
        const scrollPosition = window.scrollY + 100;

        sections.forEach(section => {
            const sectionTop = section.offsetTop;
            const sectionHeight = section.offsetHeight;
            const sectionId = section.getAttribute('id');

            if (scrollPosition >= sectionTop && scrollPosition < sectionTop + sectionHeight) {
                navLinksWithHref.forEach(link => {
                    link.classList.remove('active');
                    if (link.getAttribute('href') === `#${sectionId}`) {
                        link.classList.add('active');
                    }
                });
            }
        });
    }

    window.addEventListener('scroll', highlightNavOnScroll);

    // Navbar scroll effect
    const navbar = document.querySelector('.navbar');
    let lastScroll = 0;

    window.addEventListener('scroll', () => {
        const currentScroll = window.pageYOffset;

        if (currentScroll > 50) {
            navbar.style.boxShadow = '0 4px 6px -1px rgb(0 0 0 / 0.1)';
        } else {
            navbar.style.boxShadow = 'none';
        }

        lastScroll = currentScroll;
    });

    // Animate elements on scroll
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);

    // Observe feature cards
    const featureCards = document.querySelectorAll('.feature-card, .format-card, .contribute-card');
    featureCards.forEach(card => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(card);
    });

    // Observe method cards with stagger effect
    const methodCards = document.querySelectorAll('.method-card');
    methodCards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        card.style.transition = `opacity 0.6s ease ${index * 0.1}s, transform 0.6s ease ${index * 0.1}s`;
        observer.observe(card);
    });

    // Terminal typing effect (optional enhancement)
    const terminalBody = document.querySelector('.terminal-body code');
    if (terminalBody) {
        const originalContent = terminalBody.innerHTML;
        let isTypingEffectShown = false;

        const terminalObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting && !isTypingEffectShown) {
                    isTypingEffectShown = true;
                    // Just show it immediately - typing effect can be too slow
                    terminalBody.style.opacity = '1';
                }
            });
        }, { threshold: 0.5 });

        terminalObserver.observe(terminalBody);
    }

    // Add easter egg: Konami code for police siren sound
    let konamiCode = [];
    const konamiPattern = [
        'ArrowUp', 'ArrowUp', 'ArrowDown', 'ArrowDown',
        'ArrowLeft', 'ArrowRight', 'ArrowLeft', 'ArrowRight',
        'b', 'a'
    ];

    document.addEventListener('keydown', (e) => {
        konamiCode.push(e.key);
        konamiCode = konamiCode.slice(-10);

        if (konamiCode.join(',') === konamiPattern.join(',')) {
            activatePoliceMode();
        }
    });

    function activatePoliceMode() {
        // Create a fun police mode animation
        const body = document.body;
        body.style.animation = 'police-flash 0.5s linear 6';

        // Add the animation if it doesn't exist
        if (!document.querySelector('#police-mode-style')) {
            const style = document.createElement('style');
            style.id = 'police-mode-style';
            style.textContent = `
                @keyframes police-flash {
                    0%, 100% { background-color: #ffffff; }
                    25% { background-color: rgba(220, 38, 38, 0.1); }
                    75% { background-color: rgba(37, 99, 235, 0.1); }
                }
            `;
            document.head.appendChild(style);
        }

        // Show alert
        setTimeout(() => {
            alert('🚨 POLICE MODE ACTIVATED! 🚓\n\nWee-oo wee-oo! PHPCop is on high alert!');
        }, 500);

        // Reset animation
        setTimeout(() => {
            body.style.animation = '';
        }, 3000);
    }

    // Add mobile menu toggle (for future mobile nav)
    const createMobileMenu = () => {
        const nav = document.querySelector('.navbar .container');
        const navLinks = document.querySelector('.nav-links');

        if (window.innerWidth <= 768 && navLinks) {
            // Mobile menu toggle button
            if (!document.querySelector('.mobile-menu-toggle')) {
                const toggleBtn = document.createElement('button');
                toggleBtn.className = 'mobile-menu-toggle';
                toggleBtn.innerHTML = '☰';
                toggleBtn.style.cssText = `
                    display: block;
                    background: none;
                    border: none;
                    font-size: 1.5rem;
                    cursor: pointer;
                    color: var(--text-primary);
                `;

                toggleBtn.addEventListener('click', () => {
                    navLinks.style.display = navLinks.style.display === 'flex' ? 'none' : 'flex';
                    navLinks.style.flexDirection = 'column';
                    navLinks.style.position = 'absolute';
                    navLinks.style.top = '100%';
                    navLinks.style.left = '0';
                    navLinks.style.right = '0';
                    navLinks.style.background = 'white';
                    navLinks.style.padding = '1rem';
                    navLinks.style.boxShadow = '0 4px 6px -1px rgb(0 0 0 / 0.1)';
                });

                nav.insertBefore(toggleBtn, document.querySelector('.nav-cta'));
            }
        }
    };

    // Initialize mobile menu if needed
    createMobileMenu();
    window.addEventListener('resize', createMobileMenu);

    // Add particle effect to hero section (optional visual enhancement)
    const addHeroParticles = () => {
        const hero = document.querySelector('.hero');
        if (!hero || hero.querySelector('.particles')) return;

        const particles = document.createElement('div');
        particles.className = 'particles';
        particles.style.cssText = `
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            pointer-events: none;
            opacity: 0.1;
        `;

        // Create floating emoji particles
        const emojis = ['🚓', '🚨', '🛡️', '⚡'];
        for (let i = 0; i < 15; i++) {
            const particle = document.createElement('div');
            particle.textContent = emojis[Math.floor(Math.random() * emojis.length)];
            particle.style.cssText = `
                position: absolute;
                font-size: ${Math.random() * 20 + 10}px;
                left: ${Math.random() * 100}%;
                top: ${Math.random() * 100}%;
                animation: float ${Math.random() * 10 + 10}s linear infinite;
                animation-delay: ${Math.random() * 5}s;
            `;
            particles.appendChild(particle);
        }

        // Add float animation
        if (!document.querySelector('#float-animation')) {
            const style = document.createElement('style');
            style.id = 'float-animation';
            style.textContent = `
                @keyframes float {
                    0%, 100% {
                        transform: translateY(0) rotate(0deg);
                        opacity: 0;
                    }
                    10% {
                        opacity: 0.5;
                    }
                    90% {
                        opacity: 0.5;
                    }
                    50% {
                        transform: translateY(-100vh) rotate(360deg);
                    }
                }
            `;
            document.head.appendChild(style);
        }

        hero.appendChild(particles);
    };

    // Add particles to hero
    addHeroParticles();

    // Log PHPCop ASCII art to console
    console.log(`
    ╔═══════════════════════════════════════╗
    ║                                       ║
    ║         🚓 PHPCop v1.0.0 🚓          ║
    ║     Dependency Patrol Division        ║
    ║                                       ║
    ║   Protecting PHP codebases since     ║
    ║              2024                     ║
    ║                                       ║
    ╚═══════════════════════════════════════╝

    🚨 Console patrol activated!
    👮 Thanks for checking out PHPCop!
    📦 GitHub: https://github.com/hfryan/php-cop

    Easter Egg: Try the Konami Code! ⬆️⬆️⬇️⬇️⬅️➡️⬅️➡️BA
    `);
});

// Add click effect to badges
document.addEventListener('DOMContentLoaded', () => {
    const badges = document.querySelectorAll('.badges img');
    badges.forEach(badge => {
        badge.style.cursor = 'pointer';
        badge.addEventListener('click', () => {
            badge.style.transform = 'scale(1.1)';
            setTimeout(() => {
                badge.style.transform = 'scale(1)';
            }, 200);
        });
    });
});

// Preload optimization
window.addEventListener('load', () => {
    // Mark page as fully loaded
    document.body.classList.add('loaded');
});
