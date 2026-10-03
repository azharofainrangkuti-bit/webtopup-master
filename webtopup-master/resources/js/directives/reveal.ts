import type { Directive } from 'vue';

/**
 * Fades and slides an element into view the first time it enters the
 * viewport. Falls back to fully visible immediately when IntersectionObserver
 * is unavailable (SSR/older browsers).
 */
const observerMap = new WeakMap<Element, IntersectionObserver>();

export const revealDirective: Directive<HTMLElement> = {
    mounted(el) {
        if (typeof IntersectionObserver === 'undefined') {
            el.classList.add('is-revealed');
            return;
        }

        el.classList.add('reveal-on-scroll');

        const observer = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        el.classList.add('is-revealed');
                        observer.unobserve(el);
                    }
                });
            },
            { threshold: 0.15, rootMargin: '0px 0px -40px 0px' },
        );

        observer.observe(el);
        observerMap.set(el, observer);
    },
    unmounted(el) {
        observerMap.get(el)?.disconnect();
        observerMap.delete(el);
    },
};
