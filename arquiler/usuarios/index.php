<?php
include "../lib/seguridad.php";
requiereAdmin();
include "../lib/conex.php"; // incluimos clase de conexion
include "../lib/Padel.php";
$db=new Conex();
$con=$db->conectar();
$padel = new Padel($con);
$rs=$con->query("SELECT u.*, CONCAT(al.apellido, ', ', al.nombre) AS alumno_vinculado FROM usuarios u LEFT JOIN alumnos al ON al.id = u.alumno_id ORDER BY u.apellido, u.nombre");
?>
<?php include_once '../partials/template_start.php'; ?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 app-page-header">
    <div><h2 class="mb-1">Usuarios y accesos</h2><p class="text-muted">Gestiona profesores, alumnos y permisos del sistema.</p></div>
    <div class="d-flex gap-2"><a href="api.php" class="btn btn-outline-secondary" target="_blank" rel="noopener">Ver JSON</a><a href="nuevo.php" class="btn btn-primary"><i class="bi bi-person-plus"></i> Nuevo usuario</a></div>
</div>
<?php if (isset($_GET['ok'])): ?>
    <div class="alert alert-success"><?= [1 => 'Usuario creado correctamente.', 2 => 'Usuario actualizado correctamente.', 3 => 'Usuario eliminado correctamente'][(int) $_GET['ok']] ?? 'Cambios guardados.' ?></div>
<?php endif; ?>
<?php if (isset($_GET['error']) && (int) $_GET['error'] === 3): ?><div class="alert alert-danger">No se pudo eliminar el usuario.</div><?php endif; ?>
<div class="table-responsive"><table class="table table-striped align-middle">
    <thead><tr><th>Usuario</th><th>Correo</th><th>Teléfono</th><th>Ficha de alumno</th><th>Perfil</th><th>Acciones</th></tr></thead>
    <tbody>
    <?php if ($rs->num_rows === 0): ?><tr><td colspan="6" class="text-muted">No hay usuarios registrados.</td></tr><?php endif; ?>
    <?php while ($fila = $rs->fetch_assoc()): $roles = ['admin' => 'Administradora', 'profesor' => 'Profesor', 'usuario' => 'Alumno / usuario']; $rol = $fila['rol'] ?? ($fila['esadmin'] ? 'admin' : 'usuario'); ?>
        <tr>
            <td><strong><?= htmlspecialchars($fila['apellido'] . ', ' . $fila['nombre']) ?></strong><br><small class="text-muted"><?= htmlspecialchars($fila['doc'] ?? '') ?></small></td>
            <td><?= htmlspecialchars($fila['mail']) ?></td><td><?= htmlspecialchars($fila['telefono']) ?></td>
            <td><?= htmlspecialchars($fila['alumno_vinculado'] ?? '—') ?></td>
            <td><span class="badge text-bg-secondary"><?= htmlspecialchars($roles[$rol] ?? 'Usuario') ?></span></td>
            <td class="text-nowrap"><a href="editar.php?id=<?= (int) $fila['id'] ?>" class="btn btn-sm btn-outline-primary">Editar</a> <a href="borrar.php?id=<?= (int) $fila['id'] ?>" class="btn btn-sm btn-outline-danger">Borrar</a></td>
        </tr>
    <?php endwhile; ?>
    </tbody>
</table></div>

<?php include_once '../partials/template_end.php'; ?>  