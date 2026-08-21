<?php
$mensaje = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nombre = trim($_POST["nombre"] ?? "");
    $apellido = trim($_POST["apellido"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $telefono = trim($_POST["telefono"] ?? "");

    if (!$nombre || !$apellido || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Completa los datos correctamente.";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO huespedes (nombre, apellido, email, telefono) VALUES (?, ?, ?, ?)");
            $stmt->execute([$nombre, $apellido, $email, $telefono]);
            $mensaje = "Huésped registrado correctamente.";
        } catch (PDOException $e) {
            $error = "No se pudo registrar el huésped. Verifica que el correo no esté repetido.";
        }
    }
}

$huespedes = $pdo->query("SELECT * FROM huespedes ORDER BY apellido, nombre")->fetchAll();
?>

<section class="page-heading">
    <div>
        <span class="section-label">Gestión</span>
        <h2>Huéspedes</h2>
        <p>Administra los huéspedes registrados en el sistema.</p>
    </div>
</section>

<?php if ($mensaje): ?>
<div class="alert success"><?= htmlspecialchars($mensaje) ?></div>
<?php endif; ?>

<?php if ($error): ?>
<div class="alert error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<section class="panel form-panel">
    <div class="panel-heading">
        <div>
            <span class="section-label">Registro</span>
            <h3>Nuevo huésped</h3>
        </div>
    </div>

    <form method="POST" class="form-grid">
        <div class="field">
            <label for="nombre">Nombre</label>
            <input id="nombre" type="text" name="nombre" required>
        </div>
        <div class="field">
            <label for="apellido">Apellido</label>
            <input id="apellido" type="text" name="apellido" required>
        </div>
        <div class="field">
            <label for="email">Correo</label>
            <input id="email" type="email" name="email" placeholder="correo@gmail.com" required>
        </div>
        <div class="field">
            <label for="telefono">Teléfono</label>
            <input id="telefono" type="text" name="telefono">
        </div>
        <div class="form-actions">
            <button class="button primary" type="submit">Guardar huésped</button>
        </div>
    </form>
</section>

<section class="panel">
    <div class="panel-heading">
        <div>
            <span class="section-label">Listado</span>
            <h3>Huéspedes registrados</h3>
        </div>
    </div>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Correo</th>
                    <th>Teléfono</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($huespedes as $huesped): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($huesped["nombre"] . " " . $huesped["apellido"]) ?></strong></td>
                    <td><?= htmlspecialchars($huesped["email"]) ?></td>
                    <td><?= htmlspecialchars($huesped["telefono"] ?: "No registrado") ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
