</div>
            </div>
            
            <footer class="admin-footer">
                <p>&copy; <?php echo date('Y'); ?> MisterJugo - Panel de Administración</p>
                <p>Versión 1.0</p>
            </footer>
        </main>
    </div>
    
    <!-- JavaScript -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Toggle sidebar
        const toggleSidebar = document.getElementById('toggle-sidebar');
        const closeSidebar = document.getElementById('close-sidebar');
        const sidebar = document.getElementById('sidebar');
        
        if (toggleSidebar && sidebar) {
            toggleSidebar.addEventListener('click', function() {
                sidebar.classList.toggle('collapsed');
            });
        }
        
        if (closeSidebar && sidebar) {
            closeSidebar.addEventListener('click', function() {
                sidebar.classList.add('collapsed');
            });
        }
        
        // Responsive handling for mobile
        function handleResponsive() {
            if (window.innerWidth <= 768 && sidebar) {
                sidebar.classList.add('collapsed');
                
                // Show close button on mobile
                if (closeSidebar) {
                    closeSidebar.style.display = 'block';
                }
            } else {
                // Hide close button on desktop
                if (closeSidebar) {
                    closeSidebar.style.display = 'none';
                }
            }
        }
        
        // Initial call
        handleResponsive();
        
        // Listen for window resize
        window.addEventListener('resize', handleResponsive);
        
        // Inicializar datepickers si existen
        if (typeof flatpickr !== 'undefined') {
            flatpickr('.datepicker', {
                dateFormat: 'd/m/Y',
                locale: 'es'
            });
        }
        
        // Manejar confirmaciones de eliminación
        const deleteButtons = document.querySelectorAll('.delete-confirm');
        deleteButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                if (!confirm('¿Estás seguro de que deseas eliminar este elemento? Esta acción no se puede deshacer.')) {
                    e.preventDefault();
                }
            });
        });
        
        // Hacer que las notificaciones se puedan cerrar
        const notifications = document.querySelectorAll('.notification');
        notifications.forEach(notification => {
            const closeBtn = document.createElement('span');
            closeBtn.innerHTML = '&times;';
            closeBtn.className = 'notification-close';
            closeBtn.style.cursor = 'pointer';
            closeBtn.style.marginLeft = 'auto';
            closeBtn.style.fontSize = '1.2rem';
            
            closeBtn.addEventListener('click', function() {
                notification.style.opacity = '0';
                setTimeout(() => {
                    notification.style.display = 'none';
                }, 300);
            });
            
            notification.appendChild(closeBtn);
            
            // Auto-cerrar después de 5 segundos
            setTimeout(() => {
                notification.style.opacity = '0';
                setTimeout(() => {
                    notification.style.display = 'none';
                }, 300);
            }, 5000);
        });
    });
    </script>
</body>
</html>