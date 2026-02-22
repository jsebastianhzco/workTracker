document.addEventListener("DOMContentLoaded", () => {
    const userRaw = localStorage.getItem("user");
    if (!userRaw) return;

    const data = JSON.parse(userRaw);

    document.querySelectorAll("[data-bind]").forEach(el => {
        const path = el.dataset.bind.split(".");

        let value = data;
        for (const key of path) {
            if (value == null) break;
            value = value[key];
        }

        el.textContent = value ?? "";
    });
});