<?php
$mensaje = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $accion = $_POST["accion"] ?? "";

    if ($accion === "crear") {
        $huesped_id = (int)($_POST["huesped_id"] ?? 0);
        $habitacion_id = (int)($_POST["habitacion_id"] ?? 0);
        $check_in = $_POST["check_in"] ?? "";
        $check_out = $_POST["check_out"] ?? "";
        $estado = $_POST["estado"] ?? "confirmada";

        if (!$huesped_id || !$habitacion_id || !$check_in || !$check_out || $check_out <= $check_in) {
            $error = "Completa los datos correctamente.";
        } else {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM reservas WHERE habitacion_id = ? AND estado IN ('pendiente','confirmada') AND check_in < ? AND check_out > ?");
            $stmt->execute([$habitacion_id, $check_out, $check_in]);

            if ($stmt->fetchColumn() > 0) {
                $error = "La habitación no está disponible para esas fechas.";
            } else {
                $stmt = $pdo->prepare("INSERT INTO reservas (huesped_id, habitacion_id, check_in, check_out, estado) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$huesped_id, $habitacion_id, $check_in, $check_out, $estado]);
                $mensaje = "Reserva creada correctamente.";
            }
        }
    }

    if ($accion === "cancelar") {
        $stmt = $pdo->prepare("UPDATE reservas SET estado = 'cancelada' WHERE id = ?");
        $stmt->execute([(int)$_POST["id"]]);
        $mensaje = "Reserva cancelada correctamente.";
    }
}

$hoy = date("Y-m-d");
$buscar = trim($_GET["buscar"] ?? "");
$filtro_estado = $_GET["estado_filtro"] ?? "todos";

$huespedes = $pdo->query("SELECT id, CONCAT(nombre, ' ', apellido) AS nombre FROM huespedes ORDER BY apellido, nombre")->fetchAll();

$habitaciones = $pdo->query("
    SELECT h.id, h.numero, h.precio_noche, h.estado, ho.nombre AS hotel
    FROM habitaciones h
    INNER JOIN hoteles ho ON ho.id = h.hotel_id
    WHERE h.estado <> 'mantenimiento'
    ORDER BY ho.nombre, CAST(h.numero AS UNSIGNED)
")->fetchAll();

$panel_habitaciones = $pdo->prepare("
    SELECT h.numero, h.estado, ho.nombre AS hotel,
           EXISTS(
               SELECT 1
               FROM reservas r
               WHERE r.habitacion_id = h.id
               AND r.estado IN ('pendiente','confirmada')
               AND r.check_in <= ?
               AND r.check_out > ?
           ) AS reservada
    FROM habitaciones h
    INNER JOIN hoteles ho ON ho.id = h.hotel_id
    ORDER BY ho.nombre, CAST(h.numero AS UNSIGNED)
");
$panel_habitaciones->execute([$hoy, $hoy]);
$panel_habitaciones = $panel_habitaciones->fetchAll();

function estadoVisualHabitacion($habitacion) {
    if ($habitacion["estado"] === "mantenimiento") {
        return "mantenimiento";
    }

    if ($habitacion["estado"] === "limpieza") {
        return "limpieza";
    }

    if ((int)$habitacion["reservada"] === 1) {
        return "ocupada";
    }

    return "disponible";
}

$sqlReservas = "
    SELECT r.id, r.check_in, r.check_out, r.estado,
           CONCAT(h.nombre, ' ', h.apellido) AS huesped,
           h.email,
           hab.numero,
           ho.nombre AS hotel,
           DATEDIFF(r.check_out, r.check_in) AS noches,
           DATEDIFF(r.check_out, r.check_in) * hab.precio_noche AS total
    FROM reservas r
    INNER JOIN huespedes h ON h.id = r.huesped_id
    INNER JOIN habitaciones hab ON hab.id = r.habitacion_id
    INNER JOIN hoteles ho ON ho.id = hab.hotel_id
    WHERE 1=1
";

$params = [];

if ($buscar !== "") {
    $sqlReservas .= " AND (CONCAT(h.nombre, ' ', h.apellido) LIKE ? OR hab.numero LIKE ? OR ho.nombre LIKE ?)";
    $termino = "%" . $buscar . "%";
    $params[] = $termino;
    $params[] = $termino;
    $params[] = $termino;
}

if (in_array($filtro_estado, ["pendiente", "confirmada", "cancelada"], true)) {
    $sqlReservas .= " AND r.estado = ?";
    $params[] = $filtro_estado;
}

$sqlReservas .= " ORDER BY r.check_in DESC, r.id DESC";
$stmt = $pdo->prepare($sqlReservas);
$stmt->execute($params);
$reservas = $stmt->fetchAll();
?>

<section class="page-heading">
    <div>
        <span class="section-label">Booking</span>
        <h2>Administrar reservas</h2>
        <p>Gestiona las reservas y consulta el estado de las habitaciones.</p>
    </div>
    <a class="button primary" href="#nueva-reserva">+ Agregar reserva</a>
</section>

<?php if ($mensaje): ?>
<div class="alert success"><?= htmlspecialchars($mensaje) ?></div>
<?php endif; ?>

<?php if ($error): ?>
<div class="alert error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<section class="panel room-panel">
    <div class="panel-heading">
        <div>
            <span class="section-label">Room Status</span>
            <h3>Estado de habitaciones</h3>
        </div>
        <div class="panel-tools">
            <span class="date-filter">Hoy</span>
            <span class="date-value"><?= date("d/m/Y") ?></span>
        </div>
    </div>

    <div class="room-grid">
        <?php foreach ($panel_habitaciones as $habitacion): ?>
            <?php $estado = estadoVisualHabitacion($habitacion); ?>
            <div class="room <?= $estado ?>">
                <strong><?= htmlspecialchars($habitacion["numero"]) ?></strong>
                <span><?= htmlspecialchars(ucfirst($estado)) ?></span>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="legend">
        <span><i class="dot disponible"></i> Disponible</span>
        <span><i class="dot ocupada"></i> Ocupada</span>
        <span><i class="dot limpieza"></i> Limpieza</span>
        <span><i class="dot mantenimiento"></i> Mantenimiento</span>
    </div>
</section>

<section class="panel booking-panel">
    <div class="panel-heading booking-heading">
        <div>
            <span class="section-label">Booking List</span>
            <h3>Reservas registradas</h3>
        </div>
        <form method="GET" class="booking-filters">
            <input type="hidden" name="seccion" value="reservas">
            <input type="search" name="buscar" value="<?= htmlspecialchars($buscar) ?>" placeholder="Buscar habitación, huésped...">
            <select name="estado_filtro">
                <option value="todos" <?= $filtro_estado === "todos" ? "selected" : "" ?>>Todos</option>
                <option value="pendiente" <?= $filtro_estado === "pendiente" ? "selected" : "" ?>>Pendientes</option>
                <option value="confirmada" <?= $filtro_estado === "confirmada" ? "selected" : "" ?>>Confirmadas</option>
                <option value="cancelada" <?= $filtro_estado === "cancelada" ? "selected" : "" ?>>Canceladas</option>
            </select>
            <button class="filter-button" type="submit">Filtrar</button>
        </form>
    </div>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Huésped</th>
                    <th>Habitación</th>
                    <th>Hotel</th>
                    <th>Huéspedes</th>
                    <th>Check-in</th>
                    <th>Check-out</th>
                    <th>Total</th>
                    <th>Estado</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reservas as $reserva): ?>
                <tr>
                    <td>
                        <strong><?= htmlspecialchars($reserva["huesped"]) ?></strong>
                        <small><?= htmlspecialchars($reserva["email"]) ?></small>
                    </td>
                    <td><span class="room-number">#<?= htmlspecialchars($reserva["numero"]) ?></span></td>
                    <td><?= htmlspecialchars($reserva["hotel"]) ?></td>
                    <td><?= (int)$reserva["noches"] ?> noche<?= (int)$reserva["noches"] === 1 ? "" : "s" ?></td>
                    <td><?= date("d M Y", strtotime($reserva["check_in"])) ?></td>
                    <td><?= date("d M Y", strtotime($reserva["check_out"])) ?></td>
                    <td>$<?= number_format((float)$reserva["total"], 2) ?></td>
                    <td><span class="status status-<?= htmlspecialchars($reserva["estado"]) ?>"><?= htmlspecialchars(ucfirst($reserva["estado"])) ?></span></td>
                    <td>
                        <?php if ($reserva["estado"] !== "cancelada"): ?>
                        <form method="POST">
                            <input type="hidden" name="accion" value="cancelar">
                            <input type="hidden" name="id" value="<?= $reserva["id"] ?>">
                            <button class="link-button danger-text" type="submit">Cancelar</button>
                        </form>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>

                <?php if (!$reservas): ?>
                <tr>
                    <td colspan="9" class="empty">No hay reservas que coincidan con la búsqueda.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<section class="panel form-panel" id="nueva-reserva">
    <div class="panel-heading">
        <div>
            <span class="section-label">New Booking</span>
            <h3>Nueva reserva</h3>
        </div>
    </div>

    <form method="POST" class="form-grid">
        <input type="hidden" name="accion" value="crear">

        <div class="field">
            <label>Huésped</label>
            <select name="huesped_id" required>
                <option value="">Seleccionar huésped</option>
                <?php foreach ($huespedes as $huesped): ?>
                <option value="<?= $huesped["id"] ?>"><?= htmlspecialchars($huesped["nombre"]) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="field">
            <label>Habitación</label>
            <select name="habitacion_id" required>
                <option value="">Seleccionar habitación</option>
                <?php foreach ($habitaciones as $habitacion): ?>
                    <?php if ($habitacion["estado"] === "disponible"): ?>
                    <option value="<?= $habitacion["id"] ?>">
                        <?= htmlspecialchars($habitacion["hotel"]) ?> · <?= htmlspecialchars($habitacion["numero"]) ?> · $<?= number_format($habitacion["precio_noche"], 2) ?>/noche
                    </option>
                    <?php endif; ?>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="field">
            <label>Check-in</label>
            <input type="date" name="check_in" required>
        </div>

        <div class="field">
            <label>Check-out</label>
            <input type="date" name="check_out" required>
        </div>

        <div class="field">
            <label>Estado</label>
            <select name="estado">
                <option value="pendiente">Pendiente</option>
                <option value="confirmada">Confirmada</option>
            </select>
        </div>

        <div class="form-actions">
            <button class="button primary" type="submit">Guardar reserva</button>
        </div>
    </form>
</section>
