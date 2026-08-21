<?php
$habitaciones = $pdo->query("
    SELECT h.*, ho.nombre AS hotel, t.nombre AS tipo
    FROM habitaciones h
    INNER JOIN hoteles ho ON ho.id = h.hotel_id
    INNER JOIN tipos_habitacion t ON t.id = h.tipo_id
    ORDER BY ho.nombre, h.numero
")->fetchAll();
?>

<section class="page-heading">
    <div>
        <span class="section-label">Gestión</span>
        <h2>Habitaciones</h2>
        <p>Consulta el estado y la información de las habitaciones.</p>
    </div>
</section>

<section class="panel">
    <div class="panel-heading">
        <div>
            <span class="section-label">Inventario</span>
            <h3>Habitaciones registradas</h3>
        </div>
    </div>

    <div class="room-cards-grid">
        <?php foreach ($habitaciones as $habitacion): ?>
        <article class="room-info-card">
            <div class="room-info-top">
                <span class="room-number"><?= htmlspecialchars($habitacion["numero"]) ?></span>
                <span class="status status-<?= htmlspecialchars($habitacion["estado"]) ?>">
                    <?= htmlspecialchars(ucfirst($habitacion["estado"])) ?>
                </span>
            </div>
            <h3><?= htmlspecialchars($habitacion["tipo"]) ?></h3>
            <p><?= htmlspecialchars($habitacion["hotel"]) ?></p>
            <strong>$<?= number_format((float)$habitacion["precio_noche"], 2) ?> / noche</strong>
        </article>
        <?php endforeach; ?>
    </div>
</section>
