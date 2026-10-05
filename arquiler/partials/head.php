<?php
include_once __DIR__ . "/../lib/seguridad.php";
iniciarSesionSiHaceFalta();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($tituloPagina ?? 'Panel') ?> | PadelClub</title>
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/bootstrap-icons/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/padel-app.css">
</head>
<body>