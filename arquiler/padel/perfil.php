<?php
include '../lib/seguridad.php';
requiereAdmin();
include '../lib/conex.php';
include '../lib/PerfilProfesor.php';

$db = (new Conex())->conectar();
$perfilProfesor = new PerfilProfesor($db);
$perfil = $perfilProfesor->obtener();
$error = '';
$exito = isset($_GET['guardado']);
if (empty($_SESSION['csrf_perfil_profesor'])) {
    $_SESSION['csrf_perfil_profesor'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $imagenNueva = null;
    try {
        if (!hash_equals($_SESSION['csrf_perfil_profesor'], $_POST['csrf_token'] ?? '')) {
            throw new RuntimeException('La sesión del formulario venció. Actualiza la página e inténtalo nuevamente.');
        }

        $datos = [
            'nombre' => trim($_POST['nombre'] ?? ''),
            'titulo' => trim($_POST['titulo'] ?? ''),
            'descripcion' => trim($_POST['descripcion'] ?? ''),
            'telefono' => trim($_POST['telefono'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'instagram' => trim($_POST['instagram'] ?? '')
        ];
        if (strlen($datos['nombre']) > 120 || strlen($datos['titulo']) > 160 || strlen($datos['telefono']) > 40 || strlen($datos['email']) > 191 || strlen($datos['instagram']) > 100 || strlen($datos['descripcion']) > 5000) {
            throw new InvalidArgumentException('Revisa la longitud de los campos del perfil.');
        }
        if ($datos['email'] !== '' && !filter_var($datos['email'], FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Ingresa un correo electrónico válido para el perfil.');
        }

        if (!empty($_FILES['imagen']['name'])) {
            if ($_FILES['imagen']['error'] !== UPLOAD_ERR_OK) {
                $mensajeSubida = $_FILES['imagen']['error'] === UPLOAD_ERR_INI_SIZE || $_FILES['imagen']['error'] === UPLOAD_ERR_FORM_SIZE
                    ? 'La imagen supera el límite de carga permitido por el servidor (2 MB).'
                    : 'No se pudo recibir la imagen. Intenta seleccionarla nuevamente.';
                throw new RuntimeException($mensajeSubida);
            }
            if ($_FILES['imagen']['size'] > 2 * 1024 * 1024) {
                throw new InvalidArgumentException('La imagen debe pesar 2 MB o menos.');
            }

            $mime = (new finfo(FILEINFO_MIME_TYPE))->file($_FILES['imagen']['tmp_name']);
            $extensiones = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
            $infoImagen = @getimagesize($_FILES['imagen']['tmp_name']);
            if (!isset($extensiones[$mime]) || !$infoImagen || $infoImagen['mime'] !== $mime) {
                throw new InvalidArgumentException('Usa una imagen JPG, PNG o WebP válida.');
            }

            $nombreArchivo = bin2hex(random_bytes(16)) . '.' . $extensiones[$mime];
            $directorioImagenes = __DIR__ . '/../uploads/profesores';
            if (!is_dir($directorioImagenes) || !is_writable($directorioImagenes)) {
                throw new RuntimeException('La carpeta de imágenes de profesores no existe o no tiene permisos de escritura.');
            }
            $rutaImagen = $directorioImagenes . '/' . $nombreArchivo;
            if (!move_uploaded_file($_FILES['imagen']['tmp_name'], $rutaImagen)) {
                throw new RuntimeException('No se pudo guardar la imagen subida.');
            }
            $imagenNueva = $nombreArchivo;
        }

        $imagen = $imagenNueva ?: $perfil['imagen'];
        if (isset($_POST['quitar_imagen']) && $_POST['quitar_imagen'] === '1') {
            if ($imagenNueva) {
                unlink(__DIR__ . '/../uploads/profesores/' . $imagenNueva);
                $imagenNueva = null;
                throw new InvalidArgumentException('Elige una imagen nueva o desmarca la opción para quitar la foto.');
            }
            $imagen = null;
        }

        $perfilProfesor->guardar($datos, $imagen);
        if ($imagenNueva && $perfil['imagen'] && basename($perfil['imagen']) === $perfil['imagen']) {
            $imagenAnterior = __DIR__ . '/../uploads/profesores/' . $perfil['imagen'];
            if (is_file($imagenAnterior)) unlink($imagenAnterior);
        }
        unset($_SESSION['csrf_perfil_profesor']);
        header('Location: perfil.php?guardado=1');
        exit;
    } catch (Throwable $e) {
        if ($imagenNueva && is_file(__DIR__ . '/../uploads/profesores/' . $imagenNueva)) {
            unlink(__DIR__ . '/../uploads/profesores/' . $imagenNueva);
        }
        $error = $e->getMessage();
        $perfil = array_merge($perfil, $datos ?? []);
    }
}

$imagenUrl = $perfil['imagen']
    ? BASE_URL . '/uploads/profesores/' . rawurlencode($perfil['imagen'])
    : '';
include '../partials/template_start.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 app-page-header">
    <div><h2 class="mb-1">Perfil del entrenador</h2><p class="text-muted mb-0">Personaliza la presentación pública que verán quienes visiten el inicio de sesión.</p></div>
    <a class="btn btn-outline-secondary" href="<?= BASE_URL ?>/login.php" target="_blank" rel="noopener">Ver página pública</a>
</div>
<?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
<?php if ($exito): ?><div class="alert alert-success">El perfil público se actualizó correctamente.</div><?php endif; ?>
<form method="post" enctype="multipart/form-data" class="app-panel profile-form">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_perfil_profesor']) ?>">
    <div class="row g-3">
        <div class="col-md-7">
            <label class="form-label" for="nombre">Nombre del entrenador</label>
            <input class="form-control" id="nombre" name="nombre" maxlength="120" value="<?= htmlspecialchars($perfil['nombre']) ?>" placeholder="Tu nombre">
        </div>
        <div class="col-md-5">
            <label class="form-label" for="titulo">Título o especialidad</label>
            <input class="form-control" id="titulo" name="titulo" maxlength="160" value="<?= htmlspecialchars($perfil['titulo']) ?>" placeholder="Ej. Profesor de pádel">
        </div>
        <div class="col-12">
            <label class="form-label" for="descripcion">Presentación</label>
            <textarea class="form-control" id="descripcion" name="descripcion" rows="4" maxlength="5000" placeholder="Cuenta sobre tu experiencia, metodología y clases."><?= htmlspecialchars($perfil['descripcion']) ?></textarea>
        </div>
        <div class="col-md-4">
            <label class="form-label" for="telefono">Teléfono / WhatsApp</label>
            <input class="form-control" id="telefono" name="telefono" maxlength="40" value="<?= htmlspecialchars($perfil['telefono']) ?>" placeholder="+595...">
        </div>
        <div class="col-md-4">
            <label class="form-label" for="email">Correo de contacto</label>
            <input class="form-control" id="email" name="email" type="email" maxlength="191" value="<?= htmlspecialchars($perfil['email']) ?>" placeholder="profe@correo.com">
        </div>
        <div class="col-md-4">
            <label class="form-label" for="instagram">Instagram</label>
            <input class="form-control" id="instagram" name="instagram" maxlength="100" value="<?= htmlspecialchars($perfil['instagram']) ?>" placeholder="@tuusuario">
        </div>
        <div class="col-12">
            <label class="form-label" for="imagen">Foto del entrenador</label>
            <input class="form-control" id="imagen" name="imagen" type="file" accept="image/jpeg,image/png,image/webp">
            <div class="form-text">JPG, PNG o WebP; máximo 2 MB. Recomendado: foto vertical o cuadrada.</div>
        </div>
        <?php if ($imagenUrl): ?>
            <div class="col-12 d-flex align-items-center gap-3">
                <img src="<?= htmlspecialchars($imagenUrl) ?>" alt="Foto actual del entrenador" style="width: 88px; height: 88px; object-fit: cover; border-radius: 12px">
                <label class="form-check-label"><input class="form-check-input me-2" type="checkbox" name="quitar_imagen" value="1">Quitar la foto actual</label>
            </div>
        <?php endif; ?>
        <div class="col-12">
            <button class="btn btn-primary" type="submit">Guardar perfil público</button>
            <a class="btn btn-outline-secondary" href="<?= BASE_URL ?>/padel/">Volver al panel</a>
        </div>
    </div>
</form>
<?php include '../partials/template_end.php'; ?>
