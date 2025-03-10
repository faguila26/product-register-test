document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById('productoForm');
    const codigoInput = document.getElementById('codigo');
    const codigoError = document.getElementById('codigo-error');
    const nombreInput = document.getElementById('nombre');
    const nombreError = document.getElementById('nombre-error');
    const precioError = document.getElementById('precio-error');
    const bodegaSelect = document.getElementById("bodega");
    const bodegaError = document.getElementById("bodega-error");
    const sucursalSelect = document.getElementById("sucursal");
    const sucursalError = document.getElementById("sucursal-error");
    const monedaSelect = document.getElementById("moneda");
    const monedaError = document.getElementById("moneda-error");
    const precioInput = document.getElementById('precio');
    const materialSelect = document.getElementById('materialSelect');
    const materialError = document.getElementById('material-error');
    const descripcionInput = document.getElementById('descripcion');
    const descripcionError = document.getElementById('descripcion-error');
    
    const materialCheckboxes = document.querySelectorAll('input[name="material[]"]');

    // Función para cargar bodegas, sucursales y monedas usando AJAX (XMLHttpRequest)
    function cargarOpciones() {
        // Función común para manejar la carga de las opciones
        function cargarDatos(url, selector) {
            const xhr = new XMLHttpRequest();
            xhr.open('GET', url, true);
            xhr.onload = function () {
                if (xhr.status === 200) {
                    try {
                        const data = JSON.parse(xhr.responseText);
                        if (data.status === 'success' && Array.isArray(data.data)) {
                            const selectElement = document.querySelector(selector);
                            data.data.forEach(item => {
                                const option = document.createElement('option');
                                option.value = item.id;
                                option.textContent = item.nombre;
                                selectElement.appendChild(option);
                            });
                        } else {
                            console.error('Error en la respuesta:', data.message);
                        }
                    } catch (error) {
                        console.error('Error al procesar la respuesta JSON:', error);
                    }
                } else {
                    console.error(`Error al cargar datos desde ${url}:`, xhr.status);
                }
            };
            xhr.onerror = function () {
                console.error('Error de red al cargar datos desde', url);
            };
            xhr.send();
        }

        cargarDatos('http://localhost/product-register-test/register.php?type=bodegas', '#bodega');
        cargarDatos('http://localhost/product-register-test/register.php?type=sucursales', '#sucursal');
        cargarDatos('http://localhost/product-register-test/register.php?type=monedas', '#moneda');
    }

    // Función para cargar materiales
    function cargarMateriales() {
        const materialContainer = document.getElementById('material-container');
        if (!materialContainer) {
            console.error('Error: No se encontró el contenedor "material-container".');
            return;
        }

        // Aseguramos que el contenedor tenga la clase "checkbox-container" una sola vez
        materialContainer.classList.add('checkbox-container'); 

        const xhrMateriales = new XMLHttpRequest();
        xhrMateriales.open('GET', 'http://localhost/product-register-test/register.php?type=materiales', true);

        xhrMateriales.onload = function () {
            if (xhrMateriales.status === 200) {
                try {
                    const materiales = JSON.parse(xhrMateriales.responseText);

                    if (materiales.status === 'success' && Array.isArray(materiales.data)) {
                        materialContainer.innerHTML = '';  // Limpiar el contenedor antes de agregar nuevos checkboxes

                        materiales.data.forEach(material => {
                            if (material.id && material.nombre) {
                                const checkboxWrapper = document.createElement('div');
                                checkboxWrapper.classList.add('checkbox-wrapper'); // Aquí se asigna la clase a cada checkbox

                                const checkbox = document.createElement('input');
                                checkbox.type = 'checkbox';
                                checkbox.id = `material-${material.id}`;
                                checkbox.name = 'material[]';
                                checkbox.value = material.id;

                                const label = document.createElement('label');
                                label.setAttribute('for', checkbox.id);
                                label.textContent = material.nombre;

                                checkboxWrapper.appendChild(checkbox);
                                checkboxWrapper.appendChild(label);
                                materialContainer.appendChild(checkboxWrapper);
                            }
                        });
                    } else {
                        console.error('Error en la respuesta del servidor:', materiales.message || 'Datos inválidos.');
                    }
                } catch (error) {
                    console.error('Error al procesar los datos JSON:', error);
                }
            } else {
                console.error('Error al cargar materiales:', xhrMateriales.status);
            }
        };

        xhrMateriales.send(); 
    }

    // Llamar las funciones para cargar las opciones y materiales al cargar la página
    cargarOpciones();
    cargarMateriales();

    // Validación del formulario antes de enviarlo
    form.addEventListener('submit', async function (e) {
        e.preventDefault(); // Evitar el envío por defecto

        // Limpiar errores anteriores
        codigoInput.style.borderColor = '';
        codigoError.innerHTML = "";
        codigoError.style.display = 'none';

        nombreError.style.display = 'none';
        nombreError.textContent = '';

        precioError.style.display = 'none';
        precioError.textContent = '';

        sucursalSelect.style.borderColor = '';
        sucursalError.style.display = 'none';
        
        bodegaSelect.style.borderColor = '';
        bodegaError.style.display = 'none';
        
        monedaSelect.style.borderColor = '';
        const descripcion = descripcionInput.value.trim();
        descripcionInput.style.borderColor = '';
        descripcionError.style.display = 'none';
        
        descripcionError.textContent = '';

        const codigo = codigoInput.value.trim();
        console.log("Código:", codigo); // Verificar el valor del código

        const nombre = nombreInput.value.trim();
        console.log("Nombre :", nombre);

        const bodega = bodegaSelect.value;
        const sucursal = sucursalSelect.value;
        const moneda = monedaSelect.value;
        const precio = precioInput.value.trim();
        console.log("Código:", precio);

        let isValid = true;

        // Verificar si el código ya existe
        const verificarCodigoExistente = async (codigo) => {
            const response = await fetch(`/product-register-test/register.php?check_codigo=${codigo}`);
            const data = await response.json();
            if (data.exists) {
                codigoError.textContent = "El código del producto ya está registrado.";
                codigoError.style.color = "red";
                codigoError.style.display = "block";
                return false;
            }
            return true;
        };

        // Validación de los campos
        if (!codigo || !nombre || !bodega || !sucursal || !moneda || precio <= 0 || isNaN(precio)) {
            alert("Por favor, complete todos los campos correctamente.");
            return;
        }
        
        // Validación de formato: debe contener al menos una letra y un número
        const codigoRegex = /^(?=.*[A-Za-z])(?=.*\d)[A-Za-z0-9]+$/;
        if (!codigoRegex.test(codigo)) {
            mostrarError(codigoError, "El código debe contener al menos una letra y un número.");
            isValid = false;
        }

        // Validación de longitud: entre 5 y 15 caracteres
        if (codigo.length < 5 || codigo.length > 15) {
            mostrarError(codigoError, "El código debe tener entre 5 y 15 caracteres.");
            isValid = false;
        }

        // Verificar longitud del nombre
        if (nombre.length < 2 || nombre.length > 50) {
            mostrarError(nombreError, "El nombre debe tener entre 2 y 50 caracteres.");
            isValid = false;
        }

        const precioRegex = /^(?!0\d)\d{1,7}(\.\d{1,2})?$/;
        if (!precioRegex.test(precio) || parseFloat(precio) <= 0) {
            mostrarError(precioError, "El precio debe ser un número positivo con hasta dos decimales (por ejemplo, 19.99 o 1500.35).");
            isValid = false;
        }


        // Validación de longitud para la descripción
        if (descripcion.length < 10 || descripcion.length > 1000) {
            mostrarError(descripcionError, "La descripción debe tener entre 10 y 1000 caracteres.");
            isValid = false; // Evita el envío si la validación falla
        }
        // Si hay errores, detener el envío
        if (!isValid) return;
        
        // Verificar que al menos 2 materiales estén seleccionados
        const materials = document.querySelectorAll('input[name="material[]"]:checked');
        if (materials.length < 2) {
            alert("Debe seleccionar al menos 2 materiales.");
            return;
        }

        // Captura y muestra los materiales seleccionados en la consola
        const selectedMaterials = Array.from(materials).map(material => material.value);
        console.log("Materiales seleccionados:", selectedMaterials);

        // Crear el FormData con los datos del formulario
        const formData = new FormData(form);

        // Agregar los materiales seleccionados al FormData
        selectedMaterials.forEach(materialId => {
            formData.append('material[]', materialId);
        });

        // Validar la existencia en la base de datos antes de continuar
        const codigoDisponible = await verificarCodigoExistente(codigo);
        if (!codigoDisponible) return; // Si el código ya existe, detener el envío

        // Enviar la solicitud POST al servidor
        fetch('/product-register-test/register.php', {
            method: 'POST',
            body: formData,
        })
        .then(response => response.json())
        .then(data => {
            // Si la respuesta del servidor es exitosa
            if (data.status === 'success') {
                alert('Producto registrado correctamente.');
                // Limpiar todos los campos del formulario
                    form.reset();

                    // Limpiar los checkboxes manualmente (ya que form.reset() no los desmarca)
                    materialCheckboxes.forEach(checkbox => checkbox.checked = false);

                    // Opcional: Restablecer selectores a la primera opción
                    bodegaSelect.selectedIndex = 0;
                    sucursalSelect.selectedIndex = 0;
                    monedaSelect.selectedIndex = 0;
            } else {
                alert('Error al registrar el producto. Intente nuevamente.');
            }
        })
        .catch(error => {
            console.error('Error en la solicitud:', error);
            alert('Hubo un error al registrar el producto. Intente nuevamente.');
        });
    });

    // Función para mostrar mensajes de error
    function mostrarError(elemento, mensaje) {
        elemento.innerHTML += `<p style="color: red; margin: 5px 0;">${mensaje}</p>`;
        elemento.style.display = 'block';
        codigoInput.style.borderColor = 'red';
    }
});
