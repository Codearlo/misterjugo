// loader.js - Pantalla de carga para MisterJugo
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
            background: linear-gradient(135deg, #FFA649, #FF7A00);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            transition: opacity 0.3s ease;
            font-family: 'Poppins', sans-serif;
        }
        
        #page-loader.fade-out {
            opacity: 0;
        }
        
        .loader-content {
            text-align: center;
            padding: 30px;
            background-color: rgba(255, 255, 255, 0.95);
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            max-width: 300px;
            width: 90%;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.03); }
            100% { transform: scale(1); }
        }
        
        .loader-logo {
            width: 80px;
            height: auto;
            margin-bottom: 15px;
            filter: drop-shadow(0 2px 5px rgba(0, 0, 0, 0.2));
        }
        
        .loader-spinner {
            border: 4px solid #F5F5F5;
            border-top: 4px solid #FF7A00;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
            margin: 15px auto;
            box-shadow: 0 4px 10px rgba(255, 122, 0, 0.2);
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .loader-message {
            color: #2D2A26;
            font-weight: 500;
            font-size: 1.1rem;
            margin-bottom: 8px;
        }
        
        .loader-submessage {
            color: #5F5B57;
            font-size: 0.9rem;
        }
        
        /* Ocultar temporalmente el contenido de la página */
        body {
            opacity: 0;
        }
        
        body.content-loaded {
            opacity: 1;
            transition: opacity 0.5s ease;
        }
        
        body.content-loaded #page-loader {
            display: none;
        }
    `;
    document.head.appendChild(style);
    
    // Crear el elemento del loader
    var loader = document.createElement('div');
    loader.id = 'page-loader';
    loader.innerHTML = `
        <div class="loader-content">
            <img src="/images/logo_mrjugo.png" alt="Logo MisterJugo" class="loader-logo">
            <div class="loader-spinner"></div>
            <p class="loader-message">¡Preparando lo mejor para ti!</p>
            <p class="loader-submessage">Cargando jugos naturales...</p>
        </div>
    `;
    
    // Función para insertar el loader
    function insertLoader() {
        document.body.insertBefore(loader, document.body.firstChild);
        
        // Forzar recarga de CSS con versión dinámica
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
                    document.body.classList.add('content-loaded');
                }, 300);
            }, 800); // Tiempo mínimo para mostrar el loader
        }
        
        // Comprobar si los CSS están cargados
        var cssCheckInterval = setInterval(function() {
            if (checkCSSLoaded()) {
                clearInterval(cssCheckInterval);
                hideLoader();
            }
        }, 50);
        
        // Por si acaso, ocultar el loader después de 3 segundos máximo
        setTimeout(function() {
            clearInterval(cssCheckInterval);
            hideLoader();
        }, 3000);
        
        // También escuchar el evento load para eliminar el loader
        window.addEventListener('load', function() {
            clearInterval(cssCheckInterval);
            setTimeout(hideLoader, 500); // Pequeña demora después de load
        });
    }
    
    // Si el DOM ya está listo, insertar ahora
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', insertLoader);
    } else {
        insertLoader();
    }
})();