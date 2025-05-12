document.querySelector('.btn-checkout').addEventListener('click', function(e) {
    e.preventDefault(); // Evitar redirección inmediata

    const carrito = JSON.parse(localStorage.getItem('mjCarrito')) || [];

    if (carrito.length === 0) {
        alert("Tu carrito está vacío.");
        return;
    }

    fetch('/backend/guardar_carrito.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(carrito)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = '/checkout'; // Redirige al checkout solo si se guardó
        } else {
            alert("Hubo un error al procesar tu carrito.");
        }
    });
});