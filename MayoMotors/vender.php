<?php
require_once 'layout.php';

// Verificar si el usuario está logueado
if (!estaLogueado()) {
    mostrarAlerta('Debes iniciar sesión para vender un coche', 'danger');
    redirigir('login.php');
}

// Obtener marcas para el formulario
$marcas_sql = "SELECT id_marca, nombre FROM Marcas ORDER BY nombre";
$marcas_result = $conn->query($marcas_sql);

// Obtener provincias para el formulario
$provincias_sql = "SELECT id_provincia, nombre FROM Provincias ORDER BY nombre";
$provincias_result = $conn->query($provincias_sql);

// Arrays para los selects
$anos = range(date('Y'), date('Y') - 30);
$combustibles = ['Gasolina', 'Diesel', 'Híbrido', 'Eléctrico', 'GLP', 'Gas Natural'];
$cambios = ['Manual', 'Automático'];
$colores = ['Blanco','Negro','Gris/Plata','Azul','Rojo','Verde','Violeta','Rosa','Beis','Marrón','Bronce','Dorado','Naranja','Amarillo','Granate','Otros'];
$tipos = ['Turismo', 'SUV', 'Deportivo', 'Familiar', 'Berlina', 'Compacto', 'Todoterreno', 'Coupe', 'Cabrio', 'Pickup', 'Furgoneta'];

// Procesar el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recoger datos del formulario
    $matricula = limpiarInput($_POST['matricula']);
    $precio = limpiarInput($_POST['precio']);
    $color = limpiarInput($_POST['color']);
    $cambio = limpiarInput($_POST['cambio']);
    $ano = limpiarInput($_POST['ano']);
    $combustible = limpiarInput($_POST['combustible']);
    $cv = limpiarInput($_POST['cv']);
    $id_provincia = limpiarInput($_POST['provincia']);
    $id_modelo = limpiarInput($_POST['modelo']);
    $descripcion = limpiarInput($_POST['descripcion']);
    $tipo = isset($_POST['tipo']) ? limpiarInput($_POST['tipo']) : 'Turismo';
    $kilometraje = limpiarInput($_POST['kilometraje']);

    // Validaciones
    $errores = [];
    
    // Validar la matrícula (formato nuevo y antiguo)
    if (empty($matricula)) {
        $errores[] = 'La matrícula es obligatoria';
    } else {
        // Limpiar la matrícula de espacios y guiones
        $matricula_limpia = strtoupper(str_replace(['-', ' '], '', $matricula));
        
        // Validar los dos formatos de matrícula
        $formato_nuevo = preg_match('/^[0-9]{4}[A-Z]{3}$/', $matricula_limpia);
        
        $formato_personalizado = preg_match('/^[A-Z]{1,2}[0-9]{4}[A-Z]{1,2}$/', $matricula_limpia);
        
        if (!$formato_nuevo && !$formato_personalizado) {
            $errores[] = 'La matrícula debe tener un formato válido. Ejemplos: 1234ABC o B1234XX o AB1234ZY';
        } else {
        
            $matricula = $matricula_limpia;
            
            // Verificar que la matrícula no exista en la base de datos
            $sql = "SELECT id_coche FROM Coches WHERE matricula = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $matricula);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $errores[] = 'Esta matrícula ya está registrada';
            }
        }
    }
    
    // Validar el precio
    if (empty($precio) || !is_numeric($precio) || $precio <= 0 || $precio > 999999) {
        $errores[] = 'El precio debe ser un número entre 1 y 999.999 €';
    }
    
    // Validar el año
    if (empty($ano) || !is_numeric($ano) || $ano < 1900 || $ano > date('Y')) {
        $errores[] = 'El año debe ser válido';
    }
    
   // Validar los CV
    if (empty($cv) || !is_numeric($cv) || $cv <= 0 || $cv > 5000) {
        $errores[] = 'Los CV deben ser un número entre 1 y 5000';
    }

    // Validar kilometraje
    if (!is_numeric($kilometraje) || $kilometraje < 0 || $kilometraje > 999999) {
        $errores[] = 'El kilometraje debe ser un número entre 0 y 999.999';
    }
    
    // Validar el modelo y la provincia
    if (empty($id_modelo)) {
        $errores[] = 'Debes seleccionar un modelo';
    }
    
    if (empty($id_provincia)) {
        $errores[] = 'Debes seleccionar una provincia';
    }
    
    // Gestión de imágenes
    $imagenes_urls = [];
    
    // Validar que se hayan subido al menos 3 imágenes
    if (!isset($_FILES['imagenes']) || empty($_FILES['imagenes']['name'][0])) {
        $errores[] = 'Debes subir al menos 3 imágenes del coche';
    } elseif (count($_FILES['imagenes']['name']) < 3) {
        $errores[] = 'Debes subir al menos 3 imágenes del coche. Has subido ' . count($_FILES['imagenes']['name']);
    } else {
        // Crear carpeta de imágenes si no existe
        $directorio_imagenes = 'img';
        if (!is_dir($directorio_imagenes)) {
            mkdir($directorio_imagenes, 0777, true);
        }
        
        // Procesar cada imagen
        $total_files = count($_FILES['imagenes']['name']);
        $max_files = 10; // Máximo de archivos permitidos
        $min_files = 3;  // Mínimo de archivos permitidos
        
        if ($total_files > $max_files) {
            $errores[] = "Solo puedes subir un máximo de $max_files imágenes";
        } elseif ($total_files < $min_files) {
            $errores[] = "Debes subir al menos $min_files imágenes del coche";
        } else {
            $valid_images_count = 0; // Contador de imágenes válidas
            
            for ($i = 0; $i < $total_files; $i++) {
                if ($_FILES['imagenes']['error'][$i] === UPLOAD_ERR_OK) {
                    $tmp_name = $_FILES['imagenes']['tmp_name'][$i];
                    $name = $_FILES['imagenes']['name'][$i];
                    $size = $_FILES['imagenes']['size'][$i];
                    $type = $_FILES['imagenes']['type'][$i];
                    
                    // Validar el tipo de archivo
                    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/jpg'];
                    if (!in_array($type, $allowed_types)) {
                        $errores[] = "El archivo $name no es una imagen válida. Solo se permiten JPG, PNG y GIF.";
                        continue;
                    }
                    
                    // Validar el tamaño (5MB máximo)
                    $max_size = 5 * 1024 * 1024; // 5MB
                    if ($size > $max_size) {
                        $errores[] = "La imagen $name excede el tamaño máximo permitido (5MB).";
                        continue;
                    }
                    
                    // Incrementar el contador de imágenes válidas
                    $valid_images_count++;
                    
                    // Crear un nombre único
                    $file_extension = pathinfo($name, PATHINFO_EXTENSION);
                    $new_filename = 'car_' . uniqid() . '.' . $file_extension;
                    $destination = $directorio_imagenes . '/' . $new_filename;
                    
                    // Mover el archivo
                    if (move_uploaded_file($tmp_name, $destination)) {
                        $imagenes_urls[] = $destination;
                    } else {
                        $errores[] = "Error al guardar la imagen $name.";
                    }
                } else {
                    $errores[] = "Error al subir el archivo #" . ($i + 1);
                }
            }
            
            // Verificar si tenemos suficientes imágenes válidas
            if ($valid_images_count < $min_files) {
                $errores[] = "Debes subir al menos $min_files imágenes válidas. Solo se han procesado $valid_images_count correctamente.";
            }
        }
    }
    
    // Si no hay errores, registrar el coche
    if (empty($errores)) {
        try {
            // Iniciar la transacción
            $conn->begin_transaction();
            
            // Generar un ID único
            $id_coche = 'C' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            $fecha_actual = date('Y-m-d');
            
            // Realizar la inserción 
            $sql = "INSERT INTO Coches (id_coche, matricula, precio, color, cambio, ano, combustible, cv, fecha, id_usuario, id_provincia, id_modelo, tipo, descripcion, km) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssdssissssssssi", 
                $id_coche,          
                $matricula,       
                $precio,           
                $color,             
                $cambio,            
                $ano,             
                $combustible,      
                $cv,                
                $fecha_actual,      
                $_SESSION['usuario_id'], 
                $id_provincia,      
                $id_modelo,        
                $tipo,              
                $descripcion,     
                $kilometraje       
            );
            
            if (!$stmt->execute()) {
                throw new Exception("Error al insertar el coche: " . $stmt->error);
            }
            
            // Guardar referencias a las imágenes en la base de datos
            foreach ($imagenes_urls as $url_imagen) {
                $id_imagen = 'IMG' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
                
                $sql_imagen = "INSERT INTO Imagenes (id_imagen, url, id_coche) VALUES (?, ?, ?)";
                $stmt_imagen = $conn->prepare($sql_imagen);
                $stmt_imagen->bind_param("sss", $id_imagen, $url_imagen, $id_coche);
                
                if (!$stmt_imagen->execute()) {
                    throw new Exception("Error al guardar la imagen: " . $stmt_imagen->error);
                }
            }
            
            // Confirmar la transacción
            $conn->commit();
            
            mostrarAlerta('Tu coche ha sido publicado correctamente');
            redirigir('coches.php');
            
        } catch (Exception $e) {
            // Rollback en caso de error
            $conn->rollback();
            mostrarAlerta('Error: ' . $e->getMessage(), 'danger');
        }
    } else {
        // Mostrar los errores
        $mensaje_error = implode('<br>', $errores);
        mostrarAlerta($mensaje_error, 'danger');
    }
}

mostrarHeader('Vender coche');
?>

<h1 class="title">Añadir coche</h1>

<div class="form-container" style="max-width: 800px;">
    <div class="form-border"></div>
    
    <form action="vender.php" method="post" enctype="multipart/form-data">
        <div class="details-grid">
            <div class="form-group">
                <label for="marca">Marca</label>
                <select id="marca" name="marca" class="form-control" required>
                    <option value="">Selecciona marca</option>
                    <?php while ($marca = $marcas_result->fetch_assoc()): ?>
                        <option value="<?php echo $marca['id_marca']; ?>">
                            <?php echo $marca['nombre']; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="modelo">Modelo</label>
                <select id="modelo" name="modelo" class="form-control" required disabled>
                    <option value="">Selecciona primero una marca</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="tipo">Tipo</label>
                <select id="tipo" name="tipo" class="form-control" required>
                    <option value="">Selecciona tipo</option>
                    <?php foreach ($tipos as $tipo_valor): ?>
                        <option value="<?php echo $tipo_valor; ?>"><?php echo $tipo_valor; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="matricula">Matrícula</label>
                <input type="text" id="matricula" name="matricula" class="form-control" pattern="([0-9]{4}[A-Za-z]{3})|([A-Za-z]{1,2}[0-9]{4}[A-Za-z]{1,2})" required placeholder="1234ABC o PA2345AB">
            </div>
            
            <div class="form-group">
                <label for="precio">Precio (€)</label>
                <input type="number" id="precio" name="precio" class="form-control" required min="1" max="999999">
                <small class="text-muted">Entre 1 y 999.999 €</small>
            </div>
            
            <div class="form-group">
                <label for="color">Color</label>
                <select id="color" name="color" class="form-control" required>
                    <option value="">Selecciona color</option>
                    <?php foreach ($colores as $color_valor): ?>
                        <option value="<?php echo $color_valor; ?>"><?php echo $color_valor; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="ano">Año</label>
                <select id="ano" name="ano" class="form-control" required>
                    <option value="">Selecciona año</option>
                    <?php foreach ($anos as $ano_valor): ?>
                        <option value="<?php echo $ano_valor; ?>"><?php echo $ano_valor; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="cv">CV</label>
                <input type="number" id="cv" name="cv" class="form-control" required min="65" max="5000">
                <small class="text-muted">Entre 65 y 5000 Km</small>
            </div>
            
            <div class="form-group">
                <label for="kilometraje">Km</label>
                <input type="number" id="kilometraje" name="kilometraje" class="form-control" min="0" max="999999" required value="0">
                <small class="text-muted">Entre 0 y 999.999 Km</small>
            </div>
            
            <div class="form-group">
                <label for="combustible">Combustible</label>
                <select id="combustible" name="combustible" class="form-control" required>
                    <option value="">Selecciona combustible</option>
                    <?php foreach ($combustibles as $combustible_valor): ?>
                        <option value="<?php echo $combustible_valor; ?>"><?php echo $combustible_valor; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="cambio">Cambio</label>
                <select id="cambio" name="cambio" class="form-control" required>
                    <option value="">Selecciona tipo de cambio</option>
                    <?php foreach ($cambios as $cambio_valor): ?>
                        <option value="<?php echo $cambio_valor; ?>"><?php echo $cambio_valor; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="provincia">Provincia</label>
                <select id="provincia" name="provincia" class="form-control" required>
                    <option value="">Selecciona provincia</option>
                    <?php while ($provincia = $provincias_result->fetch_assoc()): ?>
                        <option value="<?php echo $provincia['id_provincia']; ?>"><?php echo $provincia['nombre']; ?></option>
                    <?php endwhile ; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="imagenes">Imágenes (mín 3, máx 10)</label>
                <input type="file" id="imagenes" name="imagenes[]" class="form-control" multiple accept="image/*" required>
                <small class="text-muted">Selecciona entre 3 y 10 imágenes. Formatos permitidos: JPG, PNG, GIF.</small>
                <div id="preview-container" style="display: flex; flex-wrap: wrap; margin-top: 10px;"></div>
                <div id="image-count" class="text-muted" style="margin-top: 5px;"></div>
            </div>
            
            <div class="form-group description-box">
                <label for="descripcion">Descripción</label>
                <textarea id="descripcion" name="descripcion" class="form-control" rows="4" placeholder="Describe las características principales del vehículo, estado, extras, etc."></textarea>
            </div>
        </div>
        
        <div style="text-align: center; margin-top: 20px;">
            <button type="submit" class="btn btn-primary" id="submit-btn">Subir</button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const marcaSelect = document.getElementById('marca');
    const modeloSelect = document.getElementById('modelo');
    const imageInput = document.getElementById('imagenes');
    const previewContainer = document.getElementById('preview-container');
    const imageCountDiv = document.getElementById('image-count');
    const submitBtn = document.getElementById('submit-btn');
    
    // Vista previa de las imágenes
    imageInput.addEventListener('change', function() {
        // Limpiar vistas previas anteriores
        previewContainer.innerHTML = '';
        
        const min_files = 3;
        const max_files = 10;
        
        // Verificar número de archivos
        if (this.files.length > max_files) {
            // Reemplazar alert por mostrarAlerta
            mostrarAlerta(`Solo puedes subir un máximo de ${max_files} imágenes.`, 'danger');
            this.value = ''; // Limpiar la selección
            imageCountDiv.textContent = 'Ninguna imagen seleccionada';
            submitBtn.disabled = true;
            return;
        }
        
        if (this.files.length < min_files) {
            imageCountDiv.textContent = `Has seleccionado ${this.files.length} imágenes. Mínimo requerido: ${min_files}`;
            imageCountDiv.style.color = 'red';
            submitBtn.disabled = true;
        } else {
            imageCountDiv.textContent = `Has seleccionado ${this.files.length} imágenes.`;
            imageCountDiv.style.color = 'green';
            submitBtn.disabled = false;
        }
        
        // Mostrar vista previa de cada imagen
        for (let i = 0; i < this.files.length; i++) {
            const file = this.files[i];
            
            // Verificar el tipo de archivo
            if (!file.type.match('image.*')) {
                continue;
            }
            
            const reader = new FileReader();
            
            reader.onload = function(e) {
                const imgWrap = document.createElement('div');
                imgWrap.style.width = '100px';
                imgWrap.style.height = '75px';
                imgWrap.style.margin = '5px';
                imgWrap.style.position = 'relative';
                
                const img = document.createElement('img');
                img.src = e.target.result;
                img.style.width = '100%';
                img.style.height = '100%';
                img.style.objectFit = 'cover';
                
                imgWrap.appendChild(img);
                previewContainer.appendChild(imgWrap);
            }
            
            reader.readAsDataURL(file);
        }
    });
    
    // Validación del formulario
    document.querySelector('form').addEventListener('submit', function(e) {
        const imageFiles = imageInput.files;
        if (imageFiles.length < 3) {
            e.preventDefault();
            // Reemplazar alert por mostrarAlerta
            mostrarAlerta('Debes subir al menos 3 imágenes del coche.', 'danger');
        }
    });
    
    // Cargar modelos según la marca seleccionada
    marcaSelect.addEventListener('change', function() {
        const marcaId = this.value;
        
        if (marcaId) {
            // Habilitar el select de los modelos
            modeloSelect.disabled = false;
            modeloSelect.innerHTML = '<option value="">Cargando modelos...</option>';
            
            // Petición a servidor AJAX para obtener modelos
            fetch('get_modelos.php?marca_id=' + marcaId)
            .then(response => response.json())
            .then(data => {
                modeloSelect.innerHTML = '<option value="">Selecciona modelo</option>';
                
                data.forEach(modelo => {
                    const option = document.createElement('option');
                    option.value = modelo.id_modelo;
                    option.textContent = modelo.nombre;
                    modeloSelect.appendChild(option);
                });
            })
            .catch(error => {
                console.error('Error:', error);
                modeloSelect.innerHTML = '<option value="">Error al cargar modelos</option>';
                })
            .catch(error => {
                console.error('Error:', error);
                modeloSelect.innerHTML = '<option value="">Error al cargar modelos</option>';
            });
        } else {
            modeloSelect.disabled = true;
            modeloSelect.innerHTML = '<option value="">Selecciona primero una marca</option>';
        }
    });

    // Referencias a los campos que necesitan validación
    const cvInput = document.getElementById('cv');
    const kmInput = document.getElementById('kilometraje');
    const precioInput = document.getElementById('precio');

    // Validación para el campo CV
    if (cvInput) {
        cvInput.addEventListener('input', function() {
            // Verificar límites y mostrar advertencias sin cambiar el valor
            if (this.value > 5000) {
                mostrarMensaje(this, 'El valor máximo recomendado es 5000 CV');
            } else if (this.value < 65 && this.value !== '') {
                mostrarMensaje(this, 'El valor mínimo recomendado es 65 CV');
            } else {
                limpiarMensaje(this);
            }
        });
        
        // Validar cuando pierde el foco
        cvInput.addEventListener('blur', function() {
            if (this.value !== '' && (this.value > 5000 || this.value < 65)) {
                mostrarMensaje(this, 'El valor de CV debe estar entre 65 y 5000');
            } else {
                limpiarMensaje(this);
            }
        });
    }

    // Validación para el campo Kilometraje
    if (kmInput) {
        kmInput.addEventListener('input', function() {
            // Verificar límites
            if (this.value > 999999) {
                this.value = 999999;
                mostrarMensaje(this, 'El valor máximo permitido es 999.999 km');
            } else if (this.value < 0) {
                this.value = 0;
                mostrarMensaje(this, 'El valor mínimo permitido es 0 km');
            } else {
                limpiarMensaje(this);
            }
        });
    }

    // Validación para el campo Precio
    if (precioInput) {
        // Establecer límite máximo
        precioInput.setAttribute('max', '999999');
        
        precioInput.addEventListener('input', function() {
            // Verificar límites
            if (this.value > 999999) {
                this.value = 999999;
                mostrarMensaje(this, 'El valor máximo permitido es 999.999 €');
            } else if (this.value <= 0 && this.value !== '') {
                this.value = 1;
                mostrarMensaje(this, 'El precio mínimo debe ser 1 €');
            } else {
                limpiarMensaje(this);
            }
        });
    }

    // Función para mostrar mensajes de error
    function mostrarMensaje(elemento, mensaje) {
        // Limpiar mensaje previo si existe
        limpiarMensaje(elemento);
        
        // Añadir clase para indicar error
        elemento.classList.add('is-invalid');
        
        // Crear y mostrar mensaje de error
        const mensajeDiv = document.createElement('div');
        mensajeDiv.className = 'invalid-feedback';
        mensajeDiv.textContent = mensaje;
        
        elemento.parentNode.appendChild(mensajeDiv);
    }

    // Función para limpiar mensajes de error
    function limpiarMensaje(elemento) {
        // Quitar clase de error
        elemento.classList.remove('is-invalid');
        
        // Buscar y eliminar mensaje de error si existe
        const mensajeExistente = elemento.parentNode.querySelector('.invalid-feedback');
        if (mensajeExistente) {
            mensajeExistente.remove();
        }
    }

    // Validar el formulario antes de enviarlo
    document.querySelector('form').addEventListener('submit', function(e) {
        let esValido = true;
        
        // Validar CV
        if (cvInput && cvInput.value) {
            const valorCV = parseInt(cvInput.value);
            if (valorCV < 65 || valorCV > 5000) {
                mostrarMensaje(cvInput, 'El valor de CV debe estar entre 65 y 5000');
                cvInput.focus();
                esValido = false;
            }
        }
        
        // Validar kilometraje
        if (kmInput && kmInput.value) {
            const valorKM = parseInt(kmInput.value);
            if (valorKM < 0 || valorKM > 999999) {
                mostrarMensaje(kmInput, 'El kilometraje debe estar entre 0 y 999.999');
                kmInput.focus();
                esValido = false;
            }
        }
        
        // Validar precio
        if (precioInput && precioInput.value) {
            const valorPrecio = parseInt(precioInput.value);
            if (valorPrecio <= 0 || valorPrecio > 999999) {
                mostrarMensaje(precioInput, 'El precio debe estar entre 1 y 999.999 €');
                precioInput.focus();
                esValido = false;
            }
        }
        
        // Si hay errores, evitar el envío del formulario
        if (!esValido) {
            e.preventDefault();
        }
    });

    // Agregar estilo para los mensajes de error si no existe
    if (!document.querySelector('#validacion-estilos')) {
        const estilos = document.createElement('style');
        estilos.id = 'validacion-estilos';
        estilos.textContent = `
            .invalid-feedback {
                display: block;
                width: 100%;
                margin-top: 0.25rem;
                font-size: 0.875em;
                color: #dc3545;
            }
            
            .is-invalid {
                border-color: #dc3545;
                padding-right: calc(1.5em + 0.75rem);
                background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'/%3e%3c/svg%3e");
                background-repeat: no-repeat;
                background-position: right calc(0.375em + 0.1875rem) center;
                background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
            }
        `;
        document.head.appendChild(estilos);
    }
});
</script>

<?php
mostrarFooter();
?>