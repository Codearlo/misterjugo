<?php if ($usuario['is_admin'] == 0): // No permitir cambiar estado de administradores ?>
                                    <a href="procesar_usuario.php?action=toggle_status&id=<?php echo $usuario['id']; ?>" class="btn btn-<?php echo $usuario['activo'] ? 'warning' : 'success'; ?> btn-sm btn-icon" title="<?php echo $usuario['activo'] ? 'Desactivar' : 'Activar'; ?> usuario">
                                        <i class="fas fa-<?php echo $usuario['activo'] ? 'user-times' : 'user-check'; ?>"></i>
                                    </a>
                                <?php endif; ?>
                                
                                <?php if ($_SESSION['admin_id'] != $usuario['id']): // No permitir cambiar contraseña propia aquí ?>
                                    <a href="procesar_usuario.php?action=reset_password&id=<?php echo $usuario['id']; ?>" class="btn btn-secondary btn-sm btn-icon" title="Restablecer contraseña" onclick="return confirm('¿Estás seguro de que deseas restablecer la contraseña de este usuario? Se generará una contraseña aleatoria.');">
                                        <i class="fas fa-key"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Paginación -->
<?php if ($total_paginas > 1): ?>
    <div class="pagination">
        <?php if ($pagina_actual > 1): ?>
            <a href="?pagina=<?php echo $pagina_actual - 1; ?><?php echo !empty($busqueda) ? '&buscar=' . urlencode($busqueda) : ''; ?><?php echo $activos !== 1 ? '&activos=' . $activos : ''; ?>">
                <i class="fas fa-chevron-left"></i>
            </a>
        <?php else: ?>
            <span class="disabled"><i class="fas fa-chevron-left"></i></span>
        <?php endif; ?>
        
        <?php
        // Calcular rango de páginas a mostrar
        $rango = 2; // Número de páginas antes y después de la actual
        $inicio_rango = max(1, $pagina_actual - $rango);
        $fin_rango = min($total_paginas, $pagina_actual + $rango);
        
        // Mostrar enlace a la primera página si no está incluida en el rango
        if ($inicio_rango > 1) {
            echo '<a href="?pagina=1' . (!empty($busqueda) ? '&buscar=' . urlencode($busqueda) : '') . ($activos !== 1 ? '&activos=' . $activos : '') . '">1</a>';
            if ($inicio_rango > 2) {
                echo '<span class="ellipsis">...</span>';
            }
        }
        
        // Mostrar páginas en el rango
        for ($i = $inicio_rango; $i <= $fin_rango; $i++) {
            if ($i == $pagina_actual) {
                echo '<span class="current">' . $i . '</span>';
            } else {
                echo '<a href="?pagina=' . $i . (!empty($busqueda) ? '&buscar=' . urlencode($busqueda) : '') . ($activos !== 1 ? '&activos=' . $activos : '') . '">' . $i . '</a>';
            }
        }
        
        // Mostrar enlace a la última página si no está incluida en el rango
        if ($fin_rango < $total_paginas) {
            if ($fin_rango < $total_paginas - 1) {
                echo '<span class="ellipsis">...</span>';
            }
            echo '<a href="?pagina=' . $total_paginas . (!empty($busqueda) ? '&buscar=' . urlencode($busqueda) : '') . ($activos !== 1 ? '&activos=' . $activos : '') . '">' . $total_paginas . '</a>';
        }
        ?>
        
        <?php if ($pagina_actual < $total_paginas): ?>
            <a href="?pagina=<?php echo $pagina_actual + 1; ?><?php echo !empty($busqueda) ? '&buscar=' . urlencode($busqueda) : ''; ?><?php echo $activos !== 1 ? '&activos=' . $activos : ''; ?>">
                <i class="fas fa-chevron-right"></i>
            </a>
        <?php else: ?>
            <span class="disabled"><i class="fas fa-chevron-right"></i></span>
        <?php endif; ?>
    </div>
<?php endif; ?>

<style>
.pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    margin-top: 20px;
    gap: 5px;
}

.pagination a, .pagination span {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 35px;
    height: 35px;
    padding: 0 10px;
    border-radius: 4px;
    text-decoration: none;
    color: var(--admin-dark);
    background-color: #fff;
    font-size: 0.9rem;
    transition: all 0.2s;
}

.pagination a:hover {
    background-color: #f1f1f1;
}

.pagination .current {
    background-color: var(--admin-primary);
    color: white;
    font-weight: 500;
}

.pagination .disabled {
    color: #ccc;
    pointer-events: none;
}

.pagination .ellipsis {
    background: none;
}
</style>

<?php
// Incluir el footer
require_once 'includes/admin_footer.php';
?>