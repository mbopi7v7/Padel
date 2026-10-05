<?php
include '../lib/seguridad.php';
requiereLogin();
if (esAdmin()) {
    header('Location: index.php');
    exit;
}
include '../lib/conex.php';
include '../lib/Padel.php';

$padel = new Padel((new Conex())->conectar());
$esProfesor = esProfesor();
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$esProfesor || ($_POST['accion'] ?? '') !== 'marcar_asistencia') {
        http_response_code(403);
        exit('No tienes permiso para realizar esta acción.');
    }
    try {
        $padel->marcarAsistencia($_POST['alquiler_id'] ?? 0, $_POST['alumno_id'] ?? 0, $_POST['asistencia'] ?? '');
        header('Location: mi-cuenta.php?asistencia=guardada');
        exit;
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}
$alumnoId = $esProfesor ? 0 : $padel->alumnoIdUsuario(usuarioLogueadoId());

$resumenes = $padel->resumenAsistencias($alumnoId);
$historial = $padel->historialAsistencias($alumnoId);
$estadisticas = [];
$sesionesTotales = 0;
$deudaTotal = 0.0;
while ($fila = $resumenes->fetch_assoc()) {
    $estadisticas[] = $fila;
    $sesionesTotales += (int) $fila['sesiones'];
    $deudaTotal += (float) $fila['deuda'];
}
function formatoGs($monto) { return number_format((float) $monto, 0, ',', '.') . ' Gs.'; }
function nombreDia($fecha) {
    $dias = ['domingo', 'lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado'];
    return $dias[(int) date('w', strtotime($fecha))];
}
include '../partials/template_start.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 app-page-header">
    <div>
        <h2 class="mb-1"><?= $esProfesor ? 'Panel del profesor' : 'Mis asistencias y deuda' ?></h2>
        <p class="text-muted mb-0"><?= $esProfesor ? 'Consulta las asistencias y los saldos pendientes del grupo.' : 'Aquí puedes consultar tus días de asistencia y el saldo pendiente.' ?></p>
    </div>
</div>

<?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
<?php if (isset($_GET['asistencia'])): ?><div class="alert alert-success">Asistencia guardada. La cuota se añadió automáticamente solo para los alumnos presentes.</div><?php endif; ?>

<?php if (!$esProfesor && $alumnoId < 1): ?>
    <div class="alert alert-warning">
        Tu cuenta aún no está asociada a una ficha de alumno. Solicita al administrador que la vincule para consultar tus asistencias y deuda.
    </div>
<?php elseif (!$esProfesor): ?>
    <?php $resumen = $estadisticas[0] ?? ['sesiones' => 0, 'pagado' => 0, 'deuda' => 0]; ?>
    <div class="row g-3 mb-4">
        <div class="col-sm-4"><div class="card h-100"><div class="card-body"><small class="text-muted">Días de asistencia</small><h3 class="mb-0"><?= (int) $resumen['sesiones'] ?></h3></div></div></div>
        <div class="col-sm-4"><div class="card h-100"><div class="card-body"><small class="text-muted">Pagado</small><h3 class="mb-0"><?= formatoGs($resumen['pagado']) ?></h3></div></div></div>
        <div class="col-sm-4"><div class="card border-danger h-100"><div class="card-body"><small class="text-muted">Deuda pendiente</small><h3 class="mb-0"><?= formatoGs($resumen['deuda']) ?></h3></div></div></div>
    </div>
<?php else: ?>
    <div class="row g-3 mb-4">
        <div class="col-sm-6"><div class="card h-100"><div class="card-body"><small class="text-muted">Asistencias registradas</small><h3 class="mb-0"><?= $sesionesTotales ?></h3></div></div></div>
        <div class="col-sm-6"><div class="card border-danger h-100"><div class="card-body"><small class="text-muted">Deuda pendiente del grupo</small><h3 class="mb-0"><?= formatoGs($deudaTotal) ?></h3></div></div></div>
    </div>
    <h4>Resumen por alumno</h4>
    <div class="table-responsive mb-4"><table class="table table-striped align-middle">
        <thead><tr><th>Alumno</th><th>Asistencias</th><th>Pagado</th><th>Deuda</th></tr></thead><tbody>
        <?php foreach ($estadisticas as $fila): ?><tr>
            <td><?= htmlspecialchars($fila['alumno']) ?></td><td><?= (int) $fila['sesiones'] ?></td>
            <td><?= formatoGs($fila['pagado']) ?></td><td class="<?= (float) $fila['deuda'] > 0 ? 'text-danger fw-semibold' : '' ?>"><?= formatoGs($fila['deuda']) ?></td>
        </tr><?php endforeach; ?>
        <?php if (!$estadisticas): ?><tr><td colspan="4" class="text-muted">Todavía no hay alumnos registrados.</td></tr><?php endif; ?>
        </tbody>
    </table></div>
<?php endif; ?>

<?php if ($esProfesor || $alumnoId > 0): ?>
    <h4><?= $esProfesor ? 'Detalle de asistencias' : 'Mis días de asistencia' ?></h4>
    <div class="table-responsive"><table class="table table-hover align-middle">
        <thead class="table-light"><tr><?php if ($esProfesor): ?><th>Alumno</th><?php endif; ?><th>Fecha</th><th>Día</th><th>Horario</th><th>Complejo / cancha</th><th>Asistencia</th><th>Importe</th><th>Pagado</th><th>Pendiente</th><?php if ($esProfesor): ?><th>Confirmar</th><?php endif; ?></tr></thead><tbody>
        <?php if ($historial->num_rows === 0): ?><tr><td colspan="<?= $esProfesor ? 10 : 8 ?>" class="text-muted">Todavía no hay asistencias registradas.</td></tr><?php endif; ?>
        <?php while ($fila = $historial->fetch_assoc()): ?>
            <tr>
                <?php if ($esProfesor): ?><td><?= htmlspecialchars($fila['alumno']) ?></td><?php endif; ?>
                <td><?= date('d/m/Y', strtotime($fila['fecha'])) ?></td>
                <td class="text-capitalize"><?= nombreDia($fila['fecha']) ?></td>
                <td><?= $fila['hora_inicio'] ? substr($fila['hora_inicio'], 0, 5) . ' - ' . substr($fila['hora_fin'], 0, 5) : number_format((float) $fila['horas'], 2, ',', '.') . ' h' ?></td>
                <td><?= htmlspecialchars($fila['complejo'] . ' - ' . $fila['cancha']) ?></td>
                <td><?php if ($fila['asistencia'] === 'programada'): ?><span class="badge text-bg-warning">Pendiente</span><?php elseif ($fila['asistencia'] === 'ausente'): ?><span class="badge text-bg-secondary">Ausente</span><?php else: ?><span class="badge text-bg-success">Asistió</span><?php endif; ?></td>
                <td><?= $fila['asistencia'] === 'programada' ? '<span class="text-muted">Al asistir: ' . formatoGs($fila['importe_programado']) . '</span>' : formatoGs($fila['importe']) ?></td>
                <td><?= formatoGs($fila['pagado']) ?></td>
                <td class="<?= (float) $fila['deuda'] > 0 ? 'text-danger fw-semibold' : '' ?>"><?= formatoGs($fila['deuda']) ?></td>
                <?php if ($esProfesor): ?><td class="attendance-actions"><?php if ($fila['asistencia'] === 'programada'): ?>
                    <form method="post"><input type="hidden" name="accion" value="marcar_asistencia"><input type="hidden" name="alquiler_id" value="<?= (int) $fila['alquiler_id'] ?>"><input type="hidden" name="alumno_id" value="<?= (int) $fila['alumno_id'] ?>"><input type="hidden" name="asistencia" value="asistio"><button class="btn btn-sm btn-success" title="Confirmar asistencia"><i class="bi bi-check-lg"></i></button></form>
                    <form method="post"><input type="hidden" name="accion" value="marcar_asistencia"><input type="hidden" name="alquiler_id" value="<?= (int) $fila['alquiler_id'] ?>"><input type="hidden" name="alumno_id" value="<?= (int) $fila['alumno_id'] ?>"><input type="hidden" name="asistencia" value="ausente"><button class="btn btn-sm btn-outline-secondary" title="Marcar ausente"><i class="bi bi-x-lg"></i></button></form>
                <?php else: ?>Registrada<?php endif; ?></td><?php endif; ?>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table></div>
<?php endif; ?>
<?php include '../partials/template_end.php'; ?>
