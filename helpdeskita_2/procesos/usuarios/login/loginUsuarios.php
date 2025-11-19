<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Log inicial
error_log("🎯 === LOGINUSUARIOS.PHP EJECUTÁNDOSE ===");

// Incluir archivos necesarios
$ruta_base = $_SERVER['DOCUMENT_ROOT'] . '/helpdeskita_2';
include_once $ruta_base . '/clases/funciones_encriptacion.php';
include_once $ruta_base . '/clases/Usuarios.php';

session_start();

// Depuración de datos POST recibidos
error_log("📥 DATOS POST RECIBIDOS:");
error_log("POST array: " . print_r($_POST, true));
error_log("Content-Type: " . ($_SERVER['CONTENT_TYPE'] ?? 'No definido'));

$usuario = $_POST['login'] ?? '';
$password = $_POST['password'] ?? '';

error_log("Usuario recibido: '" . $usuario . "'");
error_log("Password recibido: '" . $password . "'");
error_log("Longitud usuario: " . strlen($usuario));
error_log("Longitud password: " . strlen($password));

// Verificar caracter por caracter
error_log("Usuario (bytes): " . implode(' ', array_map('ord', str_split($usuario))));
error_log("Password (bytes): " . implode(' ', array_map('ord', str_split($password))));

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