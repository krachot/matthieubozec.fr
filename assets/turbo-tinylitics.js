(function () {
    const SITE_CODE = "1joFoX_Pp2-r9KHygu78";
    const SCRIPT_URL = `https://tinylytics.app/embed/${SITE_CODE}/min.js?spa`;

    function initTinylyticsTurbo() {
        if (window.tinylytics?.triggerUpdate) {
            // On écoute les changements de page Turbo
            document.addEventListener("turbo:load", () => {
                window.tinylytics.triggerUpdate();
            });
        } else {
            // Si Tinylytics n’est pas encore chargé, on retente un peu plus tard
            setTimeout(initTinylyticsTurbo, 200);
        }
    }

    function loadTinylytics() {
        // Empêche le double chargement
        if (window.tinylyticsLoaded) return;
        window.tinylyticsLoaded = true;

        const script = document.createElement("script");
        script.type = "text/javascript";
        script.defer = true;
        script.src = SCRIPT_URL;

        script.onload = () => {
            initTinylyticsTurbo();
        };

        document.body.appendChild(script);
    }

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", loadTinylytics);
    } else {
        loadTinylytics();
    }
})();
