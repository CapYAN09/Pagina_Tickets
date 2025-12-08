<?php
include_once 'config/session_config.php';
iniciarSesionSegura();

header('Content-Type: application/json');

if(isset($_SESSION['usuario'])) {
    echo json_encode([
        'status' => 'success',
        'usuario' => $_SESSION['usuario']['nombre'],
        'session_id' => session_id()
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'No hay sesión activa'
    ]);
}
?>