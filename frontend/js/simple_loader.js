// fixed_loader.js - Solución al problema de carga de estilos
(function() {
    // Configuración personalizable
    var config = {
        backgroundColor: '#ffffff',
        primaryColor: '#009688',
        loaderText: 'Cargando...',
        fadeDelay: 500,
        maxWaitTime: 3000
    };

    // Insertar estilos de manera diferente para asegurar que se apliquen correctamente
    function insertStyles() {
        var styleElem = document.createElement('style');
        styleElem.type = 'text/css';
        
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
            
            .loader-spinner {
                border: 5px solid rgba(0,0,0,0.1);
                border-top: 5px solid ${config.primaryColor};
                border-radius: 50%;
                width: 50px;
                height: 50px;
                animation: spin 1s linear infinite;
                margin: 0 auto 20px auto;
            }
            
            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
            
            .loader-text {
                color: #333;
                font-family: Arial, sans-serif;
                font-size: 16px;
                margin: 10px 0;
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
    
    // Crear un elemento de loader simplificado
    function createLoader() {
        var loader = document.createElement('div');
        loader.id = 'page-loader';
        loader.innerHTML = `
            <div class="loader-content">
                <div class="loader-spinner"></div>
                <p class="loader-text">${config.loaderText}</p>
            </div>
        `;
        return loader;
    }
    
    // Función principal para inicializar el loader
    function initLoader() {
        // Insertar estilos inmediatamente
        insertStyles();
        
        // Crear y añadir el loader al body cuando el DOM esté listo
        document.addEventListener('DOMContentLoaded', function() {
            var loader = createLoader();
            document.body.insertBefore(loader, document.body.firstChild);
            
            // Forzar recarga de CSS
            var styleSheets = document.querySelectorAll('link[rel="stylesheet"]');
            styleSheets.forEach(function(sheet) {
                var href = sheet.getAttribute('href');
                if (href && !href.includes('cdnjs.cloudflare.com')) {
                    var timestamp = Math.floor(Date.now() / 1000);
                    var newHref = href.includes('?') 
                        ? href + '&v=' + timestamp 
                        : href + '?v=' + timestamp;
                    sheet.setAttribute('href', newHref);
                }
            });
            
            // Función para comprobar si los CSS están cargados
            function checkCSSLoaded() {
                var allLoaded = true;
                styleSheets.forEach(function(sheet) {
                    try {
                        var rules = sheet.sheet && (sheet.sheet.cssRules || sheet.sheet.rules);
                        if (!rules || rules.length === 0) {
                            allLoaded = false;
                        }
                    } catch(e) {
                        // Error de seguridad al acceder a CSS de otro dominio
                        // Esto es normal, continuamos
                    }
                });
                return allLoaded;
            }
            
            // Función para ocultar el loader
            function hideLoader() {
                loader.classList.add('fade-out');
                
                setTimeout(function() {
                    document.body.classList.add('loaded');
                }, 500);
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