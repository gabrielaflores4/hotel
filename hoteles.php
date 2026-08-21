<?php
$hoteles = $pdo->query("
    SELECT ho.id, ho.nombre, ho.ciudad, ho.direccion, COUNT(h.id) AS habitaciones
    FROM hoteles ho
    LEFT JOIN habitaciones h ON h.hotel_id = ho.id
    GROUP BY ho.id, ho.nombre, ho.ciudad, ho.direccion
    ORDER BY ho.nombre
")->fetchAll();
?>

<section class="page-heading">
    <div>
        <span class="section-label">Gestión</span>
        <h2>Hoteles</h2>
        <p>Consulta los establecimientos administrados por el sistema.</p>
    </div>
</section>

<section class="panel">
    <div class="panel-heading">
        <div>
            <span class="section-label">Listado</span>
            <h3>Hoteles registrados</h3>
        </div>
    </div>

    <div class="hotel-cards-grid">
        <?php foreach ($hoteles as $hotel): ?>
        <article class="hotel-info-card">
            <div class="hotel-icon">H</div>
            <div class="hotel-info-content">
                <h3><?= htmlspecialchars($hotel["nombre"]) ?></h3>
                <p><?= htmlspecialchars($hotel["ciudad"]) ?></p>
                <span><?= htmlspecialchars($hotel["direccion"]) ?></span>
                <strong><?= (int)$hotel["habitaciones"] ?> habitaciones</strong>
            </div>
        </article>
        <?php endforeach; ?>
    </div>
</section>
