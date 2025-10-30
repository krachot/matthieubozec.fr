// --- Tinylytics + Turbo integration ---
(function () {
    const SITE_CODE = "1joFoX_Pp2-r9KHygu78"; // ton identifiant Tinylytics
    const SCRIPT_URL = `https://tinylytics.app/embed/${SITE_CODE}/min.js?spa`;

    function loadTinylytics() {
        // Évite de charger plusieurs fois le script
        if (window.tinylyticsLoaded) return;
        window.tinylyticsLoaded = true;

        // Crée et insère la balise script
        const script = document.createElement("script");
        script.type = "text/javascript";
        script.defer = true;
        script.src = SCRIPT_URL;

        // Une fois chargé, on attache le listener Turbo
        script.onload = () => {
            if (
                window.tinylytics &&
                typeof window.tinylytics.triggerUpdate === "function"
            ) {
                // Première exécution (chargement initial)
                window.tinylytics.triggerUpdate();

                // Relance Tinylytics à chaque navigation Turbo
                document.addEventListener("turbo:load", () => {
                    window.tinylytics.triggerUpdate();
                });
            } else {
                console.warn(
                    "Tinylytics script loaded, but triggerUpdate() not found.",
                );
            }
        };

        document.body.appendChild(script);
    }

    // Charge Tinylytics dès que le DOM est prêt
    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", loadTinylytics);
    } else {
        loadTinylytics();
    }
})();
