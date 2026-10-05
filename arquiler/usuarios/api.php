<?php
header('Content-Type: application/json');
include "../lib/conex.php"; // incluimos clase de conexion
include "../lib/Usuario.php";
$db=new Conex(); //creamos la conexion (instanciamos)
$con=$db->conectar(); // conectamos a la db
//$rs=$con->query($sql); // ejecutamos la consulta
$usuario= new Usuario($con);
// getById id= $_GET['id']
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id = $_GET['id'];
    $rs = $usuario->getById($id);
} else { // getAll
    $rs = $usuario->getALL();
}
$datos=[];
while ($fila= $rs->fetch_assoc()) //loop while que se ejecuta mientras haya
{
    $datos[]=$fila;
}
$respuesta=[
    "status"=>"success",
    "data"=>$datos,
    "total"=>count($datos)
];

echo json_encode($respuesta);
?>
