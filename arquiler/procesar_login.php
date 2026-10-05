<?php

include "lib/conex.php";
include "lib/Usuario.php";
include "lib/seguridad.php";

if (!isset($_POST['mail']) || !isset($_POST['contrasena'])) {
    header("Location: login.php?error=1");
    exit();
}

$mail = trim($_POST['mail']);
$contrasena = $_POST['contrasena'];

if (empty($mail) || empty($contrasena)) {
    header("Location: login.php?error=1");
    exit();
}

if (!filter_var($mail, FILTER_VALIDATE_EMAIL)) {
    header("Location: login.php?error=1");
    exit();
}

$db = new Conex();
$con = $db->conectar();

$usuario = new Usuario($con);
$rs = $usuario->getByMail($mail);

if ($rs->num_rows == 0) {
    header("Location: login.php?error=1");
    exit();
}

$fila = $rs->fetch_assoc();

if (password_verify($contrasena, $fila['contrasena'])) {

    crearSesionUsuario($fila);

    header("Location: padel/");
    exit();

} else {
    header("Location: login.php?error=1");
    exit();
}
