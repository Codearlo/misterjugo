// cache_control.js - Solución para forzar recarga de CSS
(function() {
    // Ejecutar cuando el DOM esté listo
    document.addEventListener('DOMContentLoaded', function() {
        // Buscar todas las hojas de estilo
        var styleSheets = document.querySelectorAll('link[rel="stylesheet"]');
        
        // Añadir parámetro de versión a cada hoja de estilo
        styleSheets.forEach(function(sheet) {
            var href = sheet.getAttribute('href');
            // No modificar CDNs externas para evitar problemas
            if (href && !href.includes('cdnjs.cloudflare.com')) {
                // Evitar duplicar parámetros de versión
                if (!href.includes('?v=') && !href.includes('&v=')) {
                    // Añadir parámetro con timestamp único
                    var newHref = href + (href.includes('?') ? '&' : '?') + 'v=' + Math.floor(Date.now() / 1000);
                    sheet.setAttribute('href', newHref);
                }
            }
        });
    });
})();