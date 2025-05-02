// loader.js - Pantalla de carga mientras el CSS se aplica
(function() {
    // Crear y añadir el estilo para el loader
    var style = document.createElement('style');
    style.innerHTML = `
        #page-loader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: #ffffff;
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            transition: opacity 0.3s ease;
        }
        
        #page-loader.fade-out {
            opacity: 0;
        }
        
        .loader-content {
            text-align: center;
        }
        
        .loader-spinner {
            border: 5px solid #f3f3f3;
            border-top: 5px solid #009688; /* Cambia este color al color principal de tu sitio */
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
            margin: 0 auto 15px auto;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Ocultar temporalmente el contenido de la página */
        body > * {
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        body.loaded > * {
            opacity: 1;
        }
        
        body.loaded #page-loader {
            display: none;
        }
    `;
    document.head.appendChild(style);
    
    // Crear el elemento del loader
    var loader = document.createElement('div');
    loader.id = 'page-loader';
    loader.innerHTML = `
        <div class="loader-content">
            <div class="loader-spinner"></div>
            <p>Cargando...</p>
        </div>
    `;
    
    // Añadir el loader al principio del body
    document.addEventListener('DOMContentLoaded', function() {
        document.body.insertBefore(loader, document.body.firstChild);
        
        // Forzar recarga de CSS
        var styleSheets = document.querySelectorAll('link[rel="stylesheet"]');
        styleSheets.forEach(function(sheet) {
            var href = sheet.getAttribute('href');
            if (href && !href.includes('cdnjs.cloudflare.com')) {
                if (!href.includes('?v=') && !href.includes('&v=')) {
                    var newHref = href + (href.includes('?') ? '&' : '?') + 'v=' + Math.floor(Date.now() / 1000);
                    sheet.setAttribute('href', newHref);
                }
            }
        });
        
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
        
        // Función para ocultar el loader
        function hideLoader() {
            // Añadir una pequeña demora para asegurar que todo se ve correctamente
            setTimeout(function() {
                // Primero aplicar clase de transición
                loader.classList.add('fade-out');
                
                // Después de la transición, marcar como cargado
                setTimeout(function() {
                    document.body.classList.add('loaded');
                }, 300);
            }, 500); // 500ms de demora mínima para mostrar el loader
        }
        
        // Comprobar si los CSS están cargados
        var cssCheckInterval = setInterval(function() {
            if (checkCSSLoaded()) {
                clearInterval(cssCheckInterval);
                hideLoader();
            }
        }, 50);
        
        // Por si acaso, ocultar el loader después de 2.5 segundos máximo
        setTimeout(function() {
            clearInterval(cssCheckInterval);
            hideLoader();
        }, 2500);
    });
})();