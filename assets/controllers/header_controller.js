import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    connect() {
        this.ticking = false;

        window.addEventListener("scroll", this.requestUpdate, {
            passive: true,
        });
        window.addEventListener("DOMContentLoaded", this.update, {
            once: true,
        });
        window.addEventListener("load", this.update, { once: true });

        this.update();
    }

    disconnect() {
        window.removeEventListener("scroll", this.requestUpdate);
        window.removeEventListener("DOMContentLoaded", this.update);
        window.removeEventListener("load", this.update);
    }

    requestUpdate = () => {
        if (!this.ticking) {
            requestAnimationFrame(this.update);
            this.ticking = true;
        }
    };

    update = () => {
        const threshold = this.hasThresholdValue ? this.thresholdValue : 0;
        const scrolled = window.scrollY > threshold;

        this.element.classList.toggle("is-sticky", scrolled);

        this.ticking = false;
    };
}
