// misterjugo_loader.js - Loader personalizado con estilo MisterJugo
(function() {
    // Colores extraídos del CSS de MisterJugo (naranja)
    var primaryColor = '#ff9933';       // Color naranja principal (simulando --primary-color)
    var primaryLight = '#ffb366';       // Naranja más claro (simulando --primary-light)
    var primaryDark = '#e67300';        // Naranja más oscuro (simulando --primary-dark)
    var bgColor = '#ffffff';            // Fondo blanco
    var textColor = '#333333';          // Color de texto
    var secondaryColor = '#666666';     // Color secundario para detalles
    
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
                background: linear-gradient(135deg, ${primaryLight}, ${primaryColor}) !important;
                display: flex;
                justify-content: center;
                align-items: center;
                z-index: 9999;
                transition: opacity 0.5s ease;
            }
            
            #mj-loader.fade-out {
                opacity: 0;
            }
            
            .mj-loader-card {
                background-color: #ffffff !important;
                border-radius: 12px !important;
                box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15) !important;
                padding: 30px !important;
                text-align: center !important;
                width: 85% !important;
                max-width: 320px !important;
                transition: all 0.3s ease !important;
            }
            
            .mj-loader-logo {
                text-align: center !important;
                margin-bottom: 20px !important;
            }
            
            .mj-logo-text {
                font-size: 1.8rem !important;
                font-weight: 700 !important;
                color: ${primaryColor} !important;
                margin: 10px 0 !important;
                font-family: 'Poppins', Arial, sans-serif !important;
            }
            
            .mj-loader-subtitle {
                color: ${secondaryColor} !important;
                font-size: 0.95rem !important;
                margin: 10px 0 25px !important;
                font-family: 'Poppins', Arial, sans-serif !important;
            }
            
            .mj-progress-container {
                background-color: rgba(0, 0, 0, 0.1) !important;
                height: 8px !important;
                border-radius: 4px !important;
                overflow: hidden !important;
                margin: 0 auto 20px auto !important;
            }
            
            .mj-progress-bar {
                height: 100% !important;
                width: 0% !important;
                background: linear-gradient(135deg, ${primaryColor}, ${primaryDark}) !important;
                border-radius: 4px !important;
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
        
        loaderDiv.innerHTML = `
            <div class="mj-loader-card">
                <div class="mj-loader-logo">
                    <div class="mj-logo-text">MisterJugo</div>
                </div>
                <div class="mj-loader-subtitle">Preparando todo para ti...</div>
                <div class="mj-progress-container">
                    <div class="mj-progress-bar" id="mj-progress-bar"></div>
                </div>
                <p class="mj-loader-text">Cargando...</p>
            </div>
        `;
        
        return loaderDiv;
    }
    
    // Actualizar la barra de progreso
    function updateProgress(percent) {
        var progressBar = document.getElementById('mj-progress-bar');
        if (progressBar) {
            progressBar.style.width = percent + '%';
        }
    }
    
    // Simular progreso
    function simulateProgress() {
        var progress = 0;
        var interval = setInterval(function() {
            progress += Math.random() * 5;
            if (progress > 90) {
                clearInterval(interval);
                progress = 90;
            }
            updateProgress(progress);
        }, 200);
        
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
                
                // Completar el progreso y ocultar
                updateProgress(100);
                
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
            hideLoader();
        }, 3500);
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