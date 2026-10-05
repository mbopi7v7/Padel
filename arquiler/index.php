<?php
include_once 'lib/seguridad.php';
if (estaLogueado()) {
    header('Location: padel/');
} else {
    header('Location: login.php');
}
exit;
