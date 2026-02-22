document.addEventListener("DOMContentLoaded", () => {
    const btn = document.getElementById("logout-btn");

    if (!btn) return;

    btn.addEventListener("click", () => {
        // borrar sesión
        localStorage.removeItem("token");
        localStorage.removeItem("user");

        // redirigir al login
        window.location.replace("login.php");
    });
});
