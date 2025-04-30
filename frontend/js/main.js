// frontend/js/main.js
console.log("MisterJuco - ¡JS cargado correctamente!");

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