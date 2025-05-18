<?php
// Este archivo será incluido en productos.php
// Contiene el carrito lateral y su funcionalidad

// Asegurarse de que la sesión está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<link rel="stylesheet" href="/css/carrito.css">

<!-- Carrito lateral -->
<div class="cart-sidebar" id="cart-sidebar">
    <div class="cart-header">
        <h3><i class="fas fa-shopping-cart"></i> Mi Carrito</h3>
        <button class="btn-close-cart" id="btn-close-cart">
            <i class="fas fa-times"></i>
        </button>
    </div>
    
    <div class="cart-items" id="cart-items">
        <!-- Aquí se cargarán los items del carrito con JS -->
        <div class="empty-cart">
            <i class="fas fa-shopping-basket"></i>
            <p>Tu carrito está vacío</p>
        </div>
    </div>
    
    <div class="cart-summary">
        <div class="cart-total">
            <span>Total:</span>
            <span id="cart-total-amount">S/0.00</span>
        </div>
        <div class="cart-actions">
            <a href="/checkout" class="btn-checkout">
                <i class="fas fa-check"></i> Finalizar compra
            </a>
        </div>
    </div>
</div>

<div class="cart-overlay" id="cart-overlay"></div>

<!-- Botón flotante del carrito para móviles -->
<button class="cart-float-btn" id="cart-float-btn">
    <i class="fas fa-shopping-cart"></i>
    <span class="cart-count">0</span>
</button>

<script>
// Función para gestionar el carrito
document.addEventListener('DOMContentLoaded', function() {
    // Elementos del DOM
    const cartSidebar = document.getElementById('cart-sidebar');
    const cartOverlay = document.getElementById('cart-overlay');
    const btnCloseCart = document.getElementById('btn-close-cart');
    const cartFloatBtn = document.getElementById('cart-float-btn');
    
    // Función global para añadir al carrito, disponible para otras partes de la página
    window.addToCart = function(id, nombre, precio, imagen, cantidad = 1) {
        if (!id || !nombre || !precio) {
            console.error('Datos incompletos para añadir al carrito:', { id, nombre, precio });
            return;
        }

        // Verificar si el producto ya está en el carrito
        fetch('/backend/obtener_carrito.php')
            .then(response => response.json())
            .then(carrito => {
                if (carrito && carrito.length > 0) {
                    // Buscar si el producto ya está en el carrito
                    const productoExistente = carrito.find(item => item.id === id);
                    if (productoExistente) {
                        // Ya existe, mostrar mensaje y no agregar más
                        mostrarNotificacion(`${nombre} ya está en tu carrito`);
                        openCartSidebar();
                        return;
                    }
                }
                
                // Producto nuevo, agregarlo
                const producto = {
                    id: id,
                    nombre: nombre,
                    precio: parseFloat(precio),
                    imagen: imagen || '/images/producto-default.jpg',
                    cantidad: parseInt(cantidad)
                };

                fetch('/backend/agregar_al_carrito.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(producto)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        mostrarNotificacion(`${nombre} agregado al carrito`);
                        actualizarCarritoUI(); // Actualizar UI desde sesión
                        openCartSidebar(); // Abrir carrito automáticamente
                    } else {
                        alert('Error al agregar el producto al carrito.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Ocurrió un error al agregar al carrito.');
                });
            })
            .catch(error => {
                console.error('Error al verificar carrito:', error);
            });
    };

    // Cargar el carrito desde el servidor
    function actualizarCarritoUI() {
        fetch('/backend/obtener_carrito.php')
            .then(response => response.json())
            .then(carrito => {
                const cartItems = document.getElementById('cart-items');
                const cartTotalAmount = document.getElementById('cart-total-amount');
                const cartCount = document.querySelector('.cart-count');

                if (!carrito || carrito.length === 0) {
                    cartItems.innerHTML = `
                        <div class="empty-cart">
                            <i class="fas fa-shopping-basket"></i>
                            <p>Tu carrito está vacío</p>
                        </div>`;
                    cartTotalAmount.textContent = 'S/0.00';
                    cartCount.textContent = '0';

                    if (cartFloatBtn) cartFloatBtn.classList.remove('active');
                } else {
                    let html = '';
                    let total = 0;

                    carrito.forEach(item => {
                        const subtotal = item.precio * item.cantidad;
                        total += subtotal;

                        html += `
                            <div class="cart-item" data-id="${item.id}">
                                <div class="cart-item-image">
                                    <img src="${item.imagen}" alt="${item.nombre}">
                                </div>
                                <div class="cart-item-details">
                                    <h4 class="cart-item-name">${item.nombre}</h4>
                                    <div class="cart-item-price">S/${parseFloat(item.precio).toFixed(2)}</div>
                                    <div class="cart-item-quantity">
                                        <button class="cart-quantity-btn minus" data-id="${item.id}">-</button>
                                        <span>${item.cantidad}</span>
                                        <button class="cart-quantity-btn plus" data-id="${item.id}" style="visibility: hidden;">+</button>
                                    </div>
                                </div>
                                <div class="cart-item-subtotal">
                                    <span>S/${subtotal.toFixed(2)}</span>
                                    <button class="cart-item-remove" data-id="${item.id}">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                            </div>`;
                    });

                    cartItems.innerHTML = html;
                    cartTotalAmount.textContent = `S/${total.toFixed(2)}`;
                    cartCount.textContent = carrito.reduce((sum, item) => sum + parseInt(item.cantidad), 0);

                    if (cartFloatBtn && parseInt(cartCount.textContent) > 0) {
                        cartFloatBtn.classList.add('active');
                    }

                    // Botones de cantidad en el carrito
                    const quantityBtns = document.querySelectorAll('.cart-quantity-btn');
                    quantityBtns.forEach(btn => {
                        if (btn.classList.contains('minus')) {
                            btn.addEventListener('click', function() {
                                const id = this.getAttribute('data-id');
                                updateCartItemQuantity(id, 'decrease');
                            });
                        }
                    });

                    // Eliminar items
                    const removeButtons = document.querySelectorAll('.cart-item-remove');
                    removeButtons.forEach(btn => {
                        btn.addEventListener('click', function() {
                            const id = this.getAttribute('data-id');
                            eliminarDelCarrito(id);
                        });
                    });
                }
            })
            .catch(error => {
                console.error('Error al cargar el carrito:', error);
            });
    }

    // Actualizar cantidad de un item en el carrito
    function updateCartItemQuantity(id, action) {
        fetch('/backend/obtener_carrito.php')
            .then(response => response.json())
            .then(carrito => {
                let updatedCarrito = [...carrito];
                const index = updatedCarrito.findIndex(item => item.id === id);
                
                if (index !== -1) {
                    if (action === 'increase') {
                        // No permitir aumentar la cantidad (oculto)
                        return;
                    } else if (action === 'decrease') {
                        if (updatedCarrito[index].cantidad > 1) {
                            updatedCarrito[index].cantidad -= 1;
                        } else {
                            eliminarDelCarrito(id);
                            return;
                        }
                    }
                    
                    // Actualizar el carrito en el servidor
                    fetch('/backend/actualizar_carrito.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(updatedCarrito)
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            actualizarCarritoUI();
                        }
                    });
                }
            });
    }

    // Eliminar del carrito
    function eliminarDelCarrito(id) {
        fetch(`/backend/eliminar_del_carrito.php?id=${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    actualizarCarritoUI();
                }
            });
    }

    // Mostrar notificación
    window.mostrarNotificacion = function(mensaje) {
        const notificacion = document.createElement('div');
        notificacion.className = 'notification success-notification';
        notificacion.innerHTML = `<i class="fas fa-check-circle"></i><p>${mensaje}</p>`;
        document.body.appendChild(notificacion);
        setTimeout(() => notificacion.classList.add('show'), 100);
        setTimeout(() => {
            notificacion.classList.remove('show');
            setTimeout(() => document.body.removeChild(notificacion), 300);
        }, 3000);
    };

    // Abrir carrito lateral
    window.openCartSidebar = function() {
        if (!cartSidebar || !cartOverlay) return;
        cartSidebar.classList.add('active');
        cartOverlay.classList.add('active');
        document.body.style.overflow = 'hidden'; // Evitar scroll
    };

    // Cerrar carrito lateral
    function closeCartSidebar() {
        if (!cartSidebar || !cartOverlay) return;
        cartSidebar.classList.remove('active');
        cartOverlay.classList.remove('active');
        document.body.style.overflow = ''; // Restaurar scroll
    }

    // Eventos del carrito
    if (cartFloatBtn) {
        cartFloatBtn.addEventListener('click', openCartSidebar);
    }

    if (btnCloseCart) {
        btnCloseCart.addEventListener('click', closeCartSidebar);
    }

    if (cartOverlay) {
        cartOverlay.addEventListener('click', closeCartSidebar);
    }

    // Inicialización
    actualizarCarritoUI();
});
</script>