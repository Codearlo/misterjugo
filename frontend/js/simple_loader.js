// fast_misterjugo_loader.js - Loader optimizado para MisterJugo con carga rápida
(function() {
    // Ruta del logo
    var logoPath = "images/logo_mrjugo.png";
    
    // Colores
    var bgColor = '#ffaa55';  // Naranja de fondo
    var barColor = '#e67300'; // Naranja oscuro para la barra
    
    // Tiempos (en milisegundos)
    var maxWaitTime = 2000;   // Tiempo máximo que esperará antes de ocultar (2 segundos)
    var barSpeed = 80;        // Velocidad de la barra (menor = más rápido)
    
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
                transition: opacity 0.3s ease;
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
                transition: width 0.1s linear;
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
                transition: opacity 0.3s ease;
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
    
    // Función para simular el progreso más rápido
    function startProgress() {
        var progress = 0;
        var increment = 5; // Incremento más grande para avanzar más rápido
        
        var interval = setInterval(function() {
            progress += increment;
            
            // Aumentar la velocidad de avance
            if (progress < 50) {
                increment = 5 + Math.random() * 3; // 5-8%
            } else if (progress < 85) {
                increment = 3 + Math.random() * 2; // 3-5%
            } else {
                increment = 2 + Math.random() * 1; // 2-3%
            }
            
            if (progress >= 100) {
                clearInterval(interval);
                progress = 100;
                hideLoader();
            }
            
            updateProgress(progress);
        }, barSpeed);
        
        // Tiempo máximo de espera reducido
        setTimeout(function() {
            clearInterval(interval);
            updateProgress(100);
            hideLoader();
        }, maxWaitTime);
    }
    
    // Ocultar el loader
    function hideLoader() {
        var overlay = document.getElementById('mjloader-overlay');
        if (overlay) {
            overlay.classList.add('fade-out');
            
            setTimeout(function() {
                document.body.classList.add('mjloader-done');
            }, 300); // Transición más rápida
        }
    }
    
    // Precargar el logo
    function preloadLogo(callback) {
        var img = new Image();
        img.onload = callback;
        img.onerror = callback; // Continuar incluso si falla la carga del logo
        img.src = logoPath;
    }
    
    // Iniciar todo
    function init() {
        // 1. Crear los estilos
        createStyles();
        
        // 2. Crear y mostrar el loader
        var loader = createLoader();
        
        // 3. Añadir el loader al body
        if (document.body) {
            document.body.appendChild(loader);
            // 4. Iniciar la barra de progreso rápida
            startProgress();
        } else {
            // Si no hay body aún, esperar
            document.addEventListener('DOMContentLoaded', function() {
                document.body.appendChild(loader);
                startProgress();
            });
        }
    }
    
    // Ejecutar inmediatamente
    init();
})();