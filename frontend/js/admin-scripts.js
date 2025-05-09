/**
 * Scripts para el panel de administración - MisterJugo
 */

document.addEventListener('DOMContentLoaded', function() {
    // Toggle sidebar
    const toggleSidebar = document.getElementById('toggle-sidebar');
    const closeSidebar = document.getElementById('close-sidebar');
    const sidebar = document.getElementById('sidebar');
    
    if (toggleSidebar && sidebar) {
        toggleSidebar.addEventListener('click', function() {
            sidebar.classList.toggle('collapsed');
            // Guardar preferencia en localStorage
            localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
        });
    }
    
    if (closeSidebar && sidebar) {
        closeSidebar.addEventListener('click', function() {
            sidebar.classList.add('collapsed');
            // Guardar preferencia en localStorage
            localStorage.setItem('sidebarCollapsed', 'true');
        });
    }
    
    // Recuperar preferencia de sidebar desde localStorage
    const sidebarCollapsed = localStorage.getItem('sidebarCollapsed');
    if (sidebarCollapsed === 'true' && sidebar) {
        sidebar.classList.add('collapsed');
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
            // On desktop, restore user preference
            if (sidebarCollapsed === 'true' && sidebar) {
                sidebar.classList.add('collapsed');
            } else if (sidebarCollapsed === 'false' && sidebar) {
                sidebar.classList.remove('collapsed');
            }
            
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
    
    // Datepicker initialization (if any)
    const datepickers = document.querySelectorAll('.datepicker');
    if (datepickers.length > 0 && typeof flatpickr !== 'undefined') {
        datepickers.forEach(function(picker) {
            flatpickr(picker, {
                dateFormat: 'd/m/Y',
                locale: 'es'
            });
        });
    }
    
    // Delete confirmations
    const deleteButtons = document.querySelectorAll('.delete-confirm');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            if (!confirm('¿Estás seguro de que deseas eliminar este elemento? Esta acción no se puede deshacer.')) {
                e.preventDefault();
            }
        });
    });
    
    // Make notifications dismissable
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
        
        // Auto-hide notifications after 5 seconds
        setTimeout(() => {
            notification.style.opacity = '0';
            setTimeout(() => {
                notification.style.display = 'none';
            }, 300);
        }, 5000);
    });
    
    // Tooltips for icons
    const tooltipElements = document.querySelectorAll('[title]');
    tooltipElements.forEach(el => {
        el.style.position = 'relative';
        
        el.addEventListener('mouseenter', function() {
            const title = this.getAttribute('title');
            if (!title) return;
            
            this.setAttribute('data-tooltip', title);
            this.removeAttribute('title');
            
            const tooltip = document.createElement('div');
            tooltip.className = 'tooltip';
            tooltip.textContent = title;
            tooltip.style.position = 'absolute';
            tooltip.style.backgroundColor = 'rgba(0, 0, 0, 0.8)';
            tooltip.style.color = '#fff';
            tooltip.style.padding = '5px 10px';
            tooltip.style.borderRadius = '4px';
            tooltip.style.fontSize = '0.8rem';
            tooltip.style.bottom = 'calc(100% + 5px)';
            tooltip.style.left = '50%';
            tooltip.style.transform = 'translateX(-50%)';
            tooltip.style.whiteSpace = 'nowrap';
            tooltip.style.zIndex = '1000';
            tooltip.style.opacity = '0';
            tooltip.style.transition = 'opacity 0.2s ease';
            
            this.appendChild(tooltip);
            
            setTimeout(() => {
                tooltip.style.opacity = '1';
            }, 10);
        });
        
        el.addEventListener('mouseleave', function() {
            const tooltip = this.querySelector('.tooltip');
            if (tooltip) {
                tooltip.style.opacity = '0';
                setTimeout(() => {
                    tooltip.remove();
                }, 200);
            }
            
            const originalTitle = this.getAttribute('data-tooltip');
            if (originalTitle) {
                this.setAttribute('title', originalTitle);
                this.removeAttribute('data-tooltip');
            }
        });
    });
    
    // Dropdown menus
    const dropdowns = document.querySelectorAll('.dropdown');
    dropdowns.forEach(dropdown => {
        const toggle = dropdown.querySelector('.dropdown-toggle');
        const menu = dropdown.querySelector('.dropdown-menu');
        
        if (toggle && menu) {
            let isOpen = false;
            
            toggle.addEventListener('click', function(e) {
                e.stopPropagation();
                isOpen = !isOpen;
                
                if (isOpen) {
                    menu.style.opacity = '1';
                    menu.style.visibility = 'visible';
                    menu.style.transform = 'translateY(0)';
                } else {
                    menu.style.opacity = '0';
                    menu.style.visibility = 'hidden';
                    menu.style.transform = 'translateY(10px)';
                }
            });
            
            // Close when clicking outside
            document.addEventListener('click', function() {
                if (isOpen) {
                    isOpen = false;
                    menu.style.opacity = '0';
                    menu.style.visibility = 'hidden';
                    menu.style.transform = 'translateY(10px)';
                }
            });
            
            // Prevent close when clicking on menu
            menu.addEventListener('click', function(e) {
                e.stopPropagation();
            });
        }
    });
    
    // Form validation
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        if (form.getAttribute('novalidate') === null) {
            form.addEventListener('submit', function(e) {
                if (!this.checkValidity()) {
                    e.preventDefault();
                    
                    // Add was-validated class to show validation feedback
                    this.classList.add('was-validated');
                    
                    // Find the first invalid element and focus it
                    const invalidElements = this.querySelectorAll(':invalid');
                    if (invalidElements.length > 0) {
                        invalidElements[0].focus();
                    }
                }
            });
        }
    });
    
    // Table row hover effect
    const dataTableRows = document.querySelectorAll('.data-table tbody tr');
    dataTableRows.forEach(row => {
        row.addEventListener('mouseenter', function() {
            this.style.backgroundColor = 'rgba(0, 0, 0, 0.02)';
        });
        
        row.addEventListener('mouseleave', function() {
            this.style.backgroundColor = '';
        });
    });
    
    // Active submenu items
    const currentPath = window.location.pathname;
    const sidebarLinks = document.querySelectorAll('.sidebar-nav a');
    
    sidebarLinks.forEach(link => {
        const href = link.getAttribute('href');
        
        if (href && currentPath.includes(href) && href !== '#' && href !== '/') {
            // Mark as active
            link.parentElement.classList.add('active');
            
            // If it's in a submenu, open the parent
            const submenu = link.closest('.submenu');
            if (submenu) {
                submenu.classList.add('open');
            }
        }
    });
    
    // Initialize any charts if Chart.js is available
    if (window.Chart) {
        initializeCharts();
    }
});

/**
 * Initialize charts if any
 */
function initializeCharts() {
    // Sales Chart
    const salesChartCanvas = document.getElementById('salesChart');
    if (salesChartCanvas) {
        const salesChart = new Chart(salesChartCanvas, {
            type: 'line',
            data: {
                labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
                datasets: [{
                    label: 'Ventas',
                    data: [12, 19, 3, 5, 2, 3, 20, 33, 23, 12, 5, 6],
                    backgroundColor: 'rgba(255, 122, 0, 0.1)',
                    borderColor: 'rgba(255, 122, 0, 1)',
                    borderWidth: 2,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }
    
    // Orders by Status Chart
    const ordersChartCanvas = document.getElementById('ordersChart');
    if (ordersChartCanvas) {
        const ordersChart = new Chart(ordersChartCanvas, {
            type: 'doughnut',
            data: {
                labels: ['Pendientes', 'Procesando', 'Completados', 'Cancelados'],
                datasets: [{
                    data: [12, 19, 3, 5],
                    backgroundColor: [
                        'rgba(255, 152, 0, 0.8)',
                        'rgba(33, 150, 243, 0.8)',
                        'rgba(76, 175, 80, 0.8)',
                        'rgba(244, 67, 54, 0.8)'
                    ],
                    borderColor: [
                        'rgba(255, 152, 0, 1)',
                        'rgba(33, 150, 243, 1)',
                        'rgba(76, 175, 80, 1)',
                        'rgba(244, 67, 54, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }
}