document.addEventListener("DOMContentLoaded", () => {

    const form = document.querySelector(".app-form");

    if (!form) {
        console.error("Login form not found");
        return;
    }

    form.addEventListener("submit", async (e) => {
        e.preventDefault();

        const email = document.getElementById("username").value.trim();
        const password = document.getElementById("password").value.trim();

        if (!email || !password) {
            alert("Please enter email and password");
            return;
        }

        try {
            const response = await fetch("http://localhost:9000/auth/login", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({ email, password })
            });

            const data = await response.json();

            if (!response.ok) {
                alert(data.error || "Login failed");
                return;
            }

             localStorage.setItem("token", data.token);

            localStorage.setItem("user", JSON.stringify(data.user));

            window.location.href = "app.php";

        } catch (err) {
            console.error(err);
            alert("Cannot connect to server");
        }
    });

});
