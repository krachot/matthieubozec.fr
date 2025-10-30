import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    connect() {
        const success = this.element.querySelector('[role="status"]');
        if (success) {
            success.focus({ preventScroll: true });
            return;
        }

        const error = this.element.querySelector(
            '.is-invalid, [aria-invalid="true"]',
        );
        if (error) {
            error.scrollIntoView({ behavior: "smooth", block: "center" });
            error.focus({ preventScroll: true });
        }
    }
}
