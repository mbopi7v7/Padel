<?php
include "../lib/seguridad.php";
requiereAdmin();
include "../lib/conex.php";
include "../lib/Padel.php";
$padel = new Padel((new Conex())->conectar());
$alumnosDisponibles = $padel->alumnos();
$target="guardar.php";
$titulo_form="Registrar Usuario";

$fila = [
    "id" => "",
    "nombre" => "",
    "apellido" => "",
    "mail" => "",
    "telefono" => "",
        "doc" => "",
    "fenac" => "",
        "direccion" => "",
        "contrasena" => "",
    "esadmin" => 0,
    "rol" => "usuario",
    "alumno_id" => ""
];
if (isset($_GET['alumno_id']) && ctype_digit((string) $_GET['alumno_id'])) {
    $alumnoIdSolicitado = (int) $_GET['alumno_id'];
    while ($alumno = $alumnosDisponibles->fetch_assoc()) {
        if ((int) $alumno['id'] === $alumnoIdSolicitado && empty($alumno['usuario_id'])) {
            $fila['alumno_id'] = $alumnoIdSolicitado;
            break;
        }
    }
    $alumnosDisponibles->data_seek(0);
}

include_once '../partials/template_start.php'; 
include "_form.php";
include_once '../partials/template_end.php';

?>