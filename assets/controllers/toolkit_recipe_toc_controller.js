import { Controller } from '@hotwired/stimulus';

const ACTIVE_CLASS = 'active';

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static targets = ['link'];

    connect() {
        this.linkByHeading = new Map();
        this.visible = new Set();

        this.linkTargets.forEach((link) => {
            const anchor = link.querySelector('a[href^="#"]');
            const id = anchor ? decodeURIComponent(anchor.hash.slice(1)) : '';
            const heading = id ? document.getElementById(id) : null;
            if (heading) {
                this.linkByHeading.set(heading, link);
            }
        });

        if (this.linkByHeading.size === 0) {
            return;
        }

        this.observer = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        this.visible.add(entry.target);
                    } else {
                        this.visible.delete(entry.target);
                    }
                });
                this.activate();
            },
            { rootMargin: '0px 0px -70% 0px', threshold: 0 },
        );

        this.linkByHeading.forEach((_link, heading) => this.observer.observe(heading));
        this.activate();
    }

    disconnect() {
        this.observer?.disconnect();
        this.visible?.clear();
        this.linkByHeading?.clear();
    }

    activate() {
        const activeHeading = this.pickActiveHeading();
        const activeLink = activeHeading ? this.linkByHeading.get(activeHeading) : null;

        this.linkByHeading.forEach((link) => {
            const isActive = link === activeLink;
            link.classList.toggle(ACTIVE_CLASS, isActive);
            if (isActive) {
                link.setAttribute('aria-current', 'page');
            } else {
                link.removeAttribute('aria-current');
            }
        });
    }

    pickActiveHeading() {
        if (this.visible.size > 0) {
            let top = null;
            let topY = Infinity;
            this.visible.forEach((heading) => {
                const y = heading.getBoundingClientRect().top;
                if (y < topY) {
                    topY = y;
                    top = heading;
                }
            });
            return top;
        }

        let lastAbove = null;
        this.linkByHeading.forEach((_link, heading) => {
            if (heading.getBoundingClientRect().top <= 0) {
                lastAbove = heading;
            }
        });
        return lastAbove;
    }
}
