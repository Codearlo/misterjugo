// progress_bar_loader.js - Loader con barra de progreso y colores personalizables
(function() {
    // Configuración personalizable - Modifica estos valores según tus preferencias
    var config = {
        // Colores principales
        backgroundColor: '#ffffff', // Color de fondo de la pantalla
        progressBarColor: '#009688', // Color de la barra de progreso
        progressBarBackground: '#f3f3f3', // Color de fondo de la barra
        textColor: '#333333', // Color del texto
        
        // Textos
        loadingText: 'Cargando...', // Texto principal
        
        // Configuración de la barra
        progressBarHeight: '6px', // Altura de la barra de progreso
        progressBarWidth: '250px', // Ancho de la barra de progreso
        
        // Tiempos de transición
        fadeDelay: 500, // Tiempo antes de comenzar a desvanecer (ms)
        fadeTime: 500, // Duración de la transición de desvanecimiento (ms)
        maxWaitTime: 3000, // Tiempo máximo antes de ocultar el loader (ms)
        
        // Efectos visuales adicionales
        showPulseEffect: true, // Mostrar efecto pulsante en la barra
        showShadow: true // Mostrar sombra en la barra
    };

    // Insertar estilos de manera optimizada
    function insertStyles() {
        var styleElem = document.createElement('style');
        styleElem.type = 'text/css';
        
        // Definir los estilos con las opciones personalizables
        var css = `
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
                transition: opacity ${config.fadeTime}ms ease;
            }
            
            #page-loader.fade-out {
                opacity: 0;
            }
            
            .loader-content {
                text-align: center;
                padding: 25px;
                border-radius: 8px;
                max-width: 80%;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
            }
            
            .loader-progress-container {
                background-color: ${config.progressBarBackground};
                width: ${config.progressBarWidth};
                height: ${config.progressBarHeight};
                border-radius: ${parseInt(config.progressBarHeight) / 2}px;
                overflow: hidden;
                margin: 0 auto 20px auto;
                position: relative;
                ${config.showShadow ? 'box-shadow: 0 2px 5px rgba(0,0,0,0.1);' : ''}
            }
            
            .loader-progress-bar {
                height: 100%;
                width: 0%;
                background-color: ${config.progressBarColor};
                transition: width 0.3s ease-in-out;
                position: relative;
                ${config.showPulseEffect ? 'animation: progress-pulse 1.5s ease-in-out infinite;' : ''}
            }
            
            @keyframes progress-pulse {
                0% { opacity: 1; }
                50% { opacity: 0.7; }
                100% { opacity: 1; }
            }
            
            .loader-text {
                color: ${config.textColor};
                font-family: Arial, sans-serif;
                font-size: 16px;
                margin: 10px 0;
                font-weight: 500;
            }
            
            /* Ocultar temporalmente el contenido de la página */
            body > *:not(#page-loader) {
                opacity: 0;
                transition: opacity ${config.fadeTime}ms ease;
            }
            
            body.loaded > *:not(#page-loader) {
                opacity: 1;
            }
            
            body.loaded #page-loader {
                display: none;
            }
        `;
        
        // Asegurarse de que los estilos se apliquen correctamente
        if (styleElem.styleSheet) {
            // Para IE
            styleElem.styleSheet.cssText = css;
        } else {
            // Para el resto de navegadores
            styleElem.appendChild(document.createTextNode(css));
        }
        
        // Insertar al principio del head para darle prioridad
        var head = document.head || document.getElementsByTagName('head')[0];
        head.insertBefore(styleElem, head.firstChild);
    }
    
    // Crear la estructura del loader con barra de progreso
    function createLoader() {
        var loader = document.createElement('div');
        loader.id = 'page-loader';
        loader.innerHTML = `
            <div class="loader-content">
                <div class="loader-progress-container">
                    <div class="loader-progress-bar" id="loader-progress"></div>
                </div>
                <p class="loader-text">${config.loadingText}</p>
            </div>
        `;
        return loader;
    }
    
    // Función para actualizar el progreso de la barra
    function updateProgress(percentage) {
        var progressBar = document.getElementById('loader-progress');
        if (progressBar) {
            progressBar.style.width = (percentage * 100) + '%';
        }
    }
    
    // Simular el progreso de carga para mejor experiencia de usuario
    function simulateProgress() {
        var progress = 0;
        var progressInterval = setInterval(function() {
            // Incrementar progreso de manera no lineal para hacerlo más realista
            var increment = Math.max(0.5, Math.random() * 5) * (1 - progress / 100);
            progress += increment;
            
            // Limitamos al 90% - el 100% se muestra cuando realmente está todo cargado
            if (progress > 90) {
                clearInterval(progressInterval);
                progress = 90;
            }
            
            updateProgress(progress / 100);
        }, 200);
        
        return progressInterval;
    }
    
    // Función principal para inicializar el loader
    function initLoader() {
        // Insertar estilos inmediatamente
        insertStyles();
        
        // Esperar a que el DOM esté listo
        document.addEventListener('DOMContentLoaded', function() {
            // Crear y añadir el loader
            var loader = createLoader();
            document.body.insertBefore(loader, document.body.firstChild);
            
            // Empezar simulación de progreso
            var progressInterval = simulateProgress();
            
            // Forzar recarga de CSS con timestamp para evitar caché
            var styleSheets = document.querySelectorAll('link[rel="stylesheet"]');
            var totalSheets = styleSheets.length;
            var loadedSheets = 0;
            
            styleSheets.forEach(function(sheet) {
                var href = sheet.getAttribute('href');
                if (href && !href.includes('cdnjs.cloudflare.com')) {
                    var timestamp = Math.floor(Date.now() / 1000);
                    var newHref = href.includes('?') 
                        ? href + '&v=' + timestamp 
                        : href + '?v=' + timestamp;
                    sheet.setAttribute('href', newHref);
                    
                    // Verificar si podemos detectar cuando se carga la hoja de estilo
                    var originalSheet = sheet;
                    var newSheet = sheet.cloneNode();
                    newSheet.href = newHref;
                    
                    newSheet.onload = function() {
                        loadedSheets++;
                        updateProgress(Math.min(0.9, loadedSheets / totalSheets));
                    };
                    
                    originalSheet.parentNode.insertBefore(newSheet, originalSheet.nextSibling);
                    originalSheet.parentNode.removeChild(originalSheet);
                }
            });
            
            // Función para comprobar si todos los CSS están cargados
            function checkCSSLoaded() {
                var allLoaded = true;
                for (var i = 0; i < document.styleSheets.length; i++) {
                    try {
                        var sheet = document.styleSheets[i];
                        var rules = sheet.cssRules || sheet.rules;
                        if (!rules || rules.length === 0) {
                            allLoaded = false;
                        }
                    } catch(e) {
                        // Error de seguridad al acceder a CSS de otro dominio
                        // Esto es normal, continuamos
                    }
                }
                return allLoaded;
            }
            
            // Función para ocultar el loader
            function hideLoader() {
                // Detener la simulación de progreso
                clearInterval(progressInterval);
                
                // Mostrar 100% en la barra
                updateProgress(1);
                
                // Pequeña demora para que se vea el 100%
                setTimeout(function() {
                    loader.classList.add('fade-out');
                    
                    setTimeout(function() {
                        document.body.classList.add('loaded');
                    }, config.fadeTime);
                }, config.fadeDelay);
            }
            
            // Comprobar periódicamente si los CSS están cargados
            var cssCheckInterval = setInterval(function() {
                if (checkCSSLoaded()) {
                    clearInterval(cssCheckInterval);
                    hideLoader();
                }
            }, 50);
            
            // Tiempo máximo de espera
            setTimeout(function() {
                clearInterval(cssCheckInterval);
                hideLoader();
            }, config.maxWaitTime);
        });
    }
    
    // Ejecutar inmediatamente
    initLoader();
})();