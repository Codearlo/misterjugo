<?php include 'includes/header.php'; ?>
<link rel="stylesheet" href="./css/index.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const userIcon = document.getElementById('user-menu-toggle');
        const sideMenu = document.getElementById('side-menu');
        const closeBtn = document.getElementById('close-menu-btn');

        if (userIcon && sideMenu && closeBtn) {
            userIcon.addEventListener('click', () => {
                sideMenu.classList.add('active');
            });

            closeBtn.addEventListener('click', () => {
                sideMenu.classList.remove('active');
            });
        }
    });
</script>

<!-- Hero Section -->
<div class="hero-section">
    <div class="hero-overlay"></div>
    <div class="container hero-content">
        <div class="hero-text">
            <h1>¿Ya comiste? ¿Qué esperas para ordenar?</h1>
            <p>Descubre nuestros jugos y sándwich</p>
        </div>
        <div class="btn-container">
            <a href="productos" class="btn-order-now">Ordena Ya!!</a>
        </div>
    </div>
</div>

<!-- Promociones -->
<section class="promotions">
    <h2>Promociones</h2>
    <div class="promo-cards">
        <div class="promo-card">
            <img src="./images/promo1.jpg" alt="Promoción 1">
            <div class="content">
                <h3>Jugo del Día</h3>
                <p>2x1 en todos los jugos naturales. ¡Solo por hoy!</p>
            </div>
        </div>
        <div class="promo-card">
            <img src="./images/promo2.jpg" alt="Promoción 2">
            <div class="content">
                <h3>Envío Gratis</h3>
                <p>En compras mayores a $20, recibe gratis en toda la ciudad.</p>
            </div>
        </div>
        <div class="promo-card">
            <img src="./images/promo3.jpg" alt="Promoción 3">
            <div class="content">
                <h3>Membresía VIP</h3>
                <p>Acumula puntos y canjea descuentos exclusivos cada semana.</p>
            </div>
        </div>
    </div>
</section>

<!-- Sección de Contacto -->
<section class="contact-info">
    <div class="container">
        <h2>Contáctanos</h2>
        <div class="contact-details">
            <p><i class="fas fa-map-marker-alt"></i> Dirección: Calle Principal 123, Ciudad Ejemplo</p>
            <p><i class="fas fa-phone"></i> Teléfono: +51 970 846 395</p>
            <p><i class="fas fa-clock"></i> Horario: Lunes a Sábado 8:00 AM - 8:00 PM</p>
        </div>
    </div>
</section>

<!-- Botón de WhatsApp -->
<a href="https://wa.me/+51970846395?text=Hola%20MisterJugo,%20quisiera%20más%20información." 
   target="_blank" 
   class="whatsapp-btn">
    <i class="fab fa-whatsapp"></i>
</a>

<?php include 'includes/footer.php'; ?>