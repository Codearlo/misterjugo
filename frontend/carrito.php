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

        // Crear objeto de producto
        const producto = {
            id: id,
            nombre: nombre,
            precio: parseFloat(precio),
            imagen: imagen || '/images/producto-default.jpg',
            cantidad: cantidad
        };

        // Obtener carrito actual o inicializar uno nuevo
        let carrito = [];
        try {
            const carritoGuardado = localStorage.getItem('mjCarrito');
            carrito = carritoGuardado ? JSON.parse(carritoGuardado) : [];
        } catch (e) {
            console.error('Error al obtener carrito:', e);
            carrito = [];
        }

        // Verificar si el producto ya está en el carrito
        const productoExistente = carrito.findIndex(item => item.id === id);

        if (productoExistente !== -1) {
            // Actualizar cantidad si ya existe
            carrito[productoExistente].cantidad += cantidad;
        } else {
            // Agregar nuevo producto
            carrito.push(producto);
        }

        // Guardar carrito actualizado
        localStorage.setItem('mjCarrito', JSON.stringify(carrito));

        // Mostrar notificación
        mostrarNotificacion(`${nombre} agregado al carrito`);

        // Actualizar UI del carrito
        actualizarCarritoUI();
    };

    // Función global para añadir productos con opciones personalizadas
    window.addToCartWithOptions = function(id, nombre, precio, imagen, cantidad = 1, opciones = null) {
        if (!id || !nombre || !precio) {
            console.error('Datos incompletos para añadir al carrito:', { id, nombre, precio, imagen });
            return;
        }
        
        // Crear objeto de producto con opciones
        const producto = {
            id: id + '_' + Date.now(), // ID único para productos personalizados
            producto_id: id, // ID original del producto
            nombre: nombre,
            precio: parseFloat(precio),
            imagen: imagen || '/images/producto-default.jpg',
            cantidad: cantidad,
            opciones: opciones
        };
        
        // Obtener carrito actual o inicializar uno nuevo
        let carrito = [];
        try {
            const carritoGuardado = localStorage.getItem('mjCarrito');
            carrito = carritoGuardado ? JSON.parse(carritoGuardado) : [];
        } catch (e) {
            console.error('Error al obtener carrito:', e);
            carrito = [];
        }
        
        // Para productos personalizados, siempre agregar como nuevo item
        carrito.push(producto);
        
        // Guardar carrito actualizado
        localStorage.setItem('mjCarrito', JSON.stringify(carrito));
        
        // Mostrar notificación
        mostrarNotificacion(`${nombre} agregado al carrito`);
        
        // Actualizar UI del carrito
        actualizarCarritoUI();
    };

    // Cargar el carrito desde localStorage
    function actualizarCarritoUI() {
        const cartItems = document.getElementById('cart-items');
        const cartTotalAmount = document.getElementById('cart-total-amount');
        const cartCount = document.querySelector('.cart-count');

        let carrito = [];
        try {
            const carritoGuardado = localStorage.getItem('mjCarrito');
            carrito = carritoGuardado ? JSON.parse(carritoGuardado) : [];
        } catch (e) {
            console.error('Error al obtener carrito:', e);
            carrito = [];
        }

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
                            <h4 class="cart-item-name">${item.nombre}</h4>`;
                
                // Mostrar opciones de personalización si existen
                if (item.opciones) {
                    html += `<div class="cart-item-options">`;
                    if (item.opciones.temperatura === 'helado') {
                        html += `<span class="option-tag ice"><i class="fas fa-snowflake"></i> Helado</span>`;
                    }
                    if (item.opciones.azucar === 'sin_azucar') {
                        html += `<span class="option-tag no-sugar"><i class="fas fa-times-circle"></i> Sin azúcar</span>`;
                    } else if (item.opciones.azucar === 'con_estevia') {
                        html += `<span class="option-tag stevia"><i class="fas fa-leaf"></i> Con estevia</span>`;
                    }
                    if (item.opciones.comentarios) {
                        html += `<div class="option-comments"><i class="fas fa-comment"></i> ${item.opciones.comentarios}</div>`;
                    }
                    html += `</div>`;
                }
                
                html += `
                            <div class="cart-item-price">S/${item.precio.toFixed(2)}</div>
                            <div class="cart-item-quantity">
                                <button class="cart-quantity-btn minus" data-id="${item.id}">-</button>
                                <span>${item.cantidad}</span>
                                <button class="cart-quantity-btn plus" data-id="${item.id}">+</button>
                            </div>
                        </div>
                        <div class="cart-item-subtotal">
                            <span>S/${subtotal.toFixed(2)}</span>
                            <button class="cart-item-remove" data-id="${item.id}">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                    </div>
                `;
            });

            cartItems.innerHTML = html;
            cartTotalAmount.textContent = `S/${total.toFixed(2)}`;
            cartCount.textContent = carrito.reduce((sum, item) => sum + parseInt(item.cantidad), 0);

            if (cartFloatBtn && parseInt(cartCount.textContent) > 0) {
                cartFloatBtn.classList.add('active');
            }

            // Sincronizar con la sesión para el checkout
            syncCartWithSession(carrito);

            // Botones de cantidad en el carrito
            const quantityBtns = document.querySelectorAll('.cart-quantity-btn');
            quantityBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    const action = this.classList.contains('plus') ? 'increase' : 'decrease';
                    updateCartItemQuantity(id, action);
                });
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
    }

    // Sincronizar carrito con la sesión del servidor
    function syncCartWithSession(carrito) {
        fetch('/backend/actualizar_carrito.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(carrito)
        })
        .catch(error => {
            console.error('Error al sincronizar carrito:', error);
        });
    }

    // Actualizar cantidad de un item en el carrito
    function updateCartItemQuantity(id, action) {
        let carrito = [];
        try {
            const carritoGuardado = localStorage.getItem('mjCarrito');
            carrito = carritoGuardado ? JSON.parse(carritoGuardado) : [];
        } catch (e) {
            console.error('Error al obtener carrito:', e);
            return;
        }

        const index = carrito.findIndex(item => item.id === id);

        if (index !== -1) {
            if (action === 'increase') {
                carrito[index].cantidad += 1;
            } else if (action === 'decrease') {
                if (carrito[index].cantidad > 1) {
                    carrito[index].cantidad -= 1;
                } else {
                    eliminarDelCarrito(id);
                    return;
                }
            }

            localStorage.setItem('mjCarrito', JSON.stringify(carrito));
            actualizarCarritoUI();
        }
    }

    // Eliminar del carrito
    function eliminarDelCarrito(id) {
        let carrito = [];
        try {
            const carritoGuardado = localStorage.getItem('mjCarrito');
            carrito = carritoGuardado ? JSON.parse(carritoGuardado) : [];
        } catch (e) {
            console.error('Error al obtener carrito:', e);
            return;
        }

        carrito = carrito.filter(item => item.id !== id);
        localStorage.setItem('mjCarrito', JSON.stringify(carrito));
        actualizarCarritoUI();
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

<style>
/* Estilos adicionales para opciones en el carrito */
.cart-item-options {
    display: flex;
    flex-wrap: wrap;
    gap: 4px;
    margin: 6px 0;
}

.cart-item-options .option-tag {
    display: inline-flex;
    align-items: center;
    gap: 3px;
    padding: 2px 6px;
    border-radius: 10px;
    font-size: 0.65rem;
    font-weight: 500;
    color: white;
}

.cart-item-options .option-tag.ice {
    background-color: #2196F3;
}

.cart-item-options .option-tag.no-sugar {
    background-color: #F44336;
}

.cart-item-options .option-tag.stevia {
    background-color: #4CAF50;
}

.cart-item-options .option-comments {
    font-size: 0.7rem;
    color: #666;
    font-style: italic;
    display: flex;
    align-items: center;
    gap: 3px;
    margin-top: 3px;
    width: 100%;
}

.cart-item-options .option-comments i {
    font-size: 0.65rem;
}
</style>