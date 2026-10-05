<div class="app-page-header">
    <h2 class="mb-1"><?= htmlspecialchars($titulo_form) ?></h2>
    <p class="text-muted">Configura los datos de acceso y, si corresponde, vincula la ficha del alumno.</p>
</div>
<?php if (isset($_GET['error']) && (int) $_GET['error'] === 1): ?>
    <div class="alert alert-danger">No se pudieron guardar los datos del usuario.</div>
<?php elseif (isset($_GET['error']) && (int) $_GET['error'] === 2): ?>
    <div class="alert alert-danger">No se pudo guardar el usuario. Verifica que la ficha de alumno no esté vinculada a otra cuenta.</div>
<?php endif; ?>
<div class="app-panel">
    <form action="<?= htmlspecialchars($target) ?>" method="post" class="row g-3">
        <input type="hidden" name="id" value="<?= htmlspecialchars((string) $fila['id']) ?>">
        <div class="col-md-6"><label class="form-label" for="apellido">Apellido</label><input type="text" value="<?= htmlspecialchars((string) ($fila['apellido'] ?? '')) ?>" id="apellido" name="apellido" maxlength="100" required class="form-control"></div>
        <div class="col-md-6"><label class="form-label" for="nombre">Nombre</label><input type="text" value="<?= htmlspecialchars((string) ($fila['nombre'] ?? '')) ?>" id="nombre" name="nombre" maxlength="100" required class="form-control"></div>
        <div class="col-md-6"><label class="form-label" for="mail">Correo electrónico</label><input type="email" value="<?= htmlspecialchars((string) ($fila['mail'] ?? '')) ?>" id="mail" name="mail" maxlength="191" required class="form-control"></div>
        <div class="col-md-3"><label class="form-label" for="telefono">Teléfono</label><input type="text" value="<?= htmlspecialchars((string) ($fila['telefono'] ?? '')) ?>" id="telefono" name="telefono" maxlength="191" class="form-control"></div>
        <div class="col-md-3"><label class="form-label" for="doc">Documento</label><input type="text" value="<?= htmlspecialchars((string) ($fila['doc'] ?? '')) ?>" id="doc" name="doc" maxlength="191" class="form-control"></div>
        <div class="col-md-6"><label class="form-label" for="fenac">Fecha de nacimiento</label><input type="date" value="<?= htmlspecialchars((string) ($fila['fenac'] ?? '')) ?>" id="fenac" name="fenac" class="form-control"></div>
        <div class="col-md-6"><label class="form-label" for="direccion">Dirección</label><input type="text" value="<?= htmlspecialchars((string) ($fila['direccion'] ?? '')) ?>" id="direccion" name="direccion" maxlength="191" class="form-control"></div>
        <div class="col-md-6">
            <label class="form-label" for="contrasena">Contraseña <?= empty($fila['id']) ? '' : '(opcional al editar)' ?></label>
            <input type="password" id="contrasena" name="contrasena" maxlength="191" class="form-control" <?= empty($fila['id']) ? 'required' : '' ?> autocomplete="new-password">
            <?php if (empty($fila['id'])): ?><small class="form-text">Es obligatoria para crear la cuenta.</small><?php endif; ?>
        </div>
        <div class="col-md-3"><label class="form-label" for="esadmin">¿Es administrador?</label><select id="esadmin" name="esadmin" required class="form-select"><option value="0" <?= ((int) ($fila['esadmin'] ?? 0) === 0 && ($fila['rol'] ?? 'usuario') !== 'admin') ? 'selected' : '' ?>>No</option><option value="1" <?= ((int) ($fila['esadmin'] ?? 0) === 1 || ($fila['rol'] ?? '') === 'admin') ? 'selected' : '' ?>>Sí</option></select></div>
        <div class="col-md-3"><label class="form-label" for="rol">Rol</label><select id="rol" name="rol" required class="form-select"><option value="usuario" <?= ($fila['rol'] ?? 'usuario') === 'usuario' ? 'selected' : '' ?>>Alumno / usuario</option><option value="profesor" <?= ($fila['rol'] ?? '') === 'profesor' ? 'selected' : '' ?>>Profesor</option><option value="admin" <?= ($fila['rol'] ?? '') === 'admin' || (!isset($fila['rol']) && ($fila['esadmin'] ?? 0) == 1) ? 'selected' : '' ?>>Administradora</option></select></div>
        <div class="col-12" id="vinculo-alumno">
            <label class="form-label" for="alumno_id">Ficha del alumno vinculada</label>
            <select id="alumno_id" name="alumno_id" class="form-select">
                <option value="">Sin ficha vinculada</option>
                <?php while ($alumno = $alumnosDisponibles->fetch_assoc()): ?>
                    <?php if (empty($alumno['usuario_id']) || (int) ($fila['alumno_id'] ?? 0) === (int) $alumno['id']): ?>
                        <option value="<?= (int) $alumno['id'] ?>" <?= (int) ($fila['alumno_id'] ?? 0) === (int) $alumno['id'] ? 'selected' : '' ?>><?= htmlspecialchars($alumno['apellido'] . ', ' . $alumno['nombre']) ?></option>
                    <?php endif; ?>
                <?php endwhile; ?>
            </select>
            <small class="form-text">La cuenta vinculada puede consultar sus asistencias y deuda.</small>
        </div>
        <div class="col-12 d-flex flex-wrap gap-2">
            <a href="index.php" class="btn btn-outline-secondary">Volver al listado</a>
            <button type="submit" class="btn btn-primary">Guardar usuario</button>
        </div>
    </form>
</div>
<script>
    const esadmin = document.getElementById('esadmin');
    const rol = document.getElementById('rol');
    const vinculoAlumno = document.getElementById('vinculo-alumno');
    const alumnoId = document.getElementById('alumno_id');
    function actualizarVinculoAlumno() {
        vinculoAlumno.hidden = rol.value !== 'usuario';
        alumnoId.disabled = rol.value !== 'usuario';
    }
    esadmin.addEventListener('change', function () {
        if (this.value === '1') rol.value = 'admin';
        else if (rol.value === 'admin') rol.value = 'usuario';
        actualizarVinculoAlumno();
    });
    rol.addEventListener('change', function () {
        esadmin.value = this.value === 'admin' ? '1' : '0';
        actualizarVinculoAlumno();
    });
    actualizarVinculoAlumno();
</script>
