<?php
include "../lib/seguridad.php";
requiereAdmin();
include "../lib/conex.php"; // incluimos clase de conexion
include "../lib/Usuario.php";
$db = new Conex(); //creamos la conexion (instanciamos)
$con = $db->conectar(); // conectamos a la db
//$rs=$con->query($sql); // ejecutamos la consulta
$datos = new Usuario($con);
if (isset($_GET['id'])) {
     if($rs = $datos->delete($_GET['id'])) 
        { ;
        header("Location: index.php?ok=3");
        exit(); 
        } else {
            header("Location: index.php?error=3");
            exit();
        }
} else {
    echo "No se recibió un ID válido para eliminar.";
    header("Location: index.php?error=3");
    exit();
}
