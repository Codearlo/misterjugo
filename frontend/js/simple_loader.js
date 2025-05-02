// fixed_misterjugo_loader.js - Loader simplificado para MisterJugo con logo y barra funcional
(function() {
    // Ruta del logo - Usando exactamente la misma ruta que usas en tu login
    var logoPath = "images/logo_mrjugo.png";
    
    // Colores
    var bgColor = '#ffaa55';  // Naranja de fondo
    var barColor = '#e67300'; // Naranja oscuro para la barra
    
    // Insertar los estilos básicos directamente
    function createStyles() {
        var style = document.createElement('style');
        style.textContent = `
            #mjloader-overlay {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: ${bgColor};
                z-index: 9999;
                display: flex;
                justify-content: center;
                align-items: center;
                transition: opacity 0.5s ease;
            }
            
            .mjloader-container {
                text-align: center;
                width: 300px;
            }
            
            .mjloader-logo {
                margin-bottom: 20px;
            }
            
            .mjloader-logo img {
                max-width: 120px;
                height: auto;
            }
            
            .mjloader-title {
                color: white;
                font-size: 28px;
                font-weight: bold;
                margin: 15px 0;
                font-family: Arial, sans-serif;
            }
            
            .mjloader-subtitle {
                color: white;
                font-size: 16px;
                margin: 10px 0 25px;
                font-family: Arial, sans-serif;
            }
            
            .mjloader-bar-container {
                width: 100%;
                height: 10px;
                background-color: rgba(255, 255, 255, 0.3);
                border-radius: 5px;
                overflow: hidden;
                margin-bottom: 15px;
            }
            
            .mjloader-bar {
                width: 0%;
                height: 100%;
                background-color: ${barColor};
                transition: width 0.2s ease-out;
            }
            
            .mjloader-text {
                color: white;
                font-size: 16px;
                font-weight: bold;
                text-transform: uppercase;
                font-family: Arial, sans-serif;
                letter-spacing: 1px;
            }
            
            body > *:not(#mjloader-overlay) {
                opacity: 0;
                transition: opacity 0.4s ease;
            }
            
            body.mjloader-done > *:not(#mjloader-overlay) {
                opacity: 1;
            }
            
            #mjloader-overlay.fade-out {
                opacity: 0;
            }
            
            body.mjloader-done #mjloader-overlay {
                display: none;
            }
        `;
        document.head.appendChild(style);
    }
    
    // Crear el HTML del loader
    function createLoader() {
        var overlay = document.createElement('div');
        overlay.id = 'mjloader-overlay';
        
        overlay.innerHTML = `
            <div class="mjloader-container">
                <div class="mjloader-logo">
                    <img src="${logoPath}" alt="MisterJugo Logo">
                </div>
                <div class="mjloader-title">MisterJugo</div>
                <div class="mjloader-subtitle">Preparando todo para ti...</div>
                <div class="mjloader-bar-container">
                    <div class="mjloader-bar" id="mjloader-progress"></div>
                </div>
                <div class="mjloader-text">CARGANDO...</div>
            </div>
        `;
        
        return overlay;
    }
    
    // Función para actualizar la barra de progreso
    function updateProgress(percentage) {
        var bar = document.getElementById('mjloader-progress');
        if (bar) {
            bar.style.width = percentage + '%';
        }
    }
    
    // Función para simular el progreso
    function startProgress() {
        var progress = 0;
        var interval = setInterval(function() {
            progress += Math.random() * 3 + 1;
            
            if (progress >= 100) {
                clearInterval(interval);
                progress = 100;
                
                // Cuando llega al 100%, ocultamos el loader
                setTimeout(hideLoader, 500);
            }
            
            updateProgress(progress);
        }, 200);
        
        // Máximo tiempo de espera
        setTimeout(function() {
            clearInterval(interval);
            updateProgress(100);
            setTimeout(hideLoader, 500);
        }, 5000);
    }
    
    // Ocultar el loader
    function hideLoader() {
        var overlay = document.getElementById('mjloader-overlay');
        if (overlay) {
            overlay.classList.add('fade-out');
            
            setTimeout(function() {
                document.body.classList.add('mjloader-done');
            }, 500);
        }
    }
    
    // Iniciar todo
    function init() {
        // 1. Crear los estilos
        createStyles();
        
        // 2. Esperar a que el DOM esté listo
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function() {
                var loader = createLoader();
                document.body.appendChild(loader);
                startProgress();
            });
        } else {
            var loader = createLoader();
            document.body.appendChild(loader);
            startProgress();
        }
    }
    
    // Ejecutar
    init();
})();