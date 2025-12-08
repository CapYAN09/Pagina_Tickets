<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Incluir configuración común de sesión
include_once $_SERVER['DOCUMENT_ROOT'] . '/helpdeskita_2/config/session_config.php';

// Iniciar sesión con configuración consistente
iniciarSesionSegura();

error_log("=== LOGIN USUARIOS ===");
error_log("Session ID: " . session_id());

// Incluir otros archivos
$ruta_base = $_SERVER['DOCUMENT_ROOT'] . '/helpdeskita_2';
include_once $ruta_base . '/clases/funciones_encriptacion.php';
include_once $ruta_base . '/clases/Usuarios.php';

$usuario = $_POST['login'] ?? '';
$password = $_POST['password'] ?? '';

error_log("Login intento: " . $usuario);

if(empty($usuario) || empty($password)) {
    echo "0";
    exit;
}

try {
    $Usuarios = new Usuarios();
    $resultado = $Usuarios->loginUsuario($usuario, $password);
    
    error_log("Resultado login: " . $resultado);
    
    if($resultado == 1) {
        error_log("✅ Login exitoso - Sesión: " . print_r($_SESSION['usuario'], true));
        // Forzar guardado inmediato
        session_write_close();
    }
    
    echo $resultado;
    
} catch(Exception $e) {
    error_log("Error: " . $e->getMessage());
    echo "0";
}
?>