/**
 * Función para manejar el autocompletado de distritos de Ica
 * Archivo: direcciones.js
 * Restaurante MisterJugo
 */

document.addEventListener('DOMContentLoaded', function() {
    // Lista de distritos de Ica
    const distritos = [
        "Ica",
        "La Tinguiña",
        "Los Aquijes",
        "Ocucaje",
        "Pachacútec",
        "Parcona",
        "Pueblo Nuevo",
        "Salas",
        "San José de los Molinos",
        "San Juan Bautista",
        "Santiago",
        "Subtanjalla",
        "Tate",
        "Yauca del Rosario"
    ];
    
    // Función de autocompletado
    function autocomplete(inp, arr) {
        let currentFocus;
        
        // Función que se ejecuta cuando el usuario escribe en el campo
        inp.addEventListener("input", function(e) {
            let a, b, i, val = this.value;
            
            // Cerrar cualquier lista de autocompletado abierta
            closeAllLists();
            
            if (!val) { return false; }
            currentFocus = -1;
            
            // Crear elemento DIV que contendrá los items
            a = document.createElement("DIV");
            a.setAttribute("id", this.id + "autocomplete-list");
            a.setAttribute("class", "autocomplete-items");
            
            // Añadir DIV como hijo del contenedor del autocompletado
            this.parentNode.appendChild(a);
            
            // Para cada elemento en el array...
            for (i = 0; i < arr.length; i++) {
                // Verificar si el elemento comienza con las mismas letras que el valor del input
                if (arr[i].toLowerCase().includes(val.toLowerCase())) {
                    // Crear un elemento DIV para cada elemento coincidente
                    b = document.createElement("DIV");
                    
                    // Resaltar las letras coincidentes en negrita
                    const index = arr[i].toLowerCase().indexOf(val.toLowerCase());
                    b.innerHTML = arr[i].substr(0, index);
                    b.innerHTML += "<strong>" + arr[i].substr(index, val.length) + "</strong>";
                    b.innerHTML += arr[i].substr(index + val.length);
                    
                    // Agregar un input oculto con el valor
                    b.innerHTML += "<input type='hidden' value='" + arr[i] + "'>";
                    
                    // Ejecutar cuando el usuario hace clic en un elemento
                    b.addEventListener("click", function(e) {
                        // Insertar el valor del elemento en el campo de entrada
                        inp.value = this.getElementsByTagName("input")[0].value;
                        
                        // Cerrar la lista de autocompletado
                        closeAllLists();
                    });
                    a.appendChild(b);
                }
            }
        });
        
        // Ejecutar una función cuando el usuario presiona una tecla en el teclado
        inp.addEventListener("keydown", function(e) {
            let x = document.getElementById(this.id + "autocomplete-list");
            if (x) x = x.getElementsByTagName("div");
            
            if (e.keyCode == 40) {
                // Si se presiona la tecla de flecha hacia abajo, incrementar el currentFocus
                currentFocus++;
                addActive(x);
            } else if (e.keyCode == 38) {
                // Si se presiona la tecla de flecha hacia arriba, decrementar el currentFocus
                currentFocus--;
                addActive(x);
            } else if (e.keyCode == 13) {
                // Si se presiona la tecla enter, evitar que el formulario se envíe
                e.preventDefault();
                if (currentFocus > -1) {
                    // Simular un clic en el elemento activo
                    if (x) x[currentFocus].click();
                }
            }
        });
        
        // Clasificar un elemento como "activo"
        function addActive(x) {
            if (!x) return false;
            
            // Eliminar la clase "autocomplete-active" de todos los elementos
            removeActive(x);
            if (currentFocus >= x.length) currentFocus = 0;
            if (currentFocus < 0) currentFocus = (x.length - 1);
            
            // Añadir clase "autocomplete-active"
            x[currentFocus].classList.add("autocomplete-active");
        }
        
        // Eliminar la clase "activo" de todos los elementos de autocompletado
        function removeActive(x) {
            for (let i = 0; i < x.length; i++) {
                x[i].classList.remove("autocomplete-active");
            }
        }
        
        // Cerrar todas las listas de autocompletado excepto la pasada como argumento
        function closeAllLists(elmnt) {
            const x = document.getElementsByClassName("autocomplete-items");
            for (let i = 0; i < x.length; i++) {
                if (elmnt != x[i] && elmnt != inp) {
                    x[i].parentNode.removeChild(x[i]);
                }
            }
        }
        
        // Cerrar las listas de autocompletado cuando el usuario hace clic en el documento
        document.addEventListener("click", function (e) {
            closeAllLists(e.target);
        });
    }
    
    // Inicializar autocompletado para el campo de distrito
    const distritoInput = document.getElementById("ciudad");
    if (distritoInput) {
        // Cambiar atributos para el campo de distrito
        distritoInput.setAttribute("placeholder", "Seleccione distrito");
        distritoInput.value = "Ica"; // Valor predeterminado
        
        // Asegurarse de que el padre tiene la clase de autocompletado
        distritoInput.parentNode.classList.add("autocomplete");
        
        // Iniciar autocompletado
        autocomplete(distritoInput, distritos);
        
        // Actualizar etiqueta
        const distritoLabel = document.querySelector('label[for="ciudad"]');
        if (distritoLabel) {
            distritoLabel.textContent = "Distrito";
        }
    }
    
    // Cambiar etiqueta de estado a provincia
    const estadoLabel = document.querySelector('label[for="estado"]');
    if (estadoLabel) {
        estadoLabel.textContent = "Provincia";
    }
    
    // Establecer valor predeterminado para provincia de Ica
    const provinciaInput = document.getElementById("estado");
    if (provinciaInput) {
        provinciaInput.setAttribute("placeholder", "Provincia");
        provinciaInput.value = "Ica"; // Valor predeterminado
    }
});