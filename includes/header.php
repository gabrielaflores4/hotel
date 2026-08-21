<?php
$seccion_actual = $_GET['seccion'] ?? 'reservas';
$titulos = [
    'reservas' => 'Reservas',
    'huespedes' => 'Huéspedes',
    'habitaciones' => 'Habitaciones',
    'hoteles' => 'Hoteles'
];
$titulo_actual = $titulos[$seccion_actual] ?? 'Reservas';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($titulo_actual) ?> | Hotel Reserva</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="layout">
    <aside class="sidebar">
        <div class="brand">
            <div class="brand-mark">H</div>
            <div>
                <strong>Hotel</strong>
                <span>Administración</span>
            </div>
        </div>
        <nav class="navigation">
            <a class="<?= $seccion_actual === 'reservas' ? 'active' : '' ?>" href="index.php?seccion=reservas">Reservas</a>
            <a class="<?= $seccion_actual === 'huespedes' ? 'active' : '' ?>" href="index.php?seccion=huespedes">Huéspedes</a>
            <a class="<?= $seccion_actual === 'habitaciones' ? 'active' : '' ?>" href="index.php?seccion=habitaciones">Habitaciones</a>
            <a class="<?= $seccion_actual === 'hoteles' ? 'active' : '' ?>" href="index.php?seccion=hoteles">Hoteles</a>
        </nav>
    </aside>

    <main class="main">
        <header class="topbar">
            <div>
                <span class="top-label">Hotel Reserva</span>
                <h1><?= htmlspecialchars($titulo_actual) ?></h1>
            </div>
        </header>
