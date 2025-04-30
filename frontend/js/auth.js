// frontend/js/auth.js
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('registroForm');

    if (form) {
        form.addEventListener('submit', function (e) {
            const nombre = form.nombre.value.trim();
            const email = form.email.value.trim();
            const password = form.password.value;

            if (nombre.length < 3) {
                alert("El nombre debe tener al menos 3 caracteres.");
                e.preventDefault();
                return false;
            }

            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                alert("Por favor ingresa un correo válido.");
                e.preventDefault();
                return false;
            }

            if (password.length < 6) {
                alert("La contraseña debe tener al menos 6 caracteres.");
                e.preventDefault();
                return false;
            }

            return true;
        });
    }
});