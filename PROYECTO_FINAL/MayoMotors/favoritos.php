<?php
require_once 'layout.php';

// Verificar si el usuario está logueado
if (!estaLogueado()) {
    mostrarAlerta('Debes iniciar sesión para ver tus favoritos', 'danger');
    redirigir('login.php');
}

// Obtener favoritos del usuario
$sql = "SELECT c.id_coche, c.matricula, c.precio, c.color, c.cambio, c.ano, c.combustible, c.cv, 
               mar.nombre as marca, mod.nombre as modelo, 
               p.nombre as provincia, img.url as imagen
        FROM Guardar g
        INNER JOIN Coches c ON g.id_coche = c.id_coche
        INNER JOIN Modelos mod ON c.id_modelo = mod.id_modelo
        INNER JOIN Marcas mar ON mod.id_marca = mar.id_marca
        INNER JOIN Provincias p ON c.id_provincia = p.id_provincia
        LEFT JOIN Imagenes img ON c.id_coche = img.id_coche
        WHERE g.id_usuario = ?
        GROUP BY c.id_coche
        ORDER BY c.fecha DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $_SESSION['usuario_id']);
$stmt->execute();
$result = $stmt->get_result();

mostrarHeader('Favoritos');
?>

<h1 class="title">Lista de favoritos</h1>

<!-- Lista de coches favoritos -->
<div class="car-grid">
    <?php if ($result->num_rows > 0): ?>
        <?php while ($coche = $result->fetch_assoc()): ?>
            <div class="car-card">
                <img src="<?php echo !empty($coche['imagen']) ? $coche['imagen'] : 'img/no-image.png'; ?>" alt="<?php echo $coche['marca'] . ' ' . $coche['modelo']; ?>" class="car-image">
                
                <div class="car-info">
                    <h3 class="car-title"><?php echo $coche['marca'] . ' ' . $coche['modelo']; ?></h3>
                    <p class="car-price"><?php echo number_format($coche['precio'], 2, ',', '.') . ' €'; ?></p>
                    
                    <div class="car-actions">
                        <a href="detalle_coche.php?id=<?php echo $coche['id_coche']; ?>" class="btn btn-primary car-btn">Ver Detalles</a>
                        
                        <button class="favorite-btn active" data-id="<?php echo $coche['id_coche']; ?>">
                            <i class="fas fa-heart"></i>
                        </button>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
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