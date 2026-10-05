<?php
include_once 'lib/seguridad.php';
if (estaLogueado()) {
    header('Location: padel/');
    exit;
}

include 'lib/conex.php';
include 'lib/PerfilProfesor.php';
$perfilProfesor = new PerfilProfesor((new Conex())->conectar());
$perfil = $perfilProfesor->obtener();
$mensajeError = '';
if (isset($_GET['error'])) {
    $mensajes = [
        '1' => 'El correo o la contraseña no son correctos.',
        '2' => 'Inicia sesión para acceder a tu cuenta.',
        '3' => 'Tu cuenta no tiene permiso para esa sección.'
    ];
    $mensajeError = $mensajes[(string) $_GET['error']] ?? '';
}
$imagenProfesor = $perfil['imagen']
    ? BASE_URL . '/uploads/profesores/' . rawurlencode($perfil['imagen'])
    : '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#101d2c">
    <title><?= htmlspecialchars($perfil['nombre'] ?: 'Club de pádel') ?> | Pádel</title>
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/bootstrap-icons/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/padel-site.css">
</head>
<body class="padel-site">
    <header class="site-header">
        <a class="site-brand" href="<?= BASE_URL ?>/login.php" aria-label="Inicio">
            <span class="brand-mark"><i class="bi bi-lightning-charge-fill"></i></span>
            <span>PADEL<span class="brand-accent">CLUB</span></span>
        </a>
        <span class="header-note"><i class="bi bi-geo-alt"></i> Entrenamiento · Comunidad · Pádel</span>
    </header>

    <main>
        <section class="hero-section">
            <div class="hero-copy">
                <span class="eyebrow"><span></span> TU PRÓXIMO NIVEL EMPIEZA AQUÍ</span>
                <h1>Jugá mejor.<br><span>Disfrutá cada punto.</span></h1>
                <p class="hero-description">
                    Entrenamiento de pádel para todos los niveles, con seguimiento de tus clases y un espacio para crecer dentro y fuera de la cancha.
                </p>
                <div class="hero-actions">
                    <a class="btn btn-lime" href="#acceso">Entrar a mi cuenta <i class="bi bi-arrow-right"></i></a>
                    <a class="text-link" href="#entrenador">Conocé al entrenador <i class="bi bi-arrow-down-right"></i></a>
                </div>
                <div class="hero-stats">
                    <div><strong>01</strong><span>Entrenamiento<br>personalizado</span></div>
                    <div><strong>02</strong><span>Seguimiento de<br>tus sesiones</span></div>
                    <div><strong>03</strong><span>Una comunidad<br>que juega unida</span></div>
                </div>
            </div>
            <div class="hero-visual" aria-label="Cancha de pádel">
                <div class="court-glow"></div>
                <div class="court-lines"><span></span><span></span><span></span></div>
                <div class="ball"><i class="bi bi-circle-fill"></i></div>
                <div class="visual-label"><span>PADEL</span><strong>GAME ON</strong></div>
                <div class="visual-caption"><i class="bi bi-geo-alt-fill"></i> Cada punto cuenta</div>
            </div>
        </section>

        <section class="content-grid" id="entrenador">
            <article class="coach-card">
                <div class="section-kicker">EL ENTRENADOR</div>
                <div class="coach-content">
                    <div class="coach-photo">
                        <?php if ($imagenProfesor): ?>
                            <img src="<?= htmlspecialchars($imagenProfesor) ?>" alt="Foto de <?= htmlspecialchars($perfil['nombre'] ?: 'el profesor') ?>">
                        <?php else: ?>
                            <div class="coach-placeholder"><i class="bi bi-person-fill"></i><span>Tu próxima<br>clase empieza<br>acá.</span></div>
                        <?php endif; ?>
                        <span class="photo-tag"><i class="bi bi-patch-check-fill"></i> COACH</span>
                    </div>
                    <div class="coach-info">
                        <span class="coach-label">CONOCÉ A TU PROFE</span>
                        <h2><?= htmlspecialchars($perfil['nombre'] ?: 'Tu entrenador') ?></h2>
                        <p class="coach-title"><?= htmlspecialchars($perfil['titulo']) ?></p>
                        <?php if ($perfil['descripcion']): ?>
                            <p class="coach-bio"><?= nl2br(htmlspecialchars($perfil['descripcion'])) ?></p>
                        <?php else: ?>
                            <p class="coach-bio">Técnica, estrategia y buena energía para que cada entrenamiento te acerque a tu mejor versión.</p>
                        <?php endif; ?>
                        <div class="coach-contact">
                            <?php if ($perfil['telefono']): ?><a href="tel:<?= htmlspecialchars(preg_replace('/[^0-9+]/', '', $perfil['telefono'])) ?>"><i class="bi bi-whatsapp"></i><?= htmlspecialchars($perfil['telefono']) ?></a><?php endif; ?>
                            <?php if ($perfil['email']): ?><a href="mailto:<?= htmlspecialchars($perfil['email']) ?>"><i class="bi bi-envelope"></i><?= htmlspecialchars($perfil['email']) ?></a><?php endif; ?>
                            <?php if ($perfil['instagram']): ?><a href="https://instagram.com/<?= rawurlencode(ltrim($perfil['instagram'], '@')) ?>" target="_blank" rel="noopener noreferrer"><i class="bi bi-instagram"></i><?= htmlspecialchars('@' . ltrim($perfil['instagram'], '@')) ?></a><?php endif; ?>
                        </div>
                    </div>
                </div>
            </article>

            <aside class="login-card" id="acceso">
                <div class="login-icon"><i class="bi bi-person-lock"></i></div>
                <span class="section-kicker">ÁREA PERSONAL</span>
                <h2>Qué bueno verte.</h2>
                <p class="login-intro">Ingresá para consultar tus clases, asistencias y pagos.</p>
                <?php if ($mensajeError): ?><div class="alert alert-danger py-2"><?= htmlspecialchars($mensajeError) ?></div><?php endif; ?>
                <?php if (isset($_GET['logout'])): ?><div class="alert alert-success py-2">Sesión cerrada correctamente.</div><?php endif; ?>
                <form action="procesar_login.php" method="post">
                    <div class="mb-3">
                        <label class="form-label" for="mail">Correo electrónico</label>
                        <div class="input-with-icon"><i class="bi bi-envelope"></i><input class="form-control" type="email" name="mail" id="mail" autocomplete="username" placeholder="nombre@correo.com" required></div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label" for="contrasena">Contraseña</label>
                        <div class="input-with-icon"><i class="bi bi-lock"></i><input class="form-control" type="password" name="contrasena" id="contrasena" autocomplete="current-password" placeholder="Tu contraseña" required></div>
                    </div>
                    <button class="btn btn-login w-100" type="submit">Iniciar sesión <i class="bi bi-arrow-right"></i></button>
                </form>
                <div class="login-foot"><i class="bi bi-shield-lock"></i> Acceso privado y seguro</div>
            </aside>
        </section>

        <section class="benefits-row">
            <div><i class="bi bi-calendar2-check"></i><span><strong>Tu actividad</strong><small>Revisá tus asistencias</small></span></div>
            <div><i class="bi bi-graph-up-arrow"></i><span><strong>Tu progreso</strong><small>Seguí cada entrenamiento</small></span></div>
            <div><i class="bi bi-heart-pulse"></i><span><strong>Tu comunidad</strong><small>Compartí la pasión por el pádel</small></span></div>
        </section>
    </main>
    <footer class="site-footer"><span>PADELCLUB</span><span>Nos vemos en la cancha.</span></footer>
</body>
</html>
