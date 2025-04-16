<?php
require_once 'layout.php';

// Verificar si el usuario está logueado
if (!estaLogueado()) {
    mostrarAlerta('Debes iniciar sesión para ver tus favoritos', 'danger');
    redirigir('login.php');
}

// Obtener coches favoritos - Consulta muy simplificada sin alias complejos
$sql = "SELECT g.id_coche 
        FROM Guardar AS g 
        WHERE g.id_usuario = ?";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Error en la consulta: " . $conn->error);
}

$stmt->bind_param("s", $_SESSION['usuario_id']);
$stmt->execute();
$result = $stmt->get_result();

// Recolectar IDs de coches favoritos
$coches_ids = [];
while ($row = $result->fetch_assoc()) {
    $coches_ids[] = $row['id_coche'];
}

mostrarHeader('Favoritos');
?>

<h1 class="title">Lista de favoritos</h1>

<!-- Lista de coches favoritos -->
<div class="car-grid">
    <?php if (!empty($coches_ids)): ?>
        <?php foreach ($coches_ids as $coche_id): ?>
            <?php
            // Obtener información del coche
            $coche_sql = "SELECT c.id_coche, c.matricula, c.precio, c.color, c.combustible, c.ano,
                          m1.nombre AS marca, m2.nombre AS modelo, p.nombre AS provincia
                          FROM Coches AS c
                          INNER JOIN Modelos AS m2 ON c.id_modelo = m2.id_modelo
                          INNER JOIN Marcas AS m1 ON m2.id_marca = m1.id_marca
                          INNER JOIN Provincias AS p ON c.id_provincia = p.id_provincia
                          WHERE c.id_coche = ?";
            
            $coche_stmt = $conn->prepare($coche_sql);
            if (!$coche_stmt) {
                continue; // Saltar este coche si hay error
            }
            
            $coche_stmt->bind_param("s", $coche_id);
            $coche_stmt->execute();
            $coche_result = $coche_stmt->get_result();
            
            if ($coche_result->num_rows === 0) {
                continue; // Saltar si no hay datos
            }
            
            $coche = $coche_result->fetch_assoc();
            
            // Buscar la primera imagen para este coche
            $imagen_url = 'img/no-image.png'; // Imagen por defecto
            
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
                    <p><?php echo $coche['ano'] . ' - ' . $coche['combustible']; ?></p>
                    
                    <div class="car-actions">
                        <a href="detalle_coche.php?id=<?php echo $coche['id_coche']; ?>" class="btn btn-primary car-btn">Ver Detalles</a>
                        
                        <button class="favorite-btn active" data-id="<?php echo $coche['id_coche']; ?>">
                            <i class="fas fa-heart"></i>
                        </button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="alert alert-info" style="grid-column: 1 / -1;">
            No tienes coches en tu lista de favoritos.
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
            const card = this.closest('.car-card');
            
            // Petición AJAX para quitar de favoritos
            fetch('favoritos_action.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'coche_id=' + cocheId
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.action === 'removed') {
                    // Eliminar tarjeta con animación
                    card.style.transition = 'opacity 0.5s ease';
                    card.style.opacity = '0';
                    
                    setTimeout(() => {
                        card.remove();
                        
                        // Verificar si ya no hay coches
                        if (document.querySelectorAll('.car-card').length === 0) {
                            const container = document.querySelector('.car-grid');
                            container.innerHTML = '<div class="alert alert-info" style="grid-column: 1 / -1;">No tienes coches en tu lista de favoritos.</div>';
                        }
                    }, 500);
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