(function() {
    // Ejecutar cuando el DOM esté listo
    document.addEventListener('DOMContentLoaded', function() {
        // Buscar todas las hojas de estilo
        var styleSheets = document.querySelectorAll('link[rel="stylesheet"]');
        
        // Añadir parámetro de versión a cada hoja de estilo
        styleSheets.forEach(function(sheet) {
            var href = sheet.getAttribute('href');
            // No modificar CDNs externas para evitar problemas
            if (href && !href.includes('cdnjs.cloudflare.com')) {
                // Evitar duplicar parámetros de versión
                if (!href.includes('?v=') && !href.includes('&v=')) {
                    // Añadir parámetro con timestamp único
                    var newHref = href + (href.includes('?') ? '&' : '?') + 'v=' + Math.floor(Date.now() / 1000);
                    sheet.setAttribute('href', newHref);
                }
            }
        });
    });
})();

// ultrafast_misterjugo_loader.js - Loader mínimo de alta velocidad
(function() {
    // Ruta del logo
    var logoPath = "images/logo_mrjugo.png";
    
    // Colores
    var bgColor = '#ffaa55';  // Naranja de fondo
    var barColor = '#e67300'; // Naranja oscuro para la barra
    
    // Configuración de tiempos ultra-rápidos
    var totalLoadTime = 800;  // Tiempo total en milisegundos (menos de 1 segundo)
    
    // Insertar estilos mínimos
    function createStyles() {
        var style = document.createElement('style');
        style.textContent = `
            #mjloader-overlay {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: ${bgColor};
                z-index: 9999;
                display: flex;
                justify-content: center;
                align-items: center;
                transition: opacity 0.2s ease;
            }
            
            .mjloader-container {
                text-align: center;
                width: 300px;
            }
            
            .mjloader-logo {
                margin-bottom: 15px;
            }
            
            .mjloader-logo img {
                max-width: 120px;
                height: auto;
            }
            
            .mjloader-title {
                color: white;
                font-size: 28px;
                font-weight: bold;
                margin: 10px 0;
                font-family: Arial, sans-serif;
            }
            
            .mjloader-bar-container {
                width: 100%;
                height: 10px;
                background-color: rgba(255, 255, 255, 0.3);
                border-radius: 5px;
                overflow: hidden;
                margin-bottom: 15px;
            }
            
            .mjloader-bar {
                width: 0%;
                height: 100%;
                background-color: ${barColor};
                transition: width 0.7s cubic-bezier(0.1, 0.7, 1.0, 0.1);
            }
            
            .mjloader-text {
                color: white;
                font-size: 16px;
                font-weight: bold;
                text-transform: uppercase;
                font-family: Arial, sans-serif;
            }
            
            body > *:not(#mjloader-overlay) {
                opacity: 0;
                transition: opacity 0.2s ease;
            }
            
            body.mjloader-done > *:not(#mjloader-overlay) {
                opacity: 1;
            }
            
            #mjloader-overlay.fade-out {
                opacity: 0;
            }
            
            body.mjloader-done #mjloader-overlay {
                display: none;
            }
        `;
        document.head.appendChild(style);
    }
    
    // Crear el HTML del loader
    function createLoader() {
        var overlay = document.createElement('div');
        overlay.id = 'mjloader-overlay';
        
        overlay.innerHTML = `
            <div class="mjloader-container">
                <div class="mjloader-logo">
                    <img src="${logoPath}" alt="MisterJugo Logo">
                </div>
                <div class="mjloader-title">MisterJugo</div>
                <div class="mjloader-bar-container">
                    <div class="mjloader-bar" id="mjloader-progress"></div>
                </div>
                <div class="mjloader-text">CARGANDO...</div>
            </div>
        `;
        
        return overlay;
    }
    
    // Función para mostrar un progreso rápido
    function quickLoad() {
        var progress = document.getElementById('mjloader-progress');
        if (progress) {
            // Iniciar la animación inmediatamente al 100%
            setTimeout(function() {
                progress.style.width = '100%';
            }, 10);
            
            // Ocultar después del tiempo configurado
            setTimeout(hideLoader, totalLoadTime);
        }
    }
    
    // Ocultar el loader
    function hideLoader() {
        var overlay = document.getElementById('mjloader-overlay');
        if (overlay) {
            overlay.classList.add('fade-out');
            
            setTimeout(function() {
                document.body.classList.add('mjloader-done');
            }, 200);
        }
    }
    
    // Inicializar
    function init() {
        // 1. Crear estilos
        createStyles();
        
        // 2. Crear y añadir el loader
        var loader = createLoader();
        
        if (document.body) {
            document.body.appendChild(loader);
            // Cargar rápidamente
            quickLoad();
        } else {
            document.addEventListener('DOMContentLoaded', function() {
                document.body.appendChild(loader);
                quickLoad();
            });
        }
    }
    
    // Ejecutar inmediatamente
    init();
})();