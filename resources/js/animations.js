import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

gsap.registerPlugin(ScrollTrigger);

function initScrollAnimations() {
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        return;
    }

    document.querySelectorAll('[data-animate="fade-up"]').forEach((el) => {
        gsap.from(el, {
            y: 40,
            opacity: 0,
            duration: 0.8,
            ease: 'power2.out',
            scrollTrigger: { trigger: el, start: 'top 85%' },
        });
    });

    document.querySelectorAll('[data-animate="stagger"]').forEach((group) => {
        gsap.from(group.children, {
            y: 30,
            opacity: 0,
            duration: 0.6,
            stagger: 0.12,
            ease: 'power2.out',
            scrollTrigger: { trigger: group, start: 'top 95%' },
        });
    });

    const hero = document.querySelector('[data-animate="hero"]');
    const heroPhoto = hero?.querySelector('[data-hero-photo]');

    if (hero && heroPhoto) {
        gsap.fromTo(heroPhoto, { scale: 1.02 }, { scale: 1, duration: 0.9, ease: 'power2.out' });
    }
}

document.addEventListener('DOMContentLoaded', initScrollAnimations);
