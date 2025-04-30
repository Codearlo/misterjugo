<?php include 'includes/header.php'; ?>
<link rel="stylesheet" href="./css/index.css"> <!-- Estilo solo para esta página -->

<script>
    const userIcon = document.getElementById('user-menu-toggle');
    const sideMenu = document.getElementById('side-menu');
    const closeBtn = document.getElementById('close-menu-btn');

    userIcon.addEventListener('click', () => {
        sideMenu.classList.add('active');
    });

    closeBtn.addEventListener('click', () => {
        sideMenu.classList.remove('active');
    });
</script>

<main class="hero-section">
    <div class="container">
        <div class="hero-content">
            <div class="hero-image">
                <img src="./images/jugos-misterjugo.jpg" alt="Jugos MisterJugo">
            </div>
            <div class="hero-text">
                <h1>Have you eaten yet?</h1>
                <p>Descubre nuestros jugos frescos y nutritivos.</p>
                <button class="btn-order-now">Order Now</button>
            </div>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>