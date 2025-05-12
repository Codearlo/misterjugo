.total-row.grand-total .total-value {
    color: var(--admin-primary);
}

.address-box {
    padding: 15px;
    background-color: #f9f9f9;
    border-radius: 6px;
    margin-top: 10px;
}

.status-timeline {
    display: flex;
    margin-top: 20px;
    position: relative;
}

.timeline-item {
    flex: 1;
    text-align: center;
    position: relative;
    z-index: 2;
}

.timeline-item:before {
    content: "";
    height: 3px;
    background-color: #ddd;
    position: absolute;
    top: 25px;
    left: 0;
    right: 0;
    z-index: -1;
}

.timeline-item:first-child:before {
    left: 50%;
}

.timeline-item:last-child:before {
    right: 50%;
}

.timeline-item.active:before,
.timeline-item.complete:before {
    background-color: var(--admin-primary);
}

.timeline-step {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background-color: #ddd;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 10px;
    color: #777;
    font-size: 1.3rem;
    transition: all 0.3s;
}

.timeline-item.active .timeline-step {
    background-color: var(--admin-primary);
    color: white;
}

.timeline-item.complete .timeline-step {
    background-color: var(--admin-primary);
    color: white;
}

.timeline-label {
    font-size: 0.8rem;
    font-weight: 500;
    color: #666;
}

.timeline-item.active .timeline-label {
    color: var(--admin-primary);
    font-weight: 600;
}

.timeline-item.complete .timeline-label {
    color: var(--admin-primary);
}

.status-actions {
    display: flex;
    gap: 10px;
    margin-top: 20px;
    flex-wrap: wrap;
}

@media (max-width: 768px) {
    .order-details-grid {
        grid-template-columns: 1fr;
    }
    
    .status-timeline {
        flex-wrap: wrap;
    }
    
    .timeline-item {
        flex: 0 0 50%;
        margin-bottom: 20px;
    }
    
    .timeline-item:before {
        display: none;
    }
}
</style>

<div class="order-details-grid">
    <div class="order-details-main">
        <!-- Detalles básicos del pedido -->
        <div class="order-card">
            <div class="order-card-header">
                <h3>Detalles del Pedido #<?php echo $pedido['id']; ?></h3>
                <span class="badge <?php echo getEstadoClass($pedido['estado']); ?>">
                    <?php echo ucfirst($pedido['estado']); ?>
                </span>
            </div>
            <div class="order-card-body">
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 20px;">
                    <div class="order-info-item">
                        <div class="order-info-label">Fecha del Pedido</div>
                        <div class="order-info-value"><?php echo formatearFecha($pedido['fecha_pedido']); ?></div>
                    </div>
                    
                    <div class="order-info-item">
                        <div class="order-info-label">Cliente</div>
                        <div class="order-info-value"><?php echo htmlspecialchars($pedido['usuario_nombre']); ?></div>
                    </div>
                    
                    <div class="order-info-item">
                        <div class="order-info-label">Email</div>
                        <div class="order-info-value"><?php echo htmlspecialchars($pedido['usuario_email']); ?></div>
                    </div>
                    
                    <div class="order-info-item">
                        <div class="order-info-label">Teléfono</div>
                        <div class="order-info-value"><?php echo htmlspecialchars($pedido['telefono'] ?? 'No disponible'); ?></div>
                    </div>
                </div>
                
                <!-- Timeline de estados -->
                <div class="status-timeline">
                    <div class="timeline-item <?php echo in_array($pedido['estado'], ['pendiente', 'procesando', 'completado']) ? 'complete' : ''; ?> <?php echo $pedido['estado'] === 'pendiente' ? 'active' : ''; ?>">
                        <div class="timeline-step">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div class="timeline-label">Pendiente</div>
                    </div>
                    
                    <div class="timeline-item <?php echo in_array($pedido['estado'], ['procesando', 'completado']) ? 'complete' : ''; ?> <?php echo $pedido['estado'] === 'procesando' ? 'active' : ''; ?>">
                        <div class="timeline-step">
                            <i class="fas fa-cog"></i>
                        </div>
                        <div class="timeline-label">Procesando</div>
                    </div>
                    
                    <div class="timeline-item <?php echo $pedido['estado'] === 'completado' ? 'complete active' : ''; ?>">
                        <div class="timeline-step">
                            <i class="fas fa-check"></i>
                        </div>
                        <div class="timeline-label">Completado</div>
                    </div>
                    
                    <div class="timeline-item <?php echo $pedido['estado'] === 'cancelado' ? 'active' : ''; ?>" style="<?php echo $pedido['estado'] === 'cancelado' ? 'flex: 1;' : 'display: none;'; ?>">
                        <div class="timeline-step" style="background-color: var(--admin-danger); color: white;">
                            <i class="fas fa-times"></i>
                        </div>
                        <div class="timeline-label" style="color: var(--admin-danger);">Cancelado</div>
                    </div>
                </div>
                
                <!-- Acciones según estado -->
                <?php if (!empty($estados_disponibles)): ?>
                    <div class="status-actions">
                        <?php foreach ($estados_disponibles as $estado => $texto): ?>
                            <a href="procesar_pedido.php?action=update_status&id=<?php echo $pedido_id; ?>&status=<?php echo $estado; ?>" class="btn btn-<?php echo $estado === 'cancelado' ? 'danger' : ($estado === 'completado' ? 'success' : 'primary'); ?>">
                                <i class="fas fa-<?php echo $estado === 'cancelado' ? 'times' : ($estado === 'completado' ? 'check' : 'cog'); ?>"></i> 
                                <?php echo $texto; ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Artículos del pedido -->
        <div class="order-card">
            <div class="order-card-header">
                <h3>Artículos del Pedido</h3>
            </div>
            <div class="order-card-body">
                <?php if (empty($detalles_pedido)): ?>
                    <p style="text-align: center; padding: 20px;">No hay artículos en este pedido</p>
                <?php else: ?>
                    <div class="order-items">
                        <?php foreach ($detalles_pedido as $item): ?>
                            <div class="order-item">
                                <div class="order-item-image">
                                    <?php if (!empty($item['producto_imagen'])): ?>
                                        <img src="<?php echo htmlspecialchars($item['producto_imagen']); ?>" alt="<?php echo htmlspecialchars($item['producto_nombre']); ?>">
                                    <?php else: ?>
                                        <i class="fas fa-carrot" style="color: #ddd;"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="order-item-details">
                                    <h4 class="order-item-name"><?php echo htmlspecialchars($item['producto_nombre']); ?></h4>
                                    <div class="order-item-price">Precio: S/<?php echo number_format($item['precio'], 2); ?></div>
                                    <div class="order-item-quantity">Cantidad: <?php echo $item['cantidad']; ?></div>
                                </div>
                                <div class="order-item-total">
                                    S/<?php echo number_format($item['precio'] * $item['cantidad'], 2); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="order-totals">
                        <div class="total-row">
                            <div class="total-label">Subtotal</div>
                            <div class="total-value">S/<?php echo number_format($pedido['total'] - ($pedido['costo_envio'] ?? 0), 2); ?></div>
                        </div>
                        
                        <?php if (isset($pedido['costo_envio']) && $pedido['costo_envio'] > 0): ?>
                            <div class="total-row">
                                <div class="total-label">Costo de Envío</div>
                                <div class="total-value">S/<?php echo number_format($pedido['costo_envio'], 2); ?></div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (isset($pedido['descuento']) && $pedido['descuento'] > 0): ?>
                            <div class="total-row">
                                <div class="total-label">Descuento</div>
                                <div class="total-value">-S/<?php echo number_format($pedido['descuento'], 2); ?></div>
                            </div>
                        <?php endif; ?>
                        
                        <div class="total-row grand-total">
                            <div class="total-label">Total</div>
                            <div class="total-value">S/<?php echo number_format($pedido['total'], 2); ?></div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="order-details-sidebar">
        <!-- Información de dirección -->
        <div class="order-card">
            <div class="order-card-header">
                <h3>Dirección de Entrega</h3>
            </div>
            <div class="order-card-body">
                <?php if (empty($pedido['calle'])): ?>
                    <p style="text-align: center; color: #777;">No hay información de dirección disponible</p>
                <?php else: ?>
                    <address class="address-box">
                        <strong><?php echo htmlspecialchars($pedido['usuario_nombre']); ?></strong><br>
                        <?php echo htmlspecialchars($pedido['calle']); ?><br>
                        <?php echo htmlspecialchars($pedido['ciudad']); ?>, <?php echo htmlspecialchars($pedido['estado']); ?><br>
                        CP: <?php echo htmlspecialchars($pedido['codigo_postal']); ?><br>
                        <br>
                        <strong>Teléfono:</strong> <?php echo htmlspecialchars($pedido['telefono']); ?>
                        
                        <?php if (!empty($pedido['instrucciones'])): ?>
                            <hr style="margin: 15px 0; border: 0; border-top: 1px dashed #ddd;">
                            <strong>Instrucciones:</strong><br>
                            <?php echo nl2br(htmlspecialchars($pedido['instrucciones'])); ?>
                        <?php endif; ?>
                    </address>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Acciones del pedido -->
        <div class="order-card">
            <div class="order-card-header">
                <h3>Acciones</h3>
            </div>
            <div class="order-card-body">
                <div style="display: flex; flex-direction: column; gap: 10px;">
                    <a href="pedidos.php" class="btn btn-secondary" style="width: 100%; text-align: center;">
                        <i class="fas fa-arrow-left"></i> Volver a Pedidos
                    </a>
                    
                    <a href="#" onclick="window.print()" class="btn btn-primary" style="width: 100%; text-align: center;">
                        <i class="fas fa-print"></i> Imprimir Pedido
                    </a>
                    
                    <a href="mailto:<?php echo htmlspecialchars($pedido['usuario_email']); ?>?subject=Pedido%20#<?php echo $pedido_id; ?>" class="btn btn-info" style="width: 100%; text-align: center;">
                        <i class="fas fa-envelope"></i> Contactar Cliente
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Incluir el footer
require_once 'includes/admin_footer.php';
?>