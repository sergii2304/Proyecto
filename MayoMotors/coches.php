<?php
require_once 'layout.php';

// Obtener filtros si existen
$filtros = [];
$where_conditions = [];
$params = [];
$tipos = "";

if (isset($_GET['buscar'])) {
    // Aplicar filtros
    if (!empty($_GET['marca'])) {
        $where_conditions[] = "mar.nombre = ?";
        $params[] = $_GET['marca'];
        $tipos .= "s";
    }
    
    if (!empty($_GET['modelo'])) {
        $where_conditions[] = "mod.nombre = ?";
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
        $where_conditions[] = "c.cv >= ?";
        $params[] = $_GET['cv'];
        $tipos .= "i";
    }
    
    if (!empty($_GET['precio_min'])) {
        $where_conditions[] = "c.precio >= ?";
        $params[] = $_GET['precio_min'];
        $tipos .= "d";
    }
    
    if (!empty($_GET['precio_max'])) {
        $where_conditions[] = "c.precio <= ?";
        $params[] = $_GET['precio_max'];
        $tipos .= "d";
    }
}

// Construir consulta SQL básica
$sql = "SELECT c.id_coche, c.matricula, c.precio, c.color, c.cambio, c.ano, c.combustible, c.cv, 
               mar.nombre as marca, mod.nombre as modelo, 
               p.nombre as provincia, img.url as imagen
        FROM Coches c
        INNER JOIN Modelos mod ON c.id_modelo = mod.id_modelo
        INNER JOIN Marcas mar ON mod.id_marca = mar.id_marca
        INNER JOIN Provincias p ON c.id_provincia = p.id_provincia
        LEFT JOIN Imagenes img ON c.id_coche = img.id_coche";

// Agregar condiciones de filtro si existen
if (!empty($where_conditions)) {
    $sql .= " WHERE " . implode(" AND ", $where_conditions);
}

$sql .= " GROUP BY c.id_coche ORDER BY c.fecha DESC";

// Preparar y ejecutar consulta
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Error en la preparación de la consulta: " . $conn->error);
}

if (!empty($params)) {
    // Método alternativo para bind_param con array de parámetros
    $types = $tipos;
    $bind_names[] = $types;
    for ($i = 0; $i < count($params); $i++) {
        $bind_name = 'bind' . $i;
        $$bind_name = $params[$i];
        $bind_names[] = &$$bind_name;
    }
    call_user_func_array(array($stmt, 'bind_param'), $bind_names);
}

if (!$stmt->execute()) {
    die("Error al ejecutar la consulta: " . $stmt->error);
}

$result = $stmt->get_result();

// Obtener marcas, modelos y provincias para los filtros
$marcas_sql = "SELECT nombre FROM Marcas ORDER BY nombre";
$marcas_result = $conn->query($marcas_sql);

$modelos_sql = "SELECT nombre FROM Modelos ORDER BY nombre";
$modelos_result = $conn->query($modelos_sql);

$provincias_sql = "SELECT nombre FROM Provincias ORDER BY nombre";
$provincias_result = $conn->query($provincias_sql);

// Arrays para los selects
$anos = range(date('Y'), date('Y') - 30);
$combustibles = ['Gasolina', 'Diesel', 'Híbrido', 'Eléctrico', 'GLP', 'Gas Natural'];
$cambios = ['Manual', 'Automático'];
$colores = ['Blanco', 'Negro', 'Gris', 'Plata', 'Rojo', 'Azul', 'Verde', 'Amarillo', 'Naranja', 'Marrón', 'Beige'];

mostrarHeader('Lista de coches');
?>

<h1 class="title">Lista de coches</h1>

<!-- Filtros de búsqueda -->
<div class="filter-container">
    <div class="form-border"></div>
    <form action="coches.php" method="get">
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
                    <?php while ($marca = $marcas_result->fetch_assoc()): ?>
                        <option value="<?php echo $marca['nombre']; ?>" <?php echo (isset($_GET['marca']) && $_GET['marca'] == $marca['nombre']) ? 'selected' : ''; ?>>
                            <?php echo $marca['nombre']; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="modelo">Modelo</label>
                <select id="modelo" name="modelo" class="form-control">
                    <option value="">Todos</option>
                    <?php while ($modelo = $modelos_result->fetch_assoc()): ?>
                        <option value="<?php echo $modelo['nombre']; ?>" <?php echo (isset($_GET['modelo']) && $_GET['modelo'] == $modelo['nombre']) ? 'selected' : ''; ?>>
                            <?php echo $modelo['nombre']; ?>
                        </option>
                    <?php endwhile; ?>
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
                <input type="number" id="cv" name="cv" class="form-control" value="<?php echo isset($_GET['cv']) ? htmlspecialchars($_GET['cv']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="precio_min">Precio mínimo</label>
                <input type="number" id="precio_min" name="precio_min" class="form-control" value="<?php echo isset($_GET['precio_min']) ? htmlspecialchars($_GET['precio_min']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="precio_max">Precio máximo</label>
                <input type="number" id="precio_max" name="precio_max" class="form-control" value="<?php echo isset($_GET['precio_max']) ? htmlspecialchars($_GET['precio_max']) : ''; ?>">
            </div>
        </div>
        
        <div class="filter-actions">
            <button type="submit" name="buscar" class="btn btn-primary">Buscar</button>
            <a href="coches.php" class="btn btn-danger">Quitar filtros</a>
        </div>
    </form>
</div>

<!-- Lista de coches -->
<div class="car-grid">
    <?php if ($result->num_rows > 0): ?>
        <?php while ($coche = $result->fetch_assoc()): ?>
            <div class="car-card">
                <img src="<?php echo !empty($coche['imagen']) ? $coche['imagen'] : 'img/no-image.png'; ?>" alt="<?php echo $coche['marca'] . ' ' . $coche['modelo']; ?>" class="car-image">
                
                <div class="car-info">
                    <h3 class="car-title"><?php echo $coche['marca'] . ' ' . $coche['modelo']; ?></h3>
                    <p class="car-price"><?php echo number_format($coche['precio'], 2, ',', '.') . ' €'; ?></p>
                    <p><?php echo $coche['ano'] . ' - ' . $coche['combustible']; ?></p>
                    
                    <div class="car-actions">
                        <a href="detalle_coche.php?id=<?php echo $coche['id_coche']; ?>" class="btn btn-primary car-btn">Ver Detalles</a>
                        
                        <?php if (estaLogueado()): ?>
                            <?php
                            // Verificar si el coche está en favoritos
                            $favorito_sql = "SELECT * FROM Guardar WHERE id_usuario = ? AND id_coche = ?";
                            $favorito_stmt = $conn->prepare($favorito_sql);
                            $favorito_stmt->bind_param("ss", $_SESSION['usuario_id'], $coche['id_coche']);
                            $favorito_stmt->execute();
                            $es_favorito = $favorito_stmt->get_result()->num_rows > 0;
                            ?>
                            
                            <button class="favorite-btn <?php echo $es_favorito ? 'active' : ''; ?>" data-id="<?php echo $coche['id_coche']; ?>">
                                <i class="fas fa-heart"></i>
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="alert alert-info" style="grid-column: 1 / -1;">
            No se encontraron coches con los filtros seleccionados.
        </div>
    <?php endif; ?>
</div>

<script>
// Script para manejar favoritos
document.addEventListener('DOMContentLoaded', function() {
    const favButtons = document.querySelectorAll('.favorite-btn');
    
    favButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const cocheId = this.dataset.id;
            
            // Petición AJAX para agregar/quitar de favoritos
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
                    alert(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        });
    });
});
</script>

<?php
mostrarFooter();
?>