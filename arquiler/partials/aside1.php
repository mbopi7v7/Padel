<aside class="app-sidebar col-12 col-lg-3 col-xl-2">
    <?php
    $rutaActual = rtrim((string) (parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: ''), '/');
    $basePath = rtrim(BASE_URL, '/');
    if ($basePath !== '' && strpos($rutaActual, $basePath) === 0) $rutaActual = substr($rutaActual, strlen($basePath));
    $esRutaActiva = static function ($ruta) use ($rutaActual) {
        $ruta = rtrim($ruta, '/');
        return $ruta === '/usuarios/index.php'
            ? strpos($rutaActual, '/usuarios/') === 0
            : ($rutaActual === $ruta || ($ruta === '/padel' && $rutaActual === '/padel/index.php'));
    };
    ?>
    <div class="sidebar-caption">MENÚ PRINCIPAL</div>
    <nav class="app-nav">
        <?php if (esAdmin()): ?>
            <a href="<?= BASE_URL ?>/padel/" class="app-nav-link<?= $esRutaActiva('/padel/') ? ' active' : '' ?>"><i class="bi bi-grid-1x2"></i><span>Resumen</span></a>
            <a href="<?= BASE_URL ?>/padel/alquileres.php" class="app-nav-link<?= $esRutaActiva('/padel/alquileres.php') ? ' active' : '' ?>"><i class="bi bi-calendar2-week"></i><span>Sesiones y pagos</span></a>
            <a href="<?= BASE_URL ?>/padel/recurrencias.php" class="app-nav-link<?= $esRutaActiva('/padel/recurrencias.php') ? ' active' : '' ?>"><i class="bi bi-arrow-repeat"></i><span>Clases recurrentes</span></a>
            <a href="<?= BASE_URL ?>/padel/alumnos.php" class="app-nav-link<?= $esRutaActiva('/padel/alumnos.php') ? ' active' : '' ?>"><i class="bi bi-people"></i><span>Alumnos</span></a>
            <a href="<?= BASE_URL ?>/padel/canchas.php" class="app-nav-link<?= $esRutaActiva('/padel/canchas.php') ? ' active' : '' ?>"><i class="bi bi-bounding-box"></i><span>Complejos y canchas</span></a>
            <a href="<?= BASE_URL ?>/padel/perfil.php" class="app-nav-link<?= $esRutaActiva('/padel/perfil.php') ? ' active' : '' ?>"><i class="bi bi-person-badge"></i><span>Perfil público</span></a>
            <a href="<?= BASE_URL ?>/usuarios/index.php" class="app-nav-link<?= $esRutaActiva('/usuarios/index.php') ? ' active' : '' ?>"><i class="bi bi-person-gear"></i><span>Usuarios</span></a>
        <?php else: ?>
            <a href="<?= BASE_URL ?>/padel/mi-cuenta.php" class="app-nav-link active"><i class="bi bi-calendar-check"></i><span><?= esProfesor() ? 'Asistencia del grupo' : 'Mis asistencias y deuda' ?></span></a>
        <?php endif; ?>
    </nav>
    <div class="sidebar-tip"><i class="bi bi-lightbulb"></i><strong>Todo en juego</strong><span><?= esAdmin() ? 'Organizá sesiones, pagos y alumnos desde un solo lugar.' : (esProfesor() ? 'Confirma las asistencias después de cada clase.' : 'Consulta tus clases y pagos cuando quieras.') ?></span></div>
</aside>