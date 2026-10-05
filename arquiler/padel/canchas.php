<?php
include '../lib/seguridad.php';
requiereAdmin();
include '../lib/conex.php';
include '../lib/Padel.php';
$padel = new Padel((new Conex())->conectar());
$error = '';
try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $accion = $_POST['accion'] ?? '';
        if ($accion === 'borrar') {
            $padel->borrarCancha($_POST['id'] ?? 0);
        } elseif ($accion === 'guardar_complejo') {
            $nombre = trim($_POST['nombre_complejo'] ?? '');
            if ($nombre === '' || strlen($nombre) > 100) throw new InvalidArgumentException('El nombre del complejo es obligatorio y no debe superar 100 caracteres.');
            $padel->guardarComplejo($nombre, (int) ($_POST['complejo_id'] ?? 0));
        } elseif ($accion === 'borrar_complejo') {
            $padel->borrarComplejo($_POST['complejo_id'] ?? 0);
        } elseif ($accion === 'guardar') {
            $nombre = trim($_POST['nombre'] ?? '');
            if ($nombre === '' || strlen($nombre) > 100) throw new InvalidArgumentException('El nombre es obligatorio y no debe superar 100 caracteres.');
            $padel->guardarCancha($nombre, (int) ($_POST['complejo_id'] ?? 0), (int) ($_POST['id'] ?? 0));
        }
        header('Location: canchas.php');
        exit;
    }
} catch (Throwable $e) { $error = $e->getMessage(); }
$editando = ['id' => '', 'nombre' => ''];
$complejoEditando = ['id' => '', 'nombre' => ''];
if (isset($_GET['editar'])) {
    $fila = $padel->cancha($_GET['editar'])->fetch_assoc();
    if ($fila) $editando = $fila;
}
if (isset($_GET['editar_complejo'])) {
    $fila = $padel->complejo($_GET['editar_complejo'])->fetch_assoc();
    if ($fila) $complejoEditando = $fila;
}
$canchas = $padel->canchas();
$complejos = $padel->complejos();
include '../partials/template_start.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 app-page-header">
    <div><h2 class="mb-1">Complejos y canchas</h2><p class="text-muted mb-0">Organizá las canchas dentro de cada complejo y evita nombres duplicados.</p></div>
    <a href="index.php" class="btn btn-outline-secondary">Volver al resumen</a>
</div>
<?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
<div class="row g-4 mb-4">
<section class="col-lg-5">
<div class="card h-100"><div class="card-body">
<h3 class="card-title">Complejos</h3>
<form method="post" class="row g-2 mb-3">
    <input type="hidden" name="accion" value="guardar_complejo"><input type="hidden" name="complejo_id" value="<?= htmlspecialchars((string) $complejoEditando['id']) ?>">
    <div class="col-12"><label class="form-label" for="nombre_complejo">Nombre del complejo</label><input class="form-control" id="nombre_complejo" name="nombre_complejo" maxlength="100" required value="<?= htmlspecialchars($complejoEditando['nombre']) ?>"></div>
    <div class="col-12"><button class="btn btn-primary" type="submit"><?= $complejoEditando['id'] ? 'Actualizar complejo' : 'Agregar complejo' ?></button> <?php if ($complejoEditando['id']): ?><a class="btn btn-secondary" href="canchas.php">Cancelar</a><?php endif; ?></div>
</form>
<div class="table-responsive"><table class="table table-sm align-middle mb-0"><thead><tr><th>Complejo</th><th>Canchas</th><th>Acciones</th></tr></thead><tbody>
<?php while ($complejo = $complejos->fetch_assoc()): ?><tr><td><?= htmlspecialchars($complejo['nombre']) ?></td><td><?= (int) $complejo['cantidad_canchas'] ?></td><td class="text-nowrap"><a class="btn btn-sm btn-outline-warning" href="?editar_complejo=<?= (int) $complejo['id'] ?>">Editar</a> <form class="d-inline" method="post" onsubmit="return confirm('¿Eliminar este complejo? Solo se pueden eliminar complejos sin canchas.');"><input type="hidden" name="accion" value="borrar_complejo"><input type="hidden" name="complejo_id" value="<?= (int) $complejo['id'] ?>"><button class="btn btn-sm btn-outline-danger" <?= (int) $complejo['cantidad_canchas'] > 0 ? 'disabled title="El complejo tiene canchas"' : '' ?>>Borrar</button></form></td></tr><?php endwhile; ?>
</tbody></table></div>
</div></div>
</section>
<section class="col-lg-7">
<div class="card"><div class="card-body">
<h3 class="card-title"><?= $editando['id'] ? 'Editar cancha' : 'Agregar cancha' ?></h3>
<form method="post" class="row g-2 mb-3">
    <input type="hidden" name="accion" value="guardar"><input type="hidden" name="id" value="<?= $editando['id'] ?>">
    <div class="col-md-6"><label class="form-label" for="nombre">Nombre de la cancha</label><input class="form-control" id="nombre" name="nombre" maxlength="100" required value="<?= htmlspecialchars($editando['nombre']) ?>"></div>
    <div class="col-md-6"><label class="form-label" for="complejo_id">Complejo</label><select class="form-select" id="complejo_id" name="complejo_id" required><option value="">Seleccionar...</option><?php $complejos->data_seek(0); while ($complejo = $complejos->fetch_assoc()): ?><option value="<?= (int) $complejo['id'] ?>" <?= (int) ($editando['complejo_id'] ?? 0) === (int) $complejo['id'] ? 'selected' : '' ?>><?= htmlspecialchars($complejo['nombre']) ?></option><?php endwhile; ?></select></div>
    <div class="col-12"><button class="btn btn-primary" type="submit"><?= $editando['id'] ? 'Actualizar cancha' : 'Agregar cancha' ?></button> <?php if ($editando['id']): ?><a class="btn btn-secondary" href="canchas.php">Cancelar</a><?php endif; ?></div>
</form>
</div></div>
</section>
</div>
<h4>Canchas registradas</h4><div class="table-responsive"><table class="table table-striped align-middle"><thead><tr><th>Complejo</th><th>Cancha</th><th>Acciones</th></tr></thead><tbody>
<?php while ($fila = $canchas->fetch_assoc()): ?><tr><td><?= htmlspecialchars($fila['complejo']) ?></td><td><?= htmlspecialchars($fila['nombre']) ?></td><td><a class="btn btn-sm btn-outline-warning" href="?editar=<?= (int) $fila['id'] ?>">Editar</a> <form class="d-inline" method="post" onsubmit="return confirm('¿Eliminar esta cancha?');"><input type="hidden" name="accion" value="borrar"><input type="hidden" name="id" value="<?= (int) $fila['id'] ?>"><button class="btn btn-sm btn-outline-danger">Borrar</button></form></td></tr><?php endwhile; ?>
</tbody></table></div>
<?php include '../partials/template_end.php'; ?>