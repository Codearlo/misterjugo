// improved_loader.js - Pantalla de carga mejorada con animaciones y personalización
(function() {
    // Configuración personalizable
    var config = {
        logoUrl: null, // URL de tu logo (opcional)
        primaryColor: '#009688', // Color principal de tu sitio
        secondaryColor: '#4db6ac', // Color secundario para efectos
        backgroundColor: '#ffffff', // Color de fondo
        showProgressBar: true, // Mostrar barra de progreso
        loaderType: 'pulse', // Opciones: 'spinner', 'pulse', 'dots', 'progress'
        loaderText: 'Cargando...', // Texto a mostrar
        fadeDelay: 500, // Tiempo mínimo para mostrar el loader (ms)
        maxWaitTime: 3000 // Tiempo máximo antes de ocultar el loader (ms)
    };
    
    // Crear y añadir el estilo para el loader
    var style = document.createElement('style');
    style.innerHTML = `
        #page-loader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: ${config.backgroundColor};
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            transition: opacity 0.5s ease;
        }
        
        #page-loader.fade-out {
            opacity: 0;
        }
        
        .loader-content {
            text-align: center;
            padding: 20px;
            border-radius: 8px;
            max-width: 80%;
        }
        
        .loader-logo {
            max-width: 150px;
            max-height: 100px;
            margin-bottom: 20px;
        }
        
        .loader-spinner {
            border: 5px solid rgba(0,0,0,0.1);
            border-top: 5px solid ${config.primaryColor};
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px auto;
        }
        
        .loader-pulse {
            width: 60px;
            height: 60px;
            margin: 0 auto 20px auto;
            background-color: ${config.primaryColor};
            border-radius: 100%;
            animation: pulse 1.2s infinite ease-in-out;
        }
        
        .loader-dots {
            display: flex;
            justify-content: center;
            margin: 0 auto 20px auto;
        }
        
        .loader-dots .dot {
            width: 15px;
            height: 15px;
            margin: 0 5px;
            background-color: ${config.primaryColor};
            border-radius: 50%;
            display: inline-block;
            animation: dot-pulse 1.4s infinite ease-in-out both;
        }
        
        .loader-dots .dot:nth-child(1) {
            animation-delay: -0.32s;
        }
        
        .loader-dots .dot:nth-child(2) {
            animation-delay: -0.16s;
        }
        
        .loader-progress {
            width: 200px;
            height: 4px;
            background-color: rgba(0,0,0,0.1);
            border-radius: 2px;
            margin: 0 auto 20px auto;
            overflow: hidden;
        }
        
        .loader-progress-bar {
            height: 100%;
            width: 0%;
            background-color: ${config.primaryColor};
            transition: width 0.3s ease;
        }
        
        .loader-text {
            color: #333;
            font-family: Arial, sans-serif;
            font-size: 16px;
            margin: 10px 0;
        }
        
        .loader-tip {
            color: #666;
            font-family: Arial, sans-serif;
            font-size: 14px;
            margin-top: 15px;
            max-width: 300px;
            margin-left: auto;
            margin-right: auto;
            opacity: 0;
            transition: opacity 0.5s ease;
        }
        
        .loader-tip.show {
            opacity: 1;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        @keyframes pulse {
            0% {
                transform: scale(0);
                opacity: 1;
            }
            100% {
                transform: scale(1);
                opacity: 0;
            }
        }
        
        @keyframes dot-pulse {
            0%, 80%, 100% { transform: scale(0); }
            40% { transform: scale(1); }
        }
        
        /* Ocultar temporalmente el contenido de la página */
        body > *:not(#page-loader) {
            opacity: 0;
            transition: opacity 0.5s ease;
        }
        
        body.loaded > *:not(#page-loader) {
            opacity: 1;
        }
        
        body.loaded #page-loader {
            display: none;
        }
    `;
    document.head.appendChild(style);
    
    // Consejos para mostrar durante la carga
    var loadingTips = [
        "Preparando una experiencia increíble para ti...",
        "Estamos casi listos, gracias por tu paciencia.",
        "Organizando todo para ti...",
        "Los mejores sitios merecen un momento para cargar."
    ];
    
    // Crear el HTML del loader según el tipo configurado
    function getLoaderHTML() {
        var loaderElement = '';
        
        switch(config.loaderType) {
            case 'spinner':
                loaderElement = '<div class="loader-spinner"></div>';
                break;
            case 'pulse':
                loaderElement = '<div class="loader-pulse"></div>';
                break;
            case 'dots':
                loaderElement = '<div class="loader-dots"><div class="dot"></div><div class="dot"></div><div class="dot"></div></div>';
                break;
            case 'progress':
                loaderElement = '<div class="loader-progress"><div class="loader-progress-bar"></div></div>';
                break;
            default:
                loaderElement = '<div class="loader-spinner"></div>';
        }
        
        return loaderElement;
    }
    
    // Crear el elemento del loader
    var loader = document.createElement('div');
    loader.id = 'page-loader';
    
    var logoHTML = config.logoUrl ? `<img src="${config.logoUrl}" alt="Logo" class="loader-logo">` : '';
    var progressHTML = config.showProgressBar && config.loaderType !== 'progress' ? 
        '<div class="loader-progress"><div class="loader-progress-bar"></div></div>' : '';
    
    loader.innerHTML = `
        <div class="loader-content">
            ${logoHTML}
            ${getLoaderHTML()}
            ${progressHTML}
            <p class="loader-text">${config.loaderText}</p>
            <p class="loader-tip">${loadingTips[Math.floor(Math.random() * loadingTips.length)]}</p>
        </div>
    `;
    
    // Añadir el loader al principio del body al cargar el DOM
    document.addEventListener('DOMContentLoaded', function() {
        document.body.insertBefore(loader, document.body.firstChild);
        
        // Mostrar un consejo después de un tiempo
        setTimeout(function() {
            var tip = document.querySelector('.loader-tip');
            if (tip) tip.classList.add('show');
        }, 1000);
        
        // Forzar recarga de CSS
        var styleSheets = document.querySelectorAll('link[rel="stylesheet"]');
        var totalSheets = styleSheets.length;
        var loadedSheets = 0;
        
        styleSheets.forEach(function(sheet) {
            var href = sheet.getAttribute('href');
            if (href && !href.includes('cdnjs.cloudflare.com')) {
                if (!href.includes('?v=') && !href.includes('&v=')) {
                    var newHref = href + (href.includes('?') ? '&' : '?') + 'v=' + Math.floor(Date.now() / 1000);
                    sheet.setAttribute('href', newHref);
                }
                
                // Verificar eventos para cada hoja de estilo (si es posible)
                sheet.onload = function() {
                    loadedSheets++;
                    updateProgress(loadedSheets / totalSheets);
                };
                
                sheet.onerror = function() {
                    loadedSheets++;
                    updateProgress(loadedSheets / totalSheets);
                };
            }
        });
        
        // Función para actualizar la barra de progreso
        function updateProgress(percentage) {
            var progressBar = document.querySelector('.loader-progress-bar');
            if (progressBar) {
                progressBar.style.width = (percentage * 100) + '%';
            }
        }
        
        // Función para comprobar si todos los CSS se han cargado
        function checkCSSLoaded() {
            var allLoaded = true;
            for (var i = 0; i < document.styleSheets.length; i++) {
                try {
                    // Comprobar si la hoja de estilo está cargada comprobando sus reglas
                    var sheet = document.styleSheets[i];
                    var rules = sheet.cssRules || sheet.rules;
                    if (!rules || rules.length === 0) {
                        allLoaded = false;
                    }
                } catch(e) {
                    // Error de seguridad al acceder a CSS de otro dominio
                    // Simplemente ignoramos este error
                }
            }
            return allLoaded;
        }
        
        // Función para simular progreso cuando no se puede detectar
        function simulateProgress() {
            var progress = 0;
            var progressInterval = setInterval(function() {
                progress += Math.random() * 10;
                if (progress > 90) {
                    clearInterval(progressInterval);
                    return;
                }
                updateProgress(Math.min(progress / 100, 0.9));
            }, 200);
        }
        
        // Iniciar simulación de progreso para mejor UX
        simulateProgress();
        
        // Función para ocultar el loader
        function hideLoader() {
            // Asegurarnos de que la barra de progreso llegue al 100%
            updateProgress(1);
            
            // Añadir una pequeña demora para asegurar que todo se ve correctamente
            setTimeout(function() {
                // Primero aplicar clase de transición
                loader.classList.add('fade-out');
                
                // Después de la transición, marcar como cargado
                setTimeout(function() {
                    document.body.classList.add('loaded');
                }, 500);
            }, config.fadeDelay);
        }
        
        // Comprobar si los CSS están cargados
        var cssCheckInterval = setInterval(function() {
            if (checkCSSLoaded()) {
                clearInterval(cssCheckInterval);
                hideLoader();
            }
        }, 50);
        
        // Por si acaso, ocultar el loader después del tiempo máximo definido
        setTimeout(function() {
            clearInterval(cssCheckInterval);
            hideLoader();
        }, config.maxWaitTime);
    });
})();