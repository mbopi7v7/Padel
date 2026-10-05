<?php
include '../lib/seguridad.php';
requiereAdmin();
include '../lib/conex.php';
include '../lib/Padel.php';
$padel = new Padel((new Conex())->conectar());
$precioHora = $padel->precioHora();
$error = '';
try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $accion = $_POST['accion'] ?? '';
        if ($accion === 'borrar') {
            $padel->borrarAlquiler($_POST['id'] ?? 0);
        } elseif ($accion === 'pagar_cancha') {
            $monto = (float) ($_POST['monto'] ?? 0);
            $fechaPago = $_POST['fecha_pago'] ?? date('Y-m-d');
            $medioPago = $_POST['medio_pago'] ?? 'efectivo';
            if ($monto <= 0 || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaPago) || !in_array($medioPago, ['efectivo', 'transferencia', 'otro'], true)) throw new InvalidArgumentException('Indique un monto, fecha y medio de pago válidos.');
            $padel->registrarPagoCancha($fechaPago, $monto, $medioPago, $_POST['observacion'] ?? '');
        } elseif ($accion === 'borrar_pago_cancha') {
            $padel->borrarPagoCancha($_POST['id'] ?? 0);
        } elseif ($accion === 'pagar_alumno_semana') {
            $alumnoId = (int) ($_POST['alumno_id'] ?? 0);
            $semanaDesde = $_POST['semana_desde'] ?? '';
            $monto = (float) ($_POST['monto'] ?? 0);
            $medio = $_POST['medio_pago'] ?? '';
            if ($alumnoId < 1 || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $semanaDesde)) throw new InvalidArgumentException('Seleccione un alumno y una semana válidos.');
            $padel->registrarPagoAlumnoSemana($alumnoId, $semanaDesde, $monto, $medio);
        } elseif ($accion === 'guardar') {
            $horas = (float) ($_POST['horas'] ?? 0);
            $fecha = $_POST['fecha'] ?? '';
            $horaInicio = $_POST['hora_inicio'] ?? '';
            $horaFin = $_POST['hora_fin'] ?? '';
            if ($horaInicio !== '' && $horaFin !== '') {
                $inicio = DateTime::createFromFormat('H:i', $horaInicio);
                $fin = DateTime::createFromFormat('H:i', $horaFin);
                if (!$inicio || !$fin || $fin <= $inicio) throw new InvalidArgumentException('La hora de fin debe ser posterior a la hora de inicio.');
                $horas = ($fin->getTimestamp() - $inicio->getTimestamp()) / 3600;
            }
            $alumnoIds = $_POST['alumno_id'] ?? [];
            $importes = $_POST['importe'] ?? [];
            $montosPagados = $_POST['monto_pagado'] ?? [];
            $estados = $_POST['estado_pago'] ?? [];
            $medios = $_POST['medio_pago'] ?? [];
            $periodos = $_POST['periodo'] ?? [];
            $participantes = [];
            foreach ($alumnoIds as $indice => $alumnoId) {
                if ((int) $alumnoId < 1) continue;
                $participantes[] = [
                    'alumno_id' => (int) $alumnoId,
                    'importe' => (float) ($importes[$indice] ?? 0),
                    'monto_pagado' => (float) ($montosPagados[$indice] ?? 0),
                    'estado_pago' => $estados[$indice] ?? 'pendiente',
                    'medio_pago' => $medios[$indice] ?? '',
                    'periodo' => trim($periodos[$indice] ?? '')
                ];
            }
            if (!$participantes || (int) ($_POST['cancha_id'] ?? 0) < 1 || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha) || $horas <= 0 || $horas > 24 || ($horaInicio !== '' && !preg_match('/^\d{2}:\d{2}$/', $horaInicio)) || ($horaFin !== '' && !preg_match('/^\d{2}:\d{2}$/', $horaFin))) throw new InvalidArgumentException('Debe indicar cancha, fecha, horario válido y al menos un alumno.');
            foreach ($participantes as &$participante) {
                if ($participante['estado_pago'] === 'pagado') $participante['monto_pagado'] = $participante['importe'];
                if ($participante['importe'] < 0 || $participante['monto_pagado'] < 0 || $participante['monto_pagado'] > $participante['importe'] || !in_array($participante['estado_pago'], ['pendiente', 'pagado_parcial', 'pagado'], true) || !in_array($participante['medio_pago'], ['', 'efectivo', 'transferencia', 'otro'], true)) throw new InvalidArgumentException('Revise importes, montos pagados, estados y medios de pago.');
            }
            unset($participante);
            $padel->guardarAlquiler($_POST['cancha_id'], $fecha, $horaInicio, $horaFin, $horas, $participantes, (int) ($_POST['id'] ?? 0));
        }
        header('Location: alquileres.php');
        exit;
    }
} catch (Throwable $e) { $error = $e->getMessage(); }
$editando = ['id' => '', 'cancha_id' => '', 'fecha' => date('Y-m-d'), 'hora_inicio' => '', 'hora_fin' => '', 'horas' => ''];
$participantesEditando = [['alumno_id' => '', 'importe' => '', 'monto_pagado' => '', 'estado_pago' => 'pendiente', 'medio_pago' => '', 'periodo' => '']];
if (isset($_GET['editar'])) {
    $fila = $padel->alquiler($_GET['editar'])->fetch_assoc();
    if ($fila) {
        $editando = $fila;
        $participantesEditando = [];
        $rsParticipantes = $padel->participantes($_GET['editar']);
        while ($participante = $rsParticipantes->fetch_assoc()) $participantesEditando[] = $participante;
    }
}
$alumnos = $padel->alumnos();
$canchas = $padel->canchas();
$desde = $_GET['desde'] ?? '';
$hasta = $_GET['hasta'] ?? '';
$alquileres = $padel->alquileres($desde, $hasta);
$pagosCancha = $padel->pagosCancha($desde, $hasta);
$deudasSemanales = $padel->deudasSemanales();
$resumenPeriodo = $padel->resumen($desde, $hasta);
function enlacePeriodo($etiqueta, $desde, $hasta) { return '<a class="btn btn-sm btn-outline-secondary" href="?desde=' . urlencode($desde) . '&hasta=' . urlencode($hasta) . '">' . $etiqueta . '</a>'; }
function diaSemana($fecha) {
    $dias = ['domingo', 'lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado'];
    return $dias[(int) date('w', strtotime($fecha))];
}
include '../partials/template_start.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 app-page-header"><div><h2 class="mb-1">Sesiones y pagos</h2><p class="text-muted mb-0">Controla reservas, cuotas de alumnos y pagos de cancha desde un solo lugar.</p></div><div class="d-flex gap-2"><a href="recurrencias.php" class="btn btn-outline-primary"><i class="bi bi-arrow-repeat"></i> Clases recurrentes</a><a href="index.php" class="btn btn-outline-secondary">Resumen</a></div></div>
<?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
<div class="d-flex flex-wrap gap-2 mb-3"><strong class="me-2 align-self-center">Ver:</strong><?= enlacePeriodo('Todo', '', '') ?><?= enlacePeriodo('Esta semana', date('Y-m-d', strtotime('monday this week')), date('Y-m-d', strtotime('sunday this week'))) ?><?= enlacePeriodo('Este mes', date('Y-m-01'), date('Y-m-t')) ?><form class="d-flex gap-2" method="get"><input class="form-control form-control-sm" type="date" name="desde" value="<?= htmlspecialchars($desde) ?>"><input class="form-control form-control-sm" type="date" name="hasta" value="<?= htmlspecialchars($hasta) ?>"><button class="btn btn-sm btn-primary">Filtrar</button></form></div>
<div class="row g-3 mb-4"><div class="col-md-5"><div class="card border-danger"><div class="card-body"><small class="text-muted">Saldo de cancha del período</small><h3><?= number_format((float) ($resumenPeriodo['general']['costo'] - $resumenPeriodo['general']['pagado_cancha']), 0, ',', '.') ?> Gs.</h3></div></div></div><div class="col-md-7"><form method="post" class="row g-2 align-items-end"><input type="hidden" name="accion" value="pagar_cancha"><div class="col-md-3"><label class="form-label" for="monto">Registrar pago</label><input class="form-control" id="monto" name="monto" type="number" min="1" step="1000" required></div><div class="col-md-3"><label class="form-label" for="fecha_pago">Fecha</label><input class="form-control" id="fecha_pago" name="fecha_pago" type="date" value="<?= date('Y-m-d') ?>" required></div><div class="col-md-3"><label class="form-label" for="medio_pago">Medio</label><select class="form-select" id="medio_pago" name="medio_pago"><option value="efectivo">Efectivo</option><option value="transferencia">Transferencia</option><option value="otro">Otro</option></select></div><div class="col-md-3"><button class="btn btn-success w-100">Marcar pagado</button></div></form></div></div>
<h4>Deuda semanal de alumnos</h4><div class="table-responsive mb-4"><table class="table table-sm table-striped align-middle"><thead><tr><th>Alumno</th><th>Semana</th><th>Deuda</th><th>Registrar pago</th></tr></thead><tbody>
<?php if ($deudasSemanales->num_rows === 0): ?><tr><td colspan="4" class="text-muted">No hay deudas semanales pendientes.</td></tr><?php endif; ?>
<?php while ($deuda = $deudasSemanales->fetch_assoc()): ?><tr><td><?= htmlspecialchars($deuda['alumno']) ?></td><td><?= date('d/m/Y', strtotime($deuda['semana_desde'])) ?> - <?= date('d/m/Y', strtotime($deuda['semana_desde'] . ' +6 days')) ?></td><td><?= number_format((float) $deuda['deuda'], 0, ',', '.') ?> Gs.</td><td><form method="post" class="d-flex flex-wrap gap-2"><input type="hidden" name="accion" value="pagar_alumno_semana"><input type="hidden" name="alumno_id" value="<?= $deuda['alumno_id'] ?>"><input type="hidden" name="semana_desde" value="<?= htmlspecialchars($deuda['semana_desde']) ?>"><input class="form-control form-control-sm" style="max-width: 150px" type="number" name="monto" min="1" step="any" value="<?= htmlspecialchars((string) $deuda['deuda']) ?>" required aria-label="Monto del pago"><select class="form-select form-select-sm" name="medio_pago" style="max-width: 160px" aria-label="Medio de pago"><option value="efectivo">Efectivo</option><option value="transferencia">Transferencia</option><option value="otro">Otro</option></select><button class="btn btn-sm btn-success">Aplicar</button></form></td></tr><?php endwhile; ?>
</tbody></table></div>
<form method="post" class="row g-2 mb-4 app-panel session-form" id="form-sesion" data-precio-hora="<?= htmlspecialchars((string) $precioHora) ?>">
    <input type="hidden" name="accion" value="guardar"><input type="hidden" name="id" value="<?= $editando['id'] ?>">
    <div class="col-md-4"><label class="form-label" for="cancha_id">Cancha</label><select class="form-select" id="cancha_id" name="cancha_id" required><option value="">Seleccionar...</option><?php while ($fila = $canchas->fetch_assoc()): ?><option value="<?= $fila['id'] ?>" <?= $editando['cancha_id'] == $fila['id'] ? 'selected' : '' ?>><?= htmlspecialchars($fila['complejo'] . ' - ' . $fila['nombre']) ?></option><?php endwhile; ?></select></div>
    <div class="col-md-2"><label class="form-label" for="fecha">Fecha</label><input class="form-control" type="date" id="fecha" name="fecha" required value="<?= htmlspecialchars($editando['fecha']) ?>"></div>
    <div class="col-md-2"><label class="form-label" for="hora_inicio">Desde</label><input class="form-control" type="time" id="hora_inicio" name="hora_inicio" value="<?= htmlspecialchars(substr($editando['hora_inicio'] ?? '', 0, 5)) ?>"></div>
    <div class="col-md-2"><label class="form-label" for="hora_fin">Hasta</label><input class="form-control" type="time" id="hora_fin" name="hora_fin" value="<?= htmlspecialchars(substr($editando['hora_fin'] ?? '', 0, 5)) ?>"></div>
    <div class="col-md-2"><label class="form-label" for="horas">Horas</label><input class="form-control" type="number" id="horas" name="horas" min="0.25" max="24" step="any" required value="<?= htmlspecialchars($editando['horas']) ?>"></div>
    <div class="col-12"><label class="form-label">Alumnos y cobros</label><div id="participantes">
    <?php foreach ($participantesEditando as $participante): ?><div class="row g-2 participante mb-2"><div class="col-md-2"><select class="form-select" name="alumno_id[]" required><option value="">Alumno...</option><?php $alumnos->data_seek(0); while ($fila = $alumnos->fetch_assoc()): ?><option value="<?= $fila['id'] ?>" <?= $participante['alumno_id'] == $fila['id'] ? 'selected' : '' ?>><?= htmlspecialchars($fila['apellido'] . ', ' . $fila['nombre']) ?></option><?php endwhile; ?></select></div><div class="col-md-2"><input class="form-control" type="number" name="importe[]" min="0" step="1000" placeholder="A cobrar" value="<?= htmlspecialchars($participante['importe']) ?>" required></div><div class="col-md-2"><input class="form-control" type="number" name="monto_pagado[]" min="0" step="1000" placeholder="Pagado" value="<?= htmlspecialchars($participante['monto_pagado'] ?? '') ?>" required></div><div class="col-md-2"><select class="form-select" name="estado_pago[]"><option value="pendiente" <?= $participante['estado_pago'] === 'pendiente' ? 'selected' : '' ?>>Pendiente</option><option value="pagado_parcial" <?= $participante['estado_pago'] === 'pagado_parcial' ? 'selected' : '' ?>>Parcial</option><option value="pagado" <?= $participante['estado_pago'] === 'pagado' ? 'selected' : '' ?>>Pagado</option></select></div><div class="col-md-1"><select class="form-select" name="medio_pago[]"><option value="">Medio...</option><option value="efectivo" <?= $participante['medio_pago'] === 'efectivo' ? 'selected' : '' ?>>Efectivo</option><option value="transferencia" <?= $participante['medio_pago'] === 'transferencia' ? 'selected' : '' ?>>Transferencia</option><option value="otro" <?= $participante['medio_pago'] === 'otro' ? 'selected' : '' ?>>Otro</option></select></div><div class="col-md-2"><input class="form-control" name="periodo[]" placeholder="Ej. 2026-09" value="<?= htmlspecialchars($participante['periodo'] ?? '') ?>"></div><div class="col-md-1"><button type="button" class="btn btn-outline-danger quitar">X</button></div></div><?php endforeach; ?></div><button type="button" class="btn btn-outline-primary btn-sm" id="agregar">+ Agregar alumno</button></div>
    <div class="col-12"><div id="estimacion-cancha" class="small text-muted mb-2" aria-live="polite"></div><button class="btn btn-primary" type="submit"><?= $editando['id'] ? 'Actualizar' : 'Registrar' ?></button> <?php if ($editando['id']): ?><a class="btn btn-secondary" href="alquileres.php">Cancelar</a><?php endif; ?></div>
</form>
<p class="text-muted">El costo de la cancha se calcula a <?= number_format($precioHora, 0, ',', '.') ?> Gs. por hora. El importe cobrado a cada alumno se registra por separado y puede ser mensual. El sistema avisa si el horario se cruza con otra reserva de la misma cancha.</p>
<h4>Pagos de cancha registrados</h4><div class="table-responsive mb-4"><table class="table table-sm table-striped"><thead><tr><th>Fecha</th><th>Medio</th><th>Monto</th><th></th></tr></thead><tbody><?php while ($pago = $pagosCancha->fetch_assoc()): ?><tr><td><?= htmlspecialchars($pago['fecha']) ?></td><td><?= htmlspecialchars($pago['medio_pago']) ?></td><td><?= number_format((float) $pago['monto'], 0, ',', '.') ?> Gs.</td><td><form method="post" onsubmit="return confirm('¿Eliminar este pago?');"><input type="hidden" name="accion" value="borrar_pago_cancha"><input type="hidden" name="id" value="<?= $pago['id'] ?>"><button class="btn btn-sm btn-outline-danger">Borrar</button></form></td></tr><?php endwhile; ?></tbody></table></div>
<div class="table-responsive"><table class="table table-hover align-middle"><thead class="table-light"><tr><th>Fecha</th><th>Día</th><th>Horario</th><th>Cancha</th><th>Alumnos</th><th>Deuda cancha</th><th>A cobrar</th><th>Pagado</th><th>Acciones</th></tr></thead><tbody>
<?php while ($fila = $alquileres->fetch_assoc()): ?><tr><td><?= htmlspecialchars($fila['fecha']) ?></td><td class="text-capitalize"><?= diaSemana($fila['fecha']) ?></td><td><?= $fila['hora_inicio'] ? substr($fila['hora_inicio'], 0, 5) . ' - ' . substr($fila['hora_fin'], 0, 5) : $fila['horas'] . ' h' ?></td><td><?= htmlspecialchars($fila['complejo'] . ' - ' . $fila['cancha']) ?></td><td><span class="badge text-bg-secondary me-1"><?= $fila['cantidad_alumnos'] ?></span><?= htmlspecialchars($fila['alumnos'] ?? '') ?><?php if ((int) $fila['asistencias_pendientes'] > 0): ?><br><span class="badge text-bg-warning mt-1"><?= (int) $fila['asistencias_pendientes'] ?> por confirmar</span><?php endif; ?></td><td><?= number_format((float) $fila['costo'], 0, ',', '.') ?> Gs.</td><td><?= number_format((float) $fila['ingresos'], 0, ',', '.') ?> Gs.</td><td><?= number_format((float) $fila['cobrado'], 0, ',', '.') ?> Gs.</td><td class="text-nowrap"><?php if ((int) $fila['asistencias_pendientes'] === 0): ?><a class="btn btn-sm btn-outline-warning" href="?editar=<?= (int) $fila['id'] ?>">Editar</a><?php else: ?><span class="small text-muted">Esperando profesor</span><?php endif; ?> <form class="d-inline" method="post" onsubmit="return confirm('¿Eliminar esta sesión?');"><input type="hidden" name="accion" value="borrar"><input type="hidden" name="id" value="<?= (int) $fila['id'] ?>"><button class="btn btn-sm btn-outline-danger">Borrar</button></form></td></tr><?php endwhile; ?>
</tbody></table></div><a href="index.php" class="btn btn-outline-secondary">Volver al resumen</a>
<script>
const participantes = document.getElementById('participantes');
const plantilla = participantes.querySelector('.participante').cloneNode(true);
const horas = document.getElementById('horas');
const estimacion = document.getElementById('estimacion-cancha');
const precioHora = Number(document.getElementById('form-sesion').dataset.precioHora);
function actualizarHoras() {
    const inicio = document.getElementById('hora_inicio').value;
    const fin = document.getElementById('hora_fin').value;
    if (inicio && fin) {
        const diferencia = (new Date(`1970-01-01T${fin}`) - new Date(`1970-01-01T${inicio}`)) / 3600000;
        if (diferencia > 0) horas.value = diferencia;
    }
    const cantidad = Number(horas.value);
    estimacion.textContent = cantidad > 0
        ? `Cancha: ${new Intl.NumberFormat('es-PY').format(Math.round(cantidad * precioHora))} Gs. por sesión (${cantidad.toLocaleString('es-PY')} h).`
        : 'Completa el horario o la duración para estimar el costo de cancha.';
}
horas.addEventListener('input', actualizarHoras);
actualizarHoras();
document.getElementById('hora_inicio').addEventListener('change', actualizarHoras);
document.getElementById('hora_fin').addEventListener('change', actualizarHoras);
document.getElementById('agregar').addEventListener('click', () => {
    const nuevo = plantilla.cloneNode(true);
    nuevo.querySelectorAll('input').forEach(input => input.value = '');
    nuevo.querySelectorAll('select').forEach(select => select.selectedIndex = 0);
    participantes.appendChild(nuevo);
});
participantes.addEventListener('click', event => {
    if (event.target.classList.contains('quitar') && participantes.querySelectorAll('.participante').length > 1) event.target.closest('.participante').remove();
});
</script>
<?php include '../partials/template_end.php'; ?>