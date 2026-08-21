<?php
require_once "config.php";

$seccion = $_GET["seccion"] ?? "reservas";

require_once "includes/header.php";

switch ($seccion) {
    case "huespedes":
        require "huespedes.php";
        break;
    case "habitaciones":
        require "habitaciones.php";
        break;
    case "hoteles":
        require "hoteles.php";
        break;
    default:
        require "reservas.php";
        break;
}

require_once "includes/footer.php";
