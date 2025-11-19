<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Log inicial
error_log("🎯 === LOGINUSUARIOS.PHP EJECUTÁNDOSE ===");

// Incluir archivos necesarios CON include_once
$ruta_base = $_SERVER['DOCUMENT_ROOT'] . '/helpdeskita_2';

// Usar include_once para prevenir redeclaraciones
include_once $ruta_base . '/clases/funciones_encriptacion.php';
include_once $ruta_base . '/clases/Usuarios.php';

session_start();

// Depuración de datos POST recibidos
error_log("📥 DATOS POST RECIBIDOS:");
$usuario = $_POST['login'] ?? '';
$password = $_POST['password'] ?? '';

error_log("Usuario: '" . $usuario . "'");
error_log("Password: '" . $password . "'");

if(empty($usuario) || empty($password)) {
    error_log("❌ ERROR: Campos vacíos");
    echo "0";
    exit;
}

try {
    $Usuarios = new Usuarios();
    error_log("🔧 Llamando a loginUsuario...");
    
    $resultado = $Usuarios->loginUsuario($usuario, $password);
    
    error_log("🎯 RESULTADO FINAL: " . $resultado);
    echo $resultado;
    
} catch(Exception $e) {
    error_log("💥 EXCEPCIÓN: " . $e->getMessage());
    echo "0";
}
?>