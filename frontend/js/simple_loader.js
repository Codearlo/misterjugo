alert('Script cargando');

// simple-loader.js - Versión simplificada para MisterJugo
document.addEventListener('DOMContentLoaded', function() {
    // 1. Crear el loader en el DOM
    var loader = document.createElement('div');
    loader.id = 'page-loader';
    loader.innerHTML = `
        <div class="loader-content">
            <img src="/images/logo_mrjugo.png" alt="Logo MisterJugo" class="loader-logo">
            <div class="loader-spinner"></div>
            <p class="loader-message">¡Preparando lo mejor para ti!</p>
        </div>
    `;
    document.body.insertBefore(loader, document.body.firstChild);
    
    // 2. Aplicar estilos al loader
    var style = document.createElement('style');
    style.textContent = `
        #page-loader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: #FF7A00;
            background-image: linear-gradient(135deg, #FFA649, #FF7A00);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }
        
        .loader-content {
            background-color: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            text-align: center;
            max-width: 280px;
        }
        
        .loader-logo {
            width: 80px;
            height: auto;
            margin-bottom: 15px;
        }
        
        .loader-spinner {
            border: 4px solid #F5F5F5;
            border-top: 4px solid #FF7A00;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
            margin: 15px auto;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .loader-message {
            color: #2D2A26;
            font-weight: 500;
            font-size: 1.1rem;
            margin: 10px 0 0 0;
        }
    `;
    document.head.appendChild(style);
    
    // 3. Forzar recarga de CSS añadiendo versiones
    var styleSheets = document.querySelectorAll('link[rel="stylesheet"]');
    styleSheets.forEach(function(sheet) {
        var href = sheet.getAttribute('href');
        if (href && !href.includes('cdnjs.cloudflare.com')) {
            var newHref = href + (href.includes('?') ? '&' : '?') + 'v=' + new Date().getTime();
            sheet.setAttribute('href', newHref);
        }
    });
    
    // 4. Quitar el loader después de que todo esté cargado
    function removeLoader() {
        var loader = document.getElementById('page-loader');
        if (loader) {
            // Primero hacer una transición suave
            loader.style.transition = 'opacity 0.5s ease';
            loader.style.opacity = '0';
            
            // Luego eliminar
            setTimeout(function() {
                if (loader && loader.parentNode) {
                    loader.parentNode.removeChild(loader);
                }
            }, 500);
        }
    }
    
    // 5. Quitar el loader después de que todo esté listo o pasado un máximo de tiempo
    window.addEventListener('load', function() {
        // Dar 500ms adicionales después de 'load' para permitir renderizado completo
        setTimeout(removeLoader, 500);
    });
    
    // 6. Failsafe - quitar el loader después de 3 segundos como máximo
    setTimeout(removeLoader, 3000);
});