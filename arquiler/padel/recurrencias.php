<?php
include '../lib/seguridad.php';
requiereAdmin();
include '../lib/conex.php';
include '../lib/Padel.php';

$padel = new Padel((new Conex())->conectar());
$error = '';
$dias = [1 => 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
$editando = ['id' => 0, 'nombre' => '', 'cancha_id' => '', 'dia_semana' => 1, 'hora_inicio' => '16:00', 'hora_fin' => '17:00', 'importe_asistencia' => ''];
$alumnosEditando = [];
if (isset($_GET['editar'])) {
    $editando = array_merge($editando, $padel->recurrencia($_GET['editar'])->fetch_assoc() ?: []);
    $seleccionados = $padel->alumnosRecurrencia($_GET['editar']);
    while ($fila = $seleccionados->fetch_assoc()) $alumnosEditando[] = (int) $fila['alumno_id'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $accion = $_POST['accion'] ?? '';
        if ($accion === 'guardar') {
            $ids = $_POST['alumno_id'] ?? [];
            if (!is_array($ids)) throw new InvalidArgumentException('Selecciona al menos un alumno.');
            $padel->guardarRecurrencia(
                $_POST['nombre'] ?? '',
                $_POST['cancha_id'] ?? 0,
                $_POST['dia_semana'] ?? 0,
                $_POST['hora_inicio'] ?? '',
                $_POST['hora_fin'] ?? '',
                $_POST['importe_asistencia'] ?? '',
                $ids,
                (int) ($_POST['id'] ?? 0)
            );
            header('Location: recurrencias.php?guardada=1');
            exit;
        }
        if ($accion === 'generar') {
            $resultado = $padel->generarSesionesMes($_POST['recurrencia_id'] ?? 0, $_POST['mes'] ?? '');
            header('Location: recurrencias.php?generadas=' . $resultado['creadas'] . '&omitidas=' . $resultado['omitidas'] . '&conflictos=' . $resultado['conflictos']);
            exit;
        }
        if ($accion === 'desactivar') {
            $padel->desactivarRecurrencia($_POST['recurrencia_id'] ?? 0);
            header('Location: recurrencias.php?desactivada=1');
            exit;
        }
    } catch (Throwable $e) {
        $error = $e->getMessage();
        if (($_POST['accion'] ?? '') === 'guardar') {
            $editando = array_merge($editando, $_POST);
            $alumnosEditando = array_map('intval', $_POST['alumno_id'] ?? []);
        }
    }
}

$canchas = $padel->canchas();
$alumnos = $padel->alumnos();
$recurrencias = $padel->recurrencias();
include '../partials/template_start.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 app-page-header">
    <div><h2 class="mb-1">Clases recurrentes</h2><p class="text-muted">Configura una clase semanal y genera el calendario mensual en segundos.</p></div>
    <a href="index.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Resumen</a>
</div>
<?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
<?php if (isset($_GET['guardada'])): ?><div class="alert alert-success">La clase recurrente quedó guardada.</div><?php endif; ?>
<?php if (isset($_GET['desactivada'])): ?><div class="alert alert-success">La recurrencia se desactivó. Las sesiones ya generadas se conservan.</div><?php endif; ?>
<?php if (isset($_GET['generadas'])): ?><div class="alert alert-success">Calendario generado: <?= (int) $_GET['generadas'] ?> sesiones nuevas; <?= (int) ($_GET['omitidas'] ?? 0) ?> ya existían y no se duplicaron; <?= (int) ($_GET['conflictos'] ?? 0) ?> se omitieron por cruce de horario. Las cuotas se activan solo al confirmar asistencia.</div><?php endif; ?>

<div class="row g-3 mb-4">
    <section class="col-xl-5">
        <div class="card h-100"><div class="card-body">
            <h3 class="card-title"><?= $editando['id'] ? 'Editar clase semanal' : 'Configurar clase semanal' ?></h3>
            <p class="small text-muted">Los alumnos se preinscriben. El profesor confirma quién asistió y solo entonces se genera el cobro.</p>
            <form method="post" class="row g-3">
                <input type="hidden" name="accion" value="guardar"><input type="hidden" name="id" value="<?= (int) $editando['id'] ?>">
                <div class="col-12"><label class="form-label" for="nombre">Nombre de la clase</label><input class="form-control" id="nombre" name="nombre" maxlength="100" required value="<?= htmlspecialchars($editando['nombre']) ?>" placeholder="Ej. Grupo intermedio"></div>
                <div class="col-md-6"><label class="form-label" for="dia_semana">Día de la semana</label><select class="form-select" id="dia_semana" name="dia_semana" required><?php foreach ($dias as $numero => $dia): ?><option value="<?= $numero ?>" <?= (int) $editando['dia_semana'] === $numero ? 'selected' : '' ?>><?= $dia ?></option><?php endforeach; ?></select></div>
                <div class="col-md-6"><label class="form-label" for="cancha_id">Cancha</label><select class="form-select" id="cancha_id" name="cancha_id" required><option value="">Seleccionar...</option><?php while ($cancha = $canchas->fetch_assoc()): ?><option value="<?= (int) $cancha['id'] ?>" <?= (int) $editando['cancha_id'] === (int) $cancha['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cancha['complejo'] . ' - ' . $cancha['nombre']) ?></option><?php endwhile; ?></select></div>
                <div class="col-6"><label class="form-label" for="hora_inicio">Desde</label><input class="form-control" id="hora_inicio" name="hora_inicio" type="time" required value="<?= htmlspecialchars(substr($editando['hora_inicio'], 0, 5)) ?>"></div>
                <div class="col-6"><label class="form-label" for="hora_fin">Hasta</label><input class="form-control" id="hora_fin" name="hora_fin" type="time" required value="<?= htmlspecialchars(substr($editando['hora_fin'], 0, 5)) ?>"></div>
                <div class="col-12"><label class="form-label" for="importe_asistencia">Cuota por alumno y asistencia (Gs.)</label><input class="form-control" id="importe_asistencia" name="importe_asistencia" type="number" min="0" max="9999999999.99" step="any" required value="<?= htmlspecialchars((string) $editando['importe_asistencia']) ?>"><small class="form-text">Se cobrará únicamente cuando el profesor marque «Asistió».</small></div>
                <div class="col-12"><label class="form-label" for="alumno_id">Alumnos habituales</label><select class="form-select" id="alumno_id" name="alumno_id[]" multiple size="6" required><?php $alumnos->data_seek(0); while ($alumno = $alumnos->fetch_assoc()): ?><option value="<?= (int) $alumno['id'] ?>" <?= in_array((int) $alumno['id'], $alumnosEditando, true) ? 'selected' : '' ?>><?= htmlspecialchars($alumno['apellido'] . ', ' . $alumno['nombre']) ?></option><?php endwhile; ?></select><small class="form-text">Usa Ctrl/Cmd para seleccionar varios alumnos.</small></div>
                <div class="col-12"><button class="btn btn-primary" type="submit"><?= $editando['id'] ? 'Guardar cambios' : 'Crear clase recurrente' ?></button> <?php if ($editando['id']): ?><a class="btn btn-outline-secondary" href="recurrencias.php">Cancelar</a><?php endif; ?></div>
            </form>
        </div></div>
    </section>
    <section class="col-xl-7">
        <div class="card h-100"><div class="card-body">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3"><h3 class="card-title mb-0">Clases configuradas</h3><span class="badge text-bg-success">Generación sin duplicados</span></div>
            <div class="table-responsive"><table class="table table-hover align-middle">
                <thead><tr><th>Clase</th><th>Día y hora</th><th>Cancha</th><th>Alumnos / asistencia</th><th>Calendario</th><th></th></tr></thead><tbody>
                <?php if ($recurrencias->num_rows === 0): ?><tr><td colspan="6" class="text-muted">Crea tu primera clase recurrente con el formulario.</td></tr><?php endif; ?>
                <?php while ($clase = $recurrencias->fetch_assoc()): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($clase['nombre']) ?></strong><br><small class="text-muted"><?= number_format((float) $clase['importe_asistencia'], 0, ',', '.') ?> Gs. / clase</small></td>
                        <td><?= $dias[(int) $clase['dia_semana']] ?><br><small class="text-muted"><?= substr($clase['hora_inicio'], 0, 5) ?>–<?= substr($clase['hora_fin'], 0, 5) ?></small></td>
                        <td><?= htmlspecialchars($clase['complejo'] . ' · ' . $clase['cancha']) ?></td>
                        <td><?= (int) $clase['cantidad_alumnos'] ?> alumnos<br><span class="badge <?= $clase['activa'] ? 'text-bg-success' : 'text-bg-secondary' ?>"><?= $clase['activa'] ? 'Activa' : 'Pausada' ?></span></td>
                        <td><form method="post" class="d-flex flex-wrap gap-1"><input type="hidden" name="accion" value="generar"><input type="hidden" name="recurrencia_id" value="<?= (int) $clase['id'] ?>"><input class="form-control form-control-sm" type="month" name="mes" value="<?= htmlspecialchars(date('Y-m')) ?>" required aria-label="Mes a generar"><button class="btn btn-sm btn-success" <?= !$clase['activa'] ? 'disabled' : '' ?>><i class="bi bi-calendar-plus"></i> Generar</button></form></td>
                        <td class="text-nowrap"><a class="btn btn-sm btn-outline-primary" href="?editar=<?= (int) $clase['id'] ?>" title="Editar"><i class="bi bi-pencil"></i></a><?php if ($clase['activa']): ?> <form method="post" class="d-inline" onsubmit="return confirm('¿Pausar esta clase? Las sesiones ya generadas seguirán disponibles.');"><input type="hidden" name="accion" value="desactivar"><input type="hidden" name="recurrencia_id" value="<?= (int) $clase['id'] ?>"><button class="btn btn-sm btn-outline-danger" title="Pausar"><i class="bi bi-pause"></i></button></form><?php endif; ?></td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table></div>
        </div></div>
    </section>
</div>
<div class="alert alert-info"><i class="bi bi-info-circle me-2"></i>Generar un mes crea las sesiones planificadas y evita duplicados. No registra asistencias ni deudas hasta que el profesor confirme a cada alumno desde su panel.</div>
<?php include '../partials/template_end.php'; ?>
