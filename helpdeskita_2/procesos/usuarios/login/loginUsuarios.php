<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Log para verificar que se está ejecutando
error_log("🎯 loginUsuarios.php EJECUTÁNDOSE");

// Usar rutas absolutas para evitar problemas
$ruta_base = $_SERVER['DOCUMENT_ROOT'] . '/helpdeskita_2';

// Incluir los archivos necesarios
include_once $ruta_base . '../../../clases/funciones_encriptacion.php';
include_once $ruta_base . '../../../clases/Usuarios.php';

session_start();

// Verificar que se recibieron los datos POST
if(empty($_POST['login']) || empty($_POST['password'])) {
    error_log("❌ ERROR: No se recibieron datos POST");
    echo "0";
    exit;
}

$usuario = trim($_POST['login']);
$password = trim($_POST['password']);

error_log("📥 Datos recibidos - Usuario: '$usuario', Password: '$password'");

try {
    $Usuarios = new Usuarios();
    $resultado = $Usuarios->loginUsuario($usuario, $password);
    
    error_log("🎯 Resultado del login: " . $resultado);
    echo $resultado;
    
} catch(Exception $e) {
    error_log("💥 EXCEPCIÓN: " . $e->getMessage());
    echo "0";
}
?>