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
            $padel->borrarAlumno($_POST['id'] ?? 0);
        } elseif ($accion === 'guardar') {
            $nombre = trim($_POST['nombre'] ?? '');
            $apellido = trim($_POST['apellido'] ?? '');
            if ($nombre === '' || $apellido === '' || strlen($nombre) > 100 || strlen($apellido) > 100) throw new InvalidArgumentException('Nombre y apellido son obligatorios y no deben superar 100 caracteres.');
            $padel->guardarAlumno($nombre, $apellido, (int) ($_POST['id'] ?? 0));
        }
        header('Location: alumnos.php');
        exit;
    }
} catch (Throwable $e) { $error = $e->getMessage(); }
$editando = ['id' => '', 'nombre' => '', 'apellido' => ''];
if (isset($_GET['editar'])) {
    $fila = $padel->alumno($_GET['editar'])->fetch_assoc();
    if ($fila) $editando = $fila;
}
$alumnos = $padel->alumnos();
$resumenesAlumnos = $padel->resumenAsistencias();
$metricasAlumnos = [];
while ($resumen = $resumenesAlumnos->fetch_assoc()) $metricasAlumnos[(int) $resumen['alumno_id']] = $resumen;
include '../partials/template_start.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 app-page-header">
    <div><h2 class="mb-1">Alumnos</h2><p class="text-muted">Administra el grupo y consulta asistencias y saldos en un vistazo.</p></div>
    <a href="../usuarios/nuevo.php" class="btn btn-outline-primary"><i class="bi bi-person-plus"></i> Crear cuenta de acceso</a>
</div>
<?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
<div class="app-panel">
<h3 class="card-title"><?= $editando['id'] ? 'Editar alumno' : 'Registrar alumno' ?></h3>
<form method="post" class="row g-2">
    <input type="hidden" name="accion" value="guardar"><input type="hidden" name="id" value="<?= $editando['id'] ?>">
    <div class="col-md-4"><label class="form-label" for="nombre">Nombre</label><input class="form-control" id="nombre" name="nombre" maxlength="100" required value="<?= htmlspecialchars($editando['nombre']) ?>"></div>
    <div class="col-md-4"><label class="form-label" for="apellido">Apellido</label><input class="form-control" id="apellido" name="apellido" maxlength="100" required value="<?= htmlspecialchars($editando['apellido']) ?>"></div>
    <div class="col-md-4 align-self-end"><button class="btn btn-primary" type="submit"><?= $editando['id'] ? 'Actualizar' : 'Agregar' ?></button> <?php if ($editando['id']): ?><a class="btn btn-secondary" href="alumnos.php">Cancelar</a><?php endif; ?></div>
</form>
</div>
<h4>Seguimiento del grupo</h4>
<div class="table-responsive"><table class="table table-striped"><thead><tr><th>Alumno</th><th>Asistencias</th><th>Pagado</th><th>Deuda por asistencia</th><th>Acciones</th></tr></thead><tbody>
<?php if ($alumnos->num_rows === 0): ?><tr><td colspan="5" class="text-muted">Todavía no hay alumnos registrados.</td></tr><?php endif; ?>
<?php while ($fila = $alumnos->fetch_assoc()): $metrica = $metricasAlumnos[(int) $fila['id']] ?? ['sesiones' => 0, 'pagado' => 0, 'deuda' => 0]; ?><tr>
    <td><strong><?= htmlspecialchars($fila['apellido'] . ', ' . $fila['nombre']) ?></strong></td>
    <td><?= (int) $metrica['sesiones'] ?></td>
    <td><?= number_format((float) $metrica['pagado'], 0, ',', '.') ?> Gs.</td>
    <td class="<?= (float) $metrica['deuda'] > 0 ? 'text-danger fw-semibold' : '' ?>"><?= number_format((float) $metrica['deuda'], 0, ',', '.') ?> Gs.</td>
    <td class="text-nowrap"><?php if (!empty($fila['usuario_id'])): ?><a class="btn btn-sm btn-outline-primary" href="../usuarios/editar.php?id=<?= (int) $fila['usuario_id'] ?>" title="Editar cuenta vinculada"><i class="bi bi-person-check"></i> Cuenta</a><?php else: ?><a class="btn btn-sm btn-outline-primary" href="../usuarios/nuevo.php?alumno_id=<?= (int) $fila['id'] ?>" title="Crear cuenta vinculada"><i class="bi bi-person-plus"></i> Vincular</a><?php endif; ?> <a class="btn btn-sm btn-outline-warning" href="?editar=<?= (int) $fila['id'] ?>">Editar</a> <form class="d-inline" method="post" onsubmit="return confirm('¿Eliminar este alumno y sus asistencias?');"><input type="hidden" name="accion" value="borrar"><input type="hidden" name="id" value="<?= (int) $fila['id'] ?>"><button class="btn btn-sm btn-outline-danger">Borrar</button></form></td>
</tr><?php endwhile; ?>
</tbody></table></div>
<?php include '../partials/template_end.php'; ?>