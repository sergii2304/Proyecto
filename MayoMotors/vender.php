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
    if (empty($precio) || !is_numeric($precio) || $precio <= 0) {
        $errores[] = 'El precio debe ser un número mayor que 0';
    }
    
    // Validar el año
    if (empty($ano) || !is_numeric($ano) || $ano < 1900 || $ano > date('Y')) {
        $errores[] = 'El año debe ser válido';
    }
    
    // Validar los CV
    if (empty($cv) || !is_numeric($cv) || $cv <= 0) {
        $errores[] = 'Los CV deben ser un número mayor que 0';
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
            
            $column_check_tipo = $conn->query("SHOW COLUMNS FROM Coches LIKE 'tipo'");
            $tipo_exists = $column_check_tipo && $column_check_tipo->num_rows > 0;
            
            $column_check_desc = $conn->query("SHOW COLUMNS FROM Coches LIKE 'descripcion'");
            $desc_exists = $column_check_desc && $column_check_desc->num_rows > 0;
            
            // Preparar consulta según la existencia de las columnas
            if ($tipo_exists && $desc_exists) {
                $sql = "INSERT INTO Coches (id_coche, matricula, precio, color, cambio, ano, combustible, cv, fecha, id_usuario, id_provincia, id_modelo, tipo, descripcion) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssdssissssssss", $id_coche, $matricula, $precio, $color, $cambio, $ano, $combustible, $cv, $fecha_actual, $_SESSION['usuario_id'], $id_provincia, $id_modelo, $tipo, $descripcion);
            } elseif ($tipo_exists) {
                $sql = "INSERT INTO Coches (id_coche, matricula, precio, color, cambio, ano, combustible, cv, fecha, id_usuario, id_provincia, id_modelo, tipo) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssdssisssssss", $id_coche, $matricula, $precio, $color, $cambio, $ano, $combustible, $cv, $fecha_actual, $_SESSION['usuario_id'], $id_provincia, $id_modelo, $tipo);
                
                if (!empty($descripcion)) {

                    try {
                        $conn->query("ALTER TABLE Coches ADD COLUMN descripcion TEXT");
                        // Actualizar el coche con la descripción
                        $update_sql = "UPDATE Coches SET descripcion = ? WHERE id_coche = ?";
                        $update_stmt = $conn->prepare($update_sql);
                        $update_stmt->bind_param("ss", $descripcion, $id_coche);
                        $update_stmt->execute();
                    } catch (Exception $e) {
                        $desc_warning = true;
                    }
                }
            } elseif ($desc_exists) {
                $sql = "INSERT INTO Coches (id_coche, matricula, precio, color, cambio, ano, combustible, cv, fecha, id_usuario, id_provincia, id_modelo, descripcion) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssdssisssssss", $id_coche, $matricula, $precio, $color, $cambio, $ano, $combustible, $cv, $fecha_actual, $_SESSION['usuario_id'], $id_provincia, $id_modelo, $descripcion);
            } else {
                $sql = "INSERT INTO Coches (id_coche, matricula, precio, color, cambio, ano, combustible, cv, fecha, id_usuario, id_provincia, id_modelo) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssdssissssss", $id_coche, $matricula, $precio, $color, $cambio, $ano, $combustible, $cv, $fecha_actual, $_SESSION['usuario_id'], $id_provincia, $id_modelo);
                
                if (!empty($descripcion)) {
                    try {
                        $conn->query("ALTER TABLE Coches ADD COLUMN descripcion TEXT");
                        // Actualizar el coche con la descripción
                        $update_sql = "UPDATE Coches SET descripcion = ? WHERE id_coche = ?";
                        $update_stmt = $conn->prepare($update_sql);
                        $update_stmt->bind_param("ss", $descripcion, $id_coche);
                        $update_stmt->execute();
                    } catch (Exception $e) {
                        // Si falla, simplemente continuamos sin guardar la descripción
                        // y se muestra un aviso
                        $desc_warning = true;
                    }
                }
            }
            
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
            
            if (isset($desc_warning) && $desc_warning) {
                mostrarAlerta('Tu coche ha sido publicado correctamente, pero no se pudo guardar la descripción.', 'warning');
            } else {
                mostrarAlerta('Tu coche ha sido publicado correctamente');
            }
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
                <input type="text" id="matricula" name="matricula" class="form-control" pattern="([0-9]{4}[A-Za-z]{3})|([A-Za-z]{1,2}[0-9]{4}[A-Za-z]{1,2})" required placeholder="Formato: 1234ABC o PA2345AB">
            </div>
            
            <div class="form-group">
                <label for="precio">Precio (€)</label>
                <input type="number" id="precio" name="precio" class="form-control" required min="1">
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
                <input type="number" id="cv" name="cv" class="form-control" required min="65">
            </div>
            
            <div class="form-group">
                <label for="kilometraje">Kilometraje</label>
                <input type="number" id="kilometraje" name="kilometraje" class="form-control" min="0">
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
            alert(`Solo puedes subir un máximo de ${max_files} imágenes.`);
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
            alert('Debes subir al menos 3 imágenes del coche.');
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
            });
        } else {
            modeloSelect.disabled = true;
            modeloSelect.innerHTML = '<option value="">Selecciona primero una marca</option>';
        }
    });
});
</script>

<?php
mostrarFooter();
?>