document.addEventListener("DOMContentLoaded", function() {
    const serviceCards = document.querySelectorAll(".service-card");

    // Filter function
    function filterServices() {
        const typeFilter = document.getElementById("service-type-filter").value;
        const priceFilter = document.getElementById("price-filter").value;
        const durationFilter = document.getElementById("duration-filter").value;

        serviceCards.forEach(card => {
            const type = card.getAttribute("data-type");
            const price = parseInt(card.getAttribute("data-price"));
            const duration = parseInt(card.getAttribute("data-duration"));

            let isVisible = true;

            // Filter by service type
            if (typeFilter !== "all" && type !== typeFilter) {
                isVisible = false;
            }

            // Filter by price range
            if (priceFilter !== "all") {
                if (priceFilter === "low" && price > 1000) {
                    isVisible = false;
                } else if (priceFilter === "medium" && (price <= 1000 || price > 2000)) {
                    isVisible = false;
                } else if (priceFilter === "high" && price <= 2000) {
                    isVisible = false;
                }
            }

            // Filter by duration
            if (durationFilter !== "all") {
                if (durationFilter === "short" && duration > 60) {
                    isVisible = false;
                } else if (durationFilter === "medium" && (duration <= 60 || duration > 90)) {
                    isVisible = false;
                } else if (durationFilter === "long" && duration <= 90) {
                    isVisible = false;
                }
            }

            card.style.display = isVisible ? "block" : "none";
        });
    }

    // Add event listeners to filters
    document.getElementById("service-type-filter").addEventListener("change", filterServices);
    document.getElementById("price-filter").addEventListener("change", filterServices);
    document.getElementById("duration-filter").addEventListener("change", filterServices);

    // Initialize filters
    filterServices();
});
