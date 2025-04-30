<?php
require_once 'layout.php';

// Verificar si se proporcionó un ID de coche
if (!isset($_GET['id']) || empty($_GET['id'])) {
    mostrarAlerta('No se ha especificado ningún coche', 'danger');
    redirigir('coches.php');
}

$coche_id = $_GET['id'];

// Verificar si la columna 'descripcion' existe en la tabla
$check_descripcion = $conn->query("SHOW COLUMNS FROM Coches LIKE 'descripcion'");
$desc_exists = $check_descripcion && $check_descripcion->num_rows > 0;

// Obtener detalles del coche - incluir descripción si existe
if ($desc_exists) {
    $sql = "SELECT c.id_coche, c.matricula, c.precio, c.color, c.cambio, c.ano, c.combustible, c.cv, c.fecha, c.descripcion,
                  M.nombre as marca, MD.nombre as modelo, 
                  p.nombre as provincia, u.nombre as vendedor, u.telefono, u.correo
           FROM Coches c
           INNER JOIN Modelos MD ON c.id_modelo = MD.id_modelo
           INNER JOIN Marcas M ON MD.id_marca = M.id_marca
           INNER JOIN Provincias p ON c.id_provincia = p.id_provincia
           INNER JOIN Usuarios u ON c.id_usuario = u.id_usuario
           WHERE c.id_coche = ?";
} else {
    $sql = "SELECT c.id_coche, c.matricula, c.precio, c.color, c.cambio, c.ano, c.combustible, c.cv, c.fecha,
                  M.nombre as marca, MD.nombre as modelo, 
                  p.nombre as provincia, u.nombre as vendedor, u.telefono, u.correo
           FROM Coches c
           INNER JOIN Modelos MD ON c.id_modelo = MD.id_modelo
           INNER JOIN Marcas M ON MD.id_marca = M.id_marca
           INNER JOIN Provincias p ON c.id_provincia = p.id_provincia
           INNER JOIN Usuarios u ON c.id_usuario = u.id_usuario
           WHERE c.id_coche = ?";
}

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
    $imagenes[] = 'css/no-image.png';
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

// Obtener el tipo del coche si existe la columna
$tipo = "Turismo";
$check_tipo = $conn->query("SHOW COLUMNS FROM Coches LIKE 'tipo'");
if ($check_tipo && $check_tipo->num_rows > 0) {
    $tipo_sql = "SELECT tipo FROM Coches WHERE id_coche = ?";
    $stmt_tipo = $conn->prepare($tipo_sql);
    $stmt_tipo->bind_param("s", $coche_id);
    $stmt_tipo->execute();
    $tipo_result = $stmt_tipo->get_result();
    if ($tipo_result->num_rows > 0) {
        $tipo_row = $tipo_result->fetch_assoc();
        $tipo = $tipo_row['tipo'];
    }
}

mostrarHeader('Detalle del coche');
?>

<h1 class="title"><?php echo $coche['marca'] . ' ' . $coche['modelo']; ?></h1>

<div class="details-container">
    <!-- Carrusel de imágenes -->
    <div class="carousel-container">
        <div class="carousel-wrapper">
            <div class="carousel-slides">
                <?php foreach ($imagenes as $index => $imagen): ?>
                    <div class="carousel-slide <?php echo $index === 0 ? 'active' : ''; ?>">
                        <img src="<?php echo $imagen; ?>" alt="<?php echo $coche['marca'] . ' ' . $coche['modelo'] . ' ' . ($index + 1); ?>">
                    </div>
                <?php endforeach; ?>
            </div>
            
            <?php if (count($imagenes) > 1): ?>
                <button class="carousel-button prev" onclick="moveSlide(-1)">&#10094;</button>
                <button class="carousel-button next" onclick="moveSlide(1)">&#10095;</button>
                
                <div class="carousel-indicators">
                    <?php foreach ($imagenes as $index => $imagen): ?>
                        <span class="carousel-dot <?php echo $index === 0 ? 'active' : ''; ?>" onclick="currentSlide(<?php echo $index + 1; ?>)"></span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
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
            <input type="text" class="form-control" readonly value="<?php echo $tipo; ?>">
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
                <?php
                // Mostrar la descripción personalizada si existe, de lo contrario mostrar la generada automáticamente
                if (isset($coche['descripcion']) && !empty($coche['descripcion'])) {
                    echo htmlspecialchars($coche['descripcion']);
                } else {
                    echo $coche['marca'] . ' ' . $coche['modelo'] . ' ' . $coche['ano'] . ' en perfecto estado. ' . $tipo . ', ' . $coche['combustible'] . ', ' . $coche['cambio'] . ', ' . $coche['cv'] . ' CV. Color ' . $coche['color'] . '.';
                }
                ?>
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
        
        <a href="coches.php" class="btn btn-danger" style="margin-left: 10px;">Cerrar</a>
    </div>
</div>

<style>
.carousel-container {
    margin-bottom: 30px;
}

.carousel-wrapper {
    position: relative;
    max-width: 700px;
    margin: 0 auto;
}

.carousel-slides {
    position: relative;
    height: 400px;
    overflow: hidden;
    border-radius: 5px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
}

.carousel-slide {
    position: absolute;
    width: 100%;
    height: 100%;
    opacity: 0;
    transition: opacity 0.6s ease;
    display: none;
}

.carousel-slide.active {
    opacity: 1;
    display: block;
}

.carousel-slide img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.carousel-button {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    background-color: rgba(0, 0, 0, 0.5);
    color: white;
    border: none;
    padding: 16px;
    font-size: 18px;
    cursor: pointer;
    border-radius: 50%;
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background-color 0.3s;
}

.carousel-button:hover {
    background-color: rgba(0, 0, 0, 0.8);
}

.carousel-button.prev {
    left: 15px;
}

.carousel-button.next {
    right: 15px;
}

.carousel-indicators {
    position: absolute;
    bottom: 15px;
    left: 50%;
    transform: translateX(-50%);
    display: flex;
    justify-content: center;
    gap: 10px;
}

.carousel-dot {
    width: 12px;
    height: 12px;
    background-color: rgba(255, 255, 255, 0.5);
    border-radius: 50%;
    cursor: pointer;
    transition: background-color 0.3s;
}

.carousel-dot.active, .carousel-dot:hover {
    background-color: white;
}

.description-box p {
    white-space: pre-wrap; /* Preserva los saltos de línea */
}

@media (max-width: 768px) {
    .carousel-slides {
        height: 300px;
    }
    
    .carousel-button {
        padding: 12px;
        font-size: 16px;
        width: 40px;
        height: 40px;
    }
}
</style>

<script>
// Funciones del carrusel
let slideIndex = 1;
showSlide(slideIndex);

function moveSlide(n) {
    showSlide(slideIndex += n);
}

function currentSlide(n) {
    showSlide(slideIndex = n);
}

function showSlide(n) {
    let slides = document.getElementsByClassName("carousel-slide");
    let dots = document.getElementsByClassName("carousel-dot");
    
    if (n > slides.length) {
        slideIndex = 1;
    }
    if (n < 1) {
        slideIndex = slides.length;
    }
    
    // Ocultar todas las diapositivas
    for (let i = 0; i < slides.length; i++) {
        slides[i].classList.remove("active");
    }
    
    // Desactivar todos los puntos
    for (let i = 0; i < dots.length; i++) {
        dots[i].classList.remove("active");
    }
    
    // Mostrar la diapositiva actual
    slides[slideIndex - 1].classList.add("active");
    
    // Activar el punto actual
    if (dots.length > 0) {
        dots[slideIndex - 1].classList.add("active");
    }
}

// Añadir navegación con el teclado
document.addEventListener('keydown', function(e) {
    if (e.key === 'ArrowLeft') {
        moveSlide(-1);
    } else if (e.key === 'ArrowRight') {
        moveSlide(1);
    }
});

// Script para manejar los favoritos
document.addEventListener('DOMContentLoaded', function() {
    const favButton = document.querySelector('.favorite-btn');
    
    if (favButton) {
        favButton.addEventListener('click', function() {
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
    
    // Iniciar el carrusel automático
    const slides = document.getElementsByClassName("carousel-slide");
    if (slides.length > 1) {
        setInterval(function() {
            moveSlide(1);
        }, 5000); // Cambiar cada 5 segundos
    }
});
</script>

<?php
mostrarFooter();
?>