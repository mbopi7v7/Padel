<?php

include_once __DIR__ . "/app.php";

function iniciarSesionSiHaceFalta() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function crearSesionUsuario($fila) {
    iniciarSesionSiHaceFalta();

    session_regenerate_id(true);

    $_SESSION["usuario_id"] = $fila["id"];
    $_SESSION["alumno_id"] = $fila["alumno_id"] ?? null;
    $_SESSION["usuario_nombre"] = $fila["nombre"];
    $_SESSION["usuario_mail"] = $fila["mail"];
    $_SESSION["usuario_rol"] = $fila["rol"] ?? ($fila["esadmin"] == 1 ? "admin" : "usuario");
    $_SESSION["usuario_admin"] = $_SESSION["usuario_rol"] === "admin";
}

function cerrarSesion() {
    iniciarSesionSiHaceFalta();

    $_SESSION = [];

    session_destroy();
}

function estaLogueado() {
    iniciarSesionSiHaceFalta();

    return isset($_SESSION["usuario_id"]);
}

function esAdmin() {
    iniciarSesionSiHaceFalta();

    return isset($_SESSION["usuario_rol"]) && $_SESSION["usuario_rol"] === "admin";
}

function rolUsuario() {
    iniciarSesionSiHaceFalta();

    return $_SESSION["usuario_rol"] ?? "usuario";
}

function esProfesor() {
    return rolUsuario() === "profesor";
}

function usuarioLogueadoId() {
    iniciarSesionSiHaceFalta();

    if (isset($_SESSION["usuario_id"])) {
        return $_SESSION["usuario_id"];
    }

    return null;
}

function usuarioLogueadoNombre() {
    iniciarSesionSiHaceFalta();

    if (isset($_SESSION["usuario_nombre"])) {
        return $_SESSION["usuario_nombre"];
    }

    return "";
}

function requiereLogin() {
    iniciarSesionSiHaceFalta();

    if (!isset($_SESSION["usuario_id"])) {
        header("Location: " . BASE_URL . "/login.php?error=2");
        exit();
    }
}

function requiereAdmin() {
    iniciarSesionSiHaceFalta();

    if (!isset($_SESSION["usuario_id"])) {
        header("Location: " . BASE_URL . "/login.php?error=2");
        exit();
    }

    if ($_SESSION["usuario_admin"] != 1) {
        header("Location: " . BASE_URL . "/login.php?error=3");
        exit();
    }
}

function responderErrorAPI($mensaje) {
    header('Content-Type: application/json');

    echo json_encode([
        "status" => "error",
        "msg" => $mensaje,
        "data" => null
    ]);

    exit();
}

function requiereLoginAPI() {
    iniciarSesionSiHaceFalta();

    if (!isset($_SESSION["usuario_id"])) {
        responderErrorAPI("No autenticado.");
    }
}

function requiereAdminAPI() {
    iniciarSesionSiHaceFalta();

    if (!isset($_SESSION["usuario_id"])) {
        responderErrorAPI("No autenticado.");
    }

    if ($_SESSION["usuario_admin"] != 1) {
        responderErrorAPI("Acceso no autorizado.");
    }
}
?>