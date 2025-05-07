<?php
require_once 'layout.php';

// Obtener los filtros si existen
$filtros = [];
$where_conditions = [];
$params = [];
$tipos = "";

if (isset($_GET['buscar'])) {
    // Aplicar los filtros
    if (!empty($_GET['marca'])) {
        $where_conditions[] = "M.nombre = ?";
        $params[] = $_GET['marca'];
        $tipos .= "s";
    }
    
    if (!empty($_GET['modelo'])) {
        $where_conditions[] = "MD.nombre = ?";
        $params[] = $_GET['modelo'];
        $tipos .= "s";
    }
    
    if (!empty($_GET['ano'])) {
        $where_conditions[] = "c.ano = ?";
        $params[] = $_GET['ano'];
        $tipos .= "i";
    }
    
    if (!empty($_GET['combustible'])) {
        $where_conditions[] = "c.combustible = ?";
        $params[] = $_GET['combustible'];
        $tipos .= "s";
    }
    
    if (!empty($_GET['cambio'])) {
        $where_conditions[] = "c.cambio = ?";
        $params[] = $_GET['cambio'];
        $tipos .= "s";
    }
    
    if (!empty($_GET['provincia'])) {
        $where_conditions[] = "p.nombre = ?";
        $params[] = $_GET['provincia'];
        $tipos .= "s";
    }
    
    if (!empty($_GET['color'])) {
        $where_conditions[] = "c.color = ?";
        $params[] = $_GET['color'];
        $tipos .= "s";
    }
    
    if (!empty($_GET['cv'])) {
        // Validar y limitar el valor de CV entre 65 y 5000
        $cv_value = min(max((int)$_GET['cv'], 65), 5000);
        $where_conditions[] = "c.cv >= ?";
        $params[] = $cv_value;
        $tipos .= "i";
    }
    
    if (!empty($_GET['precio_min'])) {
        // Validar y limitar el valor de precio mínimo
        $precio_min_value = min(max((int)$_GET['precio_min'], 1), 999999);
        $where_conditions[] = "c.precio >= ?";
        $params[] = $precio_min_value;
        $tipos .= "d";
    }
    
    if (!empty($_GET['precio_max'])) {
        // Validar y limitar el valor de precio máximo
        $precio_max_value = min(max((int)$_GET['precio_max'], 1), 999999);
        $where_conditions[] = "c.precio <= ?";
        $params[] = $precio_max_value;
        $tipos .= "d";
    }
}

// Construir la consulta SQL para los filtros
$sql = "SELECT c.id_coche 
        FROM Coches AS c
        INNER JOIN Modelos AS MD ON c.id_modelo = MD.id_modelo
        INNER JOIN Marcas AS M ON MD.id_marca = M.id_marca
        INNER JOIN Provincias AS p ON c.id_provincia = p.id_provincia";

// Agregar las condiciones de filtro si existen
if (!empty($where_conditions)) {
    $sql .= " WHERE " . implode(" AND ", $where_conditions);
}

$sql .= " ORDER BY c.fecha DESC";

// Array para almacenar los IDs de coches
$coches_ids = [];

// Preparar y ejecutar la consulta
if (!empty($params)) {
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Error en la preparación de la consulta: " . $conn->error);
    }
    
    // Método alternativo para bind_param con array
    $bindParams = [$tipos];
    foreach ($params as $key => $value) {
        $bindParams[] = &$params[$key];
    }
    call_user_func_array([$stmt, 'bind_param'], $bindParams);
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $coches_ids[] = $row['id_coche'];
    }
} else {
    $result = $conn->query($sql);
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $coches_ids[] = $row['id_coche'];
        }
    }
}

// Obtener las marcas para los filtros
$marcas_sql = "SELECT id_marca, nombre FROM Marcas ORDER BY nombre";
$marcas_result = $conn->query($marcas_sql);

// Obtener los modelos - estos se filtraran por JavaScript
$modelos_sql = "SELECT nombre FROM Modelos ORDER BY nombre";
$modelos_result = $conn->query($modelos_sql);

// Obtener las provincias para los filtros
$provincias_sql = "SELECT nombre FROM Provincias ORDER BY nombre";
$provincias_result = $conn->query($provincias_sql);

// Arrays para los selects
$anos = range(date('Y'), date('Y') - 30);
$combustibles = ['Gasolina', 'Diesel', 'Híbrido', 'Eléctrico', 'GLP', 'Gas Natural'];
$cambios = ['Manual', 'Automático'];
$colores = ['Blanco','Negro','Gris/Plata','Azul','Rojo','Verde','Violeta','Rosa','Beis','Marrón','Bronce','Dorado','Naranja','Amarillo','Granate','Otros'];

mostrarHeader('Lista de coches');
?>

<h1 class="title">Lista de coches</h1>

<!-- Filtros de búsqueda -->
<div class="filter-container">
    <div class="form-border"></div>
    <form action="coches.php" method="get" id="filter-form">
        <div class="filter-grid">
            <div class="form-group">
                <label for="ano">Año</label>
                <select id="ano" name="ano" class="form-control">
                    <option value="">Todos</option>
                    <?php foreach ($anos as $ano): ?>
                        <option value="<?php echo $ano; ?>" <?php echo (isset($_GET['ano']) && $_GET['ano'] == $ano) ? 'selected' : ''; ?>>
                            <?php echo $ano; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="marca">Marca</label>
                <select id="marca" name="marca" class="form-control">
                    <option value="">Todas</option>
                    <?php 
                    // Se reinicia el puntero del resultado
                    $marcas_result->data_seek(0);
                    while ($marca = $marcas_result->fetch_assoc()): 
                    ?>
                        <option value="<?php echo $marca['nombre']; ?>" data-id="<?php echo $marca['id_marca']; ?>" <?php echo (isset($_GET['marca']) && $_GET['marca'] == $marca['nombre']) ? 'selected' : ''; ?>>
                            <?php echo $marca['nombre']; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="modelo">Modelo</label>
                <select id="modelo" name="modelo" class="form-control">
                    <option value="">Todos</option>
                    <?php if(isset($_GET['modelo']) && !empty($_GET['modelo'])): ?>
                        <option value="<?php echo $_GET['modelo']; ?>" selected><?php echo $_GET['modelo']; ?></option>
                    <?php endif; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="combustible">Combustible</label>
                <select id="combustible" name="combustible" class="form-control">
                    <option value="">Todos</option>
                    <?php foreach ($combustibles as $combustible): ?>
                        <option value="<?php echo $combustible; ?>" <?php echo (isset($_GET['combustible']) && $_GET['combustible'] == $combustible) ? 'selected' : ''; ?>>
                            <?php echo $combustible; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="cambio">Cambio</label>
                <select id="cambio" name="cambio" class="form-control">
                    <option value="">Todos</option>
                    <?php foreach ($cambios as $cambio): ?>
                        <option value="<?php echo $cambio; ?>" <?php echo (isset($_GET['cambio']) && $_GET['cambio'] == $cambio) ? 'selected' : ''; ?>>
                            <?php echo $cambio; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="provincia">Provincia</label>
                <select id="provincia" name="provincia" class="form-control">
                    <option value="">Todas</option>
                    <?php while ($provincia = $provincias_result->fetch_assoc()): ?>
                        <option value="<?php echo $provincia['nombre']; ?>" <?php echo (isset($_GET['provincia']) && $_GET['provincia'] == $provincia['nombre']) ? 'selected' : ''; ?>>
                            <?php echo $provincia['nombre']; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="color">Color</label>
                <select id="color" name="color" class="form-control">
                    <option value="">Todos</option>
                    <?php foreach ($colores as $color): ?>
                        <option value="<?php echo $color; ?>" <?php echo (isset($_GET['color']) && $_GET['color'] == $color) ? 'selected' : ''; ?>>
                            <?php echo $color; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="cv">CV mínimos</label>
                <input type="number" id="cv" name="cv" min="65" max="5000" class="form-control" value="<?php echo isset($_GET['cv']) ? htmlspecialchars(min(max((int)$_GET['cv'], 65), 5000)) : ''; ?>">
                <small class="text-muted">Min: 65CV Max: 5000CV</small>
            </div>
            
            <div class="form-group">
                <label for="precio_min">Precio mínimo</label>
                <input type="number" id="precio_min" name="precio_min" min="1" max="999999" class="form-control" value="<?php echo isset($_GET['precio_min']) ? htmlspecialchars(min(max((int)$_GET['precio_min'], 1), 999999)) : ''; ?>">
                <small class="text-muted">Min: 1€</small>
            </div>
            
            <div class="form-group">
                <label for="precio_max">Precio máximo</label>
                <input type="number" id="precio_max" name="precio_max" min="1" max="999999" class="form-control" value="<?php echo isset($_GET['precio_max']) ? htmlspecialchars(min(max((int)$_GET['precio_max'], 1), 999999)) : ''; ?>">
                <small class="text-muted">Max: 999.999€</small>
            </div>
        </div>
        
        <div class="filter-actions">
            <button type="submit" name="buscar" class="btn btn-primary">Buscar</button>
            <a href="coches.php" class="btn btn-danger">Quitar filtros</a>
        </div>
    </form>
</div>

<!-- Lista de los coches -->
<div class="car-grid">
    <?php if (!empty($coches_ids)): ?>
        <?php foreach ($coches_ids as $coche_id): ?>
            <?php
            // Obtener la información del coche
            $coche_sql = "SELECT c.id_coche, c.matricula, c.precio, c.color, c.combustible, c.ano, c.km,
                          M.nombre AS marca, MD.nombre AS modelo, p.nombre AS provincia
                          FROM Coches AS c
                          INNER JOIN Modelos AS MD ON c.id_modelo = MD.id_modelo
                          INNER JOIN Marcas AS M ON MD.id_marca = M.id_marca
                          INNER JOIN Provincias AS p ON c.id_provincia = p.id_provincia
                          WHERE c.id_coche = ?";
            
            $coche_stmt = $conn->prepare($coche_sql);
            if (!$coche_stmt) {
                continue; // Saltar este coche si hay algun error
            }
            
            $coche_stmt->bind_param("s", $coche_id);
            $coche_stmt->execute();
            $coche_result = $coche_stmt->get_result();
            
            if ($coche_result->num_rows === 0) {
                continue; // Saltar si no hay datos
            }
            
            $coche = $coche_result->fetch_assoc();
            
            // Buscar la primera imagen para este coche
            $imagen_url = 'css/no-image.png'; // Imagen por defecto
            
            $img_sql = "SELECT url FROM Imagenes WHERE id_coche = ? LIMIT 1";
            $img_stmt = $conn->prepare($img_sql);
            if ($img_stmt) {
                $img_stmt->bind_param("s", $coche_id);
                $img_stmt->execute();
                $img_result = $img_stmt->get_result();
                
                if ($img_result->num_rows > 0) {
                    $imagen = $img_result->fetch_assoc();
                    $imagen_url = $imagen['url'];
                }
            }
            ?>
            
            <div class="car-card">
                <img src="<?php echo $imagen_url; ?>" alt="<?php echo $coche['marca'] . ' ' . $coche['modelo']; ?>" class="car-image">
                
                <div class="car-info">
                    <h3 class="car-title"><?php echo $coche['marca'] . ' ' . $coche['modelo']; ?></h3>
                    <p class="car-price"><?php echo number_format($coche['precio'], 2, ',', '.') . ' €'; ?></p>
                    <p><?php echo $coche['ano'] . ' - ' . $coche['combustible'] . ' - ' . (isset($coche['km']) ? number_format($coche['km'], 0, ',', '.') . ' km' : ''); ?></p>
                    
                    <div class="car-actions">
                        <a href="detalle_coche.php?id=<?php echo $coche['id_coche']; ?>" class="btn btn-primary car-btn">Ver Detalles</a>
                        
                        <?php if (estaLogueado()): ?>
                            <?php
                            // Verificar si el coche está en favoritos
                            $favorito_sql = "SELECT * FROM Guardar WHERE id_usuario = ? AND id_coche = ?";
                            $favorito_stmt = $conn->prepare($favorito_sql);
                            $favorito_stmt->bind_param("ss", $_SESSION['usuario_id'], $coche['id_coche']);
                            $favorito_stmt->execute();
                            $favorito_result = $favorito_stmt->get_result();
                            $es_favorito = $favorito_result->num_rows > 0;
                            ?>
                            
                            <button class="favorite-btn <?php echo $es_favorito ? 'active' : ''; ?>" data-id="<?php echo $coche['id_coche']; ?>">
                                <i class="fas fa-heart"></i>
                            </button>
                            
                            <?php if (esAdmin()): ?>
                                <a href="eliminar_coche.php?id=<?php echo $coche['id_coche']; ?>" 
                                    class="btn btn-danger btn-sm delete-car-btn">
                                    <i class="fas fa-trash"></i>
                                </a>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="alert alert-info" style="grid-column: 1 / -1;">
            No se encontraron coches con los filtros seleccionados.
        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Cargar modelos según la marca seleccionada
    const marcaSelect = document.getElementById('marca');
    const modeloSelect = document.getElementById('modelo');
    
    // Función para cargar los modelos según la marca seleccionada
    function cargarModelos(marcaId, selectedModel = '') {
        if (!marcaId) {
            modeloSelect.innerHTML = '<option value="">Todos</option>';
            if (selectedModel) {
                const option = document.createElement('option');
                option.value = selectedModel;
                option.textContent = selectedModel;
                option.selected = true;
                modeloSelect.appendChild(option);
            }
            return;
        }
        
        // Petición al AJAX para obtener los modelos
        fetch('get_modelos.php?marca_id=' + marcaId)
        .then(response => response.json())
        .then(data => {
            modeloSelect.innerHTML = '<option value="">Todos</option>';
            
            data.forEach(modelo => {
                const option = document.createElement('option');
                option.value = modelo.nombre;
                option.textContent = modelo.nombre;
                
                // Si hay un modelo seleccionado previamente, marcarlo
                if (selectedModel === modelo.nombre) {
                    option.selected = true;
                }
                
                modeloSelect.appendChild(option);
            });
        })
        .catch(error => {
            console.error('Error:', error);
            modeloSelect.innerHTML = '<option value="">Error al cargar modelos</option>';
        });
    }
    
    // Manejar cambio en select de la marca
    marcaSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const marcaId = selectedOption ? selectedOption.getAttribute('data-id') : '';
        cargarModelos(marcaId);
    });
    
    // Cargar los modelos iniciales si hay una marca seleccionada
    if (marcaSelect.value) {
        const selectedOption = marcaSelect.options[marcaSelect.selectedIndex];
        const marcaId = selectedOption ? selectedOption.getAttribute('data-id') : '';
        const selectedModel = '<?php echo isset($_GET["modelo"]) ? $_GET["modelo"] : ""; ?>';
        cargarModelos(marcaId, selectedModel);
    }
    
    // Script para manejar favoritos
    const favButtons = document.querySelectorAll('.favorite-btn');
    
    favButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const cocheId = this.dataset.id;
            
            // Petición al AJAX para agregar/quitar de favoritos
            fetch('favoritos_action.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'coche_id=' + cocheId
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.classList.toggle('active');
                } else {
                    // Reemplazar alert por mostrarAlerta
                    mostrarAlerta(data.message, 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        });
    });
    
    // Manejar los botones de eliminar coche con la confirmación personalizada
    const deleteButtons = document.querySelectorAll('.delete-car-btn');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault(); // Prevenir la navegación inmediata
            
            const href = this.getAttribute('href');
            
            // Usar la función personalizada de confirmación
            mostrarConfirmacion('¿Estás seguro de que deseas eliminar este coche? Esta acción no se puede deshacer.', function(confirmed) {
                if (confirmed) {
                    window.location.href = href; // Redirigir solo si el usuario confirma
                }
            });
            
            return false; // Prevenir comportamiento por defecto
        });
    });
    
    // Validaciones para los campos de los filtros
    const cvFilterInput = document.getElementById('cv');
    const precioMinInput = document.getElementById('precio_min');
    const precioMaxInput = document.getElementById('precio_max');
    
    // Funciones para mostrar y limpiar mensajes de validación
    function mostrarMensajeValidacion(campo, mensaje) {
        // Limpiar mensaje anterior si existe
        limpiarMensajeValidacion(campo);
        
        // Crear y añadir nuevo mensaje
        const mensajeDiv = document.createElement('div');
        mensajeDiv.className = 'invalid-feedback';
        mensajeDiv.textContent = mensaje;
        campo.parentNode.appendChild(mensajeDiv);
        
        // Marcar el campo como inválido
        campo.classList.add('is-invalid');
    }
    
    function limpiarMensajeValidacion(campo) {
        campo.classList.remove('is-invalid');
        const mensajeActual = campo.parentNode.querySelector('.invalid-feedback');
        if (mensajeActual) {
            mensajeActual.remove();
        }
    }
    
    if (cvFilterInput) {
        cvFilterInput.addEventListener('input', function() {
            if (this.value > 5000) {
                mostrarMensajeValidacion(this, 'El valor máximo recomendado es 5000 CV');
            } else if (this.value < 65 && this.value !== '') {
                mostrarMensajeValidacion(this, 'El valor mínimo recomendado es 65 CV');
            } else {
                limpiarMensajeValidacion(this);
            }
        });
        
        // Validar cuando pierde el foco
        cvFilterInput.addEventListener('blur', function() {
            if (this.value !== '' && (this.value > 5000 || this.value < 65)) {
                mostrarMensajeValidacion(this, 'El valor de CV debe estar entre 65 y 5000');
            } else {
                limpiarMensajeValidacion(this);
            }
        });
    }
    
            // Modificación en la sección del script JavaScript del archivo coches.php
        if (precioMinInput) {
            precioMinInput.addEventListener('input', function() {
                if (this.value > 999999) {
                    this.value = 999999;
                    mostrarMensajeValidacion(this, 'El valor máximo permitido es 999.999 €');
                } else if (this.value < 1 && this.value !== '') {
                    this.value = 1;
                    mostrarMensajeValidacion(this, 'El valor mínimo permitido es 1 €');
                } else {
                    limpiarMensajeValidacion(this);
                }
                
                // Solo mostrar advertencia si precio mínimo > precio máximo
                if (precioMaxInput && precioMaxInput.value && 
                    parseInt(this.value) > parseInt(precioMaxInput.value)) {
                    mostrarMensajeValidacion(precioMaxInput, 'El precio mínimo es mayor que el máximo. Esto podría no mostrar resultados.');
                }
            });
        }

        if (precioMaxInput) {
            precioMaxInput.addEventListener('input', function() {
                if (this.value > 999999) {
                    this.value = 999999;
                    mostrarMensajeValidacion(this, 'El valor máximo permitido es 999.999 €');
                } else if (this.value < 1 && this.value !== '') {
                    this.value = 1;
                    mostrarMensajeValidacion(this, 'El valor mínimo permitido es 1 €');
                } else {
                    limpiarMensajeValidacion(this);
                }
                
                // Solo mostrar advertencia si precio máximo < precio mínimo
                if (precioMinInput && precioMinInput.value && 
                    parseInt(this.value) < parseInt(precioMinInput.value) && this.value !== '') {
                    mostrarMensajeValidacion(this, 'El precio máximo es menor que el mínimo. Esto podría no mostrar resultados.');
                } else {
                    limpiarMensajeValidacion(this);
                }
            });
        }
    
    // Validar el formulario antes de enviarlo
    const filterForm = document.getElementById('filter-form');
    if (filterForm) {
        filterForm.addEventListener('submit', function(e) {
            let isValid = true;
            
            // Validar CV
            if (cvFilterInput && cvFilterInput.value) {
                const cvValue = parseInt(cvFilterInput.value);
                if (cvValue < 65 || cvValue > 5000) {
                    // Reemplazar alert por mostrarAlerta
                    mostrarAlerta('El valor de CV debe estar entre 65 y 5000', 'danger');
                    cvFilterInput.focus();
                    isValid = false;
                }
            }
            
            // Validar precio mínimo
            if (precioMinInput && precioMinInput.value) {
                const precioMinValue = parseInt(precioMinInput.value);
                if (precioMinValue < 1 || precioMinValue > 999999) {
                    // Reemplazar alert por mostrarAlerta
                    mostrarAlerta('El precio mínimo debe ser un valor entre 1 y 999.999', 'danger');
                    precioMinInput.focus();
                    isValid = false;
                }
            }
            
            // Validar precio máximo
            if (precioMaxInput && precioMaxInput.value) {
                const precioMaxValue = parseInt(precioMaxInput.value);
                if (precioMaxValue < 1 || precioMaxValue > 999999) {
                    // Reemplazar alert por mostrarAlerta
                    mostrarAlerta('El precio máximo debe ser un valor entre 1 y 999.999', 'danger');
                    precioMaxInput.focus();
                    isValid = false;
                }
            }
            
            // Validar relación entre precio mínimo y máximo
            if (precioMinInput && precioMinInput.value && 
                precioMaxInput && precioMaxInput.value) {
                if (parseInt(precioMinInput.value) > parseInt(precioMaxInput.value)) {
                    // Reemplazar alert por mostrarAlerta
                    mostrarAlerta('El precio máximo debe ser mayor o igual al precio mínimo', 'danger');
                    precioMaxInput.focus();
                    isValid = false;
                }
            }
            
            if (!isValid) {
                e.preventDefault();
            }
        });
    }
});
</script>

<style>
/* Estilos para el botón de eliminar coches */
.delete-car-btn {
    display: flex;
    justify-content: center;
    align-items: center;
    width: 36px;
    height: 36px;
    padding: 0;
    border-radius: 50%;
    font-size: 0.8rem;
    transition: all 0.3s ease;
}

.delete-car-btn:hover {
    background-color: #b02a37;
    transform: scale(1.05);
}

/* Estilos para los botones en las tarjetas de coches */
.car-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 8px;
    padding-top: 10px;
}

.car-btn {
    flex: 1;
}

/* Estilos para los mensajes de validación */
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
</style>

<?php
mostrarFooter();
?>