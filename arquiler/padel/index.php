<?php
include '../lib/seguridad.php';
requiereLogin();
if (!esAdmin()) {
    header('Location: mi-cuenta.php');
    exit;
}
include '../lib/conex.php';
include '../lib/Padel.php';
$con = (new Conex())->conectar();
$padel = new Padel($con);
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'guardar_precio_hora') {
    try {
        $padel->guardarPrecioHora($_POST['precio_hora'] ?? '');
        header('Location: index.php?precio_actualizado=1');
        exit;
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}
$precioHora = $padel->precioHora();
$desde = $_GET['desde'] ?? '';
$hasta = $_GET['hasta'] ?? '';
$alumnoId = (int) ($_GET['alumno_id'] ?? 0);
$resumen = $padel->resumen($desde, $hasta, $alumnoId);
$alumnosFiltro = $padel->alumnos();
$alquileres = $padel->alquileres($desde, $hasta);
function gs($valor) { return number_format((float) $valor, 0, ',', '.') . ' Gs.'; }
function diaSemana($fecha) {
	$dias = ['domingo', 'lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado'];
	return $dias[(int) date('w', strtotime($fecha))];
}
include '../partials/template_start.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 app-page-header">
    <div><h2 class="mb-1">Gestión de pádel</h2><p class="text-muted">Sesiones, asistencias y cuentas del club en un solo lugar.</p></div>
    <a href="alquileres.php#form-sesion" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Registrar sesión</a>
</div>
<?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
<?php if (isset($_GET['precio_actualizado'])): ?><div class="alert alert-success">El precio por hora se actualizó. Se aplicará a las sesiones nuevas; las existentes conservan su precio.</div><?php endif; ?>
<form method="post" class="card card-body mb-3"><input type="hidden" name="accion" value="guardar_precio_hora"><div class="row g-2 align-items-end"><div class="col-md-5"><label class="form-label" for="precio_hora">Precio de alquiler por hora (Gs.)</label><input class="form-control" id="precio_hora" name="precio_hora" type="number" min="1" max="9999999999.99" step="any" value="<?= htmlspecialchars((string) $precioHora) ?>" required></div><div class="col-md-4"><button class="btn btn-primary" type="submit">Guardar precio</button></div><div class="col-12"><small class="text-muted">El cambio se usará al registrar nuevas sesiones. Editar una sesión existente conserva su precio original por hora.</small></div></div></form>
<div class="quick-actions">
    <a href="recurrencias.php" class="btn btn-primary"><i class="bi bi-arrow-repeat"></i> Automatizar clases semanales</a>
    <a href="alquileres.php" class="btn btn-outline-primary"><i class="bi bi-cash-coin"></i> Gestionar pagos</a>
    <a href="alumnos.php" class="btn btn-outline-primary"><i class="bi bi-people"></i> Ver alumnos y deudas</a>
    <a href="canchas.php" class="btn btn-outline-primary"><i class="bi bi-bounding-box"></i> Complejos y canchas</a>
</div>
<div class="d-flex flex-wrap gap-2 mb-3"><strong class="me-2 align-self-center">Período:</strong><a class="btn btn-sm btn-outline-secondary" href="index.php">Todo</a><a class="btn btn-sm btn-outline-secondary" href="?desde=<?= date('Y-m-d', strtotime('monday this week')) ?>&hasta=<?= date('Y-m-d', strtotime('sunday this week')) ?>">Esta semana</a><a class="btn btn-sm btn-outline-secondary" href="?desde=<?= date('Y-m-01') ?>&hasta=<?= date('Y-m-t') ?>">Este mes</a><form class="d-flex flex-wrap gap-2" method="get"><input class="form-control form-control-sm" type="date" name="desde" value="<?= htmlspecialchars($desde) ?>"><input class="form-control form-control-sm" type="date" name="hasta" value="<?= htmlspecialchars($hasta) ?>"><select class="form-select form-select-sm" name="alumno_id" aria-label="Filtrar por alumno"><option value="0">Todos los alumnos</option><?php while ($alumno = $alumnosFiltro->fetch_assoc()): ?><option value="<?= $alumno['id'] ?>" <?= $alumnoId === (int) $alumno['id'] ? 'selected' : '' ?>><?= htmlspecialchars($alumno['apellido'] . ', ' . $alumno['nombre']) ?></option><?php endwhile; ?></select><button class="btn btn-sm btn-primary">Filtrar</button></form></div>
<div class="row g-3 mb-4">
<div class="col-md-3"><div class="app-stat border-danger"><div class="card-body"><small>Saldo de canchas</small><h3><?= gs((float) $resumen['general']['costo'] - (float) $resumen['general']['pagado_cancha']) ?></h3><small>Pagado: <?= gs($resumen['general']['pagado_cancha']) ?></small></div></div></div>
<div class="col-md-3"><div class="app-stat border-success"><div class="card-body"><small>Por cobrar</small><h3><?= gs((float) $resumen['general']['ingresos'] - (float) $resumen['general']['cobrado']) ?></h3><small>Cuotas activadas por asistencia</small></div></div></div>
<div class="col-md-3"><div class="app-stat border-info"><div class="card-body"><small>Cobrado</small><h3><?= gs($resumen['general']['cobrado']) ?></h3><small>Ingresos recibidos</small></div></div></div>
<div class="col-md-3"><div class="app-stat border-secondary"><div class="card-body"><small>Horas / sesiones</small><h3><?= number_format((float) $resumen['general']['horas'], 2, ',', '.') ?> / <?= $resumen['general']['registros'] ?></h3><small>En el período seleccionado</small></div></div></div>
</div>
<h4>Ganancia y pagos por alumno</h4><div class="table-responsive"><table class="table table-striped"><thead><tr><th>Alumno</th><th>Horas</th><th>Esperado</th><th>Cobrado</th><th>Pendiente</th><th>Ganancia neta</th></tr></thead><tbody>
<?php while ($fila = $resumen['alumnos']->fetch_assoc()): ?><tr><td><?= htmlspecialchars($fila['nombre']) ?></td><td><?= $fila['horas'] ?></td><td><?= gs($fila['ingresos']) ?></td><td><?= gs($fila['cobrado']) ?></td><td><?= gs((float) $fila['ingresos'] - (float) $fila['cobrado']) ?></td><td><?= gs($fila['ganancia']) ?></td></tr><?php endwhile; ?></tbody></table></div>
<h4>Totales por cancha</h4><div class="table-responsive"><table class="table table-striped"><thead><tr><th>Complejo</th><th>Cancha</th><th>Horas</th><th>Total</th></tr></thead><tbody>
<?php while ($fila = $resumen['canchas']->fetch_assoc()): ?><tr><td><?= htmlspecialchars($fila['complejo']) ?></td><td><?= htmlspecialchars($fila['nombre']) ?></td><td><?= $fila['horas'] ?></td><td><?= gs($fila['costo']) ?></td></tr><?php endwhile; ?></tbody></table></div>
<h4>Últimas sesiones</h4><div class="table-responsive"><table class="table table-striped align-middle"><thead><tr><th>Fecha</th><th>Día</th><th>Horario</th><th>Complejo / cancha</th><th>Alumnos</th><th>Deuda cancha</th><th>Ingresos</th></tr></thead><tbody>
<?php while ($fila = $alquileres->fetch_assoc()): ?><tr><td><?= htmlspecialchars($fila['fecha']) ?></td><td class="text-capitalize"><?= diaSemana($fila['fecha']) ?></td><td><?= $fila['hora_inicio'] ? substr($fila['hora_inicio'], 0, 5) . ' - ' . substr($fila['hora_fin'], 0, 5) : $fila['horas'] . ' h' ?></td><td><?= htmlspecialchars($fila['complejo'] . ' - ' . $fila['cancha']) ?></td><td><span class="badge text-bg-secondary me-1"><?= $fila['cantidad_alumnos'] ?></span><?= htmlspecialchars($fila['alumnos'] ?? '') ?></td><td><?= gs($fila['costo']) ?></td><td><?= gs($fila['ingresos']) ?></td></tr><?php endwhile; ?></tbody></table></div>
<?php include '../partials/template_end.php'; ?>