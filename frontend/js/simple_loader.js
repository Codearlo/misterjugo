// misterjugo_fullscreen_loader.js - Loader a pantalla completa con estilo MisterJugo
(function() {
    // Colores extraídos del CSS de MisterJugo
    var bgColor = '#ffaa55';          // Color naranja de fondo (como el de la imagen)
    var barColor = '#e67300';         // Naranja más oscuro para la barra de progreso
    var textColor = '#ffffff';        // Texto en color blanco
    var logoUrl = '';                 // URL del logo (déjala vacía si aún no la tienes)
    
    // Crear y añadir los estilos del loader
    function insertLoaderStyles() {
        var styleEl = document.createElement('style');
        styleEl.type = 'text/css';
        styleEl.textContent = `
            #mj-loader {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: ${bgColor} !important;
                display: flex;
                justify-content: center;
                align-items: center;
                z-index: 9999;
                transition: opacity 0.5s ease;
            }
            
            #mj-loader.fade-out {
                opacity: 0;
            }
            
            .mj-loader-content {
                text-align: center !important;
                width: 80% !important;
                max-width: 400px !important;
            }
            
            .mj-loader-logo {
                margin-bottom: 15px !important;
            }
            
            .mj-loader-logo img {
                max-width: 100px !important;
                height: auto !important;
            }
            
            .mj-logo-text {
                font-size: 2.2rem !important;
                font-weight: 700 !important;
                color: ${textColor} !important;
                margin: 10px 0 !important;
                font-family: 'Poppins', Arial, sans-serif !important;
            }
            
            .mj-loader-subtitle {
                color: ${textColor} !important;
                font-size: 1rem !important;
                margin: 10px 0 25px !important;
                font-family: 'Poppins', Arial, sans-serif !important;
                font-weight: 500 !important;
            }
            
            .mj-progress-container {
                background-color: rgba(255, 255, 255, 0.3) !important;
                height: 10px !important;
                border-radius: 5px !important;
                overflow: hidden !important;
                margin: 0 auto 20px auto !important;
                width: 100% !important;
            }
            
            .mj-progress-bar {
                height: 100% !important;
                width: 0% !important;
                background-color: ${barColor} !important;
                border-radius: 5px !important;
                position: relative !important;
                transition: width 0.3s ease-in-out !important;
            }
            
            .mj-progress-bar::before {
                content: '' !important;
                position: absolute !important;
                top: 0 !important;
                left: -100% !important;
                width: 100% !important;
                height: 100% !important;
                background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent) !important;
                animation: shimmer 1.5s infinite !important;
            }
            
            @keyframes shimmer {
                0% { left: -100%; }
                100% { left: 100%; }
            }
            
            .mj-loader-text {
                color: ${textColor} !important;
                font-family: 'Poppins', Arial, sans-serif !important;
                font-size: 1rem !important;
                margin: 15px 0 !important;
                font-weight: 700 !important;
                letter-spacing: 1px !important;
            }
            
            /* Ocultar temporalmente el contenido de la página */
            body > *:not(#mj-loader) {
                opacity: 0 !important;
                transition: opacity 0.5s ease !important;
            }
            
            body.mj-loaded > *:not(#mj-loader) {
                opacity: 1 !important;
            }
            
            body.mj-loaded #mj-loader {
                display: none !important;
            }
            
            @keyframes fadeIn {
                from { opacity: 0; }
                to { opacity: 1; }
            }
            
            .mj-fade-in {
                animation: fadeIn 0.5s ease forwards !important;
            }
        `;
        
        document.head.insertBefore(styleEl, document.head.firstChild);
    }
    
    // Crear el HTML del loader
    function createLoaderHTML() {
        var loaderDiv = document.createElement('div');
        loaderDiv.id = 'mj-loader';
        loaderDiv.className = 'mj-fade-in';
        
        // Logo HTML (si existe URL del logo)
        var logoHTML = '';
        if (logoUrl && logoUrl.length > 0) {
            logoHTML = `<img src="${logoUrl}" alt="MisterJugo Logo">`;
        }
        
        loaderDiv.innerHTML = `
            <div class="mj-loader-content">
                <div class="mj-loader-logo">
                    ${logoHTML}
                </div>
                <div class="mj-logo-text">MisterJugo</div>
                <div class="mj-loader-subtitle">Preparando todo para ti...</div>
                <div class="mj-progress-container">
                    <div class="mj-progress-bar" id="mj-progress-bar"></div>
                </div>
                <p class="mj-loader-text">CARGANDO...</p>
            </div>
        `;
        
        return loaderDiv;
    }
    
    // Actualizar la barra de progreso de manera correcta
    function updateProgress(percent) {
        var progressBar = document.getElementById('mj-progress-bar');
        if (progressBar) {
            progressBar.style.width = percent + '%';
        }
    }
    
    // Simular progreso de forma más realista
    function simulateProgress() {
        var progress = 0;
        var interval = setInterval(function() {
            // Calcular incremento: más rápido al inicio y más lento cerca del final
            var increment = 0;
            
            if (progress < 30) {
                increment = Math.random() * 4 + 1; // Avance rápido al inicio
            } else if (progress < 60) {
                increment = Math.random() * 3 + 0.5; // Avance moderado
            } else if (progress < 85) {
                increment = Math.random() * 1.5 + 0.2; // Avance lento
            } else {
                increment = Math.random() * 0.5 + 0.1; // Muy lento al final
            }
            
            progress += increment;
            
            // Limitar al 95% - el 100% se mostrará cuando realmente esté cargado
            if (progress > 95) {
                clearInterval(interval);
                progress = 95;
            }
            
            updateProgress(progress);
        }, 150);
        
        return interval;
    }
    
    // Función principal que inicia todo
    function initLoader() {
        // 1. Insertar estilos
        insertLoaderStyles();
        
        // 2. Crear y añadir el loader
        var loader = createLoaderHTML();
        
        // Si el DOM ya está cargado, añadimos inmediatamente
        if (document.body) {
            document.body.insertBefore(loader, document.body.firstChild);
            var progressInterval = simulateProgress();
            startWatching(progressInterval);
        } else {
            // Si no, esperamos a que el DOM esté listo
            document.addEventListener('DOMContentLoaded', function() {
                document.body.insertBefore(loader, document.body.firstChild);
                var progressInterval = simulateProgress();
                startWatching(progressInterval);
            });
        }
    }
    
    // Vigilar la carga de CSS y ocultar el loader cuando esté listo
    function startWatching(progressInterval) {
        // Forzar recarga de CSS con parámetro de versión
        var styleSheets = document.querySelectorAll('link[rel="stylesheet"]');
        styleSheets.forEach(function(sheet) {
            var href = sheet.getAttribute('href');
            if (href && !href.includes('?v=') && !href.includes('&v=')) {
                var newHref = href + (href.includes('?') ? '&' : '?') + 'v=' + Math.floor(Date.now() / 1000);
                sheet.setAttribute('href', newHref);
            }
        });
        
        // Comprobar periódicamente si los CSS están cargados
        var cssCheckInterval = setInterval(function() {
            if (checkIfCSSLoaded()) {
                clearInterval(cssCheckInterval);
                clearInterval(progressInterval);
                
                // Asegurar que la barra llegue al 100%
                updateProgress(100);
                
                // Dar tiempo para ver la barra completa
                setTimeout(function() {
                    hideLoader();
                }, 500);
            }
        }, 100);
        
        // Por seguridad, un tiempo máximo
        setTimeout(function() {
            clearInterval(cssCheckInterval);
            clearInterval(progressInterval);
            updateProgress(100);
            
            // Dar tiempo para ver la barra completa
            setTimeout(function() {
                hideLoader();
            }, 500);
        }, 5000);
    }
    
    // Comprobar si los CSS se han cargado correctamente
    function checkIfCSSLoaded() {
        var allLoaded = true;
        for (var i = 0; i < document.styleSheets.length; i++) {
            try {
                var sheet = document.styleSheets[i];
                var rules = sheet.cssRules || sheet.rules;
                if (!rules || rules.length === 0) {
                    allLoaded = false;
                }
            } catch(e) {
                // Error típico de CORS, ignoramos
            }
        }
        return allLoaded;
    }
    
    // Ocultar el loader con efecto de desvanecimiento
    function hideLoader() {
        var loader = document.getElementById('mj-loader');
        if (loader) {
            loader.classList.add('fade-out');
            
            setTimeout(function() {
                document.body.classList.add('mj-loaded');
            }, 500);
        }
    }
    
    // Ejecutar todo al cargar
    initLoader();
})();