document.addEventListener("DOMContentLoaded", async () => {
    const form = document.querySelector(".shifts-form");
    if (!form) return;

    // Referencias a elementos
    const clockOutRow = form.querySelector(".js-clock-out");
    const clockInRow = document.getElementById("clock-in-section");
    const locationRow = document.getElementById("shift-form-location");
    const infoBox = document.getElementById("active-shift-info") || createInfoBox(form);

    const locationSelect = form.querySelector("select");
    const dateInput = document.getElementById("shift-date");
    const clockInInput = document.getElementById("shift-clock-in");
    const clockOutInput = clockOutRow.querySelector('input[type="time"]');

    // Datos de sesión
    const user = JSON.parse(localStorage.getItem("user"));
    const token = localStorage.getItem("token");
    if (!user?.employee?.id || !token) {
        console.error("Faltan credenciales de sesión");
        return;
    }

    const employeeId = user.employee.id;
    let activeShift = null;

    // Estado inicial: ocultamos salida hasta confirmar turno activo
    clockOutRow.style.display = "none";

    // -------- 1. VERIFICAR TURNO ACTIVO AL CARGAR --------
    try {
        const res = await fetch(`http://localhost:9000/shifts/active?employee_id=${employeeId}`, {
            headers: { "Authorization": `Bearer ${token}` }
        });
        const data = await res.json();

        if (data.ok && data.shift) {
            setupActiveShiftUI(data.shift);
        }
    } catch (err) {
        console.error("Error verificando turno:", err);
    }

    // -------- 2. MANEJADOR ÚNICO DE SUBMIT --------
    form.addEventListener("submit", async (e) => {
        e.preventDefault();
        
        if (activeShift) {
            await handleClockOut();
        } else {
            await handleClockIn();
        }
    });

    // -------- 3. FUNCIONES DE LÓGICA (CLOCK IN) --------
    async function handleClockIn() {
        const locationId = locationSelect.value;
        
        // Validación: que no sea el placeholder
        if (!locationId || locationId === "" || locationSelect.selectedIndex === 0) {
            alert("Please select a valid work location");
            return;
        }

        try {
            const res = await fetch("http://localhost:9000/shifts/start", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Authorization": `Bearer ${token}`
                },
                body: JSON.stringify({
                    employee_id: employeeId,
                    location_id: locationId
                    // El servidor debería asignar la hora de entrada actual
                })
            });

            const data = await res.json();
            if (!res.ok) throw new Error(data.error || "Error starting shift");

            activeShift = { id: data.shift_id, ...data.shift };
            setupActiveShiftUI(activeShift);
            alert("Shift started successfully!");

        } catch (err) {
            alert(err.message);
        }
    }

    // -------- 4. FUNCIONES DE LÓGICA (CLOCK OUT) --------
async function handleClockOut() {
    // 1. Primero capturamos el input de la UI
    const clockOutInput = clockOutRow.querySelector('input[type="time"]');
    
    if (!clockOutInput.value) {
        alert("Please provide a clock out time");
        return;
    }

    // 2. Definimos la fecha (Aquí es donde fallaba antes)
    const dateVal = dateInput.value || new Date().toISOString().split('T')[0];
    const fullClockOut = `${dateVal} ${clockOutInput.value}:00`; 

    // 3. AHORA SÍ usamos fullClockOut en el fetch
    try {
        const res = await fetch("http://localhost:9000/shifts/end", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "Authorization": `Bearer ${token}`
            },
            body: JSON.stringify({
                shift_id: activeShift.id,
                clock_out: fullClockOut // <--- Aquí ya está inicializada
            })
        });

        // 4. Manejo de respuesta para evitar el "Unexpected Token"
        if (res.ok) {
            alert("Shift closed successfully!");
            window.location.reload(); // Esto hace el refresh que pediste
        } else {
            const errorData = await res.text();
            console.error("Error del servidor:", errorData);
            alert("Error closing shift.");
        }

    } catch (err) {
        console.error("Error en la petición:", err);
    }
}

    // -------- 5. HELPERS DE UI --------
function setupActiveShiftUI(shift) {
    activeShift = shift;
    
    // 1. Aseguramos las filas de la UI
    if (clockInRow) clockInRow.style.display = "none";
    if (locationRow) locationRow.style.display = "none";
    
    // 2. Mostramos la fila de salida
    if (clockOutRow) clockOutRow.style.display = "block";
    
    // 3. Sincronizamos la fecha
    if (dateInput) {
        // Tomamos solo la parte YYYY-MM-DD del clock_in
        dateInput.value = shift.clock_in ? shift.clock_in.split(' ')[0] : new Date().toISOString().split('T')[0];
        dateInput.readOnly = true;
    }

    // 4. ACTUALIZACIÓN DEL TEXTO (Incluyendo el ID)
    // Buscamos el elemento justo antes de usarlo para evitar el error de 'null'
    const infoBox = document.getElementById("active-shift-info");
    
    if (infoBox) {
        infoBox.style.display = "block";
        // Aquí concatenamos el ID del shift
        infoBox.innerHTML = `<strong>Shift ID: #${shift.id}</strong> | Active since: ${shift.clock_in || 'now'}`;
    } else {
        console.warn("No se encontró el elemento #active-shift-info para mostrar el ID.");
    }
    
    // 5. Cambiamos el texto del botón
    const submitBtn = form.querySelector('button[type="submit"]');
    if (submitBtn) submitBtn.textContent = "Clock Out Now";
}

    function resetUI() {
        activeShift = null;
        form.reset();
        clockOutRow.style.display = "none";
        clockInRow.style.display = "block";
        locationRow.style.display = "block";
        infoBox.style.display = "none";
        dateInput.readOnly = false;
        form.querySelector('button[type="submit"]').textContent = "Submit";
    }

    function createInfoBox(parent) {
        const div = document.createElement("div");
        div.id = "active-shift-info";
        div.className = "alert alert-info mt-3";
        div.style.display = "none";
        parent.prepend(div);
        return div;
    }
});