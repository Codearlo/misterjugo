// simple_colored_loader.js - Versión simplificada con colores garantizados
(function() {
    // Los colores y configuración directamente en variables para más claridad
    var loaderBackgroundColor = '#ffffff';  // Color de fondo
    var progressBarColor = '#009688';       // Color de la barra de progreso (verde azulado)
    var progressBgColor = '#f3f3f3';        // Color de fondo de la barra
    var textColor = '#333333';              // Color del texto
    var loadingText = 'Cargando...';        // Texto que se muestra
    var maxWaitTime = 3000;                 // Tiempo máximo de espera

    // Crear los elementos directamente sin complicaciones
    function createAndInsertLoader() {
        // 1. Crear el elemento de estilo primero y añadirlo
        var styleEl = document.createElement('style');
        styleEl.textContent = `
            #my-page-loader {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: ${loaderBackgroundColor} !important;
                display: flex;
                justify-content: center;
                align-items: center;
                z-index: 9999;
                transition: opacity 0.5s ease;
            }
            
            #my-page-loader.fade-out {
                opacity: 0;
            }
            
            .my-loader-content {
                text-align: center;
                padding: 25px;
            }
            
            .my-progress-container {
                background-color: ${progressBgColor} !important;
                width: 250px;
                height: 8px;
                border-radius: 4px;
                overflow: hidden;
                margin: 0 auto 20px auto;
            }
            
            .my-progress-bar {
                height: 100%;
                width: 0%;
                background-color: ${progressBarColor} !important;
                transition: width 0.3s ease-in-out;
            }
            
            .my-loader-text {
                color: ${textColor} !important;
                font-family: Arial, sans-serif;
                font-size: 16px;
                margin: 10px 0;
            }
            
            body > *:not(#my-page-loader) {
                opacity: 0;
                transition: opacity 0.5s ease;
            }
            
            body.my-loaded > *:not(#my-page-loader) {
                opacity: 1;
            }
            
            body.my-loaded #my-page-loader {
                display: none;
            }
        `;
        document.head.appendChild(styleEl);
        
        // 2. Crear el elemento loader y añadirlo
        var loaderEl = document.createElement('div');
        loaderEl.id = 'my-page-loader';
        loaderEl.innerHTML = `
            <div class="my-loader-content">
                <div class="my-progress-container">
                    <div class="my-progress-bar" id="my-progress-bar"></div>
                </div>
                <p class="my-loader-text">${loadingText}</p>
            </div>
        `;
        
        // 3. Añadirlo al body tan pronto como podamos
        if (document.body) {
            document.body.insertBefore(loaderEl, document.body.firstChild);
            startLoader();
        } else {
            // Si aún no hay body, esperar a que exista
            document.addEventListener('DOMContentLoaded', function() {
                document.body.insertBefore(loaderEl, document.body.firstChild);
                startLoader();
            });
        }
    }
    
    // Actualizar el progreso de la barra
    function updateProgress(percent) {
        var progressBar = document.getElementById('my-progress-bar');
        if (progressBar) {
            progressBar.style.width = percent + '%';
        }
    }
    
    // Funcionamiento de la simulación y ocultación
    function startLoader() {
        // Simular progreso
        var progress = 0;
        var progressInterval = setInterval(function() {
            progress += Math.random() * 3;
            if (progress > 90) {
                clearInterval(progressInterval);
                progress = 90;
            }
            updateProgress(progress);
        }, 150);
        
        // Comprobar CSS y ocultar cuando todo esté listo
        var checkInterval = setInterval(function() {
            if (checkIfCSSLoaded()) {
                clearInterval(checkInterval);
                clearInterval(progressInterval);
                updateProgress(100);
                
                setTimeout(function() {
                    hideLoader();
                }, 500);
            }
        }, 100);
        
        // Establecer tiempo máximo
        setTimeout(function() {
            clearInterval(checkInterval);
            clearInterval(progressInterval);
            updateProgress(100);
            hideLoader();
        }, maxWaitTime);
    }
    
    // Revisar si los CSS se han cargado
    function checkIfCSSLoaded() {
        var allLoaded = true;
        var sheets = document.styleSheets;
        
        for (var i = 0; i < sheets.length; i++) {
            try {
                var rules = sheets[i].cssRules || sheets[i].rules;
                if (!rules || rules.length === 0) {
                    allLoaded = false;
                }
            } catch(e) {
                // Error típico de CORS, ignoramos
            }
        }
        
        return allLoaded;
    }
    
    // Ocultar el loader de forma suave
    function hideLoader() {
        var loader = document.getElementById('my-page-loader');
        if (loader) {
            loader.classList.add('fade-out');
            setTimeout(function() {
                document.body.classList.add('my-loaded');
            }, 500);
        }
    }
    
    // Asegurarnos de que los estilos se apliquen de inmediato
    createAndInsertLoader();
})();