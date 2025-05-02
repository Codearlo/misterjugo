// orange-loader-template.js - Plantilla de loader con fondo naranja
document.addEventListener('DOMContentLoaded', function() {
    // 1. Crear el estilo del loader
    var style = document.createElement('style');
    style.textContent = `
        /* Ocultar el contenido de la página temporalmente */
        body {
            opacity: 0;
            transition: opacity 0.5s ease;
        }
        
        body.content-loaded {
            opacity: 1;
        }
        
        #mrjugo-loader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 9999;
            display: flex;
            justify-content: center;
            align-items: center;
            background: linear-gradient(135deg, #FF9933, #FF7A00);
            transition: opacity 0.5s ease;
        }
        
        #mrjugo-loader.fade-out {
            opacity: 0;
        }
        
        .loader-container {
            text-align: center;
        }
        
        .spinner {
            border: 5px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top: 5px solid #fff;
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
            color: white;
            font-weight: 600;
            font-size: 18px;
        }
    `;
    document.head.appendChild(style);
    
    // 2. Crear el loader
    var loader = document.createElement('div');
    loader.id = 'mrjugo-loader';
    loader.innerHTML = `
        <div class="loader-container">
            <div class="spinner"></div>
            <div class="loader-text">Cargando MisterJugo...</div>
        </div>
    `;
    document.body.appendChild(loader);
    
    // 3. Forzar recarga de CSS
    var styleSheets = document.querySelectorAll('link[rel="stylesheet"]');
    styleSheets.forEach(function(sheet) {
        var href = sheet.getAttribute('href');
        if (href && !href.includes('cdnjs.cloudflare.com')) {
            if (!href.includes('?v=') && !href.includes('&v=')) {
                var newHref = href + (href.includes('?') ? '&' : '?') + 'v=' + new Date().getTime();
                sheet.setAttribute('href', newHref);
            }
        }
    });
    
    // 4. Ocultar el loader cuando todo esté cargado
    function hideLoader() {
        var loader = document.getElementById('mrjugo-loader');
        if (loader) {
            loader.classList.add('fade-out');
            setTimeout(function() {
                document.body.classList.add('content-loaded');
                setTimeout(function() {
                    if (loader.parentNode) {
                        loader.parentNode.removeChild(loader);
                    }
                }, 500);
            }, 300);
        }
    }
    
    // 5. Ocultar el loader después de cargar la página o tras tiempo máximo
    window.addEventListener('load', function() {
        // Esperar un poco más después de cargar para asegurar renderizado
        setTimeout(hideLoader, 300);
    });
    
    // Tiempo máximo para mostrar el loader (3 segundos)
    setTimeout(hideLoader, 3000);
});