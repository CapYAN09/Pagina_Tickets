<?php
// PRUEBA SIMPLE TEMPORAL - ELIMINAR DESPUÉS
error_reporting(E_ALL);
ini_set('display_errors', 1);

error_log("=== PRUEBA SIMPLE - Script alcanzado ===");
error_log("REMOTE_ADDR: " . $_SERVER['REMOTE_ADDR']);
error_log("REQUEST_METHOD: " . $_SERVER['REQUEST_METHOD']);
error_log("POST data: " . print_r($_POST, true));

session_start();

// Simular login exitoso
$_SESSION['usuario'] = [
    'nombre' => 'usuario_prueba',
    'id' => 1,
    'rol' => 1,
    'ubicacion' => 'test'
];

error_log("Sesión creada: " . print_r($_SESSION, true));

// Forzar respuesta exitosa
echo "1";
?>