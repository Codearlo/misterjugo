// cache_busting_loader.js - Con protección anti-caché incluida
(function() {
    // Versión del loader - cambia esto cada vez que actualices el script
    var VERSION = '1.0.0';
    
    // Ruta del logo
    var logoPath = "images/logo_mrjugo.png";
    
    // Añade timestamp para prevenir caché de la imagen
    logoPath = addCacheBuster(logoPath);
    
    // Colores
    var bgColor = '#ffaa55';  // Naranja de fondo
    var barColor = '#e67300'; // Naranja oscuro para la barra
    
    // Configuración de tiempos
    var totalLoadTime = 800;  // Tiempo total en milisegundos
    
    // Función para añadir parámetro anti-caché a URLs
    function addCacheBuster(url) {
        // Evita añadir parámetro a URLs vacías
        if (!url || url.length === 0) return url;
        
        var timestamp = new Date().getTime();
        var connector = url.indexOf('?') !== -1 ? '&' : '?';
        return url + connector + '_v=' + timestamp;
    }
    
    // Insertar estilos
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
    
    // Función para forzar la recarga de todos los CSS
    function reloadAllCSS() {
        var links = document.getElementsByTagName('link');
        for (var i = 0; i < links.length; i++) {
            var link = links[i];
            if (link.rel === 'stylesheet') {
                link.href = addCacheBuster(link.href);
            }
        }
    }
    
    // Función para cargar rápido
    function quickLoad() {
        var progress = document.getElementById('mjloader-progress');
        if (progress) {
            // Iniciar la animación inmediatamente al 100%
            setTimeout(function() {
                progress.style.width = '100%';
            }, 10);
            
            // Forzar recarga de todos los CSS para evitar caché
            reloadAllCSS();
            
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
    
    // Manejar el almacenamiento local para verificar versión
    function handleVersionCache() {
        try {
            var storedVersion = localStorage.getItem('mjloader_version');
            // Si la versión cambió, forzar recarga de página para actualizar caché
            if (storedVersion && storedVersion !== VERSION) {
                localStorage.setItem('mjloader_version', VERSION);
                // No usar reload() inmediatamente para evitar bucles
                setTimeout(function() {
                    window.location.reload(true); // true = forzar recarga desde servidor
                }, 100);
                return false;
            }
            // Guardar versión actual
            localStorage.setItem('mjloader_version', VERSION);
            return true;
        } catch (e) {
            // Si localStorage no está disponible, continuar normalmente
            return true;
        }
    }
    
    // Inicializar todo
    function init() {
        // Verificar caché - si devuelve false, se está recargando la página
        if (!handleVersionCache()) return;
        
        // Crear estilos
        createStyles();
        
        // Crear y añadir el loader
        var loader = createLoader();
        
        if (document.body) {
            document.body.appendChild(loader);
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