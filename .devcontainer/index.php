<?php
echo "<h1>¡Hola OzBa! 🚀</h1>";
?>
<?php

// Obtener todos los elementos de la carpeta actual
$items = scandir(__DIR__);

// Filtrar solo directorios válidos
$carpetas = [];

foreach ($items as $item) {

    if ($item === '.' || $item === '..') {
        continue;
    }

    // ignorar .git y otros archivos ocultos
    if ($item[0] === '.') {
        continue;
    }

    if (is_dir($item)) {
        $carpetas[] = $item;
    }
}

// ordenar carpetas
sort($carpetas);

?>

<!DOCTYPE html>
<html lang="es">

<head>

<meta charset="UTF-8">

<title>Diseño y Programación Web II</title>

<style>

body{
    font-family: Arial, Helvetica, sans-serif;
    margin:40px;
    background:#f4f6f8;
}

h1{
    color:#2c3e50;
}

ul{
    list-style:none;
    padding:0;
}

li{
    margin:8px 0;
}

a{
    text-decoration:none;
    color:#1a73e8;
    font-weight:bold;
}

a:hover{
    text-decoration:underline;
}

.carpeta{
    background:white;
    padding:10px;
    border-radius:6px;
    border:1px solid #ddd;
}

</style>

</head>

<body>

<h1>Diseño y Programación Web II</h1>

<p>
Repositorio didáctico de la materia.<br>
Cada carpeta corresponde al desarrollo realizado en una clase.
</p>

<h2>Clases disponibles</h2>

<ul>

<?php foreach ($carpetas as $c): ?>

<li class="carpeta">
    <a href="<?php echo $c; ?>" target="_blank">
        Clase <?php echo $c; ?>
    </a>
</li>

<?php endforeach; ?>

</ul>

</body>
</html>