<?php
include "../lib/seguridad.php";
requiereAdmin();
include "../lib/conex.php"; // incluimos clase de conexion
include "../lib/Usuario.php";
include "../lib/Padel.php";
$db=new Conex(); //creamos la conexion (instanciamos)
$con=$db->conectar(); // conectamos a la db
$padel = new Padel($con);
$alumnosDisponibles = $padel->alumnos();
//$rs=$con->query($sql); // ejecutamos la consulta
$dato= new Usuario($con);
if (isset($_GET['id']))
     {
    $rs=$dato->getByID($_GET['id']);
    $fila= $rs->fetch_assoc();
     }   
$target="actualizar.php";
$titulo_form="Editar Usuario";   
?>
<?php include_once '../partials/template_start.php';

include "_form.php";
include_once '../partials/template_end.php';
 ?>