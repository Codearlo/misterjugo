// Función para generar el modal de detalles con personalización de jugos
function generarModalDetalles(data) {
    const esJugo = data.producto.categoria_nombre && 
                   data.producto.categoria_nombre.toLowerCase().includes('jugo');
    
    const esJugoConLeche = data.producto.categoria_nombre && 
                          data.producto.categoria_nombre.toLowerCase().includes('leche');
    
    const esEspecial = data.producto.categoria_nombre && 
                      (data.producto.categoria_nombre.toLowerCase().includes('especial') ||
                       data.producto.categoria_nombre.toLowerCase().includes('frozen'));
    
    let html = `
        <div class="producto-modal-content">
            <div class="producto-modal-image">
                <img src="${data.producto.imagen || '/images/producto-default.jpg'}" alt="${data.producto.nombre}">
                <div class="categoria-badge">${data.producto.categoria_nombre}</div>
            </div>
            <div class="producto-modal-info">
                <div class="producto-modal-price">
                    <span class="price-label">Precio base:</span>
                    <span class="price-value" id="precio-base">S/${parseFloat(data.producto.precio).toFixed(2)}</span>
                    <div class="precio-extras" id="precio-extras" style="display: none;">
                        <span class="extras-label">Extras:</span>
                        <span class="extras-value" id="extras-total">+S/0.00</span>
                    </div>
                    <div class="precio-final">
                        <span class="final-label">Total:</span>
                        <span class="final-value" id="precio-final">S/${parseFloat(data.producto.precio).toFixed(2)}</span>
                    </div>
                </div>
                <div class="producto-modal-description">
                    <h4>Descripción:</h4>
                    <p>${data.producto.descripcion}</p>
                </div>
                <div class="producto-modal-category">
                    <h4>Categoría:</h4>
                    <p>${data.producto.categoria_nombre}</p>
                </div>
                
                <!-- Personalización para jugos, jugos con leche y especiales -->
                ${(esJugo || esJugoConLeche || esEspecial) ? `
                <div class="personalizacion-jugo">
                    <h4><i class="fas fa-cog"></i> Personaliza tu bebida</h4>
                    
                    <!-- Tipo de leche (solo para jugos con leche y especiales) -->
                    ${(esJugoConLeche || esEspecial) ? `
                    <div class="opcion-grupo">
                        <label class="opcion-label">Tipo de leche (opcional - máximo 2):</label>
                        <div class="opciones-checkbox">
                            <label class="checkbox-option">
                                <input type="checkbox" name="leche" value="almendras" data-precio="2">
                                <span class="checkbox-custom"></span>
                                <i class="fas fa-seedling"></i> Leche de almendras (+S/2.00)
                            </label>
                            <label class="checkbox-option">
                                <input type="checkbox" name="leche" value="coco" data-precio="2">
                                <span class="checkbox-custom"></span>
                                <i class="fas fa-coconut"></i> Leche de coco (+S/2.00)
                            </label>
                            <label class="checkbox-option">
                                <input type="checkbox" name="leche" value="soya" data-precio="2">
                                <span class="checkbox-custom"></span>
                                <i class="fas fa-leaf"></i> Leche de soya (+S/2.00)
                            </label>
                            <label class="checkbox-option">
                                <input type="checkbox" name="leche" value="sin_lactosa" data-precio="2">
                                <span class="checkbox-custom"></span>
                                <i class="fas fa-check-circle"></i> Sin lactosa (+S/2.00)
                            </label>
                        </div>
                        <div class="opcion-note">
                            <i class="fas fa-info-circle"></i> Puedes elegir máximo 2 tipos de leche
                        </div>
                    </div>
                    ` : ''}
                    
                    <!-- Temperatura -->
                    <div class="opcion-grupo">
                        <label class="opcion-label">Temperatura:</label>
                        <div class="opciones-radio">
                            <label class="radio-option">
                                <input type="radio" name="temperatura" value="normal" checked>
                                <span class="radio-custom"></span>
                                <i class="fas fa-thermometer-half"></i> Normal
                            </label>
                            <label class="radio-option">
                                <input type="radio" name="temperatura" value="helado">
                                <span class="radio-custom"></span>
                                <i class="fas fa-snowflake"></i> Helado
                            </label>
                        </div>
                    </div>
                    
                    <!-- Azúcar -->
                    <div class="opcion-grupo">
                        <label class="opcion-label">Endulzante:</label>
                        <div class="opciones-radio">
                            <label class="radio-option">
                                <input type="radio" name="azucar" value="normal" checked>
                                <span class="radio-custom"></span>
                                <i class="fas fa-cube"></i> Azúcar normal
                            </label>
                            <label class="radio-option">
                                <input type="radio" name="azucar" value="sin_azucar">
                                <span class="radio-custom"></span>
                                <i class="fas fa-times-circle"></i> Sin azúcar
                            </label>
                            <label class="radio-option">
                                <input type="radio" name="azucar" value="con_estevia">
                                <span class="radio-custom"></span>
                                <i class="fas fa-leaf"></i> Con estevia
                            </label>
                        </div>
                    </div>
                    
                    <!-- Comentarios especiales -->
                    <div class="opcion-grupo">
                        <label class="opcion-label" for="comentarios-jugo">
                            <i class="fas fa-comment"></i> Comentarios especiales:
                        </label>
                        <textarea 
                            id="comentarios-jugo" 
                            name="comentarios" 
                            placeholder="Ej: Extra frío, sin hielo, más concentrado..."
                            rows="3"
                        ></textarea>
                    </div>
                </div>
                ` : ''}
                
                <div class="producto-modal-actions">
                    <div class="quantity-selector">
                        <button class="quantity-btn minus" data-action="decrease">-</button>
                        <input type="number" class="quantity-input" id="modal-quantity" value="1" min="1" max="10">
                        <button class="quantity-btn plus" data-action="increase">+</button>
                    </div>
                    <button class="btn-add-cart-modal" 
                            data-id="${data.producto.id}" 
                            data-nombre="${data.producto.nombre}" 
                            data-precio="${data.producto.precio}" 
                            data-imagen="${data.producto.imagen || '/images/producto-default.jpg'}"
                            data-es-jugo="${esJugo || esJugoConLeche || esEspecial}">
                        <i class="fas fa-cart-plus"></i> Añadir al carrito
                    </button>
                </div>
            </div>
        </div>
    `;
    
    return html;
}

// Función para calcular precio con extras
function calcularPrecioFinal(precioBase) {
    let extras = 0;
    
    // Calcular extras de leche
    const lecheCheckboxes = document.querySelectorAll('input[name="leche"]:checked');
    lecheCheckboxes.forEach(checkbox => {
        extras += parseFloat(checkbox.dataset.precio);
    });
    
    const precioFinal = precioBase + extras;
    
    // Actualizar UI
    const extrasElement = document.getElementById('precio-extras');
    const extrasTotalElement = document.getElementById('extras-total');
    const precioFinalElement = document.getElementById('precio-final');
    
    if (extras > 0) {
        extrasElement.style.display = 'block';
        extrasTotalElement.textContent = `+S/${extras.toFixed(2)}`;
    } else {
        extrasElement.style.display = 'none';
    }
    
    precioFinalElement.textContent = `S/${precioFinal.toFixed(2)}`;
    
    return precioFinal;
}

// Función para obtener opciones de personalización
function obtenerOpcionesPersonalizacion() {
    const temperatura = document.querySelector('input[name="temperatura"]:checked');
    const azucar = document.querySelector('input[name="azucar"]:checked');
    const comentarios = document.getElementById('comentarios-jugo');
    const lecheCheckboxes = document.querySelectorAll('input[name="leche"]:checked');
    
    const lechesSeleccionadas = [];
    lecheCheckboxes.forEach(checkbox => {
        lechesSeleccionadas.push(checkbox.value);
    });
    
    return {
        temperatura: temperatura ? temperatura.value : 'normal',
        azucar: azucar ? azucar.value : 'normal',
        comentarios: comentarios ? comentarios.value.trim() : '',
        leches: lechesSeleccionadas,
        precioExtra: lechesSeleccionadas.length * 2 // Cada leche cuesta 2 soles extra
    };
}

// Función para manejar el click del botón "Añadir al carrito" del modal
function manejarClickCarritoModal(btnAddCartModal) {
    btnAddCartModal.addEventListener('click', function() {
        const id = this.getAttribute('data-id');
        const nombre = this.getAttribute('data-nombre');
        const precioBase = parseFloat(this.getAttribute('data-precio'));
        const imagen = this.getAttribute('data-imagen');
        const esJugo = this.getAttribute('data-es-jugo') === 'true';
        const cantidad = parseInt(document.getElementById('modal-quantity').value);
        
        if (esJugo) {
            // Para jugos, usar la función con opciones
            const opciones = obtenerOpcionesPersonalizacion();
            
            // Calcular precio final
            const precioFinal = precioBase + opciones.precioExtra;
            
            // Crear nombre personalizado para mostrar en el carrito
            let nombrePersonalizado = nombre;
            const detalles = [];
            
            if (opciones.temperatura === 'helado') {
                detalles.push('Helado');
            }
            if (opciones.azucar === 'sin_azucar') {
                detalles.push('Sin azúcar');
            } else if (opciones.azucar === 'con_estevia') {
                detalles.push('Con estevia');
            }
            
            if (opciones.leches.length > 0) {
                const lechesTexto = opciones.leches.map(leche => {
                    switch(leche) {
                        case 'almendras': return 'Leche de almendras';
                        case 'coco': return 'Leche de coco';
                        case 'soya': return 'Leche de soya';
                        case 'sin_lactosa': return 'Sin lactosa';
                        default: return leche;
                    }
                }).join(', ');
                detalles.push(lechesTexto);
            }
            
            if (detalles.length > 0) {
                nombrePersonalizado += ` (${detalles.join(', ')})`;
            }
            
            // Usar la función global con opciones y precio actualizado
            if (window.addToCartWithOptions) {
                window.addToCartWithOptions(id, nombrePersonalizado, precioFinal, imagen, cantidad, opciones);
            } else {
                console.error('Función addToCartWithOptions no disponible');
                window.addToCart(id, nombre, precioBase, imagen, cantidad);
            }
        } else {
            // Para productos normales, usar la función básica
            window.addToCart(id, nombre, precioBase, imagen, cantidad);
        }
        
        // Cerrar modal
        closeDetailsModal();
        
        // Abrir carrito si existe la función
        if (window.openCartSidebar) {
            window.openCartSidebar();
        }
    });
}

// Actualizar el código existente en productos.php
document.addEventListener('DOMContentLoaded', function() {
    // ... código existente ...
    
    // Modificar la parte donde se cargan los detalles del producto
    function openDetailsModal(productoId) {
        if (!productoDetailsModal || !modalTitle || !productoDetailsContent) return;
        
        modalTitle.textContent = 'Cargando detalles...';
        productoDetailsContent.innerHTML = `
            <div class="loading">
                <i class="fas fa-spinner fa-spin"></i>
                <p>Cargando detalles...</p>
            </div>
        `;
        
        // Mostrar el modal
        productoDetailsModal.classList.add('active');
        document.body.style.overflow = 'hidden';
        
        // Cargar los detalles del producto mediante AJAX
        fetch(`/backend/obtener_detalles_producto.php?id=${productoId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    modalTitle.textContent = data.producto.nombre;
                    
                    // Usar la nueva función para generar el HTML
                    const html = generarModalDetalles(data);
                    productoDetailsContent.innerHTML = html;
                    
                    // Agregar eventos a los botones de cantidad
                    const quantityBtns = productoDetailsContent.querySelectorAll('.quantity-btn');
                    const quantityInput = productoDetailsContent.querySelector('.quantity-input');
                    
                    quantityBtns.forEach(btn => {
                        btn.addEventListener('click', function() {
                            const action = this.getAttribute('data-action');
                            let currentValue = parseInt(quantityInput.value);
                            
                            if (action === 'increase') {
                                if (currentValue < 10) {
                                    quantityInput.value = currentValue + 1;
                                }
                            } else if (action === 'decrease') {
                                if (currentValue > 1) {
                                    quantityInput.value = currentValue - 1;
                                }
                            }
                        });
                    });
                    
                    // Configurar eventos para las opciones de leche
                    const lecheCheckboxes = document.querySelectorAll('input[name="leche"]');
                    const precioBase = parseFloat(data.producto.precio);
                    
                    lecheCheckboxes.forEach(checkbox => {
                        checkbox.addEventListener('change', function() {
                            // Limitar a máximo 2 selecciones
                            const checkedBoxes = document.querySelectorAll('input[name="leche"]:checked');
                            if (checkedBoxes.length > 2) {
                                this.checked = false;
                                alert('Puedes elegir máximo 2 tipos de leche');
                                return;
                            }
                            
                            // Actualizar precio
                            calcularPrecioFinal(precioBase);
                        });
                    });
                    
                    // Inicializar precio
                    calcularPrecioFinal(precioBase);
                    
                    // Evento para añadir al carrito desde el modal
                    const btnAddCartModal = productoDetailsContent.querySelector('.btn-add-cart-modal');
                    if (btnAddCartModal) {
                        manejarClickCarritoModal(btnAddCartModal);
                    }
                } else {
                    productoDetailsContent.innerHTML = `
                        <div class="error-message">
                            <i class="fas fa-exclamation-circle"></i>
                            <p>No se pudieron cargar los detalles del producto</p>
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                productoDetailsContent.innerHTML = `
                    <div class="error-message">
                        <i class="fas fa-exclamation-circle"></i>
                        <p>Ocurrió un error al cargar los detalles</p>
                    </div>
                `;
            });
    }
    
});