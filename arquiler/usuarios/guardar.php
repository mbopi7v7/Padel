<?php
include "../lib/seguridad.php";
requiereAdmin();
include "../lib/conex.php"; // incluimos clase de conexion
include "../lib/Usuario.php";
$db=new Conex(); //creamos la conexion (instanciamos)
$con=$db->conectar(); // conectamos a la db
//$rs=$con->query($sql); // ejecutamos la consulta
$datos= new Usuario($con);
$errores = [];
// $_REQUEST $_GET $_POST
 if(isset($_POST)) {
    //print_r($_POST);
   // Validaciones
    if (empty($_POST['nombre']) || strlen($_POST['nombre']) > 100) {
        $errores[] = "El nombre es obligatorio y no debe superar 100 caracteres.";
    }
        if (empty($_POST['apellido']) || strlen($_POST['apellido']) > 100) {
        $errores[] = "El apellido es obligatorio y no debe superar 100 caracteres.";
    }
        if (strlen($_POST['doc'] ?? '') > 191) {
            $errores[] = "El número de documento no debe superar 191 caracteres.";
    }
        if (empty($_POST['mail']) || strlen($_POST['mail']) > 191) {
        $errores[] = "El correo electrónico es obligatorio y no debe superar 191 caracteres.";
    }
    if (!filter_var($_POST['mail'], FILTER_VALIDATE_EMAIL)) {
    $errores[] = "El correo electrónico no tiene un formato válido.";
    }
    if (!empty($_POST['fenac']) && !DateTime::createFromFormat('Y-m-d', $_POST['fenac'])) {
        $errores[] = "La fecha de nacimiento no es válida.";
    }



    if (!empty($_POST['direccion']) && strlen($_POST['direccion']) > 191) {
        $errores[] = "El lugar no debe superar 191 caracteres.";
    }


    if (!in_array($_POST['rol'] ?? '', ['usuario', 'profesor', 'admin'], true)) {
        $errores[] = "El perfil seleccionado no es válido.";
    }
    if (($_POST['rol'] ?? '') === 'usuario' && !empty($_POST['alumno_id'])) {
        $alumnoId = filter_var($_POST['alumno_id'], FILTER_VALIDATE_INT);
        if ($alumnoId === false || $alumnoId < 1 || !$datos->alumnoDisponible($alumnoId)) {
            $errores[] = "La ficha de alumno seleccionada no existe o ya está vinculada a otra cuenta.";
        }
    }
//
if (empty($_POST['contrasena'])) {
    $errores[] = "La contraseña es obligatoria.";
}





//


    // Si hay errores, mostrarlos
if (empty($errores)) {

    $rs = $datos->insert($_POST);
    if (!$rs) {
        header("Location: nuevo.php?error=1");
        exit();
    }

    header("Location: index.php?ok=1");
    exit();

} else {
    header("Location: nuevo.php?error=2");
    exit();
}
    }


?>