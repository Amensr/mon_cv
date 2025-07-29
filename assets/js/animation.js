// Effet de particules pour le fond
function initParticles() {
    const canvas = document.createElement('canvas');
    canvas.style.position = 'absolute';
    canvas.style.top = '0';
    canvas.style.left = '0';
    canvas.style.zIndex = '-1';
    canvas.style.opacity = '0.3';
    document.body.appendChild(canvas);
    
    const ctx = canvas.getContext('2d');
    canvas.width = window.innerWidth;
    canvas.height = window.innerHeight;
    
    const particles = [];
    const particleCount = 50;
    
    // Créer des particules
    for (let i = 0; i < particleCount; i++) {
        particles.push({
            x: Math.random() * canvas.width,
            y: Math.random() * canvas.height,
            size: Math.random() * 3 + 1,
            speedX: Math.random() * 1 - 0.5,
            speedY: Math.random() * 1 - 0.5
        });
    }
    
    // Animer les particules
    function animateParticles() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        
        for (let i = 0; i < particles.length; i++) {
            const p = particles[i];
            
            ctx.fillStyle = getComputedStyle(document.documentElement)
                .getPropertyValue('--primary-color');
            ctx.beginPath();
            ctx.arc(p.x, p.y, p.size, 0, Math.PI * 2);
            ctx.fill();
            
            // Mouvement
            p.x += p.speedX;
            p.y += p.speedY;
            
            // Rebond sur les bords
            if (p.x < 0 || p.x > canvas.width) p.speedX *= -1;
            if (p.y < 0 || p.y > canvas.height) p.speedY *= -1;
        }
        
        requestAnimationFrame(animateParticles);
    }
    
    animateParticles();
    
    // Redimensionnement
    window.addEventListener('resize', function() {
        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;
    });
}

// Effet de glitch sur les images
function applyGlitchEffect(selector) {
    const elements = document.querySelectorAll(selector);
    
    elements.forEach(el => {
        el.addEventListener('mouseenter', function() {
            this.style.position = 'relative';
            this.style.overflow = 'hidden';
            
            const glitch = document.createElement('div');
            glitch.className = 'glitch-effect';
            glitch.style.backgroundImage = `url(${this.src})`;
            glitch.style.position = 'absolute';
            glitch.style.top = '0';
            glitch.style.left = '0';
            glitch.style.width = '100%';
            glitch.style.height = '100%';
            glitch.style.opacity = '0';
            
            this.appendChild(glitch);
            
            let glitchInterval = setInterval(() => {
                glitch.style.opacity = '0.7';
                glitch.style.transform = `translateX(${Math.random() * 10 - 5}px)`;
                glitch.style.filter = `hue-rotate(${Math.random() * 360}deg)`;
                
                setTimeout(() => {
                    glitch.style.opacity = '0';
                }, 100);
            }, 300);
            
            this.addEventListener('mouseleave', function() {
                clearInterval(glitchInterval);
                this.removeChild(glitch);
            }, { once: true });
        });
    });
}

// Initialisation des effets
document.addEventListener('DOMContentLoaded', function() {
    if (document.querySelector('.cybersecurity-cv')) {
        initParticles();
    }
    
    applyGlitchEffect('.theme-card img');
    
    // Effet de saisie automatique pour les champs
    const autoTypeElements = document.querySelectorAll('.auto-type');
    autoTypeElements.forEach(el => {
        const text = el.getAttribute('data-text');
        let i = 0;
        const typing = setInterval(() => {
            if (i < text.length) {
                el.textContent += text.charAt(i);
                i++;
            } else {
                clearInterval(typing);
            }
        }, 100);
    });
});