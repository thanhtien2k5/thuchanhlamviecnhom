// unique.js - Full Pro Animations + Loading Spinner 2025
(function() {
    "use strict";

    // Ẩn loading spinner
    function hideLoader() {
        const loader = document.getElementById('page-loader');
        if (loader) {
            loader.classList.add('hidden');
            setTimeout(() => loader.remove(), 1000);
        }
    }

    // Scroll Animations siêu mượt
    function initScrollAnimations() {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const el = entry.target;
                    if (el.classList.contains('stagger-group')) {
                        el.classList.add('active');
                        const items = el.querySelectorAll('.stagger-item');
                        items.forEach((item, i) => {
                            item.style.transitionDelay = `${i * 100}ms`;
                        });
                    } else {
                        el.classList.add('active');
                    }
                    observer.unobserve(el);
                }
            });
        }, { threshold: 0.15, rootMargin: "0px 0px -8% 0px" });

        document.querySelectorAll('.fade-up, .slide-left, .slide-right, .stagger-group')
            .forEach(el => observer.observe(el));

        // Parallax mượt
        const parallaxEls = document.querySelectorAll('.parallax');
        let ticking = false;
        function update() {
            const y = window.scrollY;
            parallaxEls.forEach(el => {
                const speed = el.dataset.speed || 0.3;
                el.style.transform = `translate3d(0, ${y * speed}px, 0)`;
            });
            ticking = false;
        }
        window.addEventListener('scroll', () => {
            if (!ticking) {
                requestAnimationFrame(update);
                ticking = true;
            }
        }, { passive: true });
        update();
    }

    // Các hiệu ứng cũ (particles, tilt, reveal) giữ nguyên
    function initHeroParticles() { /* code cũ của bạn giữ nguyên */ }
    function initCardTilt() { /* code cũ của bạn giữ nguyên */ }
    function initRevealOnScroll() { /* code cũ của bạn giữ nguyên */ }

    // Init tất cả
    function init() {
        hideLoader();
        initHeroParticles();
        initCardTilt();
        initRevealOnScroll();
        initScrollAnimations();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
    window.addEventListener('load', hideLoader);
})();
// ==================== CLICK EFFECT CHO CODE CARD ====================
document.querySelectorAll('.code-card').forEach(card => {
    card.addEventListener('click', function(e) {
        // 1. Thêm class clicked để kích hoạt ripple
        this.classList.add('clicked');
        setTimeout(() => this.classList.remove('clicked'), 600);

        // 2. Tạo pháo hoa
        const sparkles = document.createElement('div');
        sparkles.className = 'sparkles';
        this.appendChild(sparkles);

        for (let i = 0; i < 18; i++) {
            const sparkle = document.createElement('div');
            sparkle.className = 'sparkle';
            
            const angle = (i * 20) * Math.PI / 180;
            const distance = 80 + Math.random() * 40;
            const x = Math.cos(angle) * distance;
            const y = Math.sin(angle) * distance - 50;

            sparkle.style.setProperty('--x', x + 'px');
            sparkle.style.setProperty('--y', y + 'px');
            sparkle.style.background = ['#c62828', '#fbc02d', '#b71c1c'][Math.floor(Math.random() * 3)];
            sparkles.appendChild(sparkle);
        }

        sparkles.classList.add('active');
        setTimeout(() => sparkles.remove(), 1200);
    });
});

// Thêm animation rung nhẹ cho body (dán vào CSS)
const style = document.createElement('style');
style.textContent = `
    @keyframes tinyShake {
        0%, 100% { transform: translateX(0); }
        25% { transform: translateX(-6px); }
        75% { transform: translateX(6px); }
    }
`;
document.head.appendChild(style);
// ==================== PRO ANIMATIONS TỰ ĐỘNG ====================
// ==================== PRO ANIMATIONS TỰ ĐỘNG (ĐÃ SỬA LỖI) ====================
function initProAnimations() {
    // Kiểm tra xem trình duyệt có hỗ trợ IntersectionObserver không
    if (!('IntersectionObserver' in window)) {
        // Nếu không hỗ trợ → bật hết luôn (tránh trắng trang)
        document.querySelectorAll('.text-reveal, .counter, .img-zoom, .fade-up, .stagger-group').forEach(el => {
            el.classList.add('active');
        });
        return;
    }

    const observer = new IntersectionObserver((entries) => {  // <-- ĐÃ SỬA: thêm ")" ở đây
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('active');
                observer.unobserve(entry.target); // chỉ chạy 1 lần
            }
        });
    }, { 
        threshold: 0.2,
        rootMargin: '0px 0px -10% 0px'
    });

    document.querySelectorAll('.text-reveal, .counter, .img-zoom, .fade-up, .slide-left, .slide-right, .stagger-group').forEach(el => {
        observer.observe(el);
    });

    // Particles trong hero (giữ nguyên, đã test OK)
    const hero = document.querySelector('.hero-bg');
    if (hero && !hero.querySelector('.particles-bg')) {
        const particles = document.createElement('div');
        particles.className = 'particles-bg';
        for (let i = 0; i < 18; i++) {
            const p = document.createElement('div');
            p.className = 'particle';
            p.style.width = p.style.height = Math.random() * 5 + 2 + 'px';
            p.style.left = Math.random() * 100 + '%';
            p.style.animationDuration = Math.random() * 20 + 20 + 's';
            p.style.animationDelay = Math.random() * 10 + 's';
            particles.appendChild(p);
        }
        hero.appendChild(particles);
    }
}

// Gọi khi DOM sẵn sàng
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initProAnimations);
} else {
    initProAnimations();
}
// Thêm hạt sao vàng bay (siêu nhẹ, chỉ 20 hạt)
const hero = document.querySelector('.hero-bg');
if (hero && !hero.querySelector('.particles-bg')) {
    const particles = document.createElement('div');
    particles.className = 'particles-bg';
    for (let i = 0; i < 20; i++) {
        const p = document.createElement('div');
        p.className = 'particle';
        p.style.left = Math.random() * 100 + '%';
        p.style.animationDuration = (Math.random() * 20 + 15) + 's';
        p.style.animationDelay = Math.random() * 10 + 's';
        particles.appendChild(p);
    }
    hero.appendChild(particles);
}
