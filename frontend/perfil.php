<?php
// Verificar si el usuario está logueado
session_start();
if (!isset($_SESSION['user_id'])) {
    // Si no está logueado, redirigir al login con un mensaje
    $_SESSION['error_login'] = "Debes iniciar sesión para acceder a tu perfil";
    header("Location: /login");
    exit;
}

// Conexión a base de datos
require_once '../backend/conexion.php';

// Obtener el ID del usuario actual
$user_id = $_SESSION['user_id'];

// Verificar si hay mensajes de éxito o error
$exito_perfil = isset($_SESSION['exito_perfil']) ? $_SESSION['exito_perfil'] : '';
$error_perfil = isset($_SESSION['error_perfil']) ? $_SESSION['error_perfil'] : '';

// Limpiar mensajes de sesión
unset($_SESSION['exito_perfil']);
unset($_SESSION['error_perfil']);

// Obtener datos del usuario
$stmt = $conn->prepare("SELECT id, nombre, email FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();

// Obtener direcciones del usuario
$direcciones = [];
$stmt = $conn->prepare("SELECT * FROM direcciones WHERE usuario_id = ? ORDER BY es_predeterminada DESC, fecha_creacion DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $direcciones[] = $row;
    }
}

// Obtener estadísticas de pedidos
$stats = [
    'total' => 0,
    'pendientes' => 0,
    'completados' => 0,
    'cancelados' => 0
];

$stmt = $conn->prepare("SELECT COUNT(*) as total FROM pedidos WHERE usuario_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$stats['total'] = $row['total'];

$stmt = $conn->prepare("SELECT COUNT(*) as total FROM pedidos WHERE usuario_id = ? AND estado = 'pendiente'");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$stats['pendientes'] = $row['total'];

$stmt = $conn->prepare("SELECT COUNT(*) as total FROM pedidos WHERE usuario_id = ? AND estado = 'completado'");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$stats['completados'] = $row['total'];

$stmt = $conn->prepare("SELECT COUNT(*) as total FROM pedidos WHERE usuario_id = ? AND estado = 'cancelado'");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$stats['cancelados'] = $row['total'];

// Incluir el archivo header
include 'includes/header.php';
?>

<link rel="stylesheet" href="/css/perfil.css">

<!-- Inicio de la página de perfil con la clase específica -->
<div class="perfil-page">
    <div class="main-content">
        <div class="container">
            <!-- Eliminado título y subtítulo según lo solicitado -->
            
            <!-- Notificaciones debajo del título -->
            <?php if (!empty($exito_perfil)): ?>
                <div class="notification success">
                    <i class="fas fa-check-circle"></i>
                    <div>
                        <p><?php echo $exito_perfil; ?></p>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($error_perfil)): ?>
                <div class="notification error">
                    <i class="fas fa-exclamation-circle"></i>
                    <div>
                        <p><?php echo $error_perfil; ?></p>
                    </div>
                </div>
            <?php endif; ?>
            
            <div class="profile-container">
                <!-- SECCIÓN DE INFORMACIÓN PERSONAL -->
                <div class="profile-section personal-info">
                    <div class="section-header">
                        <h3><i class="fas fa-user-circle"></i> Información Personal</h3>
                        <button class="btn-edit-info btn-action" id="btn-edit-personal">
                            <i class="fas fa-edit"></i> Editar
                        </button>
                    </div>
                    
                    <div class="section-content" id="personal-info-view">
                        <div class="info-group">
                            <span class="info-label">Nombre:</span>
                            <span class="info-value"><?php echo htmlspecialchars($usuario['nombre']); ?></span>
                        </div>
                        <div class="info-group">
                            <span class="info-label">Email:</span>
                            <span class="info-value"><?php echo htmlspecialchars($usuario['email']); ?></span>
                        </div>
                    </div>
                    
                    <div class="section-content hidden" id="personal-info-edit">
                        <form id="form-personal-info" action="/backend/actualizar_perfil.php" method="POST">
                            <div class="form-group">
                                <label for="nombre">Nombre</label>
                                <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($usuario['nombre']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($usuario['email']); ?>" required>
                            </div>
                            <div class="form-actions">
                                <button type="button" class="btn-cancel" id="btn-cancel-personal">Cancelar</button>
                                <button type="submit" class="btn-save">Guardar Cambios</button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Sección de cambio de contraseña eliminada -->
                
                <!-- SECCIÓN DE ESTADÍSTICAS DE PEDIDOS -->
                <div class="profile-section stats-section">
                    <div class="section-header">
                        <h3><i class="fas fa-chart-pie"></i> Mis Estadísticas</h3>
                        <a href="/pedidos" class="btn-view-orders btn-action">
                            <i class="fas fa-list"></i> Ver historial de pedidos
                        </a>
                    </div>
                    
                    <div class="section-content">
                        <div class="stats-grid">
                            <div class="stat-card">
                                <div class="stat-icon">
                                    <i class="fas fa-shopping-bag"></i>
                                </div>
                                <div class="stat-data">
                                    <span class="stat-value"><?php echo $stats['total']; ?></span>
                                    <span class="stat-label">Pedidos Totales</span>
                                </div>
                            </div>
                            
                            <div class="stat-card">
                                <div class="stat-icon pending">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <div class="stat-data">
                                    <span class="stat-value"><?php echo $stats['pendientes']; ?></span>
                                    <span class="stat-label">Pendientes</span>
                                </div>
                            </div>
                            
                            <div class="stat-card">
                                <div class="stat-icon completed">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <div class="stat-data">
                                    <span class="stat-value"><?php echo $stats['completados']; ?></span>
                                    <span class="stat-label">Completados</span>
                                </div>
                            </div>
                            
                            <div class="stat-card">
                                <div class="stat-icon cancelled">
                                    <i class="fas fa-times-circle"></i>
                                </div>
                                <div class="stat-data">
                                    <span class="stat-value"><?php echo $stats['cancelados']; ?></span>
                                    <span class="stat-label">Cancelados</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- SECCIÓN DE DIRECCIÓN PREDETERMINADA -->
                <div class="profile-section addresses-section">
                    <div class="section-header">
                        <h3><i class="fas fa-map-marker-alt"></i> Dirección de Entrega</h3>
                        <a href="/direcciones" class="btn-view-addresses btn-action">
                            <i class="fas fa-list"></i> Ver todas mis direcciones
                        </a>
                    </div>
                    
                    <div class="section-content">
                        <?php 
                        // Buscar dirección predeterminada
                        $dirPredeterminada = null;
                        foreach ($direcciones as $dir) {
                            if ($dir['es_predeterminada']) {
                                $dirPredeterminada = $dir;
                                break;
                            }
                        }
                        ?>
                        
                        <?php if ($dirPredeterminada): ?>
                            <div class="address-card default">
                                <div class="default-badge">
                                    <i class="fas fa-star"></i> Predeterminada
                                </div>
                                
                                <div class="address-content">
                                    <p class="address-line">
                                        <i class="fas fa-home"></i> 
                                        <?php echo htmlspecialchars($dirPredeterminada['calle']); ?>
                                    </p>
                                    <p class="address-line">
                                        <i class="fas fa-map"></i>
                                        <?php echo htmlspecialchars($dirPredeterminada['ciudad']); ?>, 
                                        <?php echo htmlspecialchars($dirPredeterminada['estado']); ?>, 
                                        CP: <?php echo htmlspecialchars($dirPredeterminada['codigo_postal']); ?>
                                    </p>
                                    <p class="address-line">
                                        <i class="fas fa-phone"></i>
                                        <?php echo htmlspecialchars($dirPredeterminada['telefono']); ?>
                                    </p>
                                    <?php if (!empty($dirPredeterminada['instrucciones'])): ?>
                                        <p class="address-instructions">
                                            <i class="fas fa-info-circle"></i>
                                            <?php echo htmlspecialchars($dirPredeterminada['instrucciones']); ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="no-addresses">
                                <div class="empty-state">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <h3>No tienes dirección predeterminada</h3>
                                    <p>Configura una dirección para facilitar tus pedidos</p>
                                    <a href="/direcciones" class="btn-add-primary">
                                        <i class="fas fa-plus"></i> Añadir dirección
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modales eliminados, ya que la gestión de direcciones se hace en otra página -->
</div>
<!-- Fin de la página de perfil -->

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Referencias a elementos DOM para información personal
    const btnEditPersonal = document.getElementById('btn-edit-personal');
    const btnCancelPersonal = document.getElementById('btn-cancel-personal');
    const personalInfoView = document.getElementById('personal-info-view');
    const personalInfoEdit = document.getElementById('personal-info-edit');
    
    // Función para alternar la edición de información personal
    function togglePersonalInfoEdit() {
        personalInfoView.classList.toggle('hidden');
        personalInfoEdit.classList.toggle('hidden');
    }
    
    // Eventos para edición de información personal
    btnEditPersonal.addEventListener('click', togglePersonalInfoEdit);
    btnCancelPersonal.addEventListener('click', togglePersonalInfoEdit);
    
    // Si hay notificaciones, mostrarlas y ocultarlas después de un tiempo
    const notifications = document.querySelectorAll('.notification');
    if (notifications.length > 0) {
        // Mostrar después de un pequeño retraso para evitar problemas de layout
        setTimeout(function() {
            notifications.forEach(notification => {
                notification.style.opacity = '1';
            });
            
            // Ocultar después de 5 segundos
            setTimeout(function() {
                notifications.forEach(notification => {
                    notification.style.opacity = '0';
                    setTimeout(() => {
                        notification.style.display = 'none';
                    }, 500);
                });
            }, 5000);
        }, 300);
    }
});
</script>

<?php
// Incluir el footer
include 'includes/footer.php';
?>