<header class="app-topbar">
    <a class="app-brand" href="<?= BASE_URL ?>/padel/"><span class="app-brand-mark"><i class="bi bi-lightning-charge-fill"></i></span><span>PADEL<span>CLUB</span></span></a>
    <div class="app-topbar-actions">
        <span class="app-user"><i class="bi bi-person-circle"></i><span><?= htmlspecialchars(usuarioLogueadoNombre()) ?></span><small><?= esAdmin() ? 'Administración' : (esProfesor() ? 'Profesor' : 'Alumno') ?></small></span>
        <a class="app-logout" href="<?= BASE_URL ?>/logout.php" title="Cerrar sesión" aria-label="Cerrar sesión"><i class="bi bi-box-arrow-right"></i><span>Salir</span></a>
    </div>
</header>