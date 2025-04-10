<?php
require_once 'layout.php';

// Verificar si se proporcionó un ID de coche
if (!isset($_GET['id']) || empty($_GET['id'])) {
    mostrarAlerta('No se ha especificado ningún coche', 'danger');
    redirigir('coches.php');
}

$coche_id = $_GET['id'];

// Obtener detalles del coche
$sql = "SELECT c.id_coche, c.matricula, c.precio, c.color, c.cambio, c.ano, c.combustible, c.cv, c.fecha,
               mar.nombre as marca, mod.nombre as modelo, 
               p.nombre as provincia, u.nombre as vendedor, u.telefono, u.correo
        FROM Coches c
        INNER JOIN Modelos mod ON c.id_modelo = mod.id_modelo
        INNER JOIN Marcas mar ON mod.id_marca = mar.id_marca
        INNER JOIN Provincias p ON c.id_provincia = p.id_provincia
        INNER JOIN Usuarios u ON c.id_usuario = u.id_usuario
        WHERE c.id_coche = ?";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Error en la preparación de la consulta: " . $conn->error);
}

$stmt->bind_param("s", $coche_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    mostrarAlerta('El coche especificado no existe', 'danger');
    redirigir('coches.php');
}

$coche = $result->fetch_assoc();

// Obtener imágenes del coche
$imagenes_sql = "SELECT url FROM Imagenes WHERE id_coche = ?";
$stmt = $conn->prepare($imagenes_sql);
$stmt->bind_param("s", $coche_id);
$stmt->execute();
$imagenes_result = $stmt->get_result();

$imagenes = [];
while ($imagen = $imagenes_result->fetch_assoc()) {
    $imagenes[] = $imagen['url'];
}

// Si no hay imágenes, usar imagen por defecto
if (empty($imagenes)) {
    $imagenes[] = 'img/no-image.png';
}

// Verificar si el coche está en favoritos (si el usuario está logueado)
$es_favorito = false;
if (estaLogueado()) {
    $favorito_sql = "SELECT * FROM Guardar WHERE id_usuario = ? AND id_coche = ?";
    $stmt = $conn->prepare($favorito_sql);
    $stmt->bind_param("ss", $_SESSION['usuario_id'], $coche_id);
    $stmt->execute();
    $es_favorito = $stmt->get_result()->num_rows > 0;
}

mostrarHeader('Detalle del coche');
?>

<h1 class="title"><?php echo $coche['marca'] . ' ' . $coche['modelo']; ?></h1>

<div class="details-container">
    <!-- Imagen principal del coche -->
    <div style="text-align: center; margin-bottom: 20px;">
        <img src="<?php echo $imagenes[0]; ?>" alt="<?php echo $coche['marca'] . ' ' . $coche['modelo']; ?>" class="details-image" style="max-width: 600px;">
    </div>
    
    <!-- Galería de imágenes (si hay más de una) -->
    <?php if (count($imagenes) > 1): ?>
        <div style="display: flex; justify-content: center; gap: 10px; margin-bottom: 20px; flex-wrap: wrap;">
            <?php foreach ($imagenes as $index => $imagen): ?>
                <img src="<?php echo $imagen; ?>" alt="<?php echo $coche['marca'] . ' ' . $coche['modelo'] . ' ' . ($index + 1); ?>" style="width: 100px; height: 75px; object-fit: cover; cursor: pointer;" onclick="mostrarImagen('<?php echo $imagen; ?>')">
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
    <!-- Detalles del coche -->
    <div class="details-grid">
        <div class="form-group">
            <label>Marca</label>
            <input type="text" class="form-control" readonly value="<?php echo $coche['marca']; ?>">
        </div>
        
        <div class="form-group">
            <label>Modelo</label>
            <input type="text" class="form-control" readonly value="<?php echo $coche['modelo']; ?>">
        </div>
        
        <div class="form-group">
            <label>Tipo</label>
            <input type="text" class="form-control" readonly value="Turismo">
        </div>
        
        <div class="form-group">
            <label>Precio</label>
            <input type="text" class="form-control" readonly value="<?php echo number_format($coche['precio'], 2, ',', '.') . ' €'; ?>">
        </div>
        
        <div class="form-group">
            <label>CV</label>
            <input type="text" class="form-control" readonly value="<?php echo $coche['cv']; ?>">
        </div>
        
        <div class="form-group">
            <label>Año</label>
            <input type="text" class="form-control" readonly value="<?php echo $coche['ano']; ?>">
        </div>
        
        <div class="form-group">
            <label>Km</label>
            <input type="text" class="form-control" readonly value="<?php echo rand(0, 150000); ?>">
        </div>
        
        <div class="form-group">
            <label>Combustible</label>
            <input type="text" class="form-control" readonly value="<?php echo $coche['combustible']; ?>">
        </div>
        
        <div class="form-group">
            <label>Cambio</label>
            <input type="text" class="form-control" readonly value="<?php echo $coche['cambio']; ?>">
        </div>
        
        <div class="form-group">
            <label>Color</label>
            <input type="text" class="form-control" readonly value="<?php echo $coche['color']; ?>">
        </div>
        
        <div class="form-group">
            <label>Nombre Propietario</label>
            <input type="text" class="form-control" readonly value="<?php echo $coche['vendedor']; ?>">
        </div>
        
        <div class="form-group">
            <label>Provincia</label>
            <input type="text" class="form-control" readonly value="<?php echo $coche['provincia']; ?>">
        </div>
        
        <div class="form-group">
            <label>Teléfono</label>
            <input type="text" class="form-control" readonly value="<?php echo $coche['telefono']; ?>">
        </div>
        
        <div class="form-group description-box">
            <label>Descripción</label>
            <p style="margin-top: 10px;">
                <?php echo $coche['marca'] . ' ' . $coche['modelo'] . ' ' . $coche['ano'] . ' en perfecto estado. Turismo, ' . $coche['combustible'] . ', ' . $coche['cambio'] . ', ' . $coche['cv'] . ' CV. Color ' . $coche['color'] . '.'; ?>
            </p>
        </div>
    </div>
    
    <!-- Acciones -->
    <div style="text-align: center; margin-top: 20px;">
        <?php if (estaLogueado()): ?>
            <button class="favorite-btn <?php echo $es_favorito ? 'active' : ''; ?>" data-id="<?php echo $coche_id; ?>" style="margin-right: 10px; width: auto; padding: 10px 20px;">
                <i class="fas fa-heart" style="margin-right: 5px;"></i> 
                <?php echo $es_favorito ? 'Quitar de favoritos' : 'Añadir a favoritos'; ?>
            </button>
        <?php endif; ?>
        
        <a href="contacto.php" class="btn btn-primary">Contactar</a>
        <a href="coches.php" class="btn btn-danger" style="margin-left: 10px;">Cerrar</a>
    </div>
</div>

<script>
// Función para mostrar imagen grande al hacer clic en una miniatura
function mostrarImagen(url) {
    document.querySelector('.details-image').src = url;
}

// Script para manejar favoritos
document.addEventListener('DOMContentLoaded', function() {
    const favButton = document.querySelector('.favorite-btn');
    
    if (favButton) {
        favButton.addEventListener('click', function() {
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
                    
                    if (data.action === 'added') {
                        this.innerHTML = '<i class="fas fa-heart" style="margin-right: 5px;"></i> Quitar de favoritos';
                    } else {
                        this.innerHTML = '<i class="fas fa-heart" style="margin-right: 5px;"></i> Añadir a favoritos';
                    }
                } else {
                    alert(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        });
    }
});
</script>

<?php
mostrarFooter();
?>