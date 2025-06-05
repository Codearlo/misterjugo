document.addEventListener('DOMContentLoaded', function() {
    // Referencias a elementos DOM
    const direccionSelect = document.getElementById('direccion_id');
    const addressDisplay = document.getElementById('address-display');
    const phoneDisplay = document.getElementById('phone-display');
    const instructionsDisplay = document.getElementById('instructions-display');
    const instructionsRow = document.getElementById('instructions-row');
    
    // Campos ocultos
    const telefonoHidden = document.getElementById('telefono_hidden');
    const direccionCompleta = document.getElementById('direccion_completa');
    const instruccionesHidden = document.getElementById('instrucciones_hidden');
    
    // Sincronizar carrito con formulario
    function syncCartWithForm() {
        let carrito = [];
        try {
            const carritoGuardado = localStorage.getItem('mjCarrito');
            carrito = carritoGuardado ? JSON.parse(carritoGuardado) : [];
        } catch (e) {
            console.error('Error al obtener carrito:', e);
            carrito = [];
        }
        
        // Actualizar el campo oculto con los datos del carrito
        const carritoDataField = document.querySelector('input[name="carrito_data"]');
        if (carritoDataField) {
            carritoDataField.value = JSON.stringify(carrito);
        }
    }
    
    // Sincronizar carrito al cargar la página
    syncCartWithForm();
    
    if (direccionSelect) {
        direccionSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const calle = selectedOption.dataset.calle;
            const ciudad = selectedOption.dataset.ciudad;
            const estado = selectedOption.dataset.estado;
            const codigo = selectedOption.dataset.codigo;
            const telefono = selectedOption.dataset.telefono;
            const instrucciones = selectedOption.dataset.instrucciones;
            
            // Actualizar la dirección mostrada
            addressDisplay.textContent = `${calle}, ${ciudad}, ${estado}, CP: ${codigo}`;
            phoneDisplay.textContent = telefono;
            
            // Actualizar campos ocultos
            telefonoHidden.value = telefono;
            direccionCompleta.value = `${calle}, ${ciudad}, ${estado}, CP: ${codigo}`;
            instruccionesHidden.value = instrucciones || '';
            
            // Mostrar/ocultar instrucciones según si existen o no
            if (instrucciones && instrucciones.trim() !== '') {
                instructionsDisplay.textContent = instrucciones;
                instructionsRow.style.display = 'flex';
            } else {
                instructionsRow.style.display = 'none';
            }
        });
    }
    
    // Validación del formulario antes de enviar
    const checkoutForm = document.getElementById('checkout-form');
    const submitBtn = document.getElementById('btn-realizar-pedido');
    
    if (checkoutForm && submitBtn) {
        // Deshabilitar el envío múltiple del formulario
        checkoutForm.addEventListener('submit', function(e) {
            // Verificar si ya se envió
            if (this.getAttribute('data-submitting') === 'true') {
                e.preventDefault();
                return false;
            }
            
            // Sincronizar carrito antes de enviar
            syncCartWithForm();
            
            // Validar que hay una dirección seleccionada
            const direccionId = document.getElementById('direccion_id');
            if (!direccionId || !direccionId.value) {
                e.preventDefault();
                alert('Por favor selecciona una dirección de entrega');
                return false;
            }
            
            // Validar que hay un nombre
            const nombre = document.getElementById('nombre');
            if (!nombre || !nombre.value.trim()) {
                e.preventDefault();
                alert('Por favor ingresa tu nombre para el pedido');
                return false;
            }
            
            // Validar que hay productos en el carrito
            let carrito = [];
            try {
                const carritoGuardado = localStorage.getItem('mjCarrito');
                carrito = carritoGuardado ? JSON.parse(carritoGuardado) : [];
            } catch (e) {
                console.error('Error al obtener carrito:', e);
                carrito = [];
            }
            
            if (!carrito || carrito.length === 0) {
                e.preventDefault();
                alert('Tu carrito está vacío. Agrega algunos productos antes de continuar.');
                return false;
            }
            
            // Marcar como enviando y deshabilitar el botón
            this.setAttribute('data-submitting', 'true');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando pedido...';
            
            // Procesar el pedido con AJAX
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('/backend/procesar_pedido.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Mostrar mensaje de éxito
                    mostrarMensajeExito('¡Pedido realizado con éxito!');
                    
                    // Limpiar el carrito
                    localStorage.removeItem('mjCarrito');
                    
                    // Abrir WhatsApp si hay URL
                    if (data.whatsapp_url) {
                        setTimeout(() => {
                            window.open(data.whatsapp_url, '_blank');
                        }, 1000);
                    }
                    
                    // Redirigir a pedidos después de un momento
                    setTimeout(() => {
                        window.location.href = '/pedidos';
                    }, 2000);
                    
                } else {
                    // Mostrar error
                    mostrarMensajeError(data.message || 'Error al procesar el pedido');
                    
                    // Restaurar botón
                    this.setAttribute('data-submitting', 'false');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="fas fa-check"></i> Realizar Pedido';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                mostrarMensajeError('Ocurrió un error al procesar el pedido');
                
                // Restaurar botón
                this.setAttribute('data-submitting', 'false');
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-check"></i> Realizar Pedido';
            });
            
            return false;
        });
    }
    
    // Función para mostrar mensaje de éxito
    function mostrarMensajeExito(mensaje) {
        // Remover mensajes existentes
        const existingMessages = document.querySelectorAll('.checkout-message');
        existingMessages.forEach(msg => msg.remove());
        
        const messageDiv = document.createElement('div');
        messageDiv.className = 'checkout-message success';
        messageDiv.innerHTML = `
            <div style="
                position: fixed;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                background: #4CAF50;
                color: white;
                padding: 20px 30px;
                border-radius: 10px;
                box-shadow: 0 5px 15px rgba(0,0,0,0.3);
                z-index: 10000;
                text-align: center;
                font-size: 1.1rem;
                font-weight: 500;
                animation: slideInScale 0.3s ease;
            ">
                <i class="fas fa-check-circle" style="font-size: 2rem; margin-bottom: 10px; display: block;"></i>
                ${mensaje}
                <div style="margin-top: 10px; font-size: 0.9rem; opacity: 0.9;">
                    Redirigiendo a tus pedidos...
                </div>
            </div>
            <style>
                @keyframes slideInScale {
                    from {
                        opacity: 0;
                        transform: translate(-50%, -50%) scale(0.8);
                    }
                    to {
                        opacity: 1;
                        transform: translate(-50%, -50%) scale(1);
                    }
                }
            </style>
        `;
        
        document.body.appendChild(messageDiv);
    }
    
    // Función para mostrar mensaje de error
    function mostrarMensajeError(mensaje) {
        // Remover mensajes existentes
        const existingMessages = document.querySelectorAll('.checkout-message');
        existingMessages.forEach(msg => msg.remove());
        
        const messageDiv = document.createElement('div');
        messageDiv.className = 'checkout-message error';
        messageDiv.innerHTML = `
            <div style="
                position: fixed;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                background: #F44336;
                color: white;
                padding: 20px 30px;
                border-radius: 10px;
                box-shadow: 0 5px 15px rgba(0,0,0,0.3);
                z-index: 10000;
                text-align: center;
                font-size: 1.1rem;
                font-weight: 500;
                animation: slideInScale 0.3s ease;
                max-width: 400px;
            ">
                <i class="fas fa-exclamation-circle" style="font-size: 2rem; margin-bottom: 10px; display: block;"></i>
                ${mensaje}
                <button onclick="this.parentElement.parentElement.remove()" style="
                    margin-top: 15px;
                    background: rgba(255,255,255,0.2);
                    border: none;
                    color: white;
                    padding: 8px 15px;
                    border-radius: 5px;
                    cursor: pointer;
                ">Cerrar</button>
            </div>
        `;
        
        document.body.appendChild(messageDiv);
        
        // Auto-remover después de 5 segundos
        setTimeout(() => {
            if (messageDiv.parentNode) {
                messageDiv.remove();
            }
        }, 5000);
    }
});